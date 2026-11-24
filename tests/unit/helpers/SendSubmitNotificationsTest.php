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

    /** @var int How often the response was loaded from the database by the last captureNotifications() call */
    private $responseLoads = 0;

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
     * Run sendSubmitNotifications() for the current response without really sending the emails.
     * Counts in $responseLoads how often the response was loaded, which happens once for each answer table built.
     * @return array[] The emails that would have been sent: Type, recipient and body
     */
    private function captureNotifications(): array
    {
        $pluginManager = App()->getPluginManager();
        $listener = new class ($pluginManager, 'notificationListener') extends \PluginBase {
            /** @var array[] Type, recipient and body of each email */
            public $emails = [];

            /**
             * Records the email and marks it as sent
             * @return void
             */
            public function beforeSurveyEmail()
            {
                $event = $this->getEvent();
                $this->emails[] = [
                    'type' => $event->get('type'),
                    'to' => $event->get('to')[0][0],
                    'body' => $event->get('body'),
                ];
                $event->set('send', false);
                $event->set('error', null);
            }
        };
        $this->responseLoads = 0;
        $countResponseLoad = function () {
            $this->responseLoads++;
        };
        $responseModel = \SurveyDynamic::model(self::$surveyId);
        $responseModel->attachEventHandler('onBeforeFind', $countResponseLoad);
        $pluginManager->subscribe($listener, 'beforeSurveyEmail');
        try {
            sendSubmitNotifications(self::$surveyId);
        } finally {
            $pluginManager->unsubscribe($listener, 'beforeSurveyEmail');
            $responseModel->detachEventHandler('onBeforeFind', $countResponseLoad);
        }
        return $listener->emails;
    }

    /**
     * Set the body of the basic admin notification template
     * @param string $body
     * @return void
     */
    private function setBasicNotificationBody(string $body): void
    {
        $languageSetting = \SurveyLanguageSetting::model()->findByPk([
            'surveyls_survey_id' => self::$surveyId,
            'surveyls_language' => 'en',
        ]);
        $languageSetting->email_admin_notification = $body;
        $languageSetting->save(false);
    }

    /**
     * Every recipient of the detailed notification gets the answer table, the basic notification
     * (whose template does not use it) is sent without it. The table is built only once (issue #18563).
     * @return void
     */
    public function testAnswerTableBuiltOnceForAllRecipients(): void
    {
        $this->setBasicNotificationBody('New response: {VIEWRESPONSEURL}');
        $emails = $this->captureNotifications();

        $this->assertSame(
            ['basic1@example.org', 'basic2@example.org', 'detailed1@example.org', 'detailed2@example.org'],
            array_column($emails, 'to')
        );
        foreach ($emails as $email) {
            if ($email['type'] === 'admin_responses') {
                $this->assertStringContainsString("<table class='printouttable'", $email['body']);
                $this->assertStringContainsString('printanswersanswertext', $email['body']);
            } else {
                $this->assertSame('admin_notification', $email['type']);
                $this->assertStringNotContainsString('printouttable', $email['body']);
            }
        }
        $this->assertSame($emails[2]['body'], $emails[3]['body']);
        $this->assertSame(1, $this->responseLoads, 'The answer table must be built only once for all recipients.');
    }

    /**
     * A basic notification template using {ANSWERTABLE} still gets the answer table (issue #18507)
     * @return void
     */
    public function testAnswerTableInBasicNotification(): void
    {
        $this->setBasicNotificationBody('New response: {ANSWERTABLE}');
        $emails = $this->captureNotifications();

        $this->assertCount(4, $emails);
        foreach ($emails as $email) {
            $this->assertStringContainsString("<table class='printouttable'", $email['body']);
            $this->assertStringContainsString('printanswersanswertext', $email['body']);
        }
        $this->assertSame(1, $this->responseLoads, 'The answer table must be built only once for all recipients.');
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
