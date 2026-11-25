<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SubmitNotificationSender;
use Yii;

/**
 * Tests for the admin notification emails sent by SubmitNotificationSender
 */
class SubmitNotificationSenderTest extends TestBaseClass
{
    /** @var int ID of the response the notifications are sent for */
    private static $responseId;

    /** @var int How often the response was loaded from the database by the last captureNotifications() call */
    private $responseLoads = 0;

    /** @var array Survey info passed to the sender */
    private $surveyInfo;

    /** @var mixed Debug setting before the test */
    private $originalDebug;

    /** @var mixed Logged in user before the test */
    private $originalLoginId;

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
     * Prepare the session like at the end of a survey
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        global $thissurvey;
        \Survey::model()->resetCache();
        $this->surveyInfo = getSurveyInfo(self::$surveyId, 'en', true);
        // Only the deprecated sendSubmitNotifications() reads the global
        $thissurvey = null;
        $_SESSION['responses_' . self::$surveyId] = ['srid' => self::$responseId, 's_lang' => 'en'];
        Yii::app()->setController(new DummyController('dummyid'));
        Yii::app()->setConfig('demoMode', false);
        $this->originalDebug = Yii::app()->getConfig('debug');
        $this->originalLoginId = Yii::app()->session['loginID'];
        Yii::app()->setConfig('debug', 0);
    }

    /**
     * Remove the survey session and the stored failed emails, restore the changed settings
     * @return void
     */
    protected function tearDown(): void
    {
        unset($_SESSION['responses_' . self::$surveyId]);
        \FailedEmail::model()->deleteAllByAttributes(['surveyid' => self::$surveyId]);
        Yii::app()->setConfig('demoMode', false);
        Yii::app()->setConfig('debug', $this->originalDebug);
        Yii::app()->session['loginID'] = $this->originalLoginId;
        parent::tearDown();
    }

    /**
     * Run the given send without really sending the emails.
     * Counts in $responseLoads how often the response was loaded, which happens once for each answer table built.
     * @param callable $send Sends the notifications
     * @param string|null $error If set, every email fails with this error
     * @return array[] The emails that would have been sent: Type, recipient and body
     */
    private function captureNotifications(callable $send, ?string $error = null): array
    {
        $pluginManager = App()->getPluginManager();
        $listener = new class ($pluginManager, 'notificationListener') extends \PluginBase {
            /** @var array[] Type, recipient and body of each email */
            public $emails = [];

            /** @var string|null Error to report for each email */
            public $error = null;

            /**
             * Records the email and marks it as sent, or as failed if an error is set
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
                $event->set('error', $this->error);
            }
        };
        $listener->error = $error;
        $this->responseLoads = 0;
        $countResponseLoad = function () {
            $this->responseLoads++;
        };
        $responseModel = \SurveyDynamic::model(self::$surveyId);
        $responseModel->attachEventHandler('onBeforeFind', $countResponseLoad);
        $pluginManager->subscribe($listener, 'beforeSurveyEmail');
        try {
            $send();
        } finally {
            $pluginManager->unsubscribe($listener, 'beforeSurveyEmail');
            $responseModel->detachEventHandler('onBeforeFind', $countResponseLoad);
        }
        return $listener->emails;
    }

    /**
     * Send the notifications of the current response with the sender
     * @param array|null $result Set to the counts returned by the sender
     * @param string|null $error If set, every email fails with this error
     * @return array[] The emails that would have been sent: Type, recipient and body
     */
    private function captureSend(?array &$result = null, ?string $error = null): array
    {
        return $this->captureNotifications(
            function () use (&$result) {
                $sender = new SubmitNotificationSender(self::$surveyId);
                $result = $sender->sendForCurrentResponse($this->surveyInfo);
            },
            $error
        );
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
     * Get failed notifications like FailedEmailController::actionResend() passes them to the sender
     * @param int $responseId
     * @return array
     */
    private function getFailedEmails(int $responseId): array
    {
        $resendVars = json_encode([
            'message_type' => 'html',
            'Subject' => 'Stored subject',
            'uniqueid' => 'abc',
            'boundary' => [1 => 'b1', 2 => 'b2', 3 => 'b3'],
            'MIMEBody' => 'Stored body',
        ]);
        $emails = [];
        $types = [SubmitNotificationSender::TYPE_BASIC, SubmitNotificationSender::TYPE_DETAILED];
        foreach ($types as $index => $type) {
            $emails[$type][] = [
                'id' => 900000 + $index,
                'responseId' => $responseId,
                'recipient' => 'resend@example.org',
                'language' => 'en',
                'resendVars' => $resendVars,
            ];
        }
        return $emails;
    }

    /**
     * Every recipient of the detailed notification gets the answer table, the basic notification
     * (whose template does not use it) is sent without it. The table is built only once (issue #18563).
     * @return void
     */
    public function testAnswerTableBuiltOnceForAllRecipients(): void
    {
        $this->setBasicNotificationBody('New response: {VIEWRESPONSEURL}');
        $emails = $this->captureSend($result);

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
                $this->assertStringContainsString('responses/view', $email['body']);
            }
        }
        $this->assertSame($emails[2]['body'], $emails[3]['body']);
        $this->assertSame(1, $this->responseLoads, 'The answer table must be built only once for all recipients.');
        $this->assertSame(['successfullEmailCount' => 4, 'failedEmailCount' => 0], $result);
        $this->assertEquals(0, \FailedEmail::model()->countByAttributes(['surveyid' => self::$surveyId]));
    }

    /**
     * A basic notification template using {ANSWERTABLE} still gets the answer table (issue #18507)
     * @return void
     */
    public function testAnswerTableInBasicNotification(): void
    {
        $this->setBasicNotificationBody('New response: {ANSWERTABLE}');
        $emails = $this->captureSend();

        $this->assertCount(4, $emails);
        foreach ($emails as $email) {
            $this->assertStringContainsString("<table class='printouttable'", $email['body']);
            $this->assertStringContainsString('printanswersanswertext', $email['body']);
        }
        $this->assertSame(1, $this->responseLoads, 'The answer table must be built only once for all recipients.');
    }

    /**
     * Nothing is sent if the survey has no notification recipients
     * @return void
     */
    public function testNoRecipients(): void
    {
        $this->surveyInfo['emailnotificationto'] = '';
        $this->surveyInfo['emailresponseto'] = '';
        $emails = $this->captureSend($result);

        $this->assertSame([], $emails);
        $this->assertSame(0, $this->responseLoads, 'No answer table is needed without recipients.');
        $this->assertSame(['successfullEmailCount' => 0, 'failedEmailCount' => 0], $result);
    }

    /**
     * Notifications that could not be sent are counted and stored for a later resend, one entry for each
     * recipient and type. Without debug mode the error is not shown.
     * @return void
     */
    public function testFailedNotificationsAreStored(): void
    {
        ob_start();
        try {
            $emails = $this->captureSend($result, 'Mail server not reachable');
        } finally {
            $output = ob_get_clean();
        }

        $this->assertCount(4, $emails);
        $this->assertSame(['successfullEmailCount' => 0, 'failedEmailCount' => 4], $result);
        $this->assertSame('', $output);

        $failedEmails = \FailedEmail::model()->findAllByAttributes(['surveyid' => self::$surveyId], ['order' => 'id']);
        $stored = [];
        foreach ($failedEmails as $failedEmail) {
            $stored[] = [$failedEmail->email_type, $failedEmail->recipient];
            $this->assertEquals(self::$responseId, $failedEmail->responseid);
            $this->assertSame('en', $failedEmail->language);
            $this->assertSame(\FailedEmail::STATE_FAILED, $failedEmail->status);
            $this->assertSame('Mail server not reachable', $failedEmail->error_message);
            $this->assertIsArray(json_decode($failedEmail->resend_vars, true));
        }
        $this->assertSame(
            [
                ['admin_notification', 'basic1@example.org'],
                ['admin_notification', 'basic2@example.org'],
                ['admin_responses', 'detailed1@example.org'],
                ['admin_responses', 'detailed2@example.org'],
            ],
            $stored
        );
    }

    /**
     * In debug mode, a user who may update the survey settings sees why each notification failed
     * @return void
     */
    public function testSendErrorShownInDebugMode(): void
    {
        Yii::app()->setConfig('debug', 2);
        Yii::app()->session['loginID'] = 1;
        ob_start();
        try {
            $this->captureSend($result, 'Mail server not reachable');
        } finally {
            $output = ob_get_clean();
        }

        $this->assertSame(
            2,
            substr_count($output, 'Basic admin notification could not be sent because of error: Mail server not reachable')
        );
        $this->assertSame(
            2,
            substr_count($output, 'Detailed admin notification could not be sent because of error: Mail server not reachable')
        );
    }

    /**
     * Resending a failed notification must not fail with an exception if its response was deleted
     * @return void
     */
    public function testResendWithDeletedResponse(): void
    {
        // In demo mode nothing is sent, the resend is reported as failed
        Yii::app()->setConfig('demoMode', true);
        $emails = $this->getFailedEmails(self::$responseId + 1000);

        $result = (new SubmitNotificationSender(self::$surveyId))->resend($emails);

        $this->assertSame(['successfullEmailCount' => 0, 'failedEmailCount' => 2], $result);
    }

    /**
     * The deprecated sendSubmitNotifications() still sends the notifications of the current response
     * with the survey info from the global, and resends failed notifications
     * @return void
     */
    public function testDeprecatedHelperFunction(): void
    {
        global $thissurvey;
        // Without the survey info global there are no recipients
        $emails = $this->captureNotifications(function () use (&$result) {
            $result = sendSubmitNotifications(self::$surveyId, [], true);
        });
        $this->assertSame([], $emails);
        $this->assertSame(['successfullEmailCount' => 0, 'failedEmailCount' => 0], $result);

        $thissurvey = $this->surveyInfo;
        $emails = $this->captureNotifications(function () use (&$result) {
            $result = sendSubmitNotifications(self::$surveyId, [], true);
        });
        $this->assertCount(4, $emails);
        $this->assertSame(['successfullEmailCount' => 4, 'failedEmailCount' => 0], $result);

        $emails = $this->captureNotifications(function () use (&$result) {
            $result = sendSubmitNotifications(self::$surveyId);
        });
        $this->assertCount(4, $emails);
        $this->assertNull($result);

        Yii::app()->setConfig('demoMode', true);
        $result = sendSubmitNotifications(self::$surveyId, $this->getFailedEmails(self::$responseId), true);
        $this->assertSame(['successfullEmailCount' => 0, 'failedEmailCount' => 2], $result);
    }
}
