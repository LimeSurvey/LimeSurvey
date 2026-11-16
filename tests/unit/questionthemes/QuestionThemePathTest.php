<?php

namespace ls\tests;

use QuestionTheme;
use ZipArchive;

/**
 * Paths stored for user question themes installed in userquestionthemerootdir.
 */
class QuestionThemePathTest extends TestBaseClass
{
    /** @var string[] Theme directories created by the tests */
    private $createdDirs = [];

    /**
     * Removes the question theme directories created by the tests.
     *
     * @return void
     */
    public function tearDown(): void
    {
        \Yii::import('application.helpers.common_helper', true);
        foreach ($this->createdDirs as $dir) {
            rmdirr($dir);
        }
        $this->createdDirs = [];
        parent::tearDown();
    }

    /**
     * The user question theme root dir is built from the upload dir.
     *
     * @return void
     */
    public function testUserQuestionThemeRootDirIsInUploadDir()
    {
        $this->assertSame(
            \Yii::app()->getConfig('uploaddir') . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'question',
            \Yii::app()->getConfig('userquestionthemerootdir')
        );
    }

    /**
     * Theme paths in the upload dir are stored relative to the root dir, so the preview image is found.
     *
     * @return void
     */
    public function testUserQuestionThemePathsAreRelativeToRootDir()
    {
        $themeName = 'PathTest' . uniqid();
        $xmlFolder = $this->extractRangeSliderTheme(\Yii::app()->getConfig('userquestionthemerootdir'), $themeName);

        $metaData = QuestionTheme::getQuestionMetaData($xmlFolder);

        $expectedXmlPath = 'upload/themes/question/' . $themeName . '/survey/questions/answer/multiplenumeric';
        $this->assertSame(0, $metaData['coreTheme']);
        $this->assertSame($expectedXmlPath, $metaData['xml_path']);
        $this->assertSame('/' . $expectedXmlPath . '/assets/preview.png', $metaData['image_path']);

        $questionTheme = new QuestionTheme();
        $questionTheme->xml_path = $metaData['xml_path'];
        $questionTheme->core_theme = 0;
        $this->assertSame($themeName . '/survey/questions/answer/multiplenumeric', $questionTheme->getRelativeXmlPath());
    }

    /**
     * A user question theme root dir outside the root dir is still detected as user theme dir.
     *
     * @return void
     */
    public function testUserQuestionThemeRootDirOutsideRootDir()
    {
        $originalRootDir = \Yii::app()->getConfig('userquestionthemerootdir');
        $userQuestionThemeRootDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lsquestionthemes' . uniqid();
        $this->createdDirs[] = $userQuestionThemeRootDir;
        \Yii::app()->setConfig('userquestionthemerootdir', $userQuestionThemeRootDir);
        try {
            $xmlFolder = $this->extractRangeSliderTheme($userQuestionThemeRootDir, 'Range-Slider');

            $metaData = QuestionTheme::getQuestionMetaData($xmlFolder);

            $this->assertSame(0, $metaData['coreTheme']);
            $this->assertSame(str_replace('\\', '/', $xmlFolder), $metaData['xml_path']);
            // The preview can't be reached through the base URL, so the default screenshot is used
            $this->assertSame(\Yii::app()->getConfig('imageurl') . '/screenshots/K.png', $metaData['image_path']);
        } finally {
            \Yii::app()->setConfig('userquestionthemerootdir', $originalRootDir);
        }
    }

    /**
     * Extracts the Range-Slider test question theme into $rootDir/$themeName.
     *
     * @param string $rootDir Directory to extract the theme to
     * @param string $themeName Name of the theme directory
     * @return string Absolute path of the folder containing config.xml
     */
    private function extractRangeSliderTheme($rootDir, $themeName)
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lsrangeslider' . uniqid();
        $this->createdDirs[] = $tempDir;
        $zip = new ZipArchive();
        $this->assertTrue($zip->open(ROOT . '/tests/data/file_upload/rangeslider_tpartner.zip'));
        $zip->extractTo($tempDir);
        $zip->close();

        if (!is_dir($rootDir)) {
            mkdir($rootDir, 0777, true);
        }
        $themeDir = $rootDir . DIRECTORY_SEPARATOR . $themeName;
        $this->createdDirs[] = $themeDir;
        rename($tempDir . DIRECTORY_SEPARATOR . 'Range-Slider', $themeDir);

        return $themeDir . DIRECTORY_SEPARATOR . 'survey' . DIRECTORY_SEPARATOR . 'questions' . DIRECTORY_SEPARATOR . 'answer' . DIRECTORY_SEPARATOR . 'multiplenumeric';
    }
}
