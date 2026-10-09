<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * Displaying an all in one survey must not create the response, only submitting the page does.
 * See mantis #17444
 */
class AllInOneResponseCreationTest extends TestBaseClass
{
    /**
     * Import the survey, switch it to all in one with date stamps and activate it
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_666368.lss';
        self::importSurvey($surveyFile);
        \Survey::model()->updateByPk(self::$surveyId, ['format' => 'A', 'datestamp' => 'Y']);
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
     * The response is created when the page is submitted, with the date the survey was displayed as start date
     */
    public function testResponseCreatedOnlyWhenPageIsSubmitted()
    {
        list($question, $group, $sgqa) = self::$testHelper->getSgqa('Q00', self::$surveyId);
        $sessionId = 'responses_' . self::$surveyId;

        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \buildsurveysession(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'survey',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );
        // Display the survey, like SurveyRuntimeHelper does on first load
        \LimeExpressionManager::JumpTo(1, false, false, true);

        $this->assertArrayNotHasKey('srid', $_SESSION[$sessionId], 'No response was created by displaying the survey');
        $this->assertEquals(0, $this->countResponses(), 'No response saved by displaying the survey');
        $this->assertArrayHasKey('startdate', $_SESSION[$sessionId], 'Start date is kept in session');

        // Submit the page later on
        $_SESSION[$sessionId]['startdate'] = '2020-01-02 03:04:05';
        $_POST['relevance' . $question->qid] = 1;
        $_POST['relevanceG0'] = 1;
        $_POST['thisstep'] = 1;
        $_POST['sid'] = self::$surveyId;
        $_POST[$sgqa] = 'Answer';
        \LimeExpressionManager::JumpTo(1, false, true, true);

        $responses = \Yii::app()->db->createCommand('SELECT * FROM {{responses_' . self::$surveyId . '}}')->queryAll();
        $this->assertCount(1, $responses, 'Response created when submitting the page');
        $this->assertEquals('Answer', $responses[0][$sgqa], 'Answer saved with the response');
        $this->assertEquals('2020-01-02 03:04:05', $responses[0]['startdate'], 'Start date is the date the survey was displayed');

        $response = \Response::model(self::$surveyId)->findByPk($responses[0]['id']);
        $this->assertTrue($response->hasAnswers(), 'Response with an answer has answers');
        $response->setAttribute($sgqa, '');
        $this->assertFalse($response->hasAnswers(), 'Response without answers has no answers');
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
