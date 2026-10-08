<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Qr;

/**
 * Use https://image-charts.com to provide a QR code
 */
class ImageChartsQRCodeProvider extends BaseHTTPQRCodeProvider
{
    public function __construct(protected bool $verifyssl = false, public string $errorcorrectionlevel = 'L', public int $margin = 1)
    {
    }

    public function getMimeType(): string
    {
        return 'image/png';
    }

    public function getQRCodeImage(string $qrText, int $size): string
    {
        return $this->getContent($this->getUrl($qrText, $size));
    }

    public function getUrl(string $qrText, int $size): string
    {
        $queryParameters = array(
            'cht' => 'qr',
            'chs' => ceil($size / 2) . 'x' . ceil($size / 2),
            'chld' => $this->errorcorrectionlevel . '|' . $this->margin,
            'chl' => $qrText,
        );

        return 'https://image-charts.com/chart?' . http_build_query($queryParameters);
    }
}
