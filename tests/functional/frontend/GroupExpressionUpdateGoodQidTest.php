<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2022-04-20
 * @group expression
 */
class GroupExpressionUpdateGoodQidTest extends TestBaseClassWeb
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
        $surveyFile =  'tests/data/surveys/limesurvey_survey_groupEMupdateAndGoodQID.lss';
        self::importSurvey($surveyFile);
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        self::$surveyUrl = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => "Y",
            ]
        );
    }

    /**
     * Check if group text was updated
     * @see https://bugs.limesurvey.org/view.php?id=17967
     * 17967: Group description are not updated by javascript expression
     **/
    public function testGroupExpresssionAndGoodQid()
    {
        $web = self::$webDriver;
        $questions = $this->getAllSurveyQuestions();
        try {
            $web->get(self::$surveyUrl);
            $web->next();
            /* Group update */
            $inGroupTitleCurrent = $web->findElement(WebDriverBy::cssSelector('.group-title .G1Q00001NAOK'))->getText();
            $this->assertEquals("", $inGroupTitleCurrent, "Group title G1Q00001NAOK seems not empty, get “".$inGroupTitleCurrent."”");
            $textSgqa = 'Q' .$questions['G1Q00001']->qid;
            $web->answerTextQuestion($textSgqa, 'CheckUpdated');
            $inGroupTitleCurrent = $web->findElement(WebDriverBy::cssSelector('.group-title .G1Q00001NAOK'))->getText();
            $this->assertEquals("CheckUpdated", $inGroupTitleCurrent, "Group title seems not updated, get “".$inGroupTitleCurrent."”");
            $inQuestionHelpCurrent = $web->findElement(WebDriverBy::cssSelector('#question' . $questions['G1Q00002']->qid . ' .G1Q00001NAOK'))->getText();
            $this->assertEquals("CheckUpdated", $inQuestionHelpCurrent, "Group title in quetsion help seems not updated, get “".$inQuestionHelpCurrent."”");
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
     * Check if last question is not used in previous condition
     * @see https://bugs.limesurvey.org/view.php?id=17966
     * 17966: twig processString assumes wrong question Id
     **/
    public function testGoodQid()
    {
        $web = self::$webDriver;
        $questions = $this->getAllSurveyQuestions();
        try {
            $web->get(self::$surveyUrl);
            $web->next();
            /* G1Q00004 must be hidden */
            $question4 = $web->findElement(WebDriverBy::id('question' . $questions['G1Q00004']->qid));
            $this->assertFalse($question4->isDisplayed());
            /* Multiple numeric question update */
            $answerBSgqa = 'Q' .$questions['G1Q00003']->qid . '_S2385';
            $web->answerTextQuestion($answerBSgqa, 40);
            /* Validate current total value */
            $totalvalue = $web->findElement(WebDriverBy::id('totalvalue_' . $questions['G1Q00003']->qid))->getText();
            $this->assertEquals("40", $totalvalue, "Total value are not updated");
            /* remaining value */
            $remainingvalue = $web->findElement(WebDriverBy::id('remainingvalue_' . $questions['G1Q00003']->qid))->getText();
            $this->assertEquals("60", $remainingvalue, "Remaining value are not updated");
            /* Check the A checkbox */
            $ACheckboxSGQ = 'Q' .$questions['G1Q00002']->qid . '_S2379';
            $web->findElement(WebDriverBy::cssSelector('#javatbd' . $ACheckboxSGQ. ' label'))->click();
            /* G1Q00004 must be shown */
            $this->assertTrue($question4->isDisplayed());
            /* Check too */
            $answerCSgqa = 'Q' .$questions['G1Q00003']->qid . '_S2389';
            $web->answerTextQuestion($answerCSgqa, 60);
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
