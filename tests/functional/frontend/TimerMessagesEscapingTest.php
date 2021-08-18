<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;
use Question;

/**
 * @since 2021-08-17
 */
class TimerMessagesEscapingTest extends TestBaseClassWeb
{
    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_HtmlInTimerMessages.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->enablePreview();
    }

    /**
     * Test HTML in timer messages is properly escaped.
     */
    public function testMessagesWithHtml()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'lang' => 'en'
            ]
        );

        try {
            self::$webDriver->get($url);

            // Wait max 10 second to find the messages.
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::className('ls-timer-message')
                )
            );

            $timerAlert = self::$webDriver->findElement(WebDriverBy::xpath("//*[contains(text(),'<script>console.log(\"foobar1\");</script>')]"));
            $this->assertNotEmpty($timerAlert, "Time limit countdown message not properly escaped");
            $timerAlert = self::$webDriver->findElement(WebDriverBy::xpath("//*[contains(text(),'<script>console.log(\"foobar2\");</script>')]"));
            $this->assertNotEmpty($timerAlert, "Time limit expiry message not properly escaped");
            $timerAlert = self::$webDriver->findElement(WebDriverBy::xpath("//*[contains(text(),'<script>console.log(\"foobar3\");</script>')]"));
            $this->assertNotEmpty($timerAlert, "1st time limit warning message not properly escaped");
            $timerAlert = self::$webDriver->findElement(WebDriverBy::xpath("//*[contains(text(),'<script>console.log(\"foobar4\");</script>')]"));
            $this->assertNotEmpty($timerAlert, "2nd time limit warning message not properly escaped");
        } catch (\Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder . '/TimerMessagesEscapingTest_testMessagesWithHtml.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL . 'Screenshot in ' . $filename . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
