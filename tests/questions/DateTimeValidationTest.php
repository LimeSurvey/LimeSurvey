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
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder.'/limesurvey_survey_834477.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->enablePreview();
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

        self::$webDriver->get(
            sprintf(
                'http://%s/index.php/%d?newtest=Y&lang=pt',
                $domain,
                self::$surveyId
            )
        );

        try {
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            file_put_contents($this->screenshotsFolder . '/tmp.png', $screenshot);
            $this->assertFalse(
                true,
                'Screenshot in ' . $this->screenshotsFolder . '/tmp.png' . PHP_EOL . $ex->getMessage()
            );
        }

        $this->assertNotEmpty($submit);
        self::$webDriver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($submit)
        );
        $submit->click();

        // After submit we should see the complete page.
        try {
            // Wait max 10 second to find this div.
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::className('completed-text')
                )
            );
            $div = self::$webDriver->findElement(WebDriverBy::className('completed-text'));
            $this->assertNotEmpty($div);
        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            file_put_contents($this->screenshotsFolder . '/tmp.png', $screenshot);
            $this->assertFalse(
                true,
                'Screenshot in ' . $this->screenshotsFolder . '/tmp.png' . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
