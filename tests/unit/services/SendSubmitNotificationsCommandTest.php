<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;
use LimeSurvey\Models\Services\SendSubmitNotificationsCommand;
use LimeSurvey\Models\Services\Session;

class SendSubmitNotificationsCommandTest extends TestCase
{
    public function testGetEmailResponseTo()
    {
        $mailer = $this
            ->getMockBuilder(LimeMailer::class)
            ->getMock();
        $session = $this
            ->getMockBuilder(Session::class)
            ->getMock();
        $ssnc = new SendSubmitNotificationsCommand([], $mailer, null);
    }
}
