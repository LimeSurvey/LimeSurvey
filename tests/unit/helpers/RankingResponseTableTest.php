<?php

namespace ls\tests;

/**
 * Tests for the response table (ANSWERTABLE / notification emails) with ranking questions.
 */
class RankingResponseTableTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->loadHelper('common');
        \Yii::app()->loadHelper('frontend');
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_583654_ranking_response_table.lss');
        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();
    }

    /**
     * Ranking answers are stored as JSON in a single column; building the full
     * response table must not try to read the virtual per-rank fields as columns.
     *
     * @see https://bugs.limesurvey.org/view.php?id=20721
     * @return void
     */
    public function testFullResponseTableWithRankingQuestion(): void
    {
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'type' => \Question::QT_R_RANKING]);
        $rankingColumn = 'Q' . $question->qid;
        $responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'fr',
            $rankingColumn => json_encode(['SQ002', 'SQ001']),
        ]);

        $table = getFullResponseTable(self::$surveyId, $responseId, 'fr', false);

        $this->assertArrayHasKey($rankingColumn, $table);
        $this->assertStringContainsString('1: Option B', $table[$rankingColumn][2]);
        $this->assertStringContainsString('2: Option A', $table[$rankingColumn][2]);
        foreach (array_keys($table) as $key) {
            $this->assertStringNotContainsString($rankingColumn . '_S', (string) $key);
        }

        $html = getResponseTableReplacement(self::$surveyId, $responseId, 'fr', true);
        $this->assertStringContainsString('Option B', $html);
    }

    /**
     * Print answers must list the ranked items from the JSON column in rank order.
     *
     * @return void
     */
    public function testQuestionArrayWithRankingQuestion(): void
    {
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0, 'type' => \Question::QT_R_RANKING]);
        $responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords([
            'startlanguage' => 'fr',
            'Q' . $question->qid => json_encode(['SQ002', 'SQ001']),
        ]);
        $response = \SurveyDynamic::model(self::$surveyId)->findByPk($responseId);

        $questionArray = \SurveyDynamic::model(self::$surveyId)->getQuestionArray($question, $response, true, false, false, 'fr');

        $this->assertSame(
            [
                ['value' => 'SQ002', 'subquestion' => 'Option B', 'answertext' => 'Option B'],
                ['value' => 'SQ001', 'subquestion' => 'Option A', 'answertext' => 'Option A'],
            ],
            $questionArray['answervalues']
        );
    }
}
