<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2017-11-13
 * @group ajaxmode
 */
class AjaxModeTest extends TestBaseClassWeb
{
    /**
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$webDriver->manage()->timeouts()->implicitlyWait(3);
    }

    /**
     * 
     */
    public static function teardownAfterClass()
    {
        parent::teardownAfterClass();
    }

    /**
     * 
     */
    public function testBasic()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_366446.lss';
        self::importSurvey($surveyFile);

        // Activate survey.
        self::$testHelper->activateSurvey(self::$surveyId);

        // Get questions.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }

        // Make sure there are no responses in database.
        $query = sprintf(
            'SELECT * FROM {{survey_%d}}',
            self::$surveyId
        );
        $db = \Yii::app()->getDb();
        var_dump($db->createCommand($query)->queryAll());

        // Execute survey.
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
            // Click welcome page.
            self::$webDriver->get($url);
            $nextButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $nextButton->click();

            // Find yes-no radio buttons, click "Yes".
            $items = self::$webDriver->findElements(WebDriverBy::cssSelector('ul.yesno-button li'));
            $items[0]->click();

            // Check that EM is reacting.
            $div = self::$webDriver->findElement(WebDriverBy::cssSelector('div#question' . $questions['q2']->qid));
            $this->assertEquals($div->getText(), 'The previous answer was FALSE');

            // Click "No".
            $items[1]->click();

            // Check EM.
            $div = self::$webDriver->findElement(WebDriverBy::cssSelector('div#question' . $questions['q2']->qid));
            $this->assertEquals($div->getText(), 'The previous answer was TRUE');

            // Click submit.
            $submitButton = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submitButton->click();

            // Check so that we see end page.
            $completed = self::$webDriver->findElement(WebDriverBy::cssSelector('div.completed-text'));
            $this->assertEquals($completed->getText(), "Thank you!\nYour survey responses have been recorded.");

        } catch (NoSuchElementException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/AjaxModeTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
        // Check answer in database.
        $query = sprintf(
            'SELECT * FROM {{survey_%d}}',
            self::$surveyId
        );
        var_dump($db->createCommand($query)->queryAll());
    }
}
