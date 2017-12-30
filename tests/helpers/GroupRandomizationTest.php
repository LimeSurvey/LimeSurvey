<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2017-11-02
 * @group rand
 */
class GroupRandomizationTest extends TestBaseClassWeb
{
    /**
     * @var int
     */
    public static $surveyId = null;

    /**
     */
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        self::$testHelper->connectToOriginalDatabase();

        \Yii::app()->session['loginID'] = 1;

        $surveyFile = __DIR__ . '/../data/surveys/limesurvey_survey_88881.lss';
        if (!file_exists($surveyFile)) {
            echo 'Fatal error: found no survey file';
            exit(4);
        }

        $translateLinksFields = false;
        $newSurveyName = null;
        try {
            $result = importSurveyFile(
                $surveyFile,
                $translateLinksFields,
                $newSurveyName,
                null
            );
        } catch (\CDbException $ex) {
            self::assertTrue(
                false,
                'Could not import survey limesurvey_survey_88881.lss: ' . $ex->getMessage()
            );
        }

        if ($result) {
            self::$surveyId = $result['newsid'];
        } else {
            echo 'Fatal error: Could not import survey';
            exit(5);
        }
    }

    /**
     * Selenium setup.
     */
    public function setUp()
    {
        $domain = getenv('DOMAIN');
        if (empty($domain)) {
            echo 'Must specify DOMAIN environment variable to run this test, like "DOMAIN=localhost/limesurvey" or "DOMAIN=limesurvey.localhost".';
            exit(6);
        }

        //$capabilities = DesiredCapabilities::phantomjs();
        //$this->webDriver = RemoteWebDriver::create('http://localhost:4444/', $capabilities);
    }

    /**
     * 
     */
    public static function teardownAfterClass()
    {
        $result = \Survey::model()->deleteSurvey(self::$surveyId, true);
        if (!$result) {
            echo ('Fatal error: Could not clean up survey ' . self::$surveyId);
            exit(8);
        }
    }

    /**
     * Tear down fixture.
     */
    public function tearDown()
    {
        // Close Firefox.
        self::$webDriver->quit();
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

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . $domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            array(
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'lang' => 'pt'
            )
        );

        self::$webDriver->get($url);
        $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        $this->assertNotEmpty($submit);
        self::$webDriver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($submit)
        );
        $submit->click();

        $body = self::$webDriver->findElement(WebDriverBy::tagName('body'));
        $text = $body->getText();

        // There should be no PHP notice.
        $this->assertTrue(strpos($text, 'PHP notice') === false, $text);

        // NB: This is how to take a screenshot, if necessary.
        //$screenshot = self::$webDriver->takeScreenshot();
        //file_put_contents(__DIR__ . '/screenshot.png', $screenshot);

        self::$testHelper->deactivateSurvey(self::$surveyId);
    }
}
