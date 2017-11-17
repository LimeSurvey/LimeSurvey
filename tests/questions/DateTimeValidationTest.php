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
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'lang' => 'pt'
            ]
        );

        self::$webDriver->get($url);

        try {
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/DateTimeValidationTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' . $filename . PHP_EOL . $ex->getMessage()
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
            $screenshot = $this->webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/DateTimeValidationTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
