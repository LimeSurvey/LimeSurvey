<?php

declare(strict_types=1);

namespace Tests\Providers\Qr;

use PHPUnit\Framework\TestCase;
use RobThree\Auth\Algorithm;
use RobThree\Auth\Providers\Qr\HandlesDataUri;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;

class IQRCodeProviderTest extends TestCase
{
    use HandlesDataUri;

    public function testTotpUriIsCorrect(): void
    {
        $qr = new TestQrProvider();

        $tfa = new TwoFactorAuth('Test&Issuer', 6, 30, Algorithm::Sha1, $qr);
        $data = $this->DecodeDataUri($tfa->getQRCodeImageAsDataUri('Test&Label', 'VMR466AB62ZBOKHE'));
        $this->assertSame('test/test', $data['mimetype']);
        $this->assertSame('base64', $data['encoding']);
        $this->assertSame('otpauth://totp/Test%26Label?secret=VMR466AB62ZBOKHE&issuer=Test%26Issuer&period=30&algorithm=SHA1&digits=6@200', $data['data']);
    }

    public function testTotpUriIsCorrectNoIssuer(): void
    {
        $qr = new TestQrProvider();

        /**
         * The library specifies the issuer is null by default however in PHP 8.1
         * there is a deprecation warning for passing null as a string argument to rawurlencode
         */

        $tfa = new TwoFactorAuth(null, 6, 30, Algorithm::Sha1, $qr);
        $data = $this->DecodeDataUri($tfa->getQRCodeImageAsDataUri('Test&Label', 'VMR466AB62ZBOKHE'));
        $this->assertSame('test/test', $data['mimetype']);
        $this->assertSame('base64', $data['encoding']);
        $this->assertSame('otpauth://totp/Test%26Label?secret=VMR466AB62ZBOKHE&issuer=&period=30&algorithm=SHA1&digits=6@200', $data['data']);
    }

    public function testGetQRCodeImageAsDataUriThrowsOnInvalidSize(): void
    {
        $qr = new TestQrProvider();

        $tfa = new TwoFactorAuth('Test', 6, 30, Algorithm::Sha1, $qr);

        $this->expectException(TwoFactorAuthException::class);

        $tfa->getQRCodeImageAsDataUri('Test', 'VMR466AB62ZBOKHE', 0);
    }
}
