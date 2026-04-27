<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use LSDbCriteria;
use Question;
use SurveyDynamic;

/**
 * Base abstract class for all question processors used in statistics that
 * provides common functionality.
 * Each concrete processor must implement the `process()` method
 * that returns one or more `StatisticsChartDTO` instances.
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

    /** @var bool Completed responses filter */
    private $completed = null;

    /** @var int Min ID for responses filter */
    private $minId = null;

    /** @var int Max ID for responses filter */
    private $maxId = null;

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
     * Assign available answers to the processor (if applicable).
     *
     * @param array $answers List of answers
     */
    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

    public function setCompleted(?bool $completed): AbstractQuestionProcessor
    {
        $this->completed = $completed;
        return $this;
    }

    public function setMinId(?int $minId): AbstractQuestionProcessor
    {
        $this->minId = $minId;
        return $this;
    }

    public function setMaxId(?int $maxId): AbstractQuestionProcessor
    {
        $this->maxId = $maxId;
        return $this;
    }

    /**
     * Returns the count of non-empty responses for a column.
     *
     * @param string $fieldName Column name in the response table
     * @return int
     */
    protected function countFieldResponses(string $fieldName): int
    {
        $db = $this->getDb();
        ['table' => $table, 'where' => $where, 'params' => $params] = $this->buildFilteredQuery();
        $col = $db->quoteColumnName($fieldName);

        $sql = "SELECT SUM(CASE WHEN $col IS NOT NULL AND $col <> '' THEN 1 ELSE 0 END) AS cnt FROM $table" . $where;
        return (int)$db->createCommand($sql)->queryScalar($params);
    }

    /**
     * Returns the count of null/empty responses for a column.
     *
     * @param string $fieldName Column name in the response table
     * @return int
     */
    protected function countFieldNullResponses(string $fieldName): int
    {
        $db = $this->getDb();
        ['table' => $table, 'where' => $where, 'params' => $params] = $this->buildFilteredQuery();
        $col = $db->quoteColumnName($fieldName);

        $sql = "SELECT SUM(CASE WHEN $col IS NULL OR $col = '' THEN 1 ELSE 0 END) AS cnt FROM $table" . $where;
        return (int)$db->createCommand($sql)->queryScalar($params);
    }

    /**
    * Gets column aggregate response
    * @param mixed $fields
    * @param mixed $params
    * @return array|bool
    */
    public function getAggregateResponses($fields, $params)
    {
        $model = SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();
        $command = $db->createCommand()
            ->select(implode(",", $fields))
            ->from("{{responses_" . $this->surveyId . "}}")
        ;
        return $command->query($params)->read();
    }

    /**
     * Returns non-empty response counts for multiple columns.
     *
     * @param  string[] $fieldNames Column names in the response table
     * @return array<string, int> fieldName => count
     */
    protected function batchGetResponseCounts(array $fieldNames): array
    {
        if (empty($fieldNames)) {
            return [];
        }

        $db = $this->getDb();
        ['table' => $table, 'where' => $where, 'params' => $params] = $this->buildFilteredQuery();
        $selects = [];

        foreach ($fieldNames as $i => $field) {
            $col = $db->quoteColumnName($field);
            $alias = $db->quoteColumnName('_ctn' . $i);
            $selects[] = "SUM(CASE WHEN $col IS NOT NULL AND $col <> '' THEN 1 ELSE 0 END) AS $alias";
        }

        $sql = 'SELECT ' . implode(', ', $selects) . " FROM $table" . $where;
        $row = $db->createCommand($sql)->queryRow(true, $params) ?: [];

        $result = [];
        foreach ($fieldNames as $i => $field) {
            $result[$field] = (int)($row['_ctn' . $i] ?? 0);
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

        $result = $this->runAggregateSelect([$fieldname], $codes, $labels);

        return $result[$fieldname] ?? [[], []];
    }

    /**
     * Process multiple subquestion fields × answer codes in a query.
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

        return $this->runAggregateSelect($fieldNames, $codes, $labels);
    }

    /**
     * Get aggregate response counts for fields to specific values
     *
     * @param string[] $fieldNames
     * @param string[] $codes
     * @param string[] $labels
     * @return array<string, array{0: string[], 1: array[]}>
     */
    private function runAggregateSelect(array $fieldNames, array $codes, array $labels): array
    {
        $addNoAnswer = in_array($this->question['type'], $this->noAnswerTypes);
        $db = $this->getDb();
        ['table' => $table, 'where' => $where, 'params' => $params] = $this->buildFilteredQuery();

        $selects = [];
        $aliasMap = [];

        foreach ($fieldNames as $fi => $field) {
            $col = $db->quoteColumnName($field);
            $fieldKey = '_f' . $fi;

            foreach ($codes as $ci => $code) {
                $paramKey = ':' . $fieldKey . '_c' . $ci;
                $rawAlias = '_alias' . $fieldKey . '_c' . $ci;
                $selects[] = "SUM(CASE WHEN $col = $paramKey THEN 1 ELSE 0 END) AS " . $db->quoteColumnName($rawAlias);
                $params[$paramKey] = (string)$code;
                $aliasMap[$field]['codes'][$ci] = $rawAlias;
            }

            if ($addNoAnswer) {
                $blankRaw = '_blank' . $fieldKey;
                $selects[] = "SUM(CASE WHEN $col IS NULL OR $col = '' THEN 1 ELSE 0 END) AS " . $db->quoteColumnName($blankRaw);
                $aliasMap[$field]['blank'] = $blankRaw;
            }
        }

        $sql = 'SELECT ' . implode(', ', $selects) . " FROM $table" . $where;
        $row = $db->createCommand($sql)->queryRow(true, $params) ?: [];

        $output = [];
        foreach ($fieldNames as $field) {
            $legend = [];
            $items = [];

            foreach ($codes as $ci => $code) {
                $label = $labels[$ci] ?? (string)$code;
                $count = (int)($row[$aliasMap[$field]['codes'][$ci]] ?? 0);
                $legend[] = $label;
                $items[] = ['key' => (string)$code, 'title' => $label, 'value' => $count];
            }

            if ($addNoAnswer) {
                $legend[] = 'NoAnswer';
                $items[] = [
                    'key' => 'NoAnswer',
                    'title' => 'No Answer',
                    'value' => (int)($row[$aliasMap[$field]['blank']] ?? 0),
                ];
            }

            $output[$field] = [$legend, $items];
        }

        return $output;
    }

    /**
     * @param array $data
     * @param string $key
     * @return float|int
     */
    protected function calculateTotal($data, $key = 'value')
    {
        return array_sum(array_column($data, $key));
    }

    /**
     * Returns the active DB connection
     */
    private function getDb(): \CDbConnection
    {
        return SurveyDynamic::model($this->surveyId)->getDbConnection();
    }

    /**
     * Returns the response table name and (where, params) with filters applied.
     *
     * @return array{table: string, where: string, params: array}
     */
    private function buildFilteredQuery(): array
    {
        $db = $this->getDb();
        $table = $db->quoteTableName('{{responses_' . $this->surveyId . '}}');
        $criteria = new LSDbCriteria();

        if ($this->completed !== null) {
            $criteria->addCondition('submitdate IS' . ($this->completed ? ' NOT ' : ' ') . 'NULL');
        }

        if ($this->minId !== null) {
            $criteria->compare('id', '>=' . (int)$this->minId);
        }

        if ($this->maxId !== null) {
            $criteria->compare('id', '<=' . (int)$this->maxId);
        }

        $where = $criteria->condition ? (' WHERE ' . $criteria->condition) : '';
        $params = $criteria->params ?? [];

        return compact('table', 'where', 'params');
    }

    /**
     * Process a question into one or more statistics charts.
     *
     * @return StatisticsChartDTO[]|StatisticsChartDTO
     */
    abstract public function process();
}
