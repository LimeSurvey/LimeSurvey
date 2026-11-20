<?php

namespace ls\tests;

use LimeSurvey\DI;
use LimeSurvey\Models\Services\HiddenQuestionAttributeProvider;
use LimeSurvey\Models\Services\QuestionAggregateService\AttributesService;

class HiddenQuestionAttributeProviderTest extends TestBaseClass
{
    /**
     * Imports a survey with a single choice list question (Q00).
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_496242.lss');
    }

    /**
     * The image select themes get the per-option image attributes, both translated: Each language can have its
     * own image. The settings for images added later are one for all languages.
     *
     * @return void
     */
    public function testImageSelectThemesHaveImageOptionAttributes()
    {
        $provider = new HiddenQuestionAttributeProvider();
        foreach (['L' => 'image_select-listradio', 'M' => 'image_select-multiplechoice'] as $type => $theme) {
            $definitions = $provider->getDefinitions(['questionType' => $type, 'questionTheme' => $theme]);

            $this->assertArrayHasKey('image', $definitions);
            $this->assertSame('image_option_settings', $definitions['image_option_settings']['name']);
            $this->assertTrue($definitions['image_option_settings']['i18n']);
            $this->assertSame('image_option_alt', $definitions['image_option_alt']['name']);
            $this->assertTrue($definitions['image_option_alt']['i18n']);
            $this->assertSame('image_option_defaults', $definitions['image_option_defaults']['name']);
            $this->assertFalse($definitions['image_option_defaults']['i18n']);
            $this->assertFalse($definitions['image_option_defaults']['xssfilter']);
        }
    }

    /**
     * Other themes only get the attributes of all question types.
     *
     * @return void
     */
    public function testOtherThemesHaveNoImageOptionAttributes()
    {
        $provider = new HiddenQuestionAttributeProvider();
        $definitions = $provider->getDefinitions(['questionType' => 'L', 'questionTheme' => 'listradio']);

        $this->assertSame(['image'], array_keys($definitions));
        $this->assertSame([], $provider->getDefinitions(['questionTheme' => 'image_select-listradio']));
    }

    /**
     * The theme of the question is used, unless a theme is given explicitly.
     *
     * @return void
     */
    public function testThemeComesFromTheQuestionOrTheOption()
    {
        $question = $this->getQuestion('image_select-listradio');
        $provider = new HiddenQuestionAttributeProvider();

        $this->assertArrayHasKey('image_option_alt', $provider->getDefinitions(['question' => $question]));
        $this->assertArrayNotHasKey(
            'image_option_alt',
            $provider->getDefinitions(['question' => $question, 'questionTheme' => 'listradio'])
        );
    }

    /**
     * Saving a question removes attributes its type doesn't define: The image option attributes
     * (including the not translated image_option_defaults) stay for image select themes only.
     *
     * @return void
     */
    public function testSaveCleanupKeepsImageOptionAttributesOfImageSelectThemes()
    {
        $attributesService = DI::getContainer()->get(AttributesService::class);

        $question = $this->getQuestion('image_select-listradio');
        $this->setImageOptionAttributes($question);
        $attributesService->sanitizeAttributesByType($question);
        $this->assertSame(
            ['image_option_alt', 'image_option_defaults', 'image_option_settings'],
            $this->getImageOptionAttributeNames($question)
        );

        $question = $this->getQuestion('listradio');
        $attributesService->sanitizeAttributesByType($question);
        $this->assertSame([], $this->getImageOptionAttributeNames($question));
    }

    /**
     * The image option attributes are stored unchanged when the XSS filter is active: Filtering would
     * entity-encode the alt texts and break the JSON on curly braces.
     *
     * @return void
     */
    public function testImageOptionAttributesAreNotXssFiltered()
    {
        $question = $this->getQuestion('image_select-listradio');
        $values = [
            'image_option_settings' => '{"A1":{"width":200,"height":100,"radius":8}}',
            'image_option_alt' => '{"A1":"Fish & Chips {2024} <b>\"quoted\"</b>"}',
        ];

        $forcedAll = \Yii::app()->getConfig('filterxsshtml_forcedall');
        $allowForcedSuperadmin = \Yii::app()->getConfig('filterxsshtml_allowforcedsuperadmin');
        \Yii::app()->setConfig('filterxsshtml_forcedall', true);
        \Yii::app()->setConfig('filterxsshtml_allowforcedsuperadmin', false);
        try {
            foreach ($values as $name => $value) {
                \QuestionAttribute::model()->deleteAllByAttributes(['qid' => $question->qid, 'attribute' => $name]);
                $attribute = new \QuestionAttribute();
                $attribute->qid = $question->qid;
                $attribute->attribute = $name;
                $attribute->value = $value;
                $attribute->language = self::$testSurvey->language;
                $this->assertTrue($attribute->save(), print_r($attribute->getErrors(), true));
            }
        } finally {
            \Yii::app()->setConfig('filterxsshtml_forcedall', $forcedAll);
            \Yii::app()->setConfig('filterxsshtml_allowforcedsuperadmin', $allowForcedSuperadmin);
        }

        foreach ($values as $name => $value) {
            $stored = \QuestionAttribute::model()->resetScope()->findByAttributes(
                ['qid' => $question->qid, 'attribute' => $name]
            );
            $this->assertSame($value, $stored->value);
            $this->assertNotNull(json_decode($stored->value));
        }
    }

    /**
     * Returns the list question of the test survey with the given question theme.
     *
     * @param string $theme the question theme name to set
     * @return \Question the question
     */
    private function getQuestion($theme)
    {
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'Q00']);
        $question->question_theme_name = $theme;
        $this->assertTrue($question->save());
        return $question;
    }

    /**
     * Stores all image option attributes for the question.
     *
     * @param \Question $question the question
     * @return void
     */
    private function setImageOptionAttributes($question)
    {
        $attribute = new \QuestionAttribute();
        $attribute->setQuestionAttributeWithLanguage(
            $question->qid,
            'image_option_settings',
            '{"A1":{"width":200}}',
            self::$testSurvey->language
        );
        $attribute->setQuestionAttributeWithLanguage(
            $question->qid,
            'image_option_alt',
            '{"A1":"Lime"}',
            self::$testSurvey->language
        );
        $attribute->setQuestionAttributeWithLanguage(
            $question->qid,
            'image_option_defaults',
            '{"width":200,"height":100}',
            ''
        );
    }

    /**
     * Returns the names of the image option attributes stored for the question.
     *
     * @param \Question $question the question
     * @return string[] the sorted attribute names
     */
    private function getImageOptionAttributeNames($question)
    {
        $names = array_map(
            function ($attribute) {
                return $attribute->attribute;
            },
            \QuestionAttribute::model()->resetScope()->findAll(
                "qid = :qid AND attribute LIKE 'image_option%'",
                [':qid' => $question->qid]
            )
        );
        sort($names);
        return $names;
    }
}
