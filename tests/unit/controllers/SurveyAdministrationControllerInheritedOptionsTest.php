<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;
use ReflectionMethod;
use Survey;
use SurveyAdministrationController;
use SurveysGroups;
use SurveysGroupsettings;

/**
 * Loading the inherited option values for the survey settings screens
 * must not alter the cached survey used by getSurveyInfo().
 */
class SurveyAdministrationControllerInheritedOptionsTest extends TestBaseClass
{
    /** @var SurveysGroups */
    private static $surveysGroup;

    /** @var SurveysGroupsettings */
    private static $surveysGroupSettings;

    /**
     * Import a survey using its own theme, placed in a survey group with a different theme.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveysGroup = new SurveysGroups();
        $surveysGroup->name = 'group_18138';
        $surveysGroup->sortorder = 0;
        $surveysGroup->created_by = 1;
        $surveysGroup->title = 'Survey group for issue 18138';
        $surveysGroup->owner_id = 1;
        $surveysGroup->alwaysavailable = 1;
        $surveysGroup->save();
        self::$surveysGroup = $surveysGroup;

        $surveysGroupSettings = new SurveysGroupsettings();
        $surveysGroupSettings->gsid = $surveysGroup->gsid;
        $surveysGroupSettings->owner_id = -1;
        $surveysGroupSettings->template = 'bootswatch';
        $surveysGroupSettings->save();
        self::$surveysGroupSettings = $surveysGroupSettings;

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_454287.lss');
        Survey::model()->updateByPk(self::$surveyId, ['gsid' => $surveysGroup->gsid, 'template' => 'vanilla']);
        Survey::model()->resetCache();
        self::$testSurvey = Survey::model()->findByPk(self::$surveyId);

        \Yii::import('application.controllers.SurveyAdministrationController', true);
    }

    /**
     * Delete the test survey, then its survey group.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::$surveysGroupSettings->delete();
        self::$surveysGroup->delete();
    }

    /**
     * The settings screens get the group's values, while getSurveyInfo() keeps the survey's own theme.
     */
    public function testInheritedOptionsDoNotLeakIntoSurveyInfo()
    {
        $controller = new SurveyAdministrationController('surveyAdministration');
        $method = new ReflectionMethod($controller, 'loadSurveyWithInheritedOptions');
        $method->setAccessible(true);

        $survey = $method->invoke($controller, self::$surveyId);

        $this->assertSame('bootswatch', $survey->oOptions->template, 'The settings screens should get the theme inherited from the survey group.');
        $this->assertSame('vanilla', Survey::model()->findByPk(self::$surveyId)->aOptions['template'], 'The cached survey should keep its own theme.');
        $this->assertSame('vanilla', getSurveyInfo(self::$surveyId, '', true)['template'], 'getSurveyInfo() should return the theme of the survey.');
    }
}
