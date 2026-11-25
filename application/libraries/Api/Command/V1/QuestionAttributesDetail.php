<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Models\Services\{
    CoreQuestionAttributeProvider,
    PluginQuestionAttributeProvider,
    QuestionAttributeHelper,
    QuestionAttributeProvider,
    ReactQuestionAttributeConfig,
    ThemeQuestionAttributeProvider
};

/**
 * Returns the editor attribute definitions of every installed question theme,
 * keyed by theme name: the attributes from the question attribute providers
 * (core config.xml, extended theme config.xml, plugins) merged with the react/*.xml overlays.
 */
class QuestionAttributesDetail implements CommandInterface
{
    protected ResponseFactory $responseFactory;
    protected ReactQuestionAttributeConfig $reactQuestionAttributeConfig;
    protected QuestionAttributeHelper $questionAttributeHelper;

    /** @var array<string, array<string, array<string, mixed>>|null> Base attributes cached by question type */
    private array $baseAttributesByType = [];

    /**
     * @param ResponseFactory $responseFactory
     * @param ReactQuestionAttributeConfig $reactQuestionAttributeConfig
     * @param QuestionAttributeHelper $questionAttributeHelper
     */
    public function __construct(
        ResponseFactory $responseFactory,
        ReactQuestionAttributeConfig $reactQuestionAttributeConfig,
        QuestionAttributeHelper $questionAttributeHelper
    ) {
        $this->responseFactory = $responseFactory;
        $this->reactQuestionAttributeConfig = $reactQuestionAttributeConfig;
        $this->questionAttributeHelper = $questionAttributeHelper;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request): Response
    {
        return $this->responseFactory->makeSuccess([
            'success' => true,
            'attributesDetails' => $this->getThemeAttributes(),
        ]);
    }

    /**
     * @return array<string, array{attributes: array<string, array<string, mixed>>}>
     */
    protected function getThemeAttributes(): array
    {
        /** @var \QuestionTheme[] $questionThemes */
        $questionThemes = \QuestionTheme::model()->findAll();

        $baseThemeNames = [];
        foreach ($questionThemes as $questionTheme) {
            if ($questionTheme->core_theme && (string) $questionTheme->extends === '') {
                $baseThemeNames[$questionTheme->question_type] = (string) $questionTheme->name;
            }
        }

        $result = [];
        foreach ($questionThemes as $questionTheme) {
            $attributes = $this->getAttributes($questionTheme);
            if (empty($attributes)) {
                continue;
            }
            $result[$questionTheme->name] = [
                'attributes' => $this->reactQuestionAttributeConfig->apply(
                    $attributes,
                    (string) $questionTheme->name,
                    $baseThemeNames[$questionTheme->question_type] ?? null
                ),
            ];
        }

        return $result;
    }

    /**
     * Returns the attribute definitions of a question theme, built the same way as
     * QuestionAttributeFetcher: base type attributes, then the extended theme's own attributes,
     * then the plugin attributes (an attribute with an empty inputtype removes the inherited one).
     *
     * @param \QuestionTheme $questionTheme
     * @return array<string, array<string, mixed>>
     */
    protected function getAttributes(\QuestionTheme $questionTheme): array
    {
        $questionType = (string) $questionTheme->question_type;
        $attributes = $this->getBaseAttributes($questionType);
        if ($attributes === null) {
            return [];
        }

        $options = ['questionType' => $questionType, 'questionTheme' => (string) $questionTheme->name];
        foreach ([new ThemeQuestionAttributeProvider(), new PluginQuestionAttributeProvider()] as $provider) {
            $definitions = $this->getDefinitions($provider, $options);
            if (empty($definitions)) {
                continue;
            }
            try {
                $attributes = $this->questionAttributeHelper->mergeQuestionAttributes(
                    $attributes,
                    $this->questionAttributeHelper->sanitizeQuestionAttributes($definitions)
                );
            } catch (\Throwable $e) {
                // Malformed theme or plugin attributes: keep the attributes merged so far
            }
        }

        return $attributes;
    }

    /**
     * Returns the attributes of the base theme of the question type (the theme used at runtime),
     * with the <generalattributes> flagged as general.
     *
     * @param string $questionType
     * @return array<string, array<string, mixed>>|null Null when the base config.xml can't be read
     */
    protected function getBaseAttributes(string $questionType): ?array
    {
        if (array_key_exists($questionType, $this->baseAttributesByType)) {
            return $this->baseAttributesByType[$questionType];
        }

        $provider = new CoreQuestionAttributeProvider();
        $all = $this->getDefinitions($provider, ['questionType' => $questionType]);
        $advanced = $this->getDefinitions($provider, ['questionType' => $questionType, 'advancedOnly' => true]);
        if ($all === null || $advanced === null) {
            return $this->baseAttributesByType[$questionType] = null;
        }

        $attributes = [];
        foreach ($all as $name => $definition) {
            if ($name === 'other' && !$this->supportsOther($questionType)) {
                continue;
            }
            $attributes[$name] = isset($advanced[$name])
                ? $definition
                // General attributes are question properties: only the name is relevant
                : ['name' => $name, 'general' => true];
        }

        return $this->baseAttributesByType[$questionType] = $this->questionAttributeHelper->sanitizeQuestionAttributes($attributes);
    }

    /**
     * Reads <metadata><other> from the base theme's config.xml
     * (the question_theme.settings column lacks it on older installations).
     *
     * @param string $questionType
     * @return bool
     */
    private function supportsOther(string $questionType): bool
    {
        $useInternalErrors = libxml_use_internal_errors(true);
        try {
            $config = \ExtensionConfig::loadFromFileCached(\QuestionTheme::getQuestionXMLPathForBaseType($questionType));
            $metadata = $config !== null ? $config->getNodeAsArray('metadata') : [];
        } catch (\Throwable $e) {
            $metadata = [];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
        }

        return !empty($metadata['other']);
    }

    /**
     * A broken theme or plugin must not break the endpoint for all other themes.
     *
     * @param QuestionAttributeProvider $provider
     * @param array<string, mixed> $options
     * @return array<string, array<string, mixed>>|null Null on failure
     */
    private function getDefinitions(QuestionAttributeProvider $provider, array $options): ?array
    {
        // Collect libxml errors instead of raising warnings (turned into errors by Yii)
        $useInternalErrors = libxml_use_internal_errors(true);
        try {
            return (array) $provider->getDefinitions($options);
        } catch (\Throwable $e) {
            return null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
        }
    }
}
