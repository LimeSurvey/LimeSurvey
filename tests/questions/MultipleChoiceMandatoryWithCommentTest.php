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
     * Test submit question without comment.
     */
    public function testNoComment()
    {
        // Import survey.
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_479717.lss';
        self::importSurvey($surveyFile);
        self::$testHelper->activateSurvey(self::$surveyId);

        // To make writing shorter.
        $web  = self::$webDriver;

        $sgqa = $this->getSgqa();
        $url  = $this->getSurveyUrl();

        try {
            self::$webDriver->get($url);
            sleep(2);
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
        return $sgqa;
    }
}
