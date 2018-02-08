<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @since 2018-02-08
 * @group multmand
 */
class MultipleChoiceMandatoryWithComment extends TestBaseClassWeb
{

    /**
     * Import and activate survey at every test.
     */
    public function setup()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_479717.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Delete test survey after every test.
     */
    public function tearDown()
    {
        if (self::$testSurvey) {
            self::$testSurvey->delete();
            self::$testSurvey = null;
        }
    }
    /**
     * Test submit question without comment.
     */
    public function testNoComment()
    {
        // To make writing shorter.
        $web  = self::$webDriver;

        list($sgqa, $subquestions) = $this->getSgqa();
        $url = $this->getSurveyUrl();
        $sid = self::$testSurvey->sid;
        $dbo = \Yii::app()->getDb();

        try {
            self::$webDriver->get($url);

            // Click "First"
            $label = $web->findElement(WebDriverBy::id('label-answer' . $sgqa . 'SQ001'));
            $label->click();

            // Submit
            $web->submit();
            sleep(2);

            $query = "SELECT * FROM {{survey_$sid}}";
            $answers = $dbo->createCommand($query)->queryAll();

            $this->assertCount(1, $answers, 'Exactly one answer');
            $this->assertEquals('Y', $answers[0][$sgqa . 'SQ001'], 'Checkbox is Y');
            $this->assertEmpty($answers[0][$sgqa . 'SQ001comment'], 'No comment');

            // Check db
        } catch (\Exception $ex) {
            self::$testHelper->takeScreenshot($web, 'MultipleChoiceMandatoryWithComment');
            $this->assertFalse(
                true,
                'Url: ' . $url . PHP_EOL
                .  'Screenshot taken.' . PHP_EOL
                .  self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Never check the box, just write a comment. The box should check automatically.
     */
    public function testOnlyComment()
    {
        
    }

    /**
     * @return array
     */
    protected function getSgqa()
    {
        // Get questions.
        // TODO: Use \createFieldMap instead?
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $questionObjects = $survey->groups[0]->questions;
        $questions = [];
        foreach ($questionObjects as $q) {
            $questions[$q->title] = $q;
        }
        $subquestions = [];
        foreach ($questions['q1']->subquestions as $subq) {
            $subquestions[$subq->title] = $subq;
        }
        $sgqa = self::$surveyId . 'X' . $survey->groups[0]->gid . 'X' . $questions['q1']->qid;
        return [$sgqa, $subquestions];
    }
}
