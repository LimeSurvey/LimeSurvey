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
     * Gets the number of responses where the field is answered.
     *
     * @param string $fieldName Field name (column in survey table)
     * @param string|null $value Specific value to filter on (optional)
     * @return int Response count
     */
    protected function getResponseCount(string $fieldName, $value = null): int
    {
        $model = SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();
        $col = $db->quoteColumnName($fieldName);

        $criteria = new LSDbCriteria();

        $criteria->addCondition("$col IS NOT NULL");
        $criteria->addCondition("$col != ''");

        if ($value !== null) {
            $criteria->compare($fieldName, $value);
        }

        $this->applyFilters($criteria);

        return (int)$model->count($criteria);
    }


    protected function getResponseNotAnsweredCount(string $fieldName): int
    {
        $model = SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();
        $col = $db->quoteColumnName($fieldName);

        $criteria = new LSDbCriteria();
        $criteria->addCondition("($col IS NULL OR $col = '')");
        $this->applyFilters($criteria);

        return (int)$model->count($criteria);
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
     * @param string $rt Question key (sidXgidXqid)
     * @param array $codes Answer codes
     * @param array $labels Optional display labels (aligned with $codes)
     * @return array [legend[], items[]]
     */
    protected function buildItemsFromCodes($rt, array $codes, array $labels = []): array
    {
        $legend = [];
        $items = [];

        foreach ($codes as $i => $code) {
            $count = $this->getResponseCount($rt, (string)$code);
            $title = $labels[$i] ?? (string)$code;
            $legend[] = $title;

            $items[] = ['key' => (string)$code, 'title' => $title, 'value' => $count];
        }

        if (in_array($this->question['type'], $this->noAnswerTypes)) {
            $items[] = ['key' => 'NoAnswer', 'title' => 'No Answer', 'value' => $this->getResponseNotAnsweredCount($rt)];
        }

        return [$legend, $items];
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
     * Process a question into one or more statistics charts.
     *
     * @return StatisticsChartDTO[]|StatisticsChartDTO
     */
    abstract public function process();
}
