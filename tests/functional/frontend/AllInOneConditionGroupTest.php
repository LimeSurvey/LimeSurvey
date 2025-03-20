<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2022-04-20
 * @group expression
 */
class AllInOneConditionGroupTest extends TestBaseClassWeb
{

    /** keep surveyurl */
    protected static $surveyUrl;

    /**
     * Import survey before test
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey.
        $surveyFile =  'tests/data/surveys/limesurvey_survey_allinoneCondition.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Check if group condition work on all in one survey
     * @see https://bugs.limesurvey.org/view.php?id=18035
     **/
    public function testAllInOneConditionGroup()
    {

        $web = self::$webDriver;
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('https://' . self::$domain . '/index.php');
        $surveyUrl = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => "Y",
            ]
        );
        $questions = $this->getAllSurveyQuestions();
        try {
            $web->get($surveyUrl);
            /* G02Q02 must be hidden */
            $questionG02Q02 = $web->findElement(WebDriverBy::id('question' . $questions['G02Q02']->qid));
            $this->assertFalse($questionG02Q02->isDisplayed());
            /* click on F Q00 : shown G02Q02 */
            $baseSGQ = 'Q' .$questions['Q00']->qid;
            $web->findElement(WebDriverBy::id('javatbd' . $baseSGQ . '_CF'))->click();
            $this->assertTrue($questionG02Q02->isDisplayed());
            /* Update G02Q02 text : check group descriptin */
            $textSgqa = 'Q' .$questions['G02Q02']->qid;
            $web->answerTextQuestion($textSgqa, 'CheckUpdated');
            $checkInGroupG02Q02Text = $web->findElement(WebDriverBy::id('checkInGroupG02Q02'))->getText();
            $this->assertEquals("CheckUpdated", $checkInGroupG02Q02Text, "checkInGroupG02Q02Text seems not updated, get “" . $checkInGroupG02Q02Text . "”");
            /* click on M Q00 : hide G02Q02*/
            $baseSGQ = 'Q' .$questions['Q00']->qid;
            $web->findElement(WebDriverBy::id('javatbd' . $baseSGQ . '_CM'))->click();
            $this->assertFalse($questionG02Q02->isDisplayed());
            $checkInGroupG02Q02Text = $web->findElement(WebDriverBy::id('checkInGroupG02Q02'))->getText();
            $this->assertEquals("", $checkInGroupG02Q02Text, "checkInGroupG02Q02Text seems not updated by relevance, get “" . $checkInGroupG02Q02Text . "”");
        } catch (\Exception $e) {
            $filename = __CLASS__ ."_". __FUNCTION__;
            self::$testHelper->takeScreenshot(self::$webDriver,$filename);
            $this->assertFalse(
                true,
                'Url: ' . self::$surveyUrl . PHP_EOL .
                'Screenshot ' .$filename . PHP_EOL . $e->getMessage()
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }
}
