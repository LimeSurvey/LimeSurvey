<?php

namespace LimeSurvey\Models\Services;

use SimpleXMLElement;

/**
 * Merges the editor (React) UI overlays from application/views/survey/questions/react/*.xml
 * into the question attributes read from the question theme config.xml files.
 */
class ReactQuestionAttributeConfig
{
    private const SIMPLE_FILE = 'simple.xml';

    /** Elements whose children are returned as a list */
    private const LIST_ELEMENTS = ['options', 'requires', 'returnValues', 'values', 'default', 'attributes', 'themes'];

    private const BOOLEAN_KEYS = ['languageBased', 'hidden', 'devOnly', 'disableWhenActive', 'action', 'virtual', 'always'];

    private string $directory;

    /** @var array<string, array<string, mixed>>|null */
    private ?array $overlays = null;

    /** @var string[] */
    private array $simpleDefault = [];

    /** @var array<string, string[]> */
    private array $simpleThemes = [];

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory
            ?? \Yii::app()->getConfig('rootdir') . '/application/views/survey/questions/react';
    }

    /**
     * @param array<string, array<string, mixed>> $attributes Attributes keyed by name
     * @param string $themeName
     * @return array<string, array<string, mixed>>
     */
    public function apply(array $attributes, string $themeName): array
    {
        $this->load();

        $result = [];
        foreach ($attributes as $name => $attribute) {
            $result[$name] = $this->mergeAttribute((string) $name, $attribute, $this->overlays[$name] ?? null);
        }

        foreach ($this->overlays as $name => $overlay) {
            if (isset($result[$name]) || empty($overlay['virtual'])) {
                continue;
            }
            $requires = $overlay['requires'] ?? [];
            $required = empty($overlay['always']) ? !empty($requires) : true;
            foreach ($requires as $requiredName) {
                $required = $required && isset($attributes[$requiredName]);
            }
            if ($required) {
                $result[$name] = $this->mergeAttribute($name, [], $overlay);
            }
        }

        $simpleList = $this->simpleThemes[$themeName] ?? $this->simpleDefault;
        foreach ($simpleList as $index => $name) {
            if (isset($result[$name])) {
                $result[$name]['simpleOrder'] = $index;
            }
        }

        return $result;
    }

    /**
     * @param string $name
     * @param array<string, mixed> $attribute Attribute read from config.xml
     * @param array<string, mixed>|null $overlay
     * @return array<string, mixed>
     */
    private function mergeAttribute(string $name, array $attribute, ?array $overlay): array
    {
        $attribute = $this->normalizeConfigAttribute($attribute);
        $isGeneral = !empty($attribute['general']);
        $category = $attribute['category'] ?? '';
        $merged = array_merge($attribute, $overlay ?? []);

        $merged['name'] = $name;
        $merged['tab'] = $category !== '' ? $category : ($overlay['tab'] ?? 'General');
        $merged['attributePath'] = !empty($merged['attributePath'])
            ? $merged['attributePath']
            : ($isGeneral ? $name : 'attributes.' . $name);
        if (!isset($overlay['languageBased'])) {
            $merged['languageBased'] = !empty($attribute['i18n']);
        }
        if (isset($merged['sortorder']) && $merged['sortorder'] !== '') {
            $merged['sortorder'] = (int) $merged['sortorder'];
        }
        // React escapes on render, so translations must not be HTML-escaped here.
        if (!empty($merged['caption'])) {
            $merged['caption'] = gT($merged['caption'], 'unescaped');
        }
        if (!empty($merged['help'])) {
            $merged['help'] = gT($merged['help'], 'unescaped');
        }
        if (!empty($merged['options'])) {
            $merged['options'] = array_map(function ($option) {
                $option['text'] = gT((string) ($option['text'] ?? ''), 'unescaped');
                return $option;
            }, $merged['options']);
        }

        return $merged;
    }

    /**
     * Brings config.xml attributes (json-decoded SimpleXML or questionHelper format) to the overlay format.
     *
     * @param array<string, mixed> $attribute
     * @return array<string, mixed>
     */
    private function normalizeConfigAttribute(array $attribute): array
    {
        foreach ($attribute as $key => $value) {
            if ($key === 'options') {
                $attribute['options'] = $this->normalizeOptions($value);
            } elseif ($value === []) {
                $attribute[$key] = '';
            }
        }
        return $attribute;
    }

    /**
     * @param mixed $options
     * @return array<int, array{value: string, text: string}>
     */
    private function normalizeOptions($options): array
    {
        if (!is_array($options) || empty($options)) {
            return [];
        }
        if (isset($options['option'])) {
            $options = isset($options['option']['value']) || isset($options['option']['text'])
                ? [$options['option']]
                : $options['option'];
        } elseif (!array_key_exists(0, $options) || !is_array($options[0])) {
            // questionHelper format: value => text
            $list = [];
            foreach ($options as $value => $text) {
                $list[] = ['value' => (string) $value, 'text' => (string) $text];
            }
            return $list;
        }

        return array_map(function ($option) {
            return [
                'value' => is_array($option['value'] ?? '') ? '' : (string) ($option['value'] ?? ''),
                'text' => is_array($option['text'] ?? '') ? '' : (string) ($option['text'] ?? ''),
            ];
        }, array_values($options));
    }

    private function load(): void
    {
        if ($this->overlays !== null) {
            return;
        }
        $this->overlays = [];

        $files = glob($this->directory . '/*.xml') ?: [];
        foreach ($files as $file) {
            $xml = simplexml_load_file($file);
            if ($xml === false) {
                continue;
            }

            if (basename($file) === self::SIMPLE_FILE) {
                $this->loadSimple($xml);
                continue;
            }

            $tab = trim((string) $xml->tab);
            $attributes = isset($xml->attributes) ? $this->toArray($xml->attributes, 'attributes') : [];
            foreach ((array) $attributes as $attribute) {
                if (!is_array($attribute) || empty($attribute['name'])) {
                    continue;
                }
                $attribute['tab'] = $tab;
                foreach (self::BOOLEAN_KEYS as $key) {
                    if (isset($attribute[$key])) {
                        $attribute[$key] = !empty($attribute[$key]) && $attribute[$key] !== '0';
                    }
                }
                foreach (['props', 'options', 'requires', 'returnValues'] as $key) {
                    if (isset($attribute[$key]) && !is_array($attribute[$key])) {
                        $attribute[$key] = [];
                    }
                }
                if (isset($attribute['dependsOn'])) {
                    $attribute['dependsOn'] = $this->normalizeDependsOn($attribute['dependsOn']);
                }
                $this->overlays[$attribute['name']] = $attribute;
            }
        }
    }

    /**
     * Returns dependsOn as a list of conditions (all must be satisfied).
     * A condition targets another attribute (`attribute`) or a raw question path (`path`, e.g. answers)
     * and checks `value`, `values` or `notEmpty`.
     *
     * @param mixed $dependsOn
     * @return array<int, array<string, mixed>>
     */
    private function normalizeDependsOn($dependsOn): array
    {
        if (!is_array($dependsOn)) {
            return [];
        }
        $conditions = $dependsOn['condition'] ?? [$dependsOn];
        if (!array_key_exists(0, $conditions)) {
            $conditions = [$conditions];
        }

        return array_values(array_filter(array_map(function ($condition) {
            if (!is_array($condition) || (empty($condition['attribute']) && empty($condition['path']))) {
                return null;
            }
            if (isset($condition['values']) && !is_array($condition['values'])) {
                $condition['values'] = [];
            }
            if (isset($condition['notEmpty'])) {
                $condition['notEmpty'] = $condition['notEmpty'] !== '0';
            }
            return $condition;
        }, $conditions)));
    }

    private function loadSimple(SimpleXMLElement $xml): void
    {
        $default = isset($xml->default) ? $this->toArray($xml->default, 'default') : [];
        $this->simpleDefault = is_array($default) ? $default : [];

        if (!isset($xml->themes)) {
            return;
        }
        foreach ($xml->themes->theme as $theme) {
            $name = trim((string) $theme->name);
            $attributes = isset($theme->attributes) ? $this->toArray($theme->attributes, 'attributes') : [];
            if ($name !== '' && is_array($attributes)) {
                $this->simpleThemes[$name] = $attributes;
            }
        }
    }

    /**
     * @param SimpleXMLElement $element
     * @param string $name
     * @return array<mixed>|string
     */
    private function toArray(SimpleXMLElement $element, string $name)
    {
        if ($element->count() === 0) {
            return trim((string) $element);
        }

        $result = [];
        $isList = in_array($name, self::LIST_ELEMENTS, true);
        foreach ($element->children() as $childName => $child) {
            $value = $this->toArray($child, $childName);
            if ($isList) {
                $result[] = $value;
            } elseif (array_key_exists($childName, $result)) {
                // Repeated element (e.g. <condition>) becomes a list
                if (!is_array($result[$childName]) || !array_key_exists(0, $result[$childName])) {
                    $result[$childName] = [$result[$childName]];
                }
                $result[$childName][] = $value;
            } else {
                $result[$childName] = $value;
            }
        }
        return $result;
    }
}
