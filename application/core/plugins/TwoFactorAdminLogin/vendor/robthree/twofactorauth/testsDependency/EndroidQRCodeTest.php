<?php

declare(strict_types=1);

namespace TestsDependency;

use PHPUnit\Framework\TestCase;
use RobThree\Auth\Algorithm;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
use RobThree\Auth\Providers\Qr\HandlesDataUri;
use RobThree\Auth\TwoFactorAuth;

class EndroidQRCodeTest extends TestCase
{
    use HandlesDataUri;

    public function testDependency(): void
    {
        $qr = new EndroidQrCodeProvider();
        $tfa = new TwoFactorAuth('Test&Issuer', 6, 30, Algorithm::Sha1, $qr);
        $data = $this->DecodeDataUri($tfa->getQRCodeImageAsDataUri('Test&Label', 'VMR466AB62ZBOKHE'));
        $this->assertSame('image/png', $data['mimetype']);
        $this->assertSame('base64', $data['encoding']);
        $this->assertNotEmpty($data['data']);
    }
}
