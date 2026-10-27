<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * Answer options filtered out by array_filter must not be saved for list and ranking questions,
 * even if submitted without JavaScript.
 *
 * @see https://bugs.limesurvey.org/view.php?id=9774
 */
class ArrayFilterServerCheckTest extends TestBaseClass
{
    /**
     * Import and activate the survey: Multiple choice Q1 (A1, A2, A3) on the first page filters
     * list radio Q2 and ranking Q3 (both A1, A2, A3) on the second page.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_497740_array_filter_list_ranking_server_check.lss');
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Reset request and session data
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * Submitted list and ranking answers that are filtered out are reset, the others are kept.
     */
    public function testFilteredAnswerOptionsAreNotSaved()
    {
        $q1 = $this->getQuestion('Q1');
        $q2 = $this->getQuestion('Q2');
        $q3 = $this->getQuestion('Q3');
        $q3Ranks = array_map(
            function ($subquestion) use ($q3) {
                return 'Q' . $q3->qid . '_S' . $subquestion->qid;
            },
            \Question::model()->findAll(['condition' => 'parent_qid = :qid', 'order' => 'question_order', 'params' => [':qid' => $q3->qid]])
        );

        $this->startSurvey();
        // First page: Only A1 and A3 are checked in Q1
        $_POST = [
            'relevance' . $q1->qid => 1,
            'relevanceG0'          => 1,
            $this->getSubquestionField($q1, 'A1') => 'Y',
            $this->getSubquestionField($q1, 'A3') => 'Y',
            'movenext'             => 'movenext',
            'thisstep'             => 1,
            'sid'                  => self::$surveyId,
        ];
        \LimeExpressionManager::NavigateForwards();

        // Second page: Filtered option A2 is submitted in both questions
        $_POST = [
            'relevance' . $q2->qid => 1,
            'relevance' . $q3->qid => 1,
            'relevanceG1'          => 1,
            'Q' . $q2->qid         => 'A2',
            $q3Ranks[0]            => 'A2',
            $q3Ranks[1]            => 'A3',
            'movesubmit'           => 'movesubmit',
            'thisstep'             => 2,
            'sid'                  => self::$surveyId,
        ];
        $result = \LimeExpressionManager::NavigateForwards();
        $this->assertTrue($result['finished'], 'The survey is submitted');

        $response = \Response::model(self::$surveyId)->find();
        $this->assertNull($response->getAttribute('Q' . $q2->qid), 'Filtered list answer is not saved');
        $this->assertSame('["A3"]', $response->getAttribute('Q' . $q3->qid), 'Filtered ranking answer is not saved and A3 moves up to the first rank');
    }

    /**
     * Start the survey in group by group mode
     */
    private function startSurvey()
    {
        \Yii::app()->setConfig('surveyID', self::$surveyId);
        \Yii::app()->setController(new DummyController('dummyid'));
        \buildsurveysession(self::$surveyId);
        \LimeExpressionManager::StartSurvey(
            self::$surveyId,
            'group',
            self::$testHelper->getSurveyOptions(self::$surveyId),
            false,
            0
        );
        \LimeExpressionManager::NavigateForwards();
    }

    /**
     * Get a question of the test survey by its code
     * @param string $title the question code
     * @return \Question
     */
    private function getQuestion($title)
    {
        return \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'title' => $title]);
    }

    /**
     * Get the response field name of a subquestion
     * @param \Question $question the parent question
     * @param string $title the subquestion code
     * @return string
     */
    private function getSubquestionField($question, $title)
    {
        $subquestion = \Question::model()->findByAttributes(['parent_qid' => $question->qid, 'title' => $title]);
        return 'Q' . $question->qid . '_S' . $subquestion->qid;
    }
}
