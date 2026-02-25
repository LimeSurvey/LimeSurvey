<?php

namespace ls\tests;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * @since 2023-04-02
 */
class MandatorySoftTest extends TestBaseClassWeb
{

    /*
     * Check basic mandatory soft functionnality with multiple page
     * Warning : some part came for Vanilla theme with a lot of JS
     * @since 2023-05-09
     **/
    public function testMandatorySoftAction()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_MandatorySoftMultiPage.lss';
        self::importSurvey($surveyFile);
        $url = $this->getSurveyUrl();
        $questions = $this->getAllSurveyQuestions();
        try {
            self::$webDriver->get($url);
            self::$webDriver->next();
            /* Try to submit */
            self::$webDriver->next();
            /* Check if question Q00 is here */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $questions['Q00']->qid))),
                'Soft mandatory Q00 question are not in 1st page'
            );
            $mandatorysoftButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('mandatory-soft-alert-box-modal')
                )
            );
            /* Check if question Q00 mandatoiry are shown */
            $MandatoryTip = trim(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $questions['Q00']->qid . ' .ls-question-mandatory'))->getText());
            $this->assertEquals("Please note that you have not answered this question. You may continue without answering.", $MandatoryTip);
            /* Find the action button (theme dependant ?) */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('mandatory-soft-alert-box-modal'))),
                'Unable to find the action button after try to submit'
            );
            $mandatorysoftButton->click();
            /* Check if question Q01 is here */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $questions['G02Q02']->qid))),
                'Soft mandatory G02Q02 question are not in 2nd page after confirm soft mandatory for Q01'
            );
            /* Try to submit */
            self::$webDriver->next();
            /* Check if question Q01 is here */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $questions['G02Q02']->qid))),
                'Soft mandatory G02Q02 question are not in 2nd page after move next'
            );
            $MandatoryTip = trim(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $questions['G02Q02']->qid . ' .ls-question-mandatory'))->getText());
            $this->assertEquals("Please note that you have not answered this question. You may continue without answering.", $MandatoryTip);
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                . 'Screenshot taken.' . PHP_EOL
                . self::$testHelper->javaTrace($ex)
            );
        }
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /*
     * Check mandatory soft checkbox don't disable mandatory question
     * @since 2023-04-02
     **/
    public function testMandatorySoftAndMandatory()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_MandatorySoftBrokeMandatory.lss';
        self::importSurvey($surveyFile);
        $url = $this->getSurveyUrl();
        $questions = $this->getAllSurveyQuestions();
        $ManOnQid = $questions['ManOn']->qid;
        $ManOnSgqa = self::$surveyId . 'X' . $questions['ManOn']->gid . 'X' . $questions['ManOn']->qid;
        try {
            self::$webDriver->get($url);
            self::$webDriver->scrollToBottom();
            self::$webDriver->next();
            /* Check if question ManOn is here */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $ManOnQid))),
                'Mandatory question are not in page'
            );
            /* Check if question ManOn mandatory are shown */
            $MandatoryTip = trim(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory'))->getText());
            $this->assertEquals("This question is mandatory", $MandatoryTip);
            /* mandatory tip shown as error : BS dependent*/
            $MandatoryTipShownAsErrorElement = self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory.text-danger'));
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory.text-danger'))),
                'Mandatory tip don\'t have text-danger class'
            );
            /* Close the dialog box */
            $modalCloseButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#bootstrap-alert-box-modal .btn-outline-secondary')
                )
            );
            $modalCloseButton->click();
            /* Wait for modal close */
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('ls-button-submit')
                )
            );
            /* Enter value in ManOn and check if move next show end (using id added manually in survey) */
            /* manSoft have a checkbox name mandSoft checked in help text */
            self::$webDriver->answerTextQuestion($ManOnSgqa, 'Some value');
            self::$webDriver->scrollToBottom();
            self::$webDriver->next();
            /** @var $surveyCompletedElement RemoteWebElement */
            $surveyCompletedElement = self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('text-completed-survey')
                )
            );
            $this->assertTrue(
                !empty($surveyCompletedElement),
                'Completed are not shown after fill mandatory question'
            );
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                . 'Screenshot taken.' . PHP_EOL
                . self::$testHelper->javaTrace($ex)
            );
        }
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }

    /*
     * Close dialog box do not disable dialog box
     * In same page with close button #20409 mantis issue
     * In next page with Continue without answering #20433 mantis issue
     * @since 2025-02-25
     */
    public function testMandatoryCloseDialog()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_MandatorySoftCloseDialog.lss';
        self::importSurvey($surveyFile);
        $url = $this->getSurveyUrl();
        $questions = $this->getAllSurveyQuestions();
        try {
            self::$webDriver->get($url);
            /* Try to submit */
            self::$webDriver->scrollToBottom();
            self::$webDriver->next();
            /* Must come back to same */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $questions['G01Q01']->qid))),
                'Soft mandatory G01Q01 question are not in 1st page after try submit'
            );
            /* wait for mandatory soft dialog box, close it with close button*/
            $modalCloseButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#bootstrap-alert-box-modal .btn-close')
                )
            );
            $modalCloseButton->click();
            /* Try to submit again */
            self::$webDriver->scrollToBottom();
            self::$webDriver->next();
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $questions['G01Q01']->qid))),
                'Soft mandatory G01Q01 question are not in 1st page after try submit after using close button'
            );
            /* Continue without answering action */
            $mandatorysoftButtonG1 = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('mandatory-soft-alert-box-modal')
                )
            );
            $mandatorysoftButtonG1->click();
            /* Must be at page 2 / Group 2 */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $questions['G02Q02']->qid))),
                'Soft mandatory G02Q02 question are not in 2nd page after use Continue without answering action'
            );
            /* Try to move next */
            self::$webDriver->scrollToBottom();
            self::$webDriver->next();
            /* Must stay at page 2 / Group 2 */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $questions['G02Q02']->qid))),
                'Soft mandatory G02Q02 question are not in 2nd page after use Continue without answering action and move next'
            );
            /* finalize and check mandatory-soft-alert-box-modal is still there */
            $mandatorysoftButtonG2 = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('mandatory-soft-alert-box-modal')
                )
            );
            $mandatorysoftButtonG2->click();
            /* End page */
            $surveyCompletedElement = self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::id('text-completed-survey')
                )
            );
            $this->assertTrue(
                !empty($surveyCompletedElement),
                'Completed page not shown after bypassing soft mandatory questions via Continue without answering'
            );
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                . 'Screenshot taken.' . PHP_EOL
                . self::$testHelper->javaTrace($ex)
            );
        }
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
