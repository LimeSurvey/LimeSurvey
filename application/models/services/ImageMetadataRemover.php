<?php

namespace LimeSurvey\Models\Services;

/**
 * Removes metadata (EXIF, XMP, IPTC, comments and text chunks) from PNG, JPEG and WebP files.
 *
 * The image data itself is copied byte for byte: Nothing is re-encoded, so the quality stays the same and no
 * memory is needed to decode large images. Colour profiles are kept, because removing them changes the colours.
 * The orientation of a JPEG is kept as the only EXIF value, so photos taken with a rotated camera stay upright.
 */
class ImageMetadataRemover
{
    /** @var string[] PNG chunks with metadata */
    private const PNG_METADATA_CHUNKS = ['eXIf', 'tEXt', 'zTXt', 'iTXt', 'tIME'];

    /** @var string[] WebP chunks with metadata */
    private const WEBP_METADATA_CHUNKS = ['EXIF', 'XMP '];

    /**
     * Removes the metadata of an image file, overwriting the file.
     *
     * @param string $path Path of the image file
     * @param int $imageType Type of the image, one of the IMAGETYPE_PNG, IMAGETYPE_JPEG or IMAGETYPE_WEBP constants
     * @return bool True if the file has no metadata left, false if it couldn't be read, parsed or written
     */
    public function removeMetadata(string $path, int $imageType): bool
    {
        $data = @file_get_contents($path);
        if ($data === false) {
            return false;
        }
        $cleanData = $this->removeMetadataFromString($data, $imageType);
        if ($cleanData === null) {
            return false;
        }
        if ($cleanData === $data) {
            return true;
        }
        return @file_put_contents($path, $cleanData) === strlen($cleanData);
    }

