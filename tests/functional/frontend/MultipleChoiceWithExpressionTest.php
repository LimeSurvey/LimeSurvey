<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;

/**
 * @since 2017-12-01
 * @group multem
 */
class MultipleChoiceWithExpressionTest extends TestBaseClassWeb
{
    /**
     * 
     */
    public function testBasic()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_352985.lss';
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
        $subquestions = [];
        foreach ($questions['Q1']->subquestions as $subq) {
            $subquestions[$subq->title] = $subq;
        }
        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['Q1']->qid . '123'; // 123 = first subquestion title.

        try {
            // Get first page.
            self::$webDriver->get($url);

            // Click on first multiple choice checkbox.
            $label = self::$webDriver->findElement(
                WebDriverBy::cssSelector(
                    sprintf(
                        'label[for="%s"]',
                        'answer' . $sgqa
                    )
                )
            );
            $label->click();

            // Check that equation reacts.
            $equation = self::$webDriver->findElement(WebDriverBy::id('question' . $questions['equation1']->qid));
            $equestionText = $equation->getText();
            $trues = substr_count($equestionText, 'true');
            $this->assertEquals(2, $trues, 'Found two "true"');

            $label->click();
            $equestionText = $equation->getText();
            $trues = substr_count($equestionText, 'true');
            $this->assertEquals(0, $trues, 'Found no "true"');
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
