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
            /* Check if question Q00 mandatory are shown */
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
            /* Find the close button (#20409) : must be mandatory modal only */
            $modalCancelButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#bootstrap-alert-box-modal .btn-outline-secondary')
                )
            );
            $this->assertCount(
                0,
                self::$webDriver->findElements(WebDriverBy::id('mandatory-soft-alert-box-modal')),
                'The modal shown are mandatory soft, must be a mandatory'
            );
            $modalCancelButton->click();
            /* Check if question ManOn mandatory are shown */
            $MandatoryTip = trim(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory'))->getText());
            $this->assertEquals("This question is mandatory", $MandatoryTip);
            /* mandatory tip shown as error : BS dependent*/
            $MandatoryTipShownAsErrorElement = self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory.text-danger'));
            $this->assertTrue(
                !empty($MandatoryTipShownAsErrorElement),
                'Mandatory tip don\'t have text-danger class'
            );
            /* Enter value in ManOn and check if move next show end (using id added manually in survey */
            self::$webDriver->answerTextQuestion($ManOnSgqa, 'Some value');
            self::$webDriver->scrollToBottom();
            self::$webDriver->next();
            /* Must have mandatory soft element */
            $modalCloseButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector('#bootstrap-alert-box-modal [data-bs-dismiss]')
                )
            );
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('mandatory-soft-alert-box-modal'))),
                'No mandatory soft shown when there are only soft mandatory question'
            );
            $modalCloseButton->click();
            /* Click on close button must not disable mandatory-soft-alert-box-modal issue #20409 */
            self::$webDriver->scrollToBottom();
            self::$webDriver->next();
            /* Must still find modal soft dialog and button */
            $mandatorysoftButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('mandatory-soft-alert-box-modal')
                )
            );
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('mandatory-soft-alert-box-modal'))),
                'No mandatory soft shown after click on close'
            );
            $mandatorysoftButton->click();
            /* Completed with success */
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
}
