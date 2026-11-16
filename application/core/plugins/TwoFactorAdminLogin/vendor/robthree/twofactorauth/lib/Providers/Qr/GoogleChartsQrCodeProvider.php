<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Qr;

// https://developers.google.com/chart/infographics/docs/qr_codes
class GoogleChartsQrCodeProvider extends BaseHTTPQRCodeProvider
{
    public function __construct(protected bool $verifyssl = false, public string $errorcorrectionlevel = 'L', public int $margin = 4, public string $encoding = 'UTF-8')
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
            'chs' => $size . 'x' . $size,
            'chld' => strtoupper($this->errorcorrectionlevel) . '|' . $this->margin,
            'cht' => 'qr',
            'choe' => $this->encoding,
            'chl' => $qrText,
        );

        return 'https://chart.googleapis.com/chart?' . http_build_query($queryParameters);
    }
}
