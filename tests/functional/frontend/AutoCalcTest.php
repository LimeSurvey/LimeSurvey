<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2018-05-29
 * @group autocalc
 */
class AutoCalcTest extends TestBaseClassWeb
{
    /**
     * 
     */
    public function testBasic()
    {
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_351443.lss';
        self::importSurvey($surveyFile);

        // Preview survey.
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'lang' => 'pt'
            ]
        );

        // Get questions.
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }

        try {
            // Get first page.
            self::$webDriver->get($url);

            $sgqa = 'Q' . $questions['CenterID']->qid;
            $centerIdInput = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $centerIdInput->sendKeys('90');

            $sgqa = 'Q' . $questions['MinimumDataOnly']->qid . '_CN';
            $minimumDataOnlyNo = self::$webDriver->findElement(WebDriverBy::id('javatbd' . $sgqa));
            $minimumDataOnlyNo->click();

            $sgqa = 'Q' . $questions['weight']->qid;
            $weightInput = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $weightInput->sendKeys('90');

            $sgqa = 'Q' . $questions['height']->qid;
            $heightInput = self::$webDriver->findElement(WebDriverBy::id('answer' . $sgqa));
            $heightInput->sendKeys('50');

            $sgqa = 'Q' . $questions['BMIReport']->qid;
            $bmiReport = self::$webDriver->findElement(WebDriverBy::id('ls-question-text-' . $sgqa));
            $bmi = $bmiReport->getText();

            $this->assertEquals('Body Mass Index (BMI) is 25.308.', $bmi, $bmi);

        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
