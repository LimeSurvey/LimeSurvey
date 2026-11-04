<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Models\Services\ReactQuestionAttributeConfig;

/**
 * Returns the editor attribute definitions of every installed question theme,
 * keyed by theme name: the config.xml attributes merged with the react/*.xml overlays.
 */
class QuestionAttributesDetail implements CommandInterface
{
    protected ResponseFactory $responseFactory;
    protected ReactQuestionAttributeConfig $reactQuestionAttributeConfig;

    /**
     * @param ResponseFactory $responseFactory
     * @param ReactQuestionAttributeConfig $reactQuestionAttributeConfig
     */
    public function __construct(
        ResponseFactory $responseFactory,
        ReactQuestionAttributeConfig $reactQuestionAttributeConfig
    ) {
        $this->responseFactory = $responseFactory;
        $this->reactQuestionAttributeConfig = $reactQuestionAttributeConfig;
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

        $baseAttributesByType = [];
        $themes = [];
        foreach ($questionThemes as $questionTheme) {
            $attributes = $this->readConfigAttributes($questionTheme);
            if ($attributes === null) {
                continue;
            }
            if ((string) $questionTheme->extends === '') {
                $baseAttributesByType[$questionTheme->question_type] = $attributes;
            }
            $themes[] = [$questionTheme, $attributes];
        }

        $result = [];
        foreach ($themes as [$questionTheme, $attributes]) {
            // Extended themes only declare their additional attributes
            if ((string) $questionTheme->extends !== '') {
                $attributes = array_merge($baseAttributesByType[$questionTheme->question_type] ?? [], $attributes);
            }
            if (empty($attributes)) {
                continue;
            }
            $result[$questionTheme->name] = [
                'attributes' => $this->reactQuestionAttributeConfig->apply($attributes, (string) $questionTheme->name),
            ];
        }

        return $result;
    }

    /**
     * Reads the <generalattributes> and <attributes> of a question theme config.xml.
     *
     * @param \QuestionTheme $questionTheme
     * @return array<string, array<string, mixed>>|null Null when the config.xml can't be read
     */
    protected function readConfigAttributes(\QuestionTheme $questionTheme): ?array
    {
        $xmlPath = (string) $questionTheme->getXmlPath();
        if ($xmlPath === '') {
            return null;
        }
        if (!preg_match('#^(/|[A-Za-z]:[\\\\/])#', $xmlPath)) {
            $xmlPath = \App()->getConfig('rootdir') . DIRECTORY_SEPARATOR . $xmlPath;
        }

        // A broken theme must not break the endpoint for all other themes:
        // collect libxml errors instead of raising warnings (turned into errors by Yii)
        $useInternalErrors = libxml_use_internal_errors(true);
        try {
            $config = \ExtensionConfig::loadFromFileCached($xmlPath . DIRECTORY_SEPARATOR . 'config.xml');
            if ($config === null) {
                return null;
            }
            $metadata = $config->getNodeAsArray('metadata');
            $nodes = [
                $config->getNodeAsArray('generalattributes'),
                $config->getNodeAsArray('attributes'),
            ];
        } catch (\Throwable $e) {
            return null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
        }

        $attributeDefinitions = [];
        foreach ($nodes as $nodeData) {
            if (empty($nodeData['attribute'])) {
                continue;
            }

            $attributes = $nodeData['attribute'];
            // A single <attribute> is decoded as a string (generalattributes) or an associative array
            if (!is_array($attributes) || !array_key_exists(0, $attributes)) {
                $attributes = [$attributes];
            }

            foreach ($attributes as $attribute) {
                // <generalattributes> only lists attribute names
                if (is_string($attribute)) {
                    if ($attribute === 'other' && empty($metadata['other'])) {
                        continue;
                    }
                    $attribute = ['name' => $attribute, 'general' => true];
                }
                if (!is_array($attribute) || empty($attribute['name']) || !is_string($attribute['name'])) {
                    continue;
                }
                $attributeDefinitions[$attribute['name']] = $attribute;
            }
        }

        return $attributeDefinitions;
    }
}
