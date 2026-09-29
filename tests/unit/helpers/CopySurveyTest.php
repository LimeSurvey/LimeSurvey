<?php

namespace ls\tests\unit\helpers;

use LimeSurvey\Models\Services\CopySurveyOptions;
use ls\tests\TestBaseClass;
use PluginSetting;
use Question;
use QuestionAttribute;
use Survey;

class CopySurveyTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_373616_copySurvey.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Test the copy survey functionality.
     *
     * @return void
     * @throws \Exception
     */
    public function testCopySurvey()
    {
        $survey = Survey::model()->findByPk(self::$testSurvey->sid);
        $result = $this->copySurvey($survey);

        $this->assertEquals($result->getErrors(), []);

        $copiedSurvey = $result->getCopiedSurvey();
        $this->assertNotNull($copiedSurvey);
        $this->assertTrue($copiedSurvey->delete(), 'Copied survey should be removable during test cleanup.');
    }

    /**
     * Test that survey-scoped plugin settings are copied with the survey.
     *
     * @return void
     * @throws \Exception
     */
    public function testCopySurveyCopiesSurveyPluginSettings()
    {
        $survey = Survey::model()->findByPk(self::$testSurvey->sid);
        $plugin = self::installAndActivatePlugin('expressionFixedDbVar');
        $sourceSettings = [
            'SEED' => '0',
            'STARTDATE' => '1',
        ];

        $this->assertNotNull($plugin, 'Expected core plugin expressionFixedDbVar to exist for plugin setting copy test.');

        foreach ($sourceSettings as $key => $value) {
            $pluginSetting = new PluginSetting();
            $pluginSetting->plugin_id = $plugin->id;
            $pluginSetting->model = 'Survey';
            $pluginSetting->model_id = $survey->sid;
            $pluginSetting->key = $key;
            $pluginSetting->value = json_encode($value);

            $this->assertTrue($pluginSetting->save(), json_encode($pluginSetting->errors));

            $savedSourceSetting = PluginSetting::model()->findByAttributes([
                'plugin_id' => $plugin->id,
                'model' => 'Survey',
                'model_id' => $survey->sid,
                'key' => $key,
            ]);

            $this->assertNotNull($savedSourceSetting, "Expected source plugin setting '{$key}' to be saved.");
            $this->assertSame(json_encode($value), $savedSourceSetting->value);
        }

        $copiedSurvey = null;
        try {
            $result = $this->copySurvey($survey);

            $this->assertEquals($result->getErrors(), []);

            $copiedSurvey = $result->getCopiedSurvey();
            $this->assertNotNull($copiedSurvey);

            foreach ($sourceSettings as $key => $value) {
                $copiedPluginSetting = PluginSetting::model()->findByAttributes([
                    'plugin_id' => $plugin->id,
                    'model' => 'Survey',
                    'model_id' => $copiedSurvey->sid,
                    'key' => $key,
                ]);

                $this->assertNotNull($copiedPluginSetting, "Survey-scoped plugin setting '{$key}' should be copied with the survey.");
                $this->assertSame(json_encode($value), $copiedPluginSetting->value);
            }
        } finally {
            PluginSetting::model()->deleteAllByAttributes([
                'plugin_id' => $plugin->id,
                'model' => 'Survey',
                'model_id' => $survey->sid,
            ]);

            if ($copiedSurvey instanceof Survey) {
                $copiedSurvey->delete();
            }

            self::deActivatePlugin('expressionFixedDbVar');
        }
    }

    /**
     * Test that multilingual (i18n) question attributes (e.g. 'printable_help', which has one
     * row per survey language) are all copied, not just one language.
     *
     * Regression test: QuestionAttribute's defaultScope indexes findAll results by the
     * 'attribute' column, so fetching a question's attributes without resetScope() collapses
     * rows sharing the same attribute name (i.e. the per-language rows of an i18n attribute)
     * down to a single entry, silently dropping the other languages during copy.
     *
     * @return void
     * @throws \Exception
     */
    public function testCopySurveyCopiesMultilingualQuestionAttributes()
    {
        $survey = Survey::model()->findByPk(self::$testSurvey->sid);
        $sourceQuestion = Question::model()->findByAttributes(['sid' => $survey->sid, 'title' => 'G01Q03']);
        $this->assertNotNull($sourceQuestion, 'Expected source question G01Q03 to exist.');

        // 'printable_help' is an i18n attribute: fixture already has one row per language for this question.
        $languageValues = ['de' => 'Hilfetext DE', 'en' => 'Help text EN'];
        foreach ($languageValues as $language => $value) {
            QuestionAttribute::model()->setQuestionAttributeWithLanguage($sourceQuestion->qid, 'printable_help', $value, $language);
        }

        $copiedSurvey = null;
        try {
            $result = $this->copySurvey($survey);
            $this->assertEquals($result->getErrors(), []);

            $copiedSurvey = $result->getCopiedSurvey();
            $this->assertNotNull($copiedSurvey);

            $copiedQuestion = Question::model()->findByAttributes(['sid' => $copiedSurvey->sid, 'title' => 'G01Q03']);
            $this->assertNotNull($copiedQuestion, 'Expected copied question G01Q03 to exist.');

            foreach ($languageValues as $language => $expectedValue) {
                $copiedAttribute = QuestionAttribute::model()->findByAttributes([
                    'qid' => $copiedQuestion->qid,
                    'attribute' => 'printable_help',
                    'language' => $language,
                ]);
                $this->assertNotNull($copiedAttribute, "Expected copied question attribute 'printable_help' for language '{$language}' to exist.");
                $this->assertSame($expectedValue, $copiedAttribute->value);
            }
        } finally {
            if ($copiedSurvey instanceof Survey) {
                $copiedSurvey->delete();
            }
            foreach ($languageValues as $language => $value) {
                QuestionAttribute::model()->setQuestionAttributeWithLanguage($sourceQuestion->qid, 'printable_help', '', $language);
            }
        }
    }

    /**
     * Copy the imported test survey using the default copy options.
     *
     * @param Survey $survey
     * @return \LimeSurvey\Models\Services\CopySurveyResult
     * @throws \Exception
     */
    private function copySurvey(Survey $survey)
    {
        $copySurveyService = new \LimeSurvey\Models\Services\CopySurvey(
            $survey,
            new CopySurveyOptions(),
            null
        );

        return $copySurveyService->copy();
    }
}
