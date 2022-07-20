<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2019-06-10
 * @author Denis Chenu
 * @group arraynumber
 */
class JsPhpCompareTest extends TestBaseClassWeb
{
    /**
     * Check array number ExpressionScript Engine system
     */
    public function testJsPhpCompareTestExpression()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_checkCompareTestSurvey.lss';
        self::importSurvey($surveyFile);

        // Go to preview.
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
        // Get questions.
        $questionObjects = \Question::model()->findAll("sid = :sid AND parent_qid = 0",array(":sid"=>self::$surveyId));
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }
        /* Used vars */
        $HiddenByRelevanceQuestion = $questions['HiddenByRelevance'];
        $singleChoiceQuestion1 = $questions['Q00'];
        $singleChoiceQuestion2 = $questions['Q01'];
        $sgqaHiddenByRelevance = $HiddenByRelevanceQuestion->sid."X".$HiddenByRelevanceQuestion->gid."X".$HiddenByRelevanceQuestion->qid;
        $sgqaQuestion1 = $singleChoiceQuestion1->sid."X".$singleChoiceQuestion1->gid."X".$singleChoiceQuestion1->qid;
        $sgqaQuestion2 = $singleChoiceQuestion2->sid."X".$singleChoiceQuestion2->gid."X".$singleChoiceQuestion2->qid;
        try {

            self::$webDriver->get($url);
            $elementsRelevance = self::$webDriver->findElements(
                WebDriverBy::cssSelector("#question".$HiddenByRelevanceQuestion->qid.".ls-irrelevant")
            );
            $this->assertCount(1, $elementsRelevance, 'Relevance Element not hidden');
            // Click on the radio
            self::$webDriver->findElement(WebDriverBy::id('answer'.$sgqaQuestion1.'5'))->click();
            self::$webDriver->findElement(WebDriverBy::id('answer'.$sgqaQuestion2.'20'))->click();

            // relevanceJsQuestion be shown
            $elementsRelevance = self::$webDriver->findElements(
                WebDriverBy::cssSelector("#question".$HiddenByRelevanceQuestion->qid.".ls-irrelevant")
            );
            $this->assertCount(0, $elementsRelevance, 'Relevance Element not shown');
            // Check the string
            $checkJs = self::$webDriver->findElement(WebDriverBy::id('TestJS1'));
            $checkJsText = $checkJs->getText();
            $this->assertEquals('Q00 lt Q01 : true', $checkJsText,"Current text of TestJS1 is \"".$checkJsText."\"");
            $checkJs = self::$webDriver->findElement(WebDriverBy::id('TestJS2'));
            $checkJsText = $checkJs->getText();
            $this->assertEquals('Q00+"" lt Q01+"" :', $checkJsText,"Current text of TestJS2 is \"".$checkJsText."\"");
            // Fill some value to relevant question
            self::$webDriver->findElement(WebDriverBy::id('answer'.$sgqaHiddenByRelevance))->sendKeys("answered");

            self::$webDriver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
            sleep(1);

            // Click next (to do the test on PHP)
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submit->click();
            
            // Check PHP string element
            $checkPhp = self::$webDriver->findElement(WebDriverBy::id('TestPHP1'));
            $checkPhpText = $checkPhp->getText();
            $this->assertEquals('Q00 lt Q01 : 1', $checkPhpText,"Current text of TestPHP1 is \"".$checkPhpText."\"");
            $checkPhp = self::$webDriver->findElement(WebDriverBy::id('TestPHP2'));
            $checkPhpText = $checkPhp->getText();
            $this->assertEquals('Q00+"" lt Q01+"" :', $checkPhpText,"Current text of TestPHP2 is \"".$checkPhpText."\"");
            // Add other check ?
        } catch (\Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/checkCompareTestSurvey.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
