<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Qr;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use RuntimeException;

class BaconQrCodeProvider implements IQRCodeProvider
{
    /**
     * Ensure we using the latest Bacon QR Code and specify default options
     */
    public function __construct(
        private readonly int $borderWidth = 4,
        private string|array $backgroundColour = '#ffffff',
        private string|array $foregroundColour = '#000000',
        private string       $format = 'png',
    ) {
        $this->backgroundColour = $this->handleColour($this->backgroundColour);
        $this->foregroundColour = $this->handleColour($this->foregroundColour);
        $this->format = strtolower($this->format);
    }

    public function getMimeType(): string
    {
        switch ($this->format) {
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

        throw new RuntimeException(sprintf('Unknown MIME-type: %s', $this->format));
    }

    public function getQRCodeImage(string $qrText, int $size): string
    {
        $backend = match ($this->format) {
            'svg' => new SvgImageBackEnd(),
            'eps' => new EpsImageBackEnd(),
            default => new ImagickImageBackEnd($this->format),
        };

        $output = $this->getQRCodeByBackend($qrText, $size, $backend);

        if ($this->format === 'svg') {
            $svg = explode("\n", $output);
            return $svg[1];
        }

        return $output;
    }

    /**
     * Abstract QR code generation function
     * providing colour changing support
     */
    private function getQRCodeByBackend($qrText, $size, ImageBackEndInterface $backend)
    {
        $rendererStyleArgs = array($size, $this->borderWidth);

        if (is_array($this->foregroundColour) && is_array($this->backgroundColour)) {
            $rendererStyleArgs = array(...$rendererStyleArgs, ...array(
                null,
                null,
                Fill::withForegroundColor(
                    new Rgb(...$this->backgroundColour),
                    new Rgb(...$this->foregroundColour),
                    new EyeFill(null, null),
                    new EyeFill(null, null),
                    new EyeFill(null, null)
                ),
            ));
        }

        $writer = new Writer(new ImageRenderer(
            new RendererStyle(...$rendererStyleArgs),
            $backend
        ));

        return $writer->writeString($qrText);
    }

    /**
     * Ensure colour is an array of three values but also
     * accept a string and assume its a 3 or 6 character hex
     */
    private function handleColour(array|string $colour): array|string
    {
        if (is_string($colour) && $colour[0] == '#') {
            $hexToRGB = static function ($input) {
                // ensure input no longer has a # for more predictable division
                // PHP 8.1 does not like implicitly casting a float to an int
                $input = trim($input, '#');

                if (strlen($input) != 3 && strlen($input) != 6) {
                    throw new RuntimeException('Colour should be a 3 or 6 character value after the #');
                }

                // split the array into three chunks
                $split = str_split($input, strlen($input) / 3);

                // cope with three character hex reference
                if (strlen($input) == 3) {
                    array_walk($split, static function (&$character) {
                        $character = str_repeat($character, 2);
                    });
                }

                // convert hex to rgb
                return array_map('hexdec', $split);
            };

            return $hexToRGB($colour);
        }

        if (is_array($colour) && count($colour) == 3) {
            return $colour;
        }

        throw new RuntimeException('Invalid colour value');
    }
}
