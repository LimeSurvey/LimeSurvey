<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @group adminviews
 */
class ReactEditorWorksTest extends TestBaseClassWeb
{

    /**
     * Setup before class.
     */
    public static function setupBeforeClass(): void
    {
        parent::setUpBeforeClass();

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

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_928171.lss';
        self::importSurvey($surveyFile);

        // Enable react editor for superadmin
        $affectedRows = \Yii::app()->db->createCommand(
            "UPDATE {{settings_user}} SET stg_value = '1' WHERE stg_name = 'editorEnabled' AND uid = 1"
        )->execute();
        self::assertGreaterThan(
            0,
            $affectedRows,
            'Editor enable failed: no user row was updated.'
       );

       // Browser login.
       self::adminLogin($username, $password, false);
    }

    /**
     * Do the test
     */
    public function testReactEditorWorks()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/');
        $web = self::$webDriver;
        $url = $urlMan->createUrl('editor/#/survey/928171/structure');

        try {
            $web->get($url);

            sleep(1);

            $web->wait()->until(
                function ($webDriver) {
                    return $webDriver->findElement(WebDriverBy::cssSelector('.survey-header-container')) !== null;
                }
            );
        } catch (\Exception $ex) {
            $screenshot = $web->takeScreenshot();
            $filename = self::$screenshotsFolder . '/' . __CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->fail(
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' . $filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    public static function tearDownAfterClass(): void
    {
        // Disable react editor for superadmin
        $affectedRows = \Yii::app()->db->createCommand(
            "UPDATE {{settings_user}} SET stg_value = '0' WHERE stg_name = 'editorEnabled' AND uid = 1"
        )->execute();
        self::assertGreaterThan(
            0,
            $affectedRows,
            'Editor disable failed: no user row was updated.'
       );
       parent::tearDownAfterClass();
    }
}