    /**
     * Removes the metadata from the content of an image file.
     *
     * @param string $data Content of the image file
     * @param int $imageType Type of the image, one of the IMAGETYPE_PNG, IMAGETYPE_JPEG or IMAGETYPE_WEBP constants
     * @return string|null The content without metadata, null if the content isn't a well-formed image of that type
     */
    public function removeMetadataFromString(string $data, int $imageType): ?string
    {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return $this->removeJpegMetadata($data);
            case IMAGETYPE_PNG:
                return $this->removePngMetadata($data);
            case IMAGETYPE_WEBP:
                return $this->removeWebpMetadata($data);
            default:
                return null;
        }
    }

    /**
     * Removes the APP1 (EXIF, XMP), APP13 (IPTC) and comment segments of a JPEG file.
     * A rotated image gets a minimal EXIF segment with only its orientation.
     *
     * @param string $data Content of the JPEG file
     * @return string|null The content without metadata, null if the file isn't a well-formed JPEG
     */
    private function removeJpegMetadata(string $data): ?string
    {
        $length = strlen($data);
        if ($length < 4 || substr($data, 0, 2) !== "\xFF\xD8") {
            return null;
        }
        $segments = [];
        $orientation = null;
        $position = 2;
        while ($position + 4 <= $length) {
            if ($data[$position] !== "\xFF") {
                return null;
            }
            $marker = ord($data[$position + 1]);
            if ($marker === 0xFF) {
                // Fill byte before a marker
                $position++;
                continue;
            }
            if ($marker === 0xD8 || $marker === 0x01 || ($marker >= 0xD0 && $marker <= 0xD7)) {
                // Markers without a length
                $segments[] = substr($data, $position, 2);
                $position += 2;
                continue;
            }
            if ($marker === 0xDA) {
                // Start of scan: The rest of the file is image data
                $segments[] = substr($data, $position);
                return $this->buildJpeg($segments, $orientation);
            }
            $segmentLength = unpack('n', substr($data, $position + 2, 2))[1];
            if ($segmentLength < 2 || $position + 2 + $segmentLength > $length) {
                return null;
            }
            $segment = substr($data, $position, 2 + $segmentLength);
            $position += 2 + $segmentLength;
            if ($marker === 0xE1) {
                if (substr($segment, 4, 6) === "Exif\0\0") {
                    $orientation = $orientation ?? $this->readExifOrientation(substr($segment, 10));
                }
                continue;
            }
            if ($marker === 0xED || $marker === 0xFE) {
                continue;
            }
            $segments[] = $segment;
        }
        return null;
    }

    /**
     * Joins the kept JPEG segments, with an orientation-only EXIF segment for rotated images.
     *
     * @param string[] $segments The kept segments, each starting with its marker
     * @param int|null $orientation EXIF orientation of the image, null if it had none
     * @return string The content of the JPEG file
     */
    private function buildJpeg(array $segments, ?int $orientation): string
    {
        if ($orientation !== null && $orientation > 1 && $orientation <= 8) {
            // A JFIF APP0 segment has to stay the first one, the EXIF segment follows it
            $insertAt = isset($segments[0]) && substr($segments[0], 0, 2) === "\xFF\xE0" ? 1 : 0;
            array_splice($segments, $insertAt, 0, [$this->buildOrientationSegment($orientation)]);
        }
        return "\xFF\xD8" . implode('', $segments);
    }

    /**
     * Builds an EXIF APP1 segment that contains only the orientation.
     *
     * @param int $orientation EXIF orientation, 2 to 8
     * @return string The segment, starting with its marker
     */
    private function buildOrientationSegment(int $orientation): string
    {
        $payload = "Exif\0\0"
            // Big-endian TIFF header, first IFD at offset 8
            . "MM\x00\x2A\x00\x00\x00\x08"
            // One entry: Tag 0x0112 (orientation), type SHORT, count 1, value
            . "\x00\x01"
            . "\x01\x12\x00\x03\x00\x00\x00\x01" . pack('n', $orientation) . "\x00\x00"
            // No next IFD
            . "\x00\x00\x00\x00";
        return "\xFF\xE1" . pack('n', strlen($payload) + 2) . $payload;
    }

    /**
     * Reads the orientation from the TIFF structure of an EXIF segment.
     *
     * @param string $tiff The EXIF data after the "Exif\0\0" header
     * @return int|null The orientation, null if there is none or the data is malformed
     */
    private function readExifOrientation(string $tiff): ?int
    {
        $byteOrder = substr($tiff, 0, 2);
        if ($byteOrder === 'II') {
            $short = 'v';
            $long = 'V';
        } elseif ($byteOrder === 'MM') {
            $short = 'n';
            $long = 'N';
        } else {
            return null;
        }
        if (strlen($tiff) < 8) {
            return null;
        }
        $ifdOffset = unpack($long, substr($tiff, 4, 4))[1];
        if ($ifdOffset + 2 > strlen($tiff)) {
            return null;
        }
        $entryCount = unpack($short, substr($tiff, $ifdOffset, 2))[1];
        for ($entry = 0; $entry < $entryCount; $entry++) {
            $entryOffset = $ifdOffset + 2 + $entry * 12;
            if ($entryOffset + 12 > strlen($tiff)) {
                return null;
            }
            if (unpack($short, substr($tiff, $entryOffset, 2))[1] === 0x0112) {
                $orientation = unpack($short, substr($tiff, $entryOffset + 8, 2))[1];
                return ($orientation >= 1 && $orientation <= 8) ? $orientation : null;
            }
        }
        return null;
    }

    /**
     * Removes the EXIF and text chunks of a PNG file.
     *
     * @param string $data Content of the PNG file
     * @return string|null The content without metadata, null if the file isn't a well-formed PNG
     */
    private function removePngMetadata(string $data): ?string
    {
        $signature = "\x89PNG\r\n\x1A\n";
        if (substr($data, 0, 8) !== $signature) {
            return null;
        }
        $length = strlen($data);
        $result = $signature;
        $position = 8;
        while ($position + 12 <= $length) {
            $chunkLength = unpack('N', substr($data, $position, 4))[1];
            $chunkType = substr($data, $position + 4, 4);
            $chunkSize = 12 + $chunkLength;
            if ($chunkLength > $length || $position + $chunkSize > $length) {
                return null;
            }
            if (!in_array($chunkType, self::PNG_METADATA_CHUNKS, true)) {
                $result .= substr($data, $position, $chunkSize);
            }
            $position += $chunkSize;
            if ($chunkType === 'IEND') {
                return $result;
            }
        }
        return null;
    }

    /**
     * Removes the EXIF and XMP chunks of a WebP file and clears their flags in the VP8X chunk.
     *
     * @param string $data Content of the WebP file
     * @return string|null The content without metadata, null if the file isn't a well-formed WebP
     */
    private function removeWebpMetadata(string $data): ?string
    {
        $length = strlen($data);
        if ($length < 12 || substr($data, 0, 4) !== 'RIFF' || substr($data, 8, 4) !== 'WEBP') {
            return null;
        }
        $chunks = '';
        $position = 12;
        while ($position + 8 <= $length) {
            $chunkType = substr($data, $position, 4);
            $chunkLength = unpack('V', substr($data, $position + 4, 4))[1];
            // Chunks are padded to an even size
            $chunkSize = 8 + $chunkLength + ($chunkLength % 2);
            if ($chunkLength > $length || $position + 8 + $chunkLength > $length) {
                return null;
            }
            $chunk = substr($data, $position, min($chunkSize, $length - $position));
            if ($chunkType === 'VP8X' && $chunkLength >= 1) {
                // Clear the EXIF (0x08) and XMP (0x04) flags
                $chunk[8] = chr(ord($chunk[8]) & ~0x0C);
            }
            if (!in_array($chunkType, self::WEBP_METADATA_CHUNKS, true)) {
                $chunks .= $chunk;
            }
            $position += $chunkSize;
        }
        if ($chunks === '') {
            return null;
        }
        return 'RIFF' . pack('V', 4 + strlen($chunks)) . 'WEBP' . $chunks;
    }
}
