<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;
use LimeSurvey\Models\Services\SendSubmitNotificationsCommand;
use LimeSurvey\Models\Services\SessionInterface;
use LimeMailer;
use Yii;
use UserManagementController;

// Needed because Session is final class and can't be mocked
class DummySession implements SessionInterface {
    public function get(string $key, $default = null) {}
    public function set(string $key, $value): void {}
    public function close(): void {}
    public function open(): void {}
    public function isActive(): bool {}
    public function getId(): ?string {}
    public function setId(string $sessionId): void {}
    public function regenerateId(): void {}
    public function discard(): void {}
    public function getName(): string {}
    public function all(): array {}
    public function remove(string $key): void {}
    public function has(string $key): bool {}
    public function pull(string $key, $default = null) {}
    public function clear(): void {}
    public function destroy(): void {}
    public function getCookieParameters(): array {}
}

class SendSubmitNotificationsCommandTest extends TestCase
{
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
    }

    public function testGetEmailResponseToEmpty()
    {
        $mailer = $this
            ->getMockBuilder(LimeMailer::class)
            ->getMock();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail' => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getEmailResponseTo([]);
        $this->assertEquals([], $result);
    }

    public function testGetEmailResponseToOneEmail()
    {
        $mailer = new LimeMailer();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail'       => false,
            'emailresponseto' => 'moo@moo.moo',
            'adminemail'      => '',
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $emails = [];
        $result = $ssnc->getEmailResponseTo($emails);
        $this->assertEquals(['moo@moo.moo'], $result);
    }

    public function testGetEmailResponseToTwoEmail()
    {
        $mailer = new LimeMailer();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail'       => false,
            'emailresponseto' => 'moo@moo.moo;foo@foo.foo',
            'adminemail'      => '',
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $emails = [];
        $result = $ssnc->getEmailResponseTo($emails);
        $this->assertEquals(['moo@moo.moo', 'foo@foo.foo'], $result);
    }

    public function testGetEmailNotificationToEmpty()
    {
        $mailer = $this
            ->getMockBuilder(LimeMailer::class)
            ->getMock();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail' => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getEmailNotificationTo([]);
        $this->assertEquals([], $result);
    }

    public function testGetEmailNotificationToOneEmail()
    {
        $mailer = new LimeMailer();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail'       => false,
            'emailnotificationto' => 'moo@moo.moo',
            'adminemail'      => '',
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $emails = [];
        $result = $ssnc->getEmailNotificationTo($emails);
        $this->assertEquals(['moo@moo.moo'], $result);
    }

    public function testGetEmailNotificationToTwoEmail()
    {
        $mailer = new LimeMailer();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail'       => false,
            'emailnotificationto' => 'moo@moo.moo;foo@foo.foo',
            'adminemail'      => '',
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $emails = [];
        $result = $ssnc->getEmailNotificationTo($emails);
        $this->assertEquals(['moo@moo.moo', 'foo@foo.foo'], $result);
    }

    public function testGetResponseIdNull()
    {
        $mailer = new LimeMailer();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail'       => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getResponseId();
        $this->assertNull($result);
    }

    public function testGetResponseIdSession()
    {
        $mailer = new LimeMailer();
        $session = $this->getMockBuilder(DummySession::class)
            ->setMethods(['get'])
            ->getMock();
        $session->method('get')->willReturn(['srid' => 1]);
        $surveyinfo = [
            'htmlemail'       => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getResponseId();
        $this->assertEquals(1, $result);
    }

    public function testGetReplacementVarsNoResponseId()
    {
        $mailer = new LimeMailer();
        $session = $this->getMockBuilder(DummySession::class)
            ->getMock();
        $surveyinfo = [
            'htmlemail' => false,
            'sid'       => 0
        ];
        $controller = $this->getMockBuilder(UserManagementController::class)
            ->disableOriginalConstructor()
            ->setMethods(['createAbsoluteUrl'])
            ->getMock();
        $controller->method('createAbsoluteUrl')->willReturn('absolute/dummy/url');
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getReplacementVars(null, $controller);
        $this->assertEquals(
            [
                'STATISTICSURL' => 'absolute/dummy/url',
                'ANSWERTABLE'   => ''
            ],
            $result
        );
    }

    public function testGetReplacementVarsResponseId()
    {
        $mailer = new LimeMailer();
        $session = $this->getMockBuilder(DummySession::class)
            ->getMock();
        $surveyinfo = [
            'htmlemail' => false,
            'sid'       => 0
        ];
        $controller = $this->getMockBuilder(UserManagementController::class)
            ->disableOriginalConstructor()
            ->setMethods(['createAbsoluteUrl'])
            ->getMock();
        // TODO: createAbsoluteUrl depends on global request object, so must mock it?
        $controller->method('createAbsoluteUrl')->willReturn('absolute/dummy/url');
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getReplacementVars(1, $controller);
        $this->assertEquals(
            [
                'STATISTICSURL' => 'absolute/dummy/url',
                'ANSWERTABLE'   => '',
                'EDITRESPONSEURL' => 'absolute/dummy/url',
                'VIEWRESPONSEURL' => 'absolute/dummy/url'

            ],
            $result
        );
    }

    public function testGetLanguageDefault()
    {
        $mailer  = $this->getMockBuilder(LimeMailer::class)->getMock();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail' => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getLanguage(App());
        $this->assertEquals('en_us', $result);
    }

    public function testGetLanguageSession()
    {
        $mailer  = $this->getMockBuilder(LimeMailer::class)->getMock();
        $session = $this
            ->getMockBuilder(DummySession::class)
            ->setMethods(['get'])
            ->getMock();
        $session->method('get')->willReturn(['s_lang' => 'de']);
        $surveyinfo = [
            'htmlemail' => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getLanguage(App());
        $this->assertEquals('de', $result);
    }

    public function testGetQuestionAttributeValueEmpty()
    {
        $mailer  = $this->getMockBuilder(LimeMailer::class)->getMock();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail' => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getQuestionAttributeValue([], '');
        $this->assertEquals('', $result);
    }

    public function testGetQuestionAttributeValueSimple()
    {
        $mailer  = $this->getMockBuilder(LimeMailer::class)->getMock();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail' => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getQuestionAttributeValue(['moo' => 10], 'moo');
        $this->assertEquals(10, $result);
    }

    public function testGetQuestionAttributeValueLang()
    {
        $mailer  = $this->getMockBuilder(LimeMailer::class)->getMock();
        $session = $this->getMockBuilder(DummySession::class)->getMock();
        $surveyinfo = [
            'htmlemail' => false,
            'sid' => 0
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
        $result = $ssnc->getQuestionAttributeValue(['moo' => ['de' => 8]], 'moo', 'de');
        $this->assertEquals(8, $result);
    }
}
