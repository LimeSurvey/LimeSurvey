<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2023-04-02
 */
class MandatorySoftAndMandatoryTest extends TestBaseClassWeb
{

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_MandatorySoftBrokeMandatory.lss';
        self::importSurvey($surveyFile);
    }

    /* Check mandatory soft checkbox don't disable mandatory question */
    public function testMandatorySoftAndMandatory()
    {
        $url = $this->getSurveyUrl();
        $questions = $this->getAllSurveyQuestions();
        $ManOnQid = $questions['ManOn']->qid;
        $ManOnSgqa = self::$surveyId . 'X' . $questions['ManOn']->gid . 'X' . $questions['ManOn']->qid;
        try {
            self::$webDriver->get($url);
            self::$webDriver->next();
            /* Check if question ManOn is here */
            $this->assertEquals(
                1, 
                count(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid))),
                'Mandatory question are not in page'
            );
            /* Check if question ManOn mandatoiry are shown */
            $MandatoryTip = trim(self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory'))->getText());
            $this->assertEquals("This question is mandatory", $MandatoryTip);
            /* mandatory tip shown as error : BS dependent*/
            $MandatoryTipShownAsErrorElement = self::$webDriver->findElement(WebDriverBy::cssSelector('#question' . $ManOnQid . ' .ls-question-mandatory.text-danger'));
            $this->assertEquals(
                1, 
                count($MandatoryTipShownAsErrorElement),
                'Mandatory tipe don\' have text-danger class'
            );
            /* Enter value in ManOn and check if move next show end (using id added manually in survey */
            self::$webDriver->answerTextQuestion($ManOnSgqa, 'Some value');
            self::$webDriver->next();
            $this->assertEquals(
                1, 
                count(self::$webDriver->findElement(WebDriverBy::cssSelector('#text-completed-survey'))),
                'Completed are not shown after fill mandatory question'
            );
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
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
