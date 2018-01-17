<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * Test survey when all other questions relevance is 0, due to first 
 * question being yes or no.
 * @since 2017-11-16
 * @group screenout
 */
class ScreenOutTest extends TestBaseClassWeb
{
    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_186734.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Test answer "No answer" on first question.
     */
    public function testNoAnswer()
    {
        // Preview survey.
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

            // Click next.
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();

            // Check that we see completed text.
            $completed = self::$webDriver->findElement(WebDriverBy::cssSelector('div.completed-text'));
            $this->assertNotEmpty($completed);

        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/ScreenOutTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    /**
     * 
     */
    public function testYes()
    {
        // Get preview link.
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

            //javatbd186734X355X1911Y
            $answerId = 'javatbd' . self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['q1']->qid . 'Y';
            $yesButton = self::$webDriver->findElement(WebDriverBy::id($answerId));
            $yesButton->click();

            // Click next.
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();

            // answer186734X355X1912
            $question2Id = 'answer' . self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['q2']->qid;
            $question2 = self::$webDriver->findElement(WebDriverBy::id($question2Id));
            $this->assertNotEmpty($question2);

            // Click next again.
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();

            // Check that we see completed text.
            $completed = self::$webDriver->findElement(WebDriverBy::cssSelector('div.completed-text'));
            $this->assertNotEmpty($completed);

        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/ScreenOutTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
