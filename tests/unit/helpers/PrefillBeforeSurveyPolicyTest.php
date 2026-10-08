<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * Values prefilled by URL must not create the response before the survey is started (welcome page, survey policy).
 * See mantis #16482
 */
class PrefillBeforeSurveyPolicyTest extends TestBaseClass
{
    /**
     * Import and activate the survey (group by group, welcome page and survey policy notice)
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_PrefillBeforeSurveyPolicy.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Reset request and session data
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * Starting the survey with a prefilled value doesn't create the response,
     * the prefilled value is saved when the response is created on the first move
     */
    public function testPrefilledValueSavedOnlyWhenSurveyStarted()
    {
        list(, , $sgqa) = self::$testHelper->getSgqa('uid', self::$surveyId);

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        $_GET['uid'] = '4242';
        \buildsurveysession(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );

        $this->assertArrayNotHasKey('srid', $_SESSION['responses_' . self::$surveyId], 'No response was created on survey start');
        $this->assertEquals(0, $this->countResponses(), 'No response saved on survey start');
        $this->assertEquals('4242', $_SESSION['responses_' . self::$surveyId][$sgqa], 'Prefilled value is set in session');

        // Leave the welcome page
        \LimeExpressionManager::NavigateForwards();

        $responses = \Yii::app()->db->createCommand('SELECT * FROM {{responses_' . self::$surveyId . '}}')->queryAll();
        $this->assertCount(1, $responses, 'Response created when leaving the welcome page');
        $this->assertEquals('4242', $responses[0][$sgqa], 'Prefilled value saved with the response');
    }

    /**
     * Count the responses of the survey
     * @return integer
     */
    private function countResponses()
    {
        return (int) \Yii::app()->db->createCommand('SELECT COUNT(*) FROM {{responses_' . self::$surveyId . '}}')->queryScalar();
    }
}
