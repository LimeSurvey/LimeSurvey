<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Qr;

/**
 * Use http://goqr.me/api/doc/create-qr-code/ to get QR code
 */
class QRServerProvider extends BaseHTTPQRCodeProvider
{
    public function __construct(protected bool $verifyssl = false, public string $errorcorrectionlevel = 'L', public int $margin = 4, public int $qzone = 1, public string $bgcolor = 'ffffff', public string $color = '000000', public string $format = 'png')
    {
    }

    public function getMimeType(): string
    {
        switch (strtolower($this->format)) {
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'svg':
                return 'image/svg+xml';
            case 'eps':
                return 'application/postscript';
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
            'size' => $size . 'x' . $size,
            'ecc' => strtoupper($this->errorcorrectionlevel),
            'margin' => $this->margin,
            'qzone' => $this->qzone,
            'bgcolor' => $this->decodeColor($this->bgcolor),
            'color' => $this->decodeColor($this->color),
            'format' => strtolower($this->format),
            'data' => $qrText,
        );

        return 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query($queryParameters);
    }

    private function decodeColor(string $value): string
    {
        return vsprintf('%d-%d-%d', sscanf($value, '%02x%02x%02x'));
    }
}
