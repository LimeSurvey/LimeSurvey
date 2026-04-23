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
     * Cache for getCountsByColumn() results, keyed by fieldName.
     * @var array<string, array<string, int>>
     */
    private array $columnCountsCache = [];

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
        $this->resetColumnCountsCache();
        return $this;
    }

    public function setMinId(?int $minId): AbstractQuestionProcessor
    {
        $this->minId = $minId;
        $this->resetColumnCountsCache();
        return $this;
    }

    public function setMaxId(?int $maxId): AbstractQuestionProcessor
    {
        $this->maxId = $maxId;
        $this->resetColumnCountsCache();
        return $this;
    }

    /**
     * Gets the number of responses where the field is answered.
     *
     * @param string $fieldName Field name (column in survey table)
     * @param string|null $value Specific value to filter on (optional)
     * @return int Response count
     */
    protected function getResponseCount(string $fieldName, $value = null): int
    {
        $counts = $this->getCountsByColumn($fieldName);
        if ($value !== null) {
            return $counts[$value] ?? 0;
        }

        $total  = 0;
        foreach ($counts as $key => $cnt) {
            if ($key !== '_null') {
                $total += $cnt;
            }
        }
        return $total;
    }

    /**
    * Gets column aggregate response
    * @param mixed $title
    * @param mixed $fields
    * @return array|bool
    */
    public function getAggregateResponses($title, $fields)
    {
        $model = SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();
        $command = $db->createCommand()
            ->select(implode(",", $fields))
            ->from("{{responses_" . $this->surveyId . "}}")
        ;
        return $command->query([":title" => $title])->read();
    }


    protected function getResponseNotAnsweredCount(string $fieldName): int
    {
        $counts = $this->getCountsByColumn($fieldName);
        return $counts['_null'] ?? 0;
    }
    /**
     * Apply common filters to criteria
     */
    protected function applyFilters(LSDbCriteria &$criteria): void
    {
        if ($this->completed !== null) {
            $criteria->addCondition('submitdate IS' . ($this->completed ? ' NOT ' : ' ') . 'NULL');
        }

        if ($this->minId !== null) {
            $criteria->compare('id', '>=' . (int)$this->minId);
        }

        if ($this->maxId !== null) {
            $criteria->compare('id', '<=' . (int)$this->maxId);
        }
    }

    /**
     * Build chart items (legend + values) from a list of answer codes.
     *
     * @param string $rt Question key Q{qid}
     * @param array $codes Answer codes
     * @param array $labels Optional display labels (aligned with $codes)
     * @return array [legend[], items[]]
     */
    protected function buildItemsFromCodes($rt, array $codes, array $labels = []): array
    {
        if (empty($codes)) {
            return [[], []];
        }

        $model = SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();
        $table = $db->quoteTableName('{{responses_' . $this->surveyId . '}}');
        $col = $db->quoteColumnName($rt);

        $criteria = new LSDbCriteria();
        $this->applyFilters($criteria);
        $where = $criteria->condition ?? '';
        $params = $criteria->params   ?? [];

        $fields = [];
        foreach ($codes as $i => $code) {
            $paramName = ':code_' . $i;
            $alias = $db->quoteColumnName('code_' . $i);
            $fields[] = "SUM(CASE WHEN $col = $paramName THEN 1 ELSE 0 END) AS $alias";
            $params[$paramName] = (string)$code;
        }

        // Add a field for unanswered/empty responses if question type has no answer option
        $shouldAddEmpty = in_array($this->question['type'], $this->noAnswerTypes);
        $emptyAlias = '_no_answer';
        if ($shouldAddEmpty) {
            $fields[] = "SUM(CASE WHEN $col IS NULL OR $col = '' THEN 1 ELSE 0 END) AS " . $db->quoteColumnName($emptyAlias);
        }

        $sql = 'SELECT ' . implode(', ', $fields) . " FROM $table" . ($where !== '' ? ' WHERE ' . $where : '');
        $row = $db->createCommand($sql)->queryRow(true, $params) ?: [];

        $legend = [];
        $items = [];

        foreach ($codes as $i => $code) {
            $title = $labels[$i] ?? (string)$code;
            $legend[] = $title;
            $count = (int)($row['code_' . $i] ?? 0);
            $items[] = ['key' => (string)$code, 'title' => $title, 'value' => $count];
        }

        if ($shouldAddEmpty) {
            $items[] = ['key' => 'NoAnswer', 'title' => 'No Answer', 'value' => (int)($row[$emptyAlias] ?? 0)];
        }

        return [$legend, $items];
    }

    /**
     * Returns all value count pairs for a response column
     * plus a '_null' key for unanswered/empty rows.
     *
     * @param string $fieldName Column name in the response table
     * @return array<string, int>  e.g. ['A' => 42, 'B' => 17, '_null' => 5]
     */
    protected function getCountsByColumn(string $fieldName): array
    {
        if (isset($this->columnCountsCache[$fieldName])) {
            return $this->columnCountsCache[$fieldName];
        }

        $model = SurveyDynamic::model($this->surveyId);
        $db    = $model->getDbConnection();
        $col   = $db->quoteColumnName($fieldName);
        $table = $db->quoteTableName('{{responses_' . $this->surveyId . '}}');

        $criteria = new LSDbCriteria();
        $this->applyFilters($criteria);
        $where  = $criteria->condition ? ('WHERE ' . $criteria->condition) : '';
        $params = $criteria->params ?? [];

        // Answered rows grouped by value
        $sql = "SELECT $col AS val, COUNT(*) AS cnt FROM $table $where GROUP BY $col";
        $rows = $db->createCommand($sql)->queryAll(true, $params);

        $counts = ['_null' => 0];
        foreach ($rows as $row) {
            $val = $row['val'];
            if ($val === null || $val === '') {
                $counts['_null'] += (int)$row['cnt'];
            } else {
                $counts[$val] = (int)$row['cnt'];
            }
        }

        $this->columnCountsCache[$fieldName] = $counts;
        return $counts;
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

    protected function resetColumnCountsCache(): void
    {
        $this->columnCountsCache = [];
    }

    /**
     * Process a question into one or more statistics charts.
     *
     * @return StatisticsChartDTO[]|StatisticsChartDTO
     */
    abstract public function process();
}
