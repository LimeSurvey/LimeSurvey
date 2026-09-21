<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class QuestionAttributesDetail implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;
    protected Permission $permission;

    /**
     * @param ResponseFactory $responseFactory
     * @param Permission $permission
     */
    public function __construct(
        ResponseFactory $responseFactory,
        Permission $permission
    ) {
        $this->responseFactory = $responseFactory;
        $this->permission = $permission;
    }

    /**
     * Fetch question attribute metadata for the current question type/theme.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param Request $request
     * @return Response
     */
    public function run(Request $request): Response
    {
        $type = \Yii::app()->request->getParam('type');
        $theme = \Yii::app()->request->getParam('theme');
        $qid = (int) \Yii::app()->request->getParam('qid', 0);

        if (empty($type) && $qid > 0) {
            $question = \Question::model()->findByPk($qid);

            if (!empty($question)) {
                $type = $question->type;
                $theme = $question->question_theme_name;
            }
        }

        if (empty($type) && empty($qid)) {
            return $this->responseFactory->makeSuccess([
                'success' => true,
                'type' => null,
                'theme' => null,
                'attributesDetails' => $this->flattenQuestionAttributes($this->getAllXmlQuestionAttributes()),
            ]);
        }

        if (empty($type)) {
            return $this->responseFactory->makeErrorBadRequest([
                'success' => false,
                'message' => \gT('Missing question type.'),
            ]);
        }

        $attributes = \QuestionAttribute::getQuestionAttributesSettings($type);

        if (!empty($theme) && $theme !== \Question::DEFAULT_QUESTION_THEME) {
            $additionalAttributes = \QuestionTheme::getAdditionalAttrFromExtendedTheme($theme, $type);
            $attributes = array_merge($attributes, $additionalAttributes);
        }

        return $this->responseFactory->makeSuccess([
            'success' => true,
            'type' => $type,
            'theme' => $theme,
            'attributes' => $attributes,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function getAllXmlQuestionAttributes(): array
    {
        $result = [];
        $questionThemeDirectories = \QuestionTheme::getQuestionThemeDirectories();

        foreach ($questionThemeDirectories as $themeType => $baseDirectory) {
            if (!is_dir($baseDirectory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($baseDirectory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile() || strtolower($file->getFilename()) !== 'config.xml') {
                    continue;
                }

                try {
                    $config = \ExtensionConfig::loadFromFile($file->getPathname());
                } catch (\Exception $e) {
                    continue;
                }

                $metadata = $config->getNodeAsArray('metadata');
                $name = $metadata['name'] ?? basename(dirname($file->getPathname()));
                $questionType = $metadata['questionType'] ?? '';

                if (empty($questionType) || empty($name)) {
                    continue;
                }

                $attributeDefinitions = [];

                foreach (['generalattributes', 'attributes'] as $nodeName) {
                    $nodeData = $config->getNodeAsArray($nodeName);
                    if (empty($nodeData['attribute'])) {
                        continue;
                    }

                    $attributes = $nodeData['attribute'];
                    if (!array_key_exists(0, $attributes)) {
                        $attributes = [$attributes];
                    }

                    foreach ($attributes as $attribute) {
                        if (empty($attribute['name'])) {
                            continue;
                        }
                        $attributeDefinitions[$attribute['name']] = $attribute;
                    }
                }

                if (empty($attributeDefinitions)) {
                    continue;
                }

                $result[$questionType][$name] = [
                    'xml_path' => str_replace(\App()->getConfig('rootdir') . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                    'attributes' => $attributeDefinitions,
                ];
            }
        }

        return $result;
    }

    /**
     * Flatten the question-type keyed result to a single-level attributes map.
     *
     * Example:
     * [
     *   '1' => ['arrays/dualscale' => [...], '5pointchoice' => [...]],
     *   '5' => ['multiplenumeric' => [...]]
     * ]
     *
     * becomes:
     * [
     *   'arrays/dualscale' => [...],
     *   '5pointchoice' => [...],
     *   'multiplenumeric' => [...]
     * ]
     *
     * @param array<string, array<string, array<string, mixed>>> $questionAttributes
     * @return array<string, array<string, mixed>>
     */
    protected function flattenQuestionAttributes(array $questionAttributes): array
    {
        $flattened = [];

        foreach ($questionAttributes as $questionTypeAttributes) {
            foreach ($questionTypeAttributes as $themeName => $definition) {
                $flattened[$themeName] = $definition;
            }
        }

        return $flattened;
    }
}
