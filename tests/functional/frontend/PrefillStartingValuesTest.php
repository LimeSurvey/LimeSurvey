<?php

namespace ls\tests;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2026-05-05
 */
class PrefillStartingValuesTest extends TestBaseClassWeb
{
    /**
     * Import needed survey
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_PrefillStartingValues.lss';
        self::importSurvey($surveyFile);
    }

    /*
     * Check if prefilling values by URL work
     * @since 2026-05-05
     **/
    public function testStartingValuesPrefilled()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'PREFILL1' => 'A3',
                'PREFILL2' => 'prefilled',
            ]
        );
        $questions = $this->getAllSurveyQuestions();
        try {
            self::$webDriver->get($url);
            /* get to 1st question page */
            self::$webDriver->next();
            /* check if answer of PREFILL1 is A3 */
            $sgqa = self::$surveyId . 'X' . $questions['PREFILL1']->gid . 'X' . $questions['PREFILL1']->qid;
            $dropdpown = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $this->assertEquals('A3', $dropdpown->getAttribute('value'));
            /* Select Y to Q00 */
            $sgqa = self::$surveyId . 'X' . $questions['Q00']->gid . 'X' . $questions['Q00']->qid;
            $yesradio1 = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa . "Y"));
            $yesradio1->click();
            /* get to 2nd question page */
            self::$webDriver->next();
            /* Select Y to Q00 */
            $sgqa = self::$surveyId . 'X' . $questions['Q00Copy']->gid . 'X' . $questions['Q00Copy']->qid;
            $yesradio2 = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa . "Y"));
            $yesradio2->click();
            /* check if answer of PREFILL2 is prefilled */
            $sgqa = self::$surveyId . 'X' . $questions['PREFILL2']->gid . 'X' . $questions['PREFILL2']->qid;
            $text = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $this->assertEquals('prefilled', $text->getAttribute('value'));
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                . 'Screenshot taken.' . PHP_EOL
                . self::$testHelper->javaTrace($ex)
            );
        }
    }
}
