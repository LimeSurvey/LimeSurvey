<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors\ResponseAggregateBatch;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\QuestionStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use ls\tests\TestBaseClass;
use Question;

class ResponseAggregateBatchGroupedValuesTest extends TestBaseClass
{
    /** @var string */
    private static $otherField;

    /** @var int[] ids of the inserted responses, in insertion order */
    private static $responseIds = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_213203_other_checked.lss', 1, 917007);
        self::$testHelper->activateSurvey(self::$surveyId);

        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'q1']);
        self::$otherField = 'Q' . $question->qid . '_Cother';

        $tableName = \Survey::model()->findByPk(self::$surveyId)->responsesTableName;
        $db = \Yii::app()->db;
        $now = date('Y-m-d H:i:s');
        foreach (['Darts', 'Basketball', 'Darts', '', null, 'Basketball', 'Darts', 'Disc golf'] as $text) {
            $db->createCommand()->insert($tableName, [
                'startlanguage' => 'en',
                'startdate' => $now,
                'datestamp' => $now,
                'submitdate' => $now,
                self::$otherField => $text,
            ]);
            self::$responseIds[] = (int)getLastInsertID($tableName);
        }
    }

    /**
     * @testdox groupedValueCounts() groups the texts, skips empty cells and sorts by count
     */
    public function testGroupsAndSortsByCount()
    {
        $batch = new ResponseAggregateBatch(self::$surveyId);

        $this->assertSame(
            [
                ['text' => 'Darts', 'count' => 3],
                ['text' => 'Basketball', 'count' => 2],
                ['text' => 'Disc golf', 'count' => 1],
            ],
            $batch->groupedValueCounts(self::$otherField)
        );
    }

    /**
     * @testdox groupedValueCounts() applies the statistics response filters
     */
    public function testAppliesResponseFilters()
    {
        // Only the last three responses: Basketball, Darts, Disc golf.
        $filters = (new StatisticsResponseFilters())->setMinId(self::$responseIds[5]);
        $batch = new ResponseAggregateBatch(self::$surveyId, $filters);

        $this->assertSame(
            [
                ['text' => 'Basketball', 'count' => 1],
                ['text' => 'Darts', 'count' => 1],
                ['text' => 'Disc golf', 'count' => 1],
            ],
            $batch->groupedValueCounts(self::$otherField)
        );
    }

    /**
     * @testdox groupedValueCounts() ignores case and surrounding spaces, keeping the first spelling
     */
    public function testMergesCaseAndSpaceVariants()
    {
        $field = $this->fillSecondOtherField(['Darts', 'darts', ' DARTS ', 'Chess'], false);
        $batch = new ResponseAggregateBatch(self::$surveyId);

        $this->assertSame(
            [
                ['text' => 'Darts', 'count' => 6],
                ['text' => 'Chess', 'count' => 2],
            ],
            $batch->groupedValueCounts($field)
        );
    }

    /**
     * @testdox groupedValueCounts() decrypts encrypted texts before grouping them
     */
    public function testDecryptsEncryptedTexts()
    {
        // Each text gets a different encrypted value, so SQL can't group them.
        $field = $this->fillSecondOtherField(['Yoga', 'Chess', 'yoga ', ''], true);
        $batch = new ResponseAggregateBatch(self::$surveyId);

        $this->assertSame(
            [
                ['text' => 'Yoga', 'count' => 4],
                ['text' => 'Chess', 'count' => 2],
            ],
            $batch->groupedValueCounts($field, true)
        );
    }

    /**
     * @testdox groupedValueCounts() keeps a value that can't be decrypted instead of failing
     */
    public function testKeepsValuesThatCannotBeDecrypted()
    {
        $field = $this->fillSecondOtherField(['Yoga', ''], true);
        \Yii::app()->db->createCommand()->update(
            \Survey::model()->findByPk(self::$surveyId)->responsesTableName,
            [$field => 'Tennis'],
            'id = :id',
            [':id' => self::$responseIds[1]]
        );
        $batch = new ResponseAggregateBatch(self::$surveyId);

        $this->assertSame(
            [
                ['text' => 'Yoga', 'count' => 4],
                ['text' => 'Tennis', 'count' => 1],
            ],
            $batch->groupedValueCounts($field, true)
        );
    }

    /**
     * @testdox The statistics output carries the grouped answers on the "other" item
     */
    public function testStatisticsOutputIncludesOtherAnswers()
    {
        $statistics = new QuestionStatistics();
        $statistics->setSurveyModel(\Survey::model()->findByPk(self::$surveyId));
        $output = $statistics->run(self::$surveyId);

        $chart = null;
        foreach ($output as $dto) {
            $array = $dto->toArray();
            if (($array['meta']['question']['code'] ?? null) === 'q1') {
                $chart = $array;
            }
        }
        $this->assertNotNull($chart);

        $other = end($chart['data']);
        $this->assertSame('other', $other['key']);
        $this->assertSame(
            [
                ['text' => 'Darts', 'count' => 3],
                ['text' => 'Basketball', 'count' => 2],
                ['text' => 'Disc golf', 'count' => 1],
            ],
            $other['otherAnswers']
        );
    }

    /**
     * Writes the texts, in turn, into the "other" column of q2 of every response.
     *
     * @param string[] $texts
     * @return string the column name
     */
    private function fillSecondOtherField(array $texts, bool $encrypt): string
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'q2']);
        $field = 'Q' . $question->qid . '_Cother';
        $tableName = \Survey::model()->findByPk(self::$surveyId)->responsesTableName;
        foreach (self::$responseIds as $index => $responseId) {
            $text = $texts[$index % count($texts)];
            \Yii::app()->db->createCommand()->update(
                $tableName,
                [$field => $encrypt && $text !== '' ? \LSActiveRecord::encryptSingle($text) : $text],
                'id = :id',
                [':id' => $responseId]
            );
        }
        return $field;
    }
}
