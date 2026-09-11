<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use InvalidArgumentException;
use Question;

/**
 * Base abstract class for all question processors used in statistics.
 *
 * A chart plan is: ['title' => string, 'legend' => string[], 'data' => array[]]
 * with each data item shaped like ['key' => ..., 'title' => ..., 'value' => callable].
 */
abstract class AbstractQuestionProcessor
{
    protected string $rt = '';

    /** @var array Current question data */
    protected array $question = [];

    /** @var int Survey ID for the current context */
    protected int $surveyId = 0;

    /** @var array Answer list for the question (if applicable) */
    protected array $answers = [];

    /** @var ResponseAggregateBatch Shared aggregate batch for the current run */
    protected ResponseAggregateBatch $batch;

    /** @var array List of question types which contain no answer option */
    protected array $noAnswerTypes = [
        Question::QT_L_LIST,
        Question::QT_EXCLAMATION_LIST_DROPDOWN,
        Question::QT_O_LIST_WITH_COMMENT,
        Question::QT_Y_YES_NO_RADIO,
        Question::QT_G_GENDER,
        Question::QT_5_POINT_CHOICE,
        Question::QT_A_ARRAY_5_POINT,
        Question::QT_B_ARRAY_10_CHOICE_QUESTIONS,
        Question::QT_C_ARRAY_YES_UNCERTAIN_NO,
        Question::QT_E_ARRAY_INC_SAME_DEC,
        Question::QT_F_ARRAY,
        Question::QT_H_ARRAY_COLUMN,
        Question::QT_1_ARRAY_DUAL,
        Question::QT_SEMICOLON_ARRAY_TEXT,
        Question::QT_S_SHORT_FREE_TEXT,
        Question::QT_T_LONG_FREE_TEXT,
        Question::QT_Q_MULTIPLE_SHORT_TEXT,
    ];

    /**
     * Build the identifier for current question.
     *
     * Ex: Qqid
     */
    public function rt(): void
    {
        if (!empty($this->question)) {
            $this->rt = 'Q' . $this->question['qid'];
        }
    }

    /**
     * Assign question metadata to the processor.
     *
     * @param array $question Question data (from DB/metadata fetch)
     * @throws InvalidArgumentException if question data is invalid
     */
    public function setQuestion(array $question): void
    {
        if (empty($question['sid']) || empty($question['gid']) || empty($question['qid'])) {
            throw new InvalidArgumentException('Invalid question data: missing required fields');
        }

        $this->question = $question;
        $this->question['title'] = flattenText($this->question['title']);
        $this->surveyId = (int)$this->question['sid'];

        $this->rt();
    }

    /**
     * Returns the question data as assigned to the processor (title flattened).
     */
    public function getQuestion(): array
    {
        return $this->question;
    }

    /**
     * Assign available answers to the processor (if applicable).
     *
     * @param array $answers List of answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

    /**
     * Assign the shared aggregate batch for the current statistics run.
     */
    public function setBatch(ResponseAggregateBatch $batch): void
    {
        $this->batch = $batch;
    }

    /**
     * Returns a deferred total response count for the survey.
     *
     * @return callable Resolves to an int once the batch has executed
     */
    public function getTotalCount(): callable
    {
        return $this->read($this->batch->countTotal());
    }

    /**
     * Returns a deferred count of non-empty responses for a column.
     *
     * @param string $fieldName Column name in the response table
     * @return callable Resolves to an int once the batch has executed
     */
    protected function countFieldResponses(string $fieldName): callable
    {
        return $this->read($this->batch->countNonEmpty($fieldName));
    }

    /**
     * Returns deferred non-empty response counts for multiple columns.
     *
     * @param  string[] $fieldNames Column names in the response table
     * @return array<string, callable> fieldName => deferred count
     */
    protected function batchGetResponseCounts(array $fieldNames): array
    {
        $result = [];
        foreach ($fieldNames as $field) {
            $result[$field] = $this->countFieldResponses($field);
        }

        return $result;
    }

    /**
     * Build chart items (legend + values) from a list of answer codes.
     *
     * @param string $fieldname Column name in the response table
     * @param string[] $codes  Answer codes
     * @param string[] $labels Display labels aligned with $codes
     * @return array [legend[], items[]]
     */
    protected function buildItemsFromCodes(string $fieldname, array $codes, array $labels = []): array
    {
        if (empty($codes)) {
            return [[], []];
        }

        $result = $this->planAggregateItems([$fieldname], $codes, $labels);

        return $result[$fieldname] ?? [[], []];
    }

    /**
     * Plan multiple subquestion fields × answer codes.
     *
     * @param string[] $fieldNames Column names (one per subquestion)
     * @param string[] $codes Answer codes to count
     * @param string[] $labels Display labels aligned with $codes
     * @return array<string, array{0: string[], 1: array[]}> fieldName => [legend[], items[]]
     */
    protected function buildBatchItemsForSubquestions(array $fieldNames, array $codes, array $labels = []): array
    {
        if (empty($fieldNames) || empty($codes)) {
            return [];
        }

        return $this->planAggregateItems($fieldNames, $codes, $labels);
    }

    /**
     * Register value counts (and a NoAnswer/blank count for the applicable
     * question types) for fields against specific answer codes.
     *
     * @param string[] $fieldNames
     * @param string[] $codes
     * @param string[] $labels
     * @return array<string, array{0: string[], 1: array[]}> fieldName => [legend[], items[]]
     */
    private function planAggregateItems(array $fieldNames, array $codes, array $labels): array
    {
        $addNoAnswer = in_array($this->question['type'], $this->noAnswerTypes);

        $output = [];
        foreach ($fieldNames as $field) {
            $legend = [];
            $items = [];

            foreach ($codes as $ci => $code) {
                $label = $labels[$ci] ?? (string)$code;
                $legend[] = $label;
                $items[] = [
                    'key' => (string)$code,
                    'title' => $label,
                    'value' => $this->read($this->batch->countValue($field, (string)$code)),
                ];
            }

            if ($addNoAnswer) {
                $legend[] = 'NoAnswer';
                $items[] = [
                    'key' => 'NoAnswer',
                    'title' => 'No answer',
                    'value' => $this->read($this->batch->countBlank($field)),
                ];
            }

            $output[$field] = [$legend, $items];
        }

        return $output;
    }

    /**
     * Closure resolving a batch alias once the batch has executed.
     */
    protected function read(string $alias): callable
    {
        return fn(): int => $this->batch->value($alias);
    }

    /**
     * Plan a question into one or more statistics chart plans.
     *
     * Must be side-effect free with respect to aggregate values: counts are
     * not available while planning, only after the batch has executed.
     *
     * @return array A chart plan or a list of chart plans
     */
    abstract public function process();
}
