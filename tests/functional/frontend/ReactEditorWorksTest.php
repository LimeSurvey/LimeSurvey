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
     * @var int UID of the admin account used for this test, resolved from ADMINUSERNAME.
     */
    private static $adminUid;

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

        // Resolve the uid for the account we are going to log in as.
        $uid = \Yii::app()->db->createCommand(
            "SELECT uid FROM {{users}} WHERE users_name = :username"
        )->queryScalar([':username' => $username]);
        self::assertNotFalse($uid, 'Could not resolve uid for ADMINUSERNAME: ' . $username);
        self::$adminUid = (int) $uid;

        // Permission to everything.
        \Yii::app()->session['loginID'] = self::$adminUid;

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_928171.lss';
        self::importSurvey($surveyFile);

        // Enable react editor for the logged-in admin
        $affectedRows = \Yii::app()->db->createCommand(
            "INSERT INTO {{settings_user}} (uid,stg_name,stg_value) VALUES (:uid,'editorEnabled','1')"
        )->execute([':uid' => self::$adminUid]);
        self::assertGreaterThan(
            0,
            $affectedRows,
            'Editor enable failed: no user row was updated.'
       );

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
        // Disable react editor for the logged-in admin
        $affectedRows = \Yii::app()->db->createCommand(
            "DELETE FROM {{settings_user}} WHERE stg_name = 'editorEnabled' AND uid = :uid"
        )->execute([':uid' => self::$adminUid]);
        self::assertGreaterThan(
            0,
            $affectedRows,
            'Editor disable failed: no user rows were deleted.'
       );

       parent::tearDownAfterClass();
    }
}
