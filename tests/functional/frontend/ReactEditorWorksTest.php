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
            "INSERT INTO {{settings_user}} (uid,stg_name,stg_value) VALUES (1,'editorEnabled','1')"
        )->execute();
        self::assertGreaterThan(
            0,
            $affectedRows,
            'Editor enable failed: no user row was updated.'
       );

    //    $affectedRows = \Yii::app()->db->createCommand(
    //         "UPDATE {{plugins}} SET active = '1' WHERE name = 'ReactEditor'"
    //     )->execute();
    //     self::assertGreaterThan(
    //         0,
    //         $affectedRows,
    //         'Editor plugin enable failed: no plugin row was updated.'
    //    );

       // Browser login.
       self::adminLogin($username, $password);
    }

    /**
     * Do the test
     */
    public function testReactEditorWorks()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $web = self::$webDriver;
        $url = $urlMan->createUrl('editorLink/index', ['route' => 'survey/928171/structure']);

        try {
            $web->get($url);

            $web->wait()->until(
                function ($webDriver) {
                    return $webDriver->findElement(WebDriverBy::cssSelector('.survey-header-container')) !== null;
                }
            );

            $this->assertTrue(
                $web->findElement(WebDriverBy::cssSelector('.survey-header-container'))->isDisplayed(),
                'React editor rendered survey welcome container'
            );
            $screenshot = $web->takeScreenshot();
            $filename = self::$screenshotsFolder . '/' .__FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            fwrite(STDERR, 'Url: ' . $url . PHP_EOL . 'Screenshot in ' . $filename . PHP_EOL);
        } catch (\Exception $ex) {
            $screenshot = $web->takeScreenshot();
            $filename = self::$screenshotsFolder . '/' .__FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->fail(
                'Url: ' . $url . PHP_EOL .
                'Current browser URL: ' . $web->getCurrentURL() . PHP_EOL .
                'Screenshot in ' . $filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    public static function tearDownAfterClass(): void
    {
        // Disable react editor for superadmin
        $affectedRows = \Yii::app()->db->createCommand(
            "DELETE FROM {{settings_user}} WHERE stg_name = 'editorEnabled' AND uid = 1"
        )->execute();
        self::assertGreaterThan(
            0,
            $affectedRows,
            'Editor disable failed: no user rows were deleted.'
       );

    //    $affectedRows = \Yii::app()->db->createCommand(
    //         "UPDATE {{plugins}} SET active = '0' WHERE name = 'ReactEditor'"
    //     )->execute();
    //     self::assertGreaterThan(
    //         0,
    //         $affectedRows,
    //         'Editor plugin disable failed: no plugin row was updated.'
    //    );

       parent::tearDownAfterClass();
    }
}
