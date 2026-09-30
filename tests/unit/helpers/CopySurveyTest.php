<?php

namespace helpers;

use LimeSurvey\Models\Services\CopySurveyOptions;
use ls\tests\TestBaseClass;
use PluginSetting;
use Question;
use Survey;
use SurveyURLParameter;

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
     * Test that survey URL parameters are copied and their target question/subquestion ids remapped.
     *
     * @return void
     * @throws \Exception
     */
    public function testCopySurveyCopiesUrlParameters()
    {
        $survey = Survey::model()->findByPk(self::$testSurvey->sid);
        $subquestion = Question::model()->find(
            'sid = :sid AND parent_qid > 0',
            [':sid' => $survey->sid]
        );
        $this->assertNotNull($subquestion, 'Expected the test survey to contain a subquestion.');
        $parentQuestion = Question::model()->findByPk($subquestion->parent_qid);

        $this->createUrlParameter($survey->sid, 'withoutTarget');
        $this->createUrlParameter($survey->sid, 'withSubquestion', $parentQuestion->qid, $subquestion->qid);

        $copiedSurvey = null;
        try {
            $result = $this->copySurvey($survey);
            $this->assertEquals($result->getErrors(), []);

            $copiedSurvey = $result->getCopiedSurvey();
            $this->assertNotNull($copiedSurvey);
            $this->assertSame(2, $result->getCntUrlParameters());

            $withoutTarget = SurveyURLParameter::model()->findByAttributes([
                'sid' => $copiedSurvey->sid,
                'parameter' => 'withoutTarget',
            ]);
            $this->assertNotNull($withoutTarget, 'Expected URL parameter without target to be copied.');
            $this->assertEmpty($withoutTarget->targetqid);
            $this->assertEmpty($withoutTarget->targetsqid);

            $withSubquestion = SurveyURLParameter::model()->findByAttributes([
                'sid' => $copiedSurvey->sid,
                'parameter' => 'withSubquestion',
            ]);
            $this->assertNotNull($withSubquestion, 'Expected URL parameter with target to be copied.');

            $copiedParentQuestion = Question::model()->findByAttributes([
                'sid' => $copiedSurvey->sid,
                'title' => $parentQuestion->title,
                'parent_qid' => 0,
            ]);
            $copiedSubquestion = Question::model()->findByAttributes([
                'sid' => $copiedSurvey->sid,
                'title' => $subquestion->title,
                'parent_qid' => $copiedParentQuestion->qid,
            ]);
            $this->assertEquals($copiedParentQuestion->qid, $withSubquestion->targetqid);
            $this->assertEquals($copiedSubquestion->qid, $withSubquestion->targetsqid);
        } finally {
            SurveyURLParameter::model()->deleteAllByAttributes(['sid' => $survey->sid]);
            if ($copiedSurvey instanceof Survey) {
                $copiedSurvey->delete();
            }
        }
    }

    /**
     * @param int $surveyId
     * @param string $parameterName
     * @param int|null $targetQid
     * @param int|null $targetSqid
     * @return void
     */
    private function createUrlParameter($surveyId, $parameterName, $targetQid = null, $targetSqid = null)
    {
        $urlParameter = new SurveyURLParameter();
        $urlParameter->sid = $surveyId;
        $urlParameter->parameter = $parameterName;
        $urlParameter->targetqid = $targetQid;
        $urlParameter->targetsqid = $targetSqid;
        $this->assertTrue($urlParameter->save(), json_encode($urlParameter->getErrors()));
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
