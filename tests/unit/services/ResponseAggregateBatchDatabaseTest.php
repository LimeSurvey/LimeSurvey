<?php

namespace ls\tests\unit\services;

use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use ls\tests\TestBaseClass;

/**
 * Statistics response conditions and completion states, run against the database.
 */
class ResponseAggregateBatchDatabaseTest extends TestBaseClass
{
    /** @var string Dual-scale array, first scale */
    private static $dualScale0;
    /** @var string Dual-scale array, second scale */
    private static $dualScale1;
    /** @var string List question, used with numeric text values for ranges */
    private static $list;
    /** @var string First option of the multiple choice question */
    private static $multiple;
    /** @var int[] Response ids, in insert order */
    private static $ids = [];

    /**
     * Import and activate the test survey (saving quota exits) and insert the responses.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/survey-dual-scale-question-api-test.lss');
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $survey->savequotaexit = 'Y';
        $survey->save();
        (new \SurveyActivator($survey))->activate();
        \Yii::app()->db->schema->refresh();
        \SurveyDynamic::model(self::$surveyId)->refreshMetaData();

        $dual = self::question('Q00');
        $dualRow = self::question('SQ001', $dual);
        $multiple = self::question('G01Q07');
        self::$dualScale0 = "Q{$dual}_S{$dualRow}#0";
        self::$dualScale1 = "Q{$dual}_S{$dualRow}#1";
        self::$list = 'Q' . self::question('G01Q06');
        self::$multiple = "Q{$multiple}_S" . self::question('SQ001', $multiple);

        $list = self::$list;
        $dual0 = self::$dualScale0;
        $dual1 = self::$dualScale1;
        $multiple = self::$multiple;
        $rows = [
            ['submitdate' => '2026-10-30 15:00:00', $list => '1', $dual0 => 'A1', $dual1 => 'B1', $multiple => 'Y'],
            ['submitdate' => '2026-10-01 08:00:00', $list => '5', $dual0 => 'A2', $multiple => 'Y'],
            [$list => '12.5', $dual0 => 'A1'],
            [$list => 'x', 'quota_exit' => 3],
            [$list => '', 'quota_exit' => 0],
            ['submitdate' => '2026-11-01 00:00:00', $list => '-3', $dual0 => 'A1', $dual1 => 'B2'],
        ];
        $model = \SurveyDynamic::model(self::$surveyId);
        foreach ($rows as $row) {
            self::$ids[] = (int)$model->insertRecords($row + ['startlanguage' => 'en']);
        }
    }

    /**
     * Equal and multi-select conditions, including dual-scale columns.
     *
     * @return void
     */
    public function testEqualAndMultiSelectConditions(): void
    {
        $this->assertSame(3, $this->countMatching([$this->condition(self::$dualScale0, 'equal', 'A1')]));
        $this->assertSame(1, $this->countMatching([$this->condition(self::$dualScale1, 'equal', 'B2')]));
        $this->assertSame(2, $this->countMatching([$this->condition(self::$multiple, 'equal', 'Y')]));
        $this->assertSame(3, $this->countMatching([$this->condition(self::$list, 'multi-select', ['1', '5', 'x'])]));
        $this->assertSame(
            1,
            $this->countMatching([$this->condition('submitdate', 'equal', '2026-10-30')]),
            'A date matches the whole day'
        );
        $this->assertSame(1, $this->countMatching([$this->condition('quotaExit', 'equal', 3)]));
        $this->assertSame(1, $this->countMatching([
            $this->condition(self::$dualScale0, 'equal', 'A1'),
            $this->condition(self::$dualScale1, 'equal', 'B1'),
        ]), 'Conditions combine with AND');
    }

    /**
     * Ranges on numeric text answers, integer and datetime columns.
     *
     * @return void
     */
    public function testRangeConditions(): void
    {
        $this->assertSame(
            2,
            $this->countMatching([$this->condition(self::$list, 'range', [0, 10])]),
            'Only numeric text in range'
        );
        $this->assertSame(2, $this->countMatching([$this->condition(self::$list, 'range', [null, 2])]));
        $this->assertSame(2, $this->countMatching([$this->condition(self::$list, 'range', ['', '2'])]));
        $this->assertSame(1, $this->countMatching([$this->condition(self::$list, 'range', ['10', null])]));
        $this->assertSame(3, $this->countMatching([$this->condition('id', 'range', [self::$ids[1], self::$ids[3]])]));
        $this->assertSame(
            2,
            $this->countMatching([$this->condition('submitdate', 'range', ['2026-10-01', '2026-10-30'])]),
            'A date-only max includes that whole day'
        );
        $this->assertSame(
            1,
            $this->countMatching([
                $this->condition('submitdate', 'range', ['2026-10-30 15:00:00', '2026-10-31 23:59:59']),
            ])
        );
    }

    /**
     * The completion states split the responses without overlap.
     *
     * @return void
     */
    public function testCompletionStates(): void
    {
        $this->assertSame(6, $this->countMatching([], 'all'));
        $this->assertSame(3, $this->countMatching([], 'completed'));
        $this->assertSame(2, $this->countMatching([], 'incomplete'));
        $this->assertSame(1, $this->countMatching([], 'screened_out'));
        $this->assertSame(2, $this->countMatching([$this->condition(self::$dualScale0, 'equal', 'A1')], 'completed'));
    }

    /**
     * Values the column can't hold are refused before they reach the database.
     *
     * @return void
     */
    public function testInvalidValuesAreRefusedBeforeTheQuery(): void
    {
        foreach (
            [
                $this->condition('quotaExit', 'equal', 'x'),
                $this->condition('id', 'range', ['1.5', '10']),
                $this->condition('submitdate', 'range', ['2026-02-31', null]),
            ] as $condition
        ) {
            try {
                $this->countMatching([$condition]);
                $this->fail('Accepted ' . json_encode($condition));
            } catch (InvalidArgumentException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }

    /**
     * Count the responses matching the conditions and completion state.
     *
     * @param array $conditions Response conditions
     * @param string|null $completion Completion state
     * @return int
     */
    private function countMatching(array $conditions, ?string $completion = null): int
    {
        $filters = new StatisticsResponseFilters();
        if ($conditions) {
            $filters->setConditions($conditions);
        }
        if ($completion !== null) {
            $filters->setCompletion($completion);
        }
        $batch = new ResponseAggregateBatch(self::$surveyId, $filters);
        $alias = $batch->countTotal();
        $batch->execute();
        return (int)$batch->value($alias);
    }

    /**
     * Build a response condition.
     *
     * @param string $key Response column
     * @param string $method equal, multi-select or range
     * @param mixed $value Condition value
     * @return array
     */
    private function condition(string $key, string $method, $value): array
    {
        return ['key' => $key, 'filterMethod' => $method, 'value' => $value];
    }

    /**
     * The qid of a question of the test survey.
     *
     * @param string $title Question code
     * @param int $parentQid Parent question for a subquestion, 0 for a question
     * @return int
     */
    private static function question(string $title, int $parentQid = 0): int
    {
        $question = \Question::model()->findByAttributes([
            'sid' => self::$surveyId,
            'title' => $title,
            'parent_qid' => $parentQid,
        ]);
        self::assertNotNull($question, "Question $title not found");
        return (int)$question->qid;
    }
}
