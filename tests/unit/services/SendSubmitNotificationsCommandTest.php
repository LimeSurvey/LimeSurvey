<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;
use LimeSurvey\Models\Services\SendSubmitNotificationsCommand;
use LimeSurvey\Models\Services\SessionInterface;
use LimeMailer;

class SendSubmitNotificationsCommandTest extends TestCase
{
    public function testGetEmailResponseTo()
    {
        $mailer = $this
            ->getMockBuilder(LimeMailer::class)
            ->getMock();
        $session = $this->getMockSession();
        $surveyinfo = [
            'htmlemail' => false
        ];
        $ssnc = new SendSubmitNotificationsCommand($surveyinfo, $mailer, $session);
    }

    private function getMockSession()
    {
        return new class implements SessionInterface {
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
        };
    }
}
