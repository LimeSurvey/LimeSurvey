<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use CDbCriteria;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use PDO;
use SurveyDynamic;
use Yii;

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

    /**
     * Build the identifier for current question.
     *
     * Ex: sidXgidXqid
     */
    public function rt(): void
    {
        if (!empty($this->question)) {
            $this->rt = $this->question['sid'] . 'X' . $this->question['gid'] . 'X' . $this->question['qid'];
        }
    }

    /**
     * Assign question metadata to the processor.
     *
     * @param array $question Question data (from DB/metadata fetch)
     */
    public function setQuestion($question): void
    {
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
    public function setAnswers($answers): void
    {
        $this->answers = $answers;
    }

    /**
     * Gets the number of responses where the field is answered.
     *
     * @param string $fieldName Field name (column in survey table)
     * @param string|null $value Specific value to filter on (optional)
     * @return int Response count
     */
    protected function getResponseCount(string $fieldName, $value = null)
    {
        $model = SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();
        $col = $db->quoteColumnName($fieldName);

        $criteria = new CDbCriteria();

        $criteria->addCondition("$col IS NOT NULL");
        $criteria->addCondition("$col != ''");

        if ($value !== null) {
            $criteria->compare($fieldName, $value);
        }

        return (int)$model->count($criteria);
    }

    /**
     * Gets the number of responses where the field is empty or null.
     *
     * @param string $fieldName Field name (column in survey table)
     * @return int Response count
     */
    protected function getResponseNotAnsweredCount(string $fieldName)
    {
        $model = SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();
        $col = $db->quoteColumnName($fieldName);

        $criteria = new CDbCriteria();
        $criteria->addCondition("($col IS NULL OR $col = '')");

        return (int)$model->count($criteria);
    }

    /**
     * Build chart items (legend + values) from a list of answer codes.
     *
     * @param string $rt Response token (sidXgidXqid)
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

        return [$legend, $items];
    }

    /**
     * Process a question into one or more statistics charts.
     *
     * @return StatisticsChartDTO[]|StatisticsChartDTO
     */
    abstract public function process();
}
