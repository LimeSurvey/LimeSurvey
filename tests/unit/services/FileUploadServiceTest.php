<?php

namespace ls\tests;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\FileUploadService;
use LimeSurvey\Models\Services\UploadValidator;
use Permission;

/**
 * Tests the image paths returned by the new editor's upload.
 */
class FileUploadServiceTest extends TestBaseClass
{
    /** @var string 1x1 transparent PNG */
    private const PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6'
        . 'kgAAAABJRU5ErkJggg==';

    /** @var string Temporary upload folder */
    private $uploadDir;

    /** @var array Original config values */
    private $originalConfig = [];

    /**
     * Sets up an empty upload folder and a "/limesurvey" subfolder install.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        foreach (['publicurl', 'uploaddir', 'uploadurl', 'uniq_upload_dir'] as $name) {
            $this->originalConfig[$name] = \Yii::app()->getConfig($name);
        }
        $this->uploadDir = sys_get_temp_dir() . '/ls-fileuploadservice-test-' . uniqid();
        mkdir($this->uploadDir . '/surveys', 0777, true);
        // Same host as the tests bootstrap, so it passes the allowed hosts check
        \Yii::app()->setConfig('publicurl', 'http://localhost/limesurvey/');
        \Yii::app()->setConfig('uploaddir', $this->uploadDir);
        \Yii::app()->setConfig('uploadurl', '/limesurvey/upload');
        \Yii::app()->setConfig('uniq_upload_dir', false);
    }

    /**
     * Restores the config and deletes the upload folder.
     * @return void
     */
    public function tearDown(): void
    {
        foreach ($this->originalConfig as $name => $value) {
            \Yii::app()->setConfig($name, $value);
        }
        \CFileHelper::removeDirectory($this->uploadDir);
        parent::tearDown();
    }

    /**
     * Paths include the install subfolder.
     * @return void
     */
    public function testUploadedImagePathContainsSubfolder()
    {
        $result = $this->storeImage(123456, 'logo.png');

        $expectedPath = '/limesurvey/upload/surveys/123456/images/logo.png';
        $this->assertTrue($result['success'], $result['uploadResultMessage'] ?? '');
        $this->assertSame($expectedPath, $result['uploaded']['filePath']);
        $this->assertSame('http://localhost' . $expectedPath, $result['uploaded']['previewUrl']);
    }

    /**
     * An absolute upload URL is used as is.
     * @return void
     */
    public function testAbsoluteUploadUrlIsKept()
    {
        \Yii::app()->setConfig('uploadurl', 'https://surveys.example.org/limesurvey/upload/');

        $result = $this->storeImage(123456, 'logo.png');

        $expectedUrl = 'https://surveys.example.org/limesurvey/upload/surveys/123456/images/logo.png';
        $this->assertSame($expectedUrl, $result['uploaded']['filePath']);
        $this->assertSame($expectedUrl, $result['uploaded']['previewUrl']);
    }

    /**
     * With uniq_upload_dir, images go to the "uniq" folder.
     * @return void
     */
    public function testUniqUploadDir()
    {
        \Yii::app()->setConfig('uniq_upload_dir', true);

        $result = $this->storeImage(123456, 'logo.png', 'uniq');

        $expectedPath = '/limesurvey/upload/surveys/uniq/images/logo.png';
        $this->assertTrue($result['success'], $result['uploadResultMessage'] ?? '');
        $this->assertSame($expectedPath, $result['uploaded']['filePath']);
    }

    /**
     * A different file with the name of an existing one gets a numbered name, so the existing file is kept.
     * @return void
     */
    public function testExistingFileNameGetsNumberedName()
    {
        $imagesDir = $this->uploadDir . '/surveys/123456/images';
        mkdir($imagesDir, 0777, true);
        file_put_contents($imagesDir . '/logo.png', 'existing');
        $permission = $this->createMock(Permission::class);
        $service = new FileUploadService(new UploadValidator(), $permission);
        $handleDuplicateFileName = new \ReflectionMethod(FileUploadService::class, 'handleDuplicateFileName');

        $this->assertSame('logo(1).png', $handleDuplicateFileName->invoke($service, 'logo.png', $imagesDir));

        file_put_contents($imagesDir . '/logo(1).png', 'existing');
        $this->assertSame('logo(2).png', $handleDuplicateFileName->invoke($service, 'logo.png', $imagesDir));
        $this->assertSame('other.png', $handleDuplicateFileName->invoke($service, 'other.png', $imagesDir));
    }

    /**
     * A PNG image for a text is saved in the survey's images folder, like the images of the classic editor.
     * @return void
     */
    public function testContentImageIsSaved()
    {
        $result = $this->storeContentImage('photo.png', base64_decode(self::PNG_BASE64));

        $expectedPath = '/limesurvey/upload/surveys/123456/images/photo.png';
        $this->assertTrue($result['success'], $result['uploadResultMessage'] ?? '');
        $this->assertSame($expectedPath, $result['uploaded']['filePath']);
        $this->assertSame('http://localhost' . $expectedPath, $result['uploaded']['previewUrl']);
    }

    /**
     * Images for texts can only be PNG, JPEG or WebP files.
     * @return void
     */
    public function testContentImageRejectsOtherFileTypes()
    {
        $result = $this->storeContentImage('animation.gif', 'GIF89a');

        $this->assertFalse($result['success']);
        $this->assertSame('This file type is not allowed to be uploaded.', $result['uploadResultMessage']);
    }

