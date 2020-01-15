<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2019-06-10
 * @author Denis Chenu
 * @group arraynumber
 */
class ArrayNumberCheckboxTest extends TestBaseClassWeb
{
    /**
     * Check array number Expression Manager system
     */
    public function testArrayCheckboxExpression()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_ArrayNumberCheckbox.lss';
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
        $checkboxQuestion = $questions["question"];
        $checkboxBaseSGQ = $checkboxQuestion->sid."X".$checkboxQuestion->gid."X".$checkboxQuestion->qid;
        $relevanceJsQuestion = $questions["QHiddenJS"];
        $relevancePHPQuestion = $questions["QHiddenPHP"];
        try {

            self::$webDriver->get($url);
            $elementsRelevance = self::$webDriver->findElements(
                WebDriverBy::cssSelector("#question".$relevanceJsQuestion->qid.".ls-irrelevant")
            );
            $this->assertCount(1, $elementsRelevance, 'JS Element not hidden');
            // Check the count
            $countJs = self::$webDriver->findElement(WebDriverBy::id("countJs"));
            $countJsText = $countJs->getText();
            $this->assertEquals('count(self) : 0', $countJsText);

            // Click on 3 checkbox, count
            self::$webDriver->findElement(WebDriverBy::cssSelector('#javatbd'.$checkboxBaseSGQ.'SY002 .answer_cell_SX002'))->click(); // Click the cell (js must click the checkbox), and td hide the real checkbox
            self::$webDriver->findElement(WebDriverBy::cssSelector('#javatbd'.$checkboxBaseSGQ.'SY003 .answer_cell_SX001'))->click(); 
            self::$webDriver->findElement(WebDriverBy::cssSelector('#javatbd'.$checkboxBaseSGQ.'SY003 .answer_cell_SX002'))->click();

            // relevanceJsQuestion be shown
            $elementsRelevance = self::$webDriver->findElements(
                WebDriverBy::cssSelector("#question".$relevanceJsQuestion->qid.".ls-irrelevant")
            );
            $this->assertCount(0, $elementsRelevance, 'JS Element not shown');
            // Check the count
            $countJs = self::$webDriver->findElement(WebDriverBy::id('countJs'));
            $countJsText = $countJs->getText();
            $this->assertEquals('count(self) : 3', $countJsText);

            // Hide element again
            self::$webDriver->findElement(WebDriverBy::cssSelector('#javatbd'.$checkboxBaseSGQ.'SY003 .answer_cell_SX002'))->click();
            // Check the count
            $countJs = self::$webDriver->findElement(WebDriverBy::id('countJs'));
            $countJsText = $countJs->getText();
            $this->assertEquals('count(self) : 2', $countJsText);

            // Click next (to do the test on PHP)
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submit->click();

            // Check PHP count
            $countPHP = self::$webDriver->findElement(WebDriverBy::id('countPHP'));
            $countPHPText = $countPHP->getText();
            $this->assertEquals('count(that.question) : 2', $countPHPText);
            // relevancePHPQuestion be hidden
            $elementsRelevance = self::$webDriver->findElements(
                WebDriverBy::cssSelector("#question".$relevancePHPQuestion->qid.".ls-irrelevant")
            );
            $this->assertCount(1, $elementsRelevance, 'PHP Element not hidden');
            // Click previous.
            $prev = self::$webDriver->findElement(WebDriverBy::id('ls-button-previous'));
            $prev->click();

            // Show (mandatory element)
            self::$webDriver->findElement(WebDriverBy::cssSelector('label[for="cbox'.$checkboxBaseSGQ.'SY003_SX002"]'))->click();
            // Try to move next (must be disable)
            $submit = self::$webDriver->findElement(WebDriverBy::id('ls-button-submit'));
            $submit->click();
            
            // Check with #bootstrap-alert-box-modal .modal-body : todo : find a way without checking boostrap-modal work too …
            // Commented : work locally, not with travis
            //~ $modalBody = self::$webDriver->findElement(
                //~ WebDriverBy::cssSelector("#bootstrap-alert-box-modal .modal-body")
            //~ );
            //~ $modalBodyText = trim($modalBody->getText());// trim since thare are \t and \n and other [:space:]
            //~ $this->assertEquals('One or more mandatory questions have not been answered. You cannot proceed until these have been completed.', $modalBodyText);
            $elementsRelevanceMandatory=self::$webDriver->findElements(WebDriverBy::cssSelector("#question".$relevanceJsQuestion->qid." .ls-question-mandatory"));
            $this->assertCount(1, $elementsRelevance, 'Move next not disable with mandatory question');

        } catch (Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder.'/ArrayNumberCheckboxTest.png';
            file_put_contents($filename, $screenshot);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL .
                'Screenshot in ' .$filename . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
