<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;

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
     * Test date question with default answer and maximum date today.
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

        try {
            self::$webDriver->get($url);

            // Submit the page with default answer.
            self::$webDriver->submit('ls-button-submit');

            // Wait max 5 second to find this div.
            self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::className('completed-text')
                )
            );
            $div = self::$webDriver->findElement(WebDriverBy::className('completed-text'));
            $this->assertNotEmpty($div);

        } catch (\Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
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
