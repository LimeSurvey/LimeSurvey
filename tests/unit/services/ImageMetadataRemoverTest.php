<?php

namespace ls\tests;

use LimeSurvey\Models\Services\ImageMetadataRemover;

/**
 * Tests the removal of metadata from uploaded images.
 */
class ImageMetadataRemoverTest extends TestBaseClass
{
    /** @var string Text used as metadata */
    private const METADATA = 'Test <b>"quoted"</b> & more';

    /** @var string 1x1 lossless WebP */
    private const WEBP_BASE64 = 'UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==';

    /** @var string 1x1 transparent PNG */
    private const PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6'
        . 'kgAAAABJRU5ErkJggg==';

    /**
     * EXIF and comments are removed from a JPEG, its orientation is kept and the image data stays the same.
     * @return void
     */
    public function testJpegMetadataIsRemovedAndOrientationKept()
    {
        $jpeg = $this->createJpeg();
        // Little-endian EXIF with orientation 6 (rotated 90°) and an image description
        $tiff = "II*\x00\x08\x00\x00\x00" . "\x02\x00"
            . "\x12\x01\x03\x00\x01\x00\x00\x00\x06\x00\x00\x00"
            . "\x0E\x01\x02\x00" . pack('V', strlen(self::METADATA) + 1) . "\x26\x00\x00\x00"
            . "\x00\x00\x00\x00" . self::METADATA . "\0";
        $exifSegment = "\xFF\xE1" . pack('n', strlen($tiff) + 8) . "Exif\0\0" . $tiff;
        $commentSegment = "\xFF\xFE" . pack('n', strlen(self::METADATA) + 2) . self::METADATA;
        $jpegWithMetadata = $this->insertAfterJfifSegment($jpeg, $exifSegment . $commentSegment);

        $result = (new ImageMetadataRemover())->removeMetadataFromString($jpegWithMetadata, IMAGETYPE_JPEG);

        $this->assertIsString($result);
        $this->assertStringNotContainsString(self::METADATA, $result);
        $this->assertSame(6, $this->readOrientation($result));
        $this->assertStringEndsWith(substr($jpeg, strpos($jpeg, "\xFF\xDA")), $result, 'The image data must stay the same.');
        $this->assertNotFalse(imagecreatefromstring($result));
    }

    /**
     * A JPEG without orientation doesn't get an EXIF segment.
     * @return void
     */
    public function testJpegWithoutOrientationGetsNoExif()
    {
        $result = (new ImageMetadataRemover())->removeMetadataFromString($this->createJpeg(), IMAGETYPE_JPEG);

        $this->assertIsString($result);
        $this->assertStringNotContainsString('Exif', $result);
        $this->assertNotFalse(imagecreatefromstring($result));
    }

    /**
     * Text and EXIF chunks are removed from a PNG.
     * @return void
     */
    public function testPngMetadataIsRemoved()
    {
        $png = base64_decode(self::PNG_BASE64);
        $chunks = '';
        foreach (['tEXt' => "Comment\0", 'eXIf' => 'MM'] as $type => $prefix) {
            $data = $prefix . self::METADATA;
            $chunks .= pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
        }
        // After the signature (8 bytes) and the IHDR chunk (25 bytes)
        $pngWithMetadata = substr($png, 0, 33) . $chunks . substr($png, 33);

        $result = (new ImageMetadataRemover())->removeMetadataFromString($pngWithMetadata, IMAGETYPE_PNG);

        $this->assertSame($png, $result);
    }

    /**
     * EXIF and XMP chunks are removed from a WebP, and their flags are cleared.
     * @return void
     */
    public function testWebpMetadataIsRemoved()
    {
        $imageChunk = substr(base64_decode(self::WEBP_BASE64), 12);
        $metadataChunks = '';
        foreach (['EXIF', 'XMP '] as $type) {
            $metadataChunks .= $type . pack('V', strlen(self::METADATA)) . self::METADATA
                . (strlen(self::METADATA) % 2 ? "\0" : '');
        }
        // Extended format: Flags with EXIF (0x08) and XMP (0x04) set, canvas 1 x 1 (stored minus one)
        $vp8x = 'VP8X' . pack('V', 10) . chr(0x0C) . "\0\0\0" . "\0\0\0" . "\0\0\0";
        $body = $vp8x . $imageChunk . $metadataChunks;
        $webpWithMetadata = 'RIFF' . pack('V', 4 + strlen($body)) . 'WEBP' . $body;

        $result = (new ImageMetadataRemover())->removeMetadataFromString($webpWithMetadata, IMAGETYPE_WEBP);

        $this->assertIsString($result);
        $this->assertStringNotContainsString(self::METADATA, $result);
        $this->assertSame(0, ord($result[20]) & 0x0C, 'The EXIF and XMP flags must be cleared.');
        $this->assertSame(strlen($result) - 8, unpack('V', substr($result, 4, 4))[1], 'The RIFF size must match.');
        $this->assertStringEndsWith($imageChunk, $result);
    }

    /**
     * Truncated or foreign files are reported as invalid.
     * @return void
     */
    public function testMalformedImagesAreRejected()
    {
        $remover = new ImageMetadataRemover();
        $jpeg = $this->createJpeg();

        $this->assertNull($remover->removeMetadataFromString(substr($jpeg, 0, 30), IMAGETYPE_JPEG));
        $this->assertNull($remover->removeMetadataFromString(substr(base64_decode(self::PNG_BASE64), 0, 40), IMAGETYPE_PNG));
        $this->assertNull($remover->removeMetadataFromString(self::METADATA, IMAGETYPE_WEBP));
        $this->assertNull($remover->removeMetadataFromString(base64_decode(self::PNG_BASE64), IMAGETYPE_JPEG));
        $this->assertNull($remover->removeMetadataFromString($jpeg, IMAGETYPE_GIF));
    }

    /**
     * Creates a small JPEG with GD.
     * @return string Content of the JPEG file
     */
    private function createJpeg(): string
    {
        $image = imagecreatetruecolor(30, 20);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 10, 10));
        ob_start();
        imagejpeg($image);
        return (string) ob_get_clean();
    }

    /**
     * Inserts segments after the JFIF APP0 segment of a JPEG.
     * @param string $jpeg Content of the JPEG file, starting with SOI and APP0
     * @param string $segments The segments to insert
     * @return string Content of the JPEG file with the segments
     */
    private function insertAfterJfifSegment(string $jpeg, string $segments): string
    {
        $app0End = 4 + unpack('n', substr($jpeg, 4, 2))[1];
        return substr($jpeg, 0, $app0End) . $segments . substr($jpeg, $app0End);
    }

    /**
     * Reads the EXIF orientation of a JPEG cleaned by ImageMetadataRemover, without needing the exif extension.
     * @param string $jpeg Content of the JPEG file
     * @return int|null The orientation, null if there is none
     */
    private function readOrientation(string $jpeg): ?int
    {
        // Big-endian orientation entry: Tag 0x0112, type SHORT, count 1, then the value
        $entryStart = strpos($jpeg, "\x01\x12\x00\x03\x00\x00\x00\x01");
        return $entryStart === false ? null : unpack('n', substr($jpeg, $entryStart + 8, 2))[1];
    }
}