    /**
     * The content of the file has to be an image of the type its extension names.
     * @return void
     */
    public function testContentImageRejectsContentNotMatchingTheExtension()
    {
        $notAnImage = $this->storeContentImage('notes.png', 'Test <b>"quoted"</b> & more');
        $pngNamedJpeg = $this->storeContentImage('photo.jpg', base64_decode(self::PNG_BASE64));

        foreach ([$notAnImage, $pngNamedJpeg] as $result) {
            $this->assertFalse($result['success']);
            $this->assertSame('This file is not a valid PNG, JPEG or WebP image.', $result['uploadResultMessage']);
        }
    }

    /**
     * Files over 10 MB are rejected.
     * @return void
     */
    public function testContentImageRejectsLargeFiles()
    {
        $result = $this->storeContentImage(
            'photo.png',
            base64_decode(self::PNG_BASE64),
            FileUploadService::CONTENT_IMAGE_MAX_SIZE + 1
        );

        $this->assertFalse($result['success']);
        $this->assertStringStartsWith('Sorry, this file is too large.', $result['uploadResultMessage']);
    }

    /**
     * Images with more pixels than the limit are rejected, before anything decodes them.
     * @return void
     */
    public function testContentImageRejectsTooManyPixels()
    {
        // Only the header is read, so a PNG header with 8000 x 6000 pixels is enough
        $header = pack('NNCCCCC', 8000, 6000, 8, 2, 0, 0, 0);
        $png = "\x89PNG\r\n\x1A\n" . pack('N', 13) . 'IHDR' . $header . pack('N', crc32('IHDR' . $header));

        $result = $this->storeContentImage('huge.png', $png);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'This image is too large. Images can have at most 40 megapixels.',
            $result['uploadResultMessage']
        );
    }

    /**
     * The metadata of an uploaded image is removed before it is saved.
     * @return void
     */
    public function testContentImageMetadataIsRemoved()
    {
        $png = base64_decode(self::PNG_BASE64);
        $text = "Comment\0Test <b>\"quoted\"</b> & more";
        $textChunk = pack('N', strlen($text)) . 'tEXt' . $text . pack('N', crc32('tEXt' . $text));
        // After the signature (8 bytes) and the IHDR chunk (25 bytes)
        $pngWithText = substr($png, 0, 33) . $textChunk . substr($png, 33);

        $result = $this->storeContentImage('photo.png', $pngWithText);

        $this->assertTrue($result['success'], $result['uploadResultMessage'] ?? '');
        $this->assertSame($png, file_get_contents($this->uploadDir . '/upload.tmp'));
    }

    /**
     * Uploading an image for a text needs the permission to update the survey content.
     * @return void
     */
    public function testContentImageNeedsPermission()
    {
        $this->expectException(PermissionDeniedException::class);

        $this->storeContentImage('photo.png', base64_decode(self::PNG_BASE64), null, false);
    }

    /**
     * Uploads an image for a text through the service.
     * The 1x1 PNG is put in the folder first, because move_uploaded_file() only works for real uploads: An upload
     * that ends up identical to it counts as already saved.
     * @param string $fileName File name
     * @param string $content Content of the uploaded file
     * @param int|null $size Size sent with the upload, defaults to the size of the content
     * @param bool $hasPermission Whether the user may update the survey content
     * @return array Result of storeContentImage()
     */
    private function storeContentImage(string $fileName, string $content, ?int $size = null, bool $hasPermission = true)
    {
        $imagesDir = $this->uploadDir . '/surveys/123456/images';
        if (!is_dir($imagesDir)) {
            mkdir($imagesDir, 0777, true);
        }
        file_put_contents($imagesDir . '/' . $fileName, base64_decode(self::PNG_BASE64));
        $tmpFile = $this->uploadDir . '/upload.tmp';
        file_put_contents($tmpFile, $content);

        $permission = $this->createMock(Permission::class);
        $permission->method('hasSurveyPermission')->willReturn($hasPermission);
        $service = new FileUploadService(new UploadValidator(), $permission);

        return $service->storeContentImage(123456, [
            'file' => [
                'name' => $fileName,
                'type' => 'image/png',
                'tmp_name' => $tmpFile,
                'error' => 0,
                'size' => $size ?? strlen($content),
            ],
        ]);
    }

    /**
     * Uploads a PNG through the service.
     * The same file is put in the folder first, because move_uploaded_file() only works for real uploads.
     * @param int $surveyId Survey ID
     * @param string $fileName File name
     * @param string|null $directoryId Upload folder name, defaults to the survey ID
     * @return array Result of storeSurveyImage()
     */
    private function storeImage(int $surveyId, string $fileName, ?string $directoryId = null)
    {
        $png = base64_decode(self::PNG_BASE64);
        $imagesDir = $this->uploadDir . '/surveys/' . ($directoryId ?? $surveyId) . '/images';
        mkdir($imagesDir, 0777, true);
        file_put_contents($imagesDir . '/' . $fileName, $png);
        $tmpFile = $this->uploadDir . '/upload.tmp';
        file_put_contents($tmpFile, $png);

        $permission = $this->createMock(Permission::class);
        $permission->method('hasSurveyPermission')->willReturn(true);
        $service = new FileUploadService(new UploadValidator(), $permission);

        return $service->storeSurveyImage($surveyId, [
            'file' => [
                'name' => $fileName,
                'type' => 'image/png',
                'tmp_name' => $tmpFile,
                'error' => 0,
                'size' => strlen($png),
            ],
        ]);
    }
}
