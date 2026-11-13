<?php

namespace ls\tests;

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
