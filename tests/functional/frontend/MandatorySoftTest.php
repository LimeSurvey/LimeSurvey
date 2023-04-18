<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2023-04-02
 */
class MandatorySoftTest extends TestBaseClassWeb
{
    /* Check mandatory soft checkbox don't disable mandatory question */
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
            self::$webDriver->next();
            /* Check if question ManOn is here */
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('question' . $ManOnQid))),
                'Mandatory question are not in page'
            );
            /* Check if question ManOn mandatoiry are shown */
            $MandatoryTip = trim(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory'))->getText());
            $this->assertEquals("This question is mandatory", $MandatoryTip);
            /* mandatory tip shown as error : BS dependent*/
            $MandatoryTipShownAsErrorElement = self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory.text-danger'));
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory.text-danger'))),
                'Mandatory tipe don\'t have text-danger class'
            );
            /* Enter value in ManOn and check if move next show end (using id added manually in survey */
            self::$webDriver->answerTextQuestion($ManOnSgqa, 'Some value');
            self::$webDriver->next();
            $this->assertTrue(
                !empty(self::$webDriver->findElement(WebDriverBy::id('text-completed-survey'))),
                'Completed are not shown after fill mandatory question'
            );
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }
        self::$testSurvey->delete();
        self::$testSurvey = null;
    }
}
