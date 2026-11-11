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
     * The image select themes get the per-option image attributes, the alt text translated.
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
            $this->assertFalse($definitions['image_option_settings']['i18n']);
            $this->assertSame('image_option_alt', $definitions['image_option_alt']['name']);
            $this->assertTrue($definitions['image_option_alt']['i18n']);
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
     * stay for image select themes only.
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
            ['image_option_alt', 'image_option_settings'],
            $this->getImageOptionAttributeNames($question)
        );

        $question = $this->getQuestion('listradio');
        $attributesService->sanitizeAttributesByType($question);
        $this->assertSame([], $this->getImageOptionAttributeNames($question));
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
     * Stores both image option attributes for the question.
     *
     * @param \Question $question the question
     * @return void
     */
    private function setImageOptionAttributes($question)
    {
        $attribute = new \QuestionAttribute();
        $attribute->setQuestionAttribute($question->qid, 'image_option_settings', '{"A1":{"width":200}}');
        $attribute->setQuestionAttributeWithLanguage(
            $question->qid,
            'image_option_alt',
            '{"A1":"Lime"}',
            self::$testSurvey->language
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
