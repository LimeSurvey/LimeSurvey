<?php

/**
 * Controller exposing question type/theme attribute metadata as JSON.
 *
 * This is useful for the frontend to render the attributes dynamically based on the question type and theme.
 */
class QuestionThemeAttributeController extends LSBaseController
{
    /**
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => ['attributes'],
                'users' => ['@'],
            ],
            ['deny'],
        ];
    }

    /**
     * Returns the attributes metadata
     *
     * Example:
     *   /questionThemeAttribute/attributes
     *   /questionThemeAttribute/attributes?qid=123
     *   /questionThemeAttribute/attributes?type=!&theme=list_dropdown
     *
     * @return void
     */
    public function actionAttributes()
    {
        $type = Yii::app()->request->getParam('type');
        $theme = Yii::app()->request->getParam('theme');
        $qid = (int) Yii::app()->request->getParam('qid', 0);

        if (empty($type) && $qid > 0) {
            $question = Question::model()->findByPk($qid);

            if (!empty($question)) {
                $type = $question->type;
                $theme = $question->question_theme_name;
            }
        }

        if (empty($type) && empty($qid)) {
            $this->renderJSON([
                'success' => true,
                'type' => null,
                'theme' => null,
                'attributes' => $this->getAllXmlQuestionAttributes(),
            ]);

            return;
        }

        if (empty($type)) {
            $this->renderJSON([
                'success' => false,
                'message' => gT('Missing question type.'),
            ]);

            return;
        }

        $attributes = QuestionAttribute::getQuestionAttributesSettings($type);

        if (!empty($theme) && $theme !== Question::DEFAULT_QUESTION_THEME) {
            $additionalAttributes = QuestionTheme::getAdditionalAttrFromExtendedTheme($theme, $type);
            $attributes = array_merge($attributes, $additionalAttributes);
        }

        $this->renderJSON([
            'success' => true,
            'type' => $type,
            'theme' => $theme,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Collect all question attributes declared inside the XML question-theme config files.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getAllXmlQuestionAttributes()
    {
        $result = [];
        $questionThemeDirectories = QuestionTheme::getQuestionThemeDirectories();

        foreach ($questionThemeDirectories as $themeType => $baseDirectory) {
            if (!is_dir($baseDirectory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($baseDirectory, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile() || strtolower($file->getFilename()) !== 'config.xml') {
                    continue;
                }

                try {
                    $config = ExtensionConfig::loadFromFile($file->getPathname());
                } catch (Exception $e) {
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
                    'xml_path' => str_replace(App()->getConfig('rootdir') . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                    'attributes' => $attributeDefinitions,
                ];
            }
        }

        return $result;
    }
}
