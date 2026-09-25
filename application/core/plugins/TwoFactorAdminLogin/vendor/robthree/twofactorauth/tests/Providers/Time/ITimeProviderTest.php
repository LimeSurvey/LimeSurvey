<?php

declare(strict_types=1);

namespace Tests\Providers\Time;

use PHPUnit\Framework\TestCase;
use RobThree\Auth\Algorithm;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;

class ITimeProviderTest extends TestCase
{
    public function testEnsureCorrectTimeDoesNotThrowForCorrectTime(): void
    {
        $this->expectNotToPerformAssertions();
        $tpr1 = new TestTimeProvider(123);
        $tpr2 = new TestTimeProvider(128);

        $tfa = new TwoFactorAuth('Test', 6, 30, Algorithm::Sha1, null, null, $tpr1);
        $tfa->ensureCorrectTime(array($tpr2));   // 128 - 123 = 5 => within default leniency
    }

    public function testEnsureCorrectTimeThrowsOnIncorrectTime(): void
    {
        $tpr1 = new TestTimeProvider(123);
        $tpr2 = new TestTimeProvider(124);

        $tfa = new TwoFactorAuth('Test', 6, 30, Algorithm::Sha1, null, null, $tpr1);

        $this->expectException(TwoFactorAuthException::class);

        $tfa->ensureCorrectTime(array($tpr2), 0);    // We force a leniency of 0, 124-123 = 1 so this should throw
    }

    public function testEnsureDefaultTimeProviderReturnsCorrectTime(): void
    {
        $this->expectNotToPerformAssertions();
        $tfa = new TwoFactorAuth('Test', 6, 30, Algorithm::Sha1);
        $tfa->ensureCorrectTime(array(new TestTimeProvider(time())), 1);    // Use a leniency of 1, should the time change between both time() calls
    }
}
