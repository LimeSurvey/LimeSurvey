<?php

namespace ls\tests;

use Yii;

/**
 * Tests for the admin notification emails sent by sendSubmitNotifications()
 */
class SendSubmitNotificationsTest extends TestBaseClass
{
    /** @var int ID of the response the notifications are sent for */
    private static $responseId;

    /**
     * Import and activate the survey, add a response
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Yii::import('application.helpers.frontend_helper', true);
        Yii::import('application.helpers.replacements_helper', true);

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_366446.lss');
        self::$testSurvey->emailnotificationto = 'basic1@example.org;basic2@example.org';
        self::$testSurvey->emailresponseto = 'detailed1@example.org;detailed2@example.org';
        self::$testSurvey->htmlemail = 'Y';
        self::$testSurvey->save();
        self::$testHelper->activateSurvey(self::$surveyId);

        $questions = \Question::model()->findAllByAttributes(['sid' => self::$surveyId, 'title' => 'q1']);
        self::$responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'en',
            'startdate' => date('Y-m-d H:i:s'),
            'datestamp' => date('Y-m-d H:i:s'),
            'submitdate' => date('Y-m-d H:i:s'),
            'Q' . $questions[0]->qid => 'Y',
        ]);
    }

    /**
     * Prepare the globals and the session like at the end of a survey
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        global $thissurvey;
        \Survey::model()->resetCache();
        $thissurvey = getSurveyInfo(self::$surveyId, 'en', true);
        $_SESSION['responses_' . self::$surveyId] = ['srid' => self::$responseId, 's_lang' => 'en'];
        Yii::app()->setController(new DummyController('dummyid'));
        Yii::app()->setConfig('demoMode', false);
    }

    /**
     * Remove the survey session and restore the demo mode setting
     * @return void
     */
    protected function tearDown(): void
    {
        unset($_SESSION['responses_' . self::$surveyId]);
        Yii::app()->setConfig('demoMode', false);
        parent::tearDown();
    }

    /**
     * Resending a failed notification must not fail with an exception if its response was deleted
     * @return void
     */
    public function testResendWithDeletedResponse(): void
    {
        // In demo mode nothing is sent, the resend is reported as failed
        Yii::app()->setConfig('demoMode', true);
        $missingResponseId = self::$responseId + 1000;
        $resendVars = json_encode([
            'message_type' => 'html',
            'Subject' => 'Stored subject',
            'uniqueid' => 'abc',
            'boundary' => [1 => 'b1', 2 => 'b2', 3 => 'b3'],
            'MIMEBody' => 'Stored body',
        ]);
        $emails = [];
        foreach (['admin_notification', 'admin_responses'] as $index => $type) {
            $emails[$type][] = [
                'id' => 900000 + $index,
                'responseId' => $missingResponseId,
                'recipient' => 'resend@example.org',
                'language' => 'en',
                'resendVars' => $resendVars,
            ];
        }

        $result = sendSubmitNotifications(self::$surveyId, $emails, true);

        $this->assertSame(['successfullEmailCount' => 0, 'failedEmailCount' => 2], $result);
    }
}
