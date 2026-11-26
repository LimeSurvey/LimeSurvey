<?php

namespace ls\tests;

use Survey;
use SurveysGroups;
use SurveysGroupsettings;
use Yii;

/**
 * SurveyActivator::activate() fixes the inherited encryption method, whatever the activation path
 * (archive import, command) : a later change of the survey group does not change the method
 * of the existing responses (#19645).
 */
class SurveyActivatorEncryptionMethodTest extends TestBaseClass
{
    /** @var SurveysGroups */
    private static $surveysGroup;

    /** @var SurveysGroupsettings */
    private static $surveysGroupSettings;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveysGroup = new SurveysGroups();
        $surveysGroup->name = 'group_19645_activator';
        $surveysGroup->sortorder = 0;
        $surveysGroup->created_by = 1;
        $surveysGroup->title = 'Survey group for issue 19645, activator';
        $surveysGroup->owner_id = 1;
        $surveysGroup->alwaysavailable = 1;
        $surveysGroup->save();
        self::$surveysGroup = $surveysGroup;

        $surveysGroupSettings = new SurveysGroupsettings();
        $surveysGroupSettings->gsid = $surveysGroup->gsid;
        $surveysGroupSettings->owner_id = -1;
        $surveysGroupSettings->encryption_method = 'H';
        $surveysGroupSettings->save();
        self::$surveysGroupSettings = $surveysGroupSettings;

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_213203_other_checked.lss');
        Survey::model()->updateByPk(self::$surveyId, ['gsid' => $surveysGroup->gsid, 'encryption_method' => 'I']);
        Survey::model()->resetCache();
        self::$testSurvey = Survey::model()->findByPk(self::$surveyId);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$surveysGroupSettings->delete();
        self::$surveysGroup->delete();
    }

    public function testActivatorFixesTheInheritedMethod(): void
    {
        $this->assertSame('I', self::$testSurvey->encryption_method);

        $result = (new \SurveyActivator(self::$testSurvey))->activate();
        Yii::app()->db->schema->refresh();

        $this->assertArrayNotHasKey('error', $result, json_encode($result['error'] ?? null));
        $this->assertSame('H', Survey::model()->findByPkNoCache(self::$surveyId)->encryption_method, 'The inherited method is saved in the survey.');

        /* A later change of the survey group does not change it */
        SurveysGroupsettings::model()->updateByPk(self::$surveysGroup->gsid, ['encryption_method' => 'B']);
        $this->assertSame('H', Survey::model()->findByPkNoCache(self::$surveyId)->getResolvedEncryptionMethod());
    }
}
