<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Qr;

/**
 * Use http://qrickit.com/qrickit_apps/qrickit_api.php to provide a QR code
 */
class QRicketProvider extends BaseHTTPQRCodeProvider
{
    public function __construct(public string $errorcorrectionlevel = 'L', public string $bgcolor = 'ffffff', public string $color = '000000', public string $format = 'p')
    {
        $this->verifyssl = false;
    }

    public function getMimeType(): string
    {
        switch (strtolower($this->format)) {
            case 'p':
                return 'image/png';
            case 'g':
                return 'image/gif';
            case 'j':
                return 'image/jpeg';
        }
        throw new QRException(sprintf('Unknown MIME-type: %s', $this->format));
    }

    public function getQRCodeImage(string $qrText, int $size): string
    {
        return $this->getContent($this->getUrl($qrText, $size));
    }

    public function getUrl(string $qrText, int $size): string
    {
        $queryParameters = array(
            'qrsize' => $size,
            'e' => strtolower($this->errorcorrectionlevel),
            'bgdcolor' => $this->bgcolor,
            'fgdcolor' => $this->color,
            't' => strtolower($this->format),
            'd' => $qrText,
        );

        return 'http://qrickit.com/api/qr?' . http_build_query($queryParameters);
    }
}
