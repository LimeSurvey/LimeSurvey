<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClassWeb;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\LocalFileDetector;
use QuestionTheme;

/**
 * Uses test data from tpartner: https://github.com/tpartner/LimeSurvey-Range-Slider-4x
 */
class QuestionThemeTest extends TestBaseClassWeb
{
    public static function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        self::adminLogin($username, $password);
    }

    public function testImportQuestionTheme()
    {

        // Delete question theme if it exists
        $theme = QuestionTheme::model()->findByAttributes(['name' => 'Range-Slider']);
        $themeDir = \Yii::app()->getConfig('userquestionthemerootdir') . '/Range-Slider';
        if (!empty($theme)) {
            \Yii::import('application.helpers.common_helper', true);
            rmdirr($themeDir);
            $theme->delete();
        }

        $urlMan = \Yii::app()->urlManager;
        $web = self::$webDriver;

        // Go to theme page
        $url = $urlMan->createUrl('themeOptions/index');
        $web->get($url);
        sleep(1);

        $button = $web->findByLinkText('Question themes');
        $button->click();

        $button = $web->findByLinkText('Upload & install');
        $button->click();

        sleep(1);

        // Click "Import"
        $fileInput = $web->findByCss('#importQuestionTemplate #the_file');
        $fileInput->setFileDetector(new LocalFileDetector());
        $file = BASEPATH . '../tests/data/file_upload/rangeslider_tpartner.zip';
        $this->assertTrue(file_exists($file));
        $fileInput->sendKeys($file)->submit();

        sleep(1);

        // Check result in database
        $theme = QuestionTheme::model()->findByAttributes(['name' => 'Range-Slider']);
        $this->assertNotEmpty($theme, 'Found installed question theme in database');

        // Check result in filesystem
        $this->assertTrue(file_exists($themeDir), 'Question theme exists in userquestionthemerootdir after install');
    }

    /**
     * @depends testImportQuestionTheme
     */
    public function testSelectQuestionThemeInQuestionEditor()
    {
        // Import survey with one group and question
        // Go to question editor
        // Select question theme
        // Check that all custom attributes are displayed
        // Add values to custom attributes
        // Save question
        // Check database
    }

    /**
     * @depends testSelectQuestionThemeInQuestionEditor
     */
    public function testPreviewQuestionThemeQuestion()
    {
        // Use previous survey
        // Activate survey
        // Execute survey
        // Answer question using question theme
        // Submit
        // Check result in database
    }
}
