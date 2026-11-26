<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * A ranking question used as array filter source filters the subquestions of the filtered question
 * by the ranked items.
 *
 * @see https://bugs.limesurvey.org/view.php?id=20883
 */
class ArrayFilterByRankingTest extends TestBaseClass
{
    /**
     * Import and activate the survey: Ranking Rank (A1 to A4, at most 2 ranks) on the first page
     * is the array filter of the multiple choice with comments question Filtered (A1 to A4) on the second page.
     *
     * @return void Nothing, the survey id is stored in self::$surveyId
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];
        $_SESSION = [];

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_208830_array_filter_by_ranking.lss');
        self::$testHelper->activateSurvey(self::$surveyId);
    }

    /**
     * Reset request and session data
     *
     * @return void Nothing
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * Only the ranked items are relevant subquestions of the filtered question,
     * also the items beyond the number of ranks.
     * The answers of the other subquestions are not saved.
     *
     * @return void Nothing, fails on a wrong relevance or saved answer
     */
    public function testOnlyRankedItemsAreRelevant()
    {
        $rank = $this->getQuestion('Rank');
        $filtered = $this->getQuestion('Filtered');
        $rankFields = array_map(
            function ($subquestion) use ($rank) {
                return 'Q' . $rank->qid . '_S' . $subquestion->qid;
            },
            \Question::model()->findAll([
                'condition' => 'parent_qid = :qid',
                'order'     => 'question_order',
                'params'    => [':qid' => $rank->qid],
            ])
        );

        $this->startSurvey();
        // First page: A4 and A2 are ranked
        $_POST = [
            'relevance' . $rank->qid => 1,
            'relevanceG0'            => 1,
            $rankFields[0]           => 'A4',
            $rankFields[1]           => 'A2',
            'movenext'               => 'movenext',
            'thisstep'               => 1,
            'sid'                    => self::$surveyId,
        ];
        \LimeExpressionManager::NavigateForwards();

        foreach (['A1' => false, 'A2' => true, 'A3' => false, 'A4' => true] as $code => $isRelevant) {
            $this->assertSame(
                $isRelevant,
                \LimeExpressionManager::SubQuestionIsRelevant($this->getSubquestionField($filtered, $code)),
                "$code relevance"
            );
        }

        // Second page: All items are submitted
        $_POST = [
            'relevance' . $filtered->qid => 1,
            'relevanceG1'                => 1,
            'movesubmit'                 => 'movesubmit',
            'thisstep'                   => 2,
            'sid'                        => self::$surveyId,
        ];
        foreach (['A1', 'A2', 'A3', 'A4'] as $code) {
            $_POST[$this->getSubquestionField($filtered, $code)] = 'Y';
        }
        $result = \LimeExpressionManager::NavigateForwards();
        $this->assertTrue($result['finished'], 'The survey is submitted');

        $response = \Response::model(self::$surveyId)->find();
        foreach (['A1' => null, 'A2' => 'Y', 'A3' => null, 'A4' => 'Y'] as $code => $expected) {
            $this->assertSame(
                $expected,
                $response->getAttribute($this->getSubquestionField($filtered, $code)),
                "$code answer"
            );
        }
    }

    /**
     * Start the survey in group by group mode
     *
     * @return void Nothing, the survey is started in the session
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
     * @return \Question The question
     */
    private function getQuestion($title)
    {
        return \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'title' => $title]);
    }

    /**
     * Get the response field name of a subquestion
     * @param \Question $question the parent question
     * @param string $title the subquestion code
     * @return string The response field name (Q{qid}_S{sqid})
     */
    private function getSubquestionField($question, $title)
    {
        $subquestion = \Question::model()->findByAttributes(['parent_qid' => $question->qid, 'title' => $title]);
        return 'Q' . $question->qid . '_S' . $subquestion->qid;
    }
}
