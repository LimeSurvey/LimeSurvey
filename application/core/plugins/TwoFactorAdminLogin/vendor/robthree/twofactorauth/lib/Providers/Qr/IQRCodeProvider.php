<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Qr;

interface IQRCodeProvider
{
    /**
     * Generate and return the QR code to embed in a web page
     *
     * @param string $qrText the value to encode in the QR code
     * @param int $size the desired size of the QR code
     *
     * @return string file contents of the QR code
     */
    public function getQRCodeImage(string $qrText, int $size): string;

    /**
     * Returns the appropriate mime type for the QR code
     * that will be generated
     */
    public function getMimeType(): string;
}
