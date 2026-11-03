<?php

declare(strict_types=1);

namespace Tests\Providers\Qr;

use RobThree\Auth\Providers\Qr\IQRCodeProvider;

class TestQrProvider implements IQRCodeProvider
{
    public function getQRCodeImage(string $qrText, int $size): string
    {
        return $qrText . '@' . $size;
    }

    public function getMimeType(): string
    {
        return 'test/test';
    }
}
