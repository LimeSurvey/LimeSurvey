<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2017-10-27
 * @group datevalidation
 */
class DateTimeValidationTest extends TestBaseClassWeb
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
        parent::setupBeforeClass();

        \Yii::app()->session['loginID'] = 1;

        $surveyFile = __DIR__ . '/../data/surveys/limesurvey_survey_834477.lss';
        if (!file_exists($surveyFile)) {
            die('Fatal error: found no survey file');
        }

        $translateLinksFields = false;
        $newSurveyName = null;
        $result = \importSurveyFile(
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
            file_put_contents(__DIR__ . '/../_output/tmp.png', $screenshot);
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
            // Wait max 10 second to find this div.
            $this->webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::className('completed-text')
                )
            );
            $div = $this->webDriver->findElement(WebDriverBy::className('completed-text'));
            $this->assertNotEmpty($div);
        } catch (NoSuchElementException $ex) {
            $screenshot = $this->webDriver->takeScreenshot();
            file_put_contents(__DIR__ . '/../_output/tmp.png', $screenshot);
            $this->assertFalse(
                true,
                'Screenshot in ' . __DIR__ . '/tmp.png' . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
