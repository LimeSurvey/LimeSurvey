<?php

namespace ls\tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2017-10-27
 * @group datevalidation
 */
class DateTimeValidationTest extends TestBaseClass
{
    /**
     * @var int
     */
    public static $surveyId = null;

    /**
     * Import survey in tests/surveys/.
     */
    public static function setupBeforeClass()
    {
        \Yii::app()->session['loginID'] = 1;

        $surveyFile = __DIR__ . '/../data/surveys/limesurvey_survey_834477.lss';
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

        self::$testHelper->enablePreview();
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
     * Destroy what had been imported.
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
    public function testBasic()
    {
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

        try {
            $submit = $this->webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        } catch (NoSuchElementException $ex) {
            $screenshot = $this->webDriver->takeScreenshot();
            file_put_contents(__DIR__ . '/tmp.png', $screenshot);
            $this->assertFalse(
                true,
                'Screenshot in ' . __DIR__ . '/tmp.png' . PHP_EOL . $ex->getMessage()
            );
        }

        $this->assertNotEmpty($submit);
        $this->webDriver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($submit)
        );
        $submit->click();

        // After submit we should see the complete page.
        try {
            $div = WebDriverBy::className('completed-text');
            $this->webDriver->wait(10,1000)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated($div)
            );
            $this->assertNotEmpty($div);
        } catch (NoSuchElementException $ex) {
            $screenshot = $this->webDriver->takeScreenshot();
            file_put_contents(__DIR__ . '/tmp.png', $screenshot);
            $this->assertFalse(
                true,
                'Screenshot in ' . __DIR__ . '/tmp.png' . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
