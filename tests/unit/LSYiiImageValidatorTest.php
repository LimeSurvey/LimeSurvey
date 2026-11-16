<?php

namespace ls\tests;

use LSYii_ImageValidator;
use Yii;

/**
 * Tests for LSYii_ImageValidator
 */
class LSYiiImageValidatorTest extends TestBaseClass
{
    /** @var string A 1x1 transparent PNG */
    private const PNG_CONTENT = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    /** @var string A plain SVG image */
    private const SVG_CONTENT = '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"><rect width="1" height="1"/></svg>';

    /** @var mixed Original allowedthemeimageformats config value */
    private $originalImageFormats;

    /** @var mixed Original imagemimetypes config value */
    private $originalImageMimeTypes;

    /** @var string[] Temporary files created by the test */
    private $tmpFiles = [];

    /**
     * Saves the config values changed by the tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->originalImageFormats = Yii::app()->getConfig('allowedthemeimageformats');
        $this->originalImageMimeTypes = Yii::app()->getConfig('imagemimetypes');
    }

    /**
     * Restores the config values and removes the temporary files
     */
    public function tearDown(): void
    {
        Yii::app()->setConfig('allowedthemeimageformats', $this->originalImageFormats);
        Yii::app()->setConfig('imagemimetypes', $this->originalImageMimeTypes);
        foreach ($this->tmpFiles as $tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
        parent::tearDown();
    }

    /**
     * Writes a temporary file
     *
     * @param string $content File content
     * @param string $extension File extension, empty for none (like an uploaded tmp file)
     * @return string Full path of the file
     */
    private function createTmpFile($content, $extension = '')
    {
        $path = tempnam(sys_get_temp_dir(), 'lsimg');
        $this->tmpFiles[] = $path;
        if ($extension !== '') {
            $path .= '.' . $extension;
            $this->tmpFiles[] = $path;
        }
        file_put_contents($path, $content);
        return $path;
    }

    /**
     * Builds an uploaded file array like an entry of $_FILES
     *
     * @param string $content File content
     * @param string $name Original file name
     * @param string $type MIME type sent by the browser
     * @return array
     */
    private function createUpload($content, $name, $type)
    {
        return [
            'tmp_name' => $this->createTmpFile($content),
            'name' => $name,
            'type' => $type,
        ];
    }

    /**
     * Images with an allowed extension and MIME type are accepted
     */
    public function testValidImagesAreAccepted()
    {
        $jpgFile = self::$dataFolder . DIRECTORY_SEPARATOR . 'file_upload' . DIRECTORY_SEPARATOR . 'dalahorse.jpg';
        $this->assertTrue(LSYii_ImageValidator::validateImage($jpgFile)['check']);

        $pngUpload = $this->createUpload(base64_decode(self::PNG_CONTENT), 'logo.PNG', 'image/png');
        $this->assertTrue(LSYii_ImageValidator::validateImage($pngUpload)['check']);
    }

    /**
     * Files whose content or sent MIME type does not match an allowed image format are rejected
     */
    public function testMismatchingImagesAreRejected()
    {
        $textUpload = $this->createUpload('Not an image', 'logo.png', 'image/png');
        $this->assertFalse(LSYii_ImageValidator::validateImage($textUpload)['check']);

        $wrongTypeUpload = $this->createUpload(base64_decode(self::PNG_CONTENT), 'logo.png', 'text/html');
        $this->assertFalse(LSYii_ImageValidator::validateImage($wrongTypeUpload)['check']);

        // SVG MIME types are only accepted when the svg extension is allowed
        $svgAsPngUpload = $this->createUpload(self::SVG_CONTENT, 'logo.png', 'image/png');
        $this->assertFalse(LSYii_ImageValidator::validateImage($svgAsPngUpload)['check']);
    }

    /**
     * SVG images are only accepted when svg is added to allowedthemeimageformats
     */
    public function testSvgDependsOnAllowedImageFormats()
    {
        $svgFile = $this->createTmpFile(self::SVG_CONTENT, 'svg');
        $this->assertFalse(LSYii_ImageValidator::validateImage($svgFile)['check']);

        Yii::app()->setConfig('allowedthemeimageformats', 'png,svg');
        $this->assertTrue(LSYii_ImageValidator::validateImage($svgFile)['check']);
    }

    /**
     * The accepted MIME types come from the imagemimetypes setting
     */
    public function testMimeTypesAreConfigurable()
    {
        $pngFile = $this->createTmpFile(base64_decode(self::PNG_CONTENT), 'png');
        Yii::app()->setConfig('imagemimetypes', ['png' => ['image/x-other']]);
        $this->assertFalse(LSYii_ImageValidator::validateImage($pngFile)['check']);

        Yii::app()->setConfig('imagemimetypes', ['png' => ['IMAGE/PNG']]);
        $this->assertTrue(LSYii_ImageValidator::validateImage($pngFile)['check']);
    }

    /**
     * Extensions without an imagemimetypes entry fall back to the file extension map
     */
    public function testMimeTypesFallBackToFileExtensionMap()
    {
        Yii::app()->setConfig('imagemimetypes', []);
        $mimeTypes = LSYii_ImageValidator::getAllowedMimeTypes(['webp', 'svg']);
        $this->assertContains('image/webp', $mimeTypes);
        $this->assertContains('image/svg+xml', $mimeTypes);
        $this->assertContains('image/svg', $mimeTypes);
        $this->assertNotContains('image/png', $mimeTypes);
    }

    /**
     * Allowed extensions are read lowercase and trimmed from allowedthemeimageformats
     */
    public function testAllowedExtensions()
    {
        Yii::app()->setConfig('allowedthemeimageformats', 'PNG, jpg,,gif ');
        $this->assertSame(['png', 'jpg', 'gif'], LSYii_ImageValidator::getAllowedExtensions());
    }
}
