<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
* Test if dashboard (card and list views) can be loaded with a corrupted survey (missing language)
**/
class DashboardLoadedWithCorruptedSurveyTest extends TestBaseClassWeb
{

    /** keep surveyurl */
    protected static $surveyUrl;

    /**
     * Import survey before test
     */
    public static function setUpBeforeClass(): void
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

        // Browser login.
        self::adminLogin($username, $password, $wait = false);

        // Import survey
        $surveyFile =  self::$surveysFolder . '/limesurvey_survey_CorruptedSurvey.lss';
        self::importSurvey($surveyFile);

        // Corrupt the survey by setting the language to empty string
        \Yii::app()->db->createCommand(
            "UPDATE {{surveys_languagesettings}} SET surveyls_language = '' WHERE surveyls_survey_id = :sid AND surveyls_language = 'de'"
        )->execute([':sid' => self::$surveyId]);
    }

    public function testDashboardCardViewLoadedWithCorruptedSurvey()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $web = self::$webDriver;

        try {
            $url = $urlMan->createUrl('dashboard/view?Survey%5Bsearched_value%5D=&active=&gsid=&viewtype=box-widget');
            $web->get($url);

            // Verify no PHP error is shown
            $pageSource = $web->getPageSource();
            $this->assertStringNotContainsString('500: Internal Server Error', $pageSource);
        } catch (\Exception $ex) {
            $screenshot = $web->takeScreenshot();
            $filename = self::$screenshotsFolder . '/' . __CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' . $filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    public function testDashboardListViewLoadedWithCorruptedSurvey()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $web = self::$webDriver;

        try {
            $url = $urlMan->createUrl('dashboard/view?active=&viewtype=list-widget');
            $web->get($url);

            // Verify no PHP error is shown
            $pageSource = $web->getPageSource();
            $this->assertStringNotContainsString('500: Internal Server Error', $pageSource);
        } catch (\Exception $ex) {
            $screenshot = $web->takeScreenshot();
            $filename = self::$screenshotsFolder . '/' . __CLASS__ . '_' . __FUNCTION__ . '.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' . $filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }
}

