<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClassWeb;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\LocalFileDetector;

/**
 *
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
        $urlMan = \Yii::app()->urlManager;
        $web = self::$webDriver;

        // Go to theme page
        $url = $urlMan->createUrl('themeOptions/index');
        $web->get($url);
        sleep(5);

        // Click "Import"
        // Choose zip file by tpartner
        // Check result in database
        // Check result in filesystem
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
