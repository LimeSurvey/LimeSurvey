<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * @since 2019-08-05
 * @group plugins
 */
class FixedFunctionExpressionPluginTest extends TestBaseClassWeb
{

    /**
     * @inheritdoc
     * Activate needed plugins
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::installAndActivatePlugin('statFunctions');
        $surveyFile = self::$surveysFolder . '/survey_archive_statCountFunctionsTest.lsa';
        self::importSurvey($surveyFile);

    }

    /* Launch survey with an already submitted token */
    public function testPluginsStats()
    {
        $questions = $this->getAllSurveyQuestions();
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'token' => 'tokenTest',
                'newtest' => "Y",
            ]
        );
        try {
            self::$webDriver->get($url);
            sleep(1); // Page did not load properly

            /* 1st page */
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submit->click();
            sleep(1); // Needed ?
            /** Simple fixed value check **/
            $textToCompare = self::$webDriver->findElement(WebDriverBy::id('statCountQ00'))->getText();
            $this->assertEquals($textToCompare, "3", 'statCount(self.sgqa) usage broken : «' . $textToCompare ."» vs «3»");
            $textToCompare = self::$webDriver->findElement(WebDriverBy::id('statCountQ01'))->getText();
            $this->assertEquals($textToCompare, "3", 'statCount(Q01.sgqa) usage broken : «' . $textToCompare ."» vs «3»");
            $textToCompare = self::$webDriver->findElement(WebDriverBy::id('statCountIfQ00'))->getText();
            $this->assertEquals($textToCompare, "0", 'statCountIfQ00(self.sgqa,"NOT") usage broken : «' . $textToCompare ."» vs «0»");
            /** Relevance (and update) check **/
            self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('question' . $questions['Q01']->qid)),
                "Q01 is not hidden by relevance"
            );
            sleep(1);
            $sgqa = "Q".$questions['Q00']->qid;
            $Input = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa ));
            $Input->sendKeys('10');
            $this->assertTrue(
                self::$webDriver->findElement(WebDriverBy::id('question'.$questions['Q01']->qid))->isDisplayed(),
                "Q01 is not shown by relevance after update Q00"
            );
            /** Submitted VS not submitted **/
            $textToCompare = self::$webDriver->findElement(WebDriverBy::id('submitted'))->getText();
            $this->assertEquals(
                $textToCompare, 
                "3",
                'statCount(Q01.sgqa) usage broken in Q01: «' . $textToCompare ."» vs «3»"
            );
            $textToCompare = self::$webDriver->findElement(WebDriverBy::id('notSubmitted'))->getText();
            $this->assertEquals(
                $textToCompare, 
                "6",
                'statCount(Q01.sgqa) usage broken in Q01: «' . $textToCompare ."» vs «6»"
            );
            /* 2nd page */
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submit->click();
            sleep(1); // Needed ?
            /** Relevance on subquestion **/
            $sgqa = "Q".$questions['Q03']->qid;
            // Line to be relevant
            $lineRelevance = self::$webDriver->findElements(
                WebDriverBy::cssSelector("#javatbdQ" . $questions['SQ001']->parent_qid . "_S" . $questions['SQ001']->qid . ".ls-irrelevant")
            );
            $this->assertCount(0, $lineRelevance, 'Relevance is broken : SQ001 is irrelevant.');
            // Line to be irrelevant
            $lineRelevance = self::$webDriver->findElements(
                WebDriverBy::cssSelector("#javatbdQ" . $questions['SQ003']->parent_qid . '_S' . $questions['SQ003']->qid . ".ls-irrelevant")
            );
            $this->assertCount(1, $lineRelevance, 'Relevance is broken : ' . ("#javatbdQ" . $questions['SQ003']->parent_qid . '_S' . $questions['SQ003']->qid) . ' is relevant.');
            /** Text of subquestion **/
            $textToCompare = self::$webDriver->findElement(WebDriverBy::id('answertext'.$sgqa.'Q' . $questions['SQ001']->parent_qid . "_S" . $questions['SQ001']->qid))->getText();
            $this->assertEquals(
                $textToCompare, 
                "Event #1 (still 7 places)",
                'Text on subquestions broken «' . $textToCompare ."» vs «Event #1 (still 7 places)»"
            );
            
            
        } catch (\Exception $e) {
            $filename = __CLASS__ ."_". __FUNCTION__;
            self::$testHelper->takeScreenshot(self::$webDriver,$filename);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot ' .$filename . PHP_EOL . $e->getMessage()
            );
        }
    }

    /**
     * @inheritdoc
     * @todo Deactivate and uninstall plugins ?
     */
    public static function tearDownAfterClass(): void
    {
        self::deActivatePlugin('statFunctions');
        parent::tearDownAfterClass();
    }

}
