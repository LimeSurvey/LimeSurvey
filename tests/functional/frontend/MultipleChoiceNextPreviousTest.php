<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2017-11-14
 * @group multiplechoice
 */
class MultipleChoiceNextPreviousTest extends TestBaseClassWeb
{
    /**
     * 
     */
    public function testNextPrevious()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_583999.lss';
        self::importSurvey($surveyFile);

        // Go to preview.
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

        // Get questions.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }

        try {

            self::$webDriver->get($url);

            // Click first checkbox.
            $lis = self::$webDriver->findElements(WebDriverBy::cssSelector('li label'));
            $this->assertCount(3, $lis);
            $lis[0]->click();

            // Click next.
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submit->click();

            // Click previous..
            $prev = self::$webDriver->findElement(WebDriverBy::id('ls-button-previous'));
            $prev->click();
            sleep(1);  // TODO: Does not work without this.

            // Click next.
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submit->click();

            // Click previous..
            $prev = self::$webDriver->findElement(WebDriverBy::id('ls-button-previous'));
            $prev->click();

            // Check value of checkbox.
            $sgqa = 'Q' . $questions['q2']->qid;
            $checkbox = self::$webDriver->findElement(WebDriverBy::id('java' . $sgqa . 'SQ001'));
            $this->assertEquals('Y', $checkbox->getAttribute('value'));

        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/MultipleChoiceNextPreviousTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
