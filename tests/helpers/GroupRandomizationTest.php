<?php

namespace ls\tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2017-11-02
 * @group rand
 */
class GroupRandomizationTest extends TestBaseClass
{
    /**
     * @var int
     */
    public static $surveyId = null;

    /**
     */
    public static function setupBeforeClass()
    {
        self::$testHelper->connectToOriginalDatabase();

        \Yii::app()->session['loginID'] = 1;

        $surveyFile = __DIR__ . '/../data/surveys/limesurvey_survey_88881.lss';
        if (!file_exists($surveyFile)) {
            die('Fatal error: found no survey file');
        }

        $translateLinksFields = false;
        $newSurveyName = null;
        $result = importSurveyFile(
            $surveyFile,
            $translateLinksFields,
            $newSurveyName,
            null
        );
        if ($result) {
            self::$surveyId = $result['newsid'];
        } else {
            die('Fatal error: Could not import survey');
        }
    }

    /**
     * Selenium setup.
     */
    public function setUp()
    {
        if (empty(getenv('DOMAIN'))) {
            die('Must specify DOMAIN environment variable to run this test, like "DOMAIN=localhost/limesurvey" or "DOMAIN=limesurvey.localhost".');
        }

        $capabilities = DesiredCapabilities::phantomjs();
        $this->webDriver = RemoteWebDriver::create('http://localhost:4444/', $capabilities);
    }

    /**
     * 
     */
    public static function teardownAfterClass()
    {
        $result = \Survey::model()->deleteSurvey(self::$surveyId, true);
        if (!$result) {
            die('Fatal error: Could not clean up survey ' . self::$surveyId);
        }
    }

    /**
     * Tear down fixture.
     */
    public function tearDown()
    {
        // Close Firefox.
        $this->webDriver->quit();
    }


    /**
     * 
     */
    public function testRunSurvey()
    {
        self::$testHelper->activateSurvey(self::$surveyId);

        $domain = getenv('DOMAIN');
        if (empty($domain)) {
            $domain = '';
        }

        $this->webDriver->get(
            sprintf(
                'http://%s/index.php/%d?newtest=Y&lang=pt',
                $domain,
                self::$surveyId
            )
        );
        $submit = $this->webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        $this->assertNotEmpty($submit);
        $this->webDriver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($submit)
        );
        $submit->click();

        $body = $this->webDriver->findElement(WebDriverBy::tagName('body'));
        $text = $body->getText();

        if (strpos($text, 'PHP notice') === false) {
            echo 'No PHP notice';
            exit(0);
        } else {
            echo 'PHP notice!';
            exit(1);
        }

        // There should be no PHP notice.
        //$this->assertTrue(strpos($text, 'PHP notice') === false, $text);

        //$screenshot = $this->webDriver->takeScreenshot();
        //file_put_contents(__DIR__ . '/screenshot.png', $screenshot);

        self::$testHelper->deactivateSurvey(self::$surveyId);
    }
}
