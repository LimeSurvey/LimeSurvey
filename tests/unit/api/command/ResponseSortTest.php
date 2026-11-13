<?php

namespace ls\tests\unit\api\command;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting\QuestionSorterInterface;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting\ResponseSort;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting\SortSql;
use ls\tests\TestBaseClass;
use SurveyDynamic;

/**
 * Sorting of the response list: The orders run against a real responses table,
 * so they are checked on every database driver of the CI.
 */
class ResponseSortTest extends TestBaseClass
{
    /** @var array<string, int> Response IDs by name */
    private static array $responseIds = [];

    /**
     * Import and activate a survey and add responses with values worth sorting.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_213203_other_checked.lss');
        self::$testHelper->activateSurvey(self::$surveyId);

        // Name => [seed, start language, submit date], inserted in this order (ascending IDs)
        $responses = [
            'seed9' => ['9', 'en', '2026-01-02 10:00:00'],
            'seed10' => ['10', 'de', null],
            'seedEmpty' => ['', 'en', '2026-01-01 10:00:00'],
            'seedNull' => [null, 'de', '2026-01-02 10:00:00'],
            'seed100' => ['100', 'en', '2026-01-03 10:00:00'],
        ];
        $table = \Survey::model()->findByPk(self::$surveyId)->responsesTableName;
        $db = \Yii::app()->db;
        foreach ($responses as $name => [$seed, $language, $submitDate]) {
            $db->createCommand()->insert($table, [
                'seed' => $seed,
                'startlanguage' => $language,
                'submitdate' => $submitDate,
                'lastpage' => 1,
            ]);
            self::$responseIds[$name] = (int) $db->getLastInsertID();
        }
    }

    /**
     * Response names in the order the given sort returns them.
     *
     * @param ResponseSort $responseSort Sort builder to test
     * @param mixed $sort Sort param as the API gets it
     * @return string[] Response names, in result order
     */
    private function getSortedNames(ResponseSort $responseSort, $sort): array
    {
        $rows = SurveyDynamic::model(self::$surveyId)->findAll(['order' => $responseSort->getOrder($sort)]);
        $names = array_flip(self::$responseIds);
        return array_map(static fn($row) => $names[(int) $row->id], $rows);
    }

    /**
     * Sort builder for the test survey, without question sorters or participants.
     *
     * @param QuestionSorterInterface[] $questionSorters Sorters for question columns
     * @return ResponseSort Sort builder
     */
    private function makeResponseSort(array $questionSorters = []): ResponseSort
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        return new ResponseSort(createFieldMap($survey, 'full', true, false, $survey->language), $questionSorters);
    }

    /**
     * @testdox Without a valid sort, the newest responses come first
     * @dataProvider invalidSortProvider
     * @param mixed $sort Sort param as the API gets it
     * @return void
     */
    public function testNewestFirstWithoutValidSort($sort): void
    {
        $this->assertSame(
            ['seed100', 'seedNull', 'seedEmpty', 'seed10', 'seed9'],
            $this->getSortedNames($this->makeResponseSort(), $sort)
        );
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public function invalidSortProvider(): array
    {
        return [
            'no sort' => [null],
            'empty sort' => [[]],
            'unknown column' => [['nonexistent' => 'asc']],
            'column that is not sortable' => [['completed' => 'asc']],
            'invalid direction' => [['seed' => 'up']],
            'SQL as column' => [['id; DROP TABLE x' => 'asc']],
        ];
    }

    /**
     * @testdox The ID sorts in both directions
     * @return void
     */
    public function testSortById(): void
    {
        $responseSort = $this->makeResponseSort();
        $this->assertSame(
            ['seed9', 'seed10', 'seedEmpty', 'seedNull', 'seed100'],
            $this->getSortedNames($responseSort, ['id' => 'asc'])
        );
        $this->assertSame(
            ['seed100', 'seedNull', 'seedEmpty', 'seed10', 'seed9'],
            $this->getSortedNames($responseSort, ['id' => 'DESC'])
        );
    }

    /**
     * @testdox The seed sorts by number, empty and missing seeds last, and descending flips the whole order
     * @return void
     */
    public function testSortBySeed(): void
    {
        $responseSort = $this->makeResponseSort();
        $this->assertSame(
            ['seed9', 'seed10', 'seed100', 'seedEmpty', 'seedNull'],
            $this->getSortedNames($responseSort, ['seed' => 'asc'])
        );
        $this->assertSame(
            ['seedNull', 'seedEmpty', 'seed100', 'seed10', 'seed9'],
            $this->getSortedNames($responseSort, ['seed' => 'desc'])
        );
    }

    /**
     * @testdox Responses with the same value come newest first, in both directions
     * @return void
     */
    public function testSameValuesNewestFirst(): void
    {
        $responseSort = $this->makeResponseSort();
        $this->assertSame(
            ['seedNull', 'seed10', 'seed100', 'seedEmpty', 'seed9'],
            $this->getSortedNames($responseSort, ['language' => 'asc'])
        );
        $this->assertSame(
            ['seed100', 'seedEmpty', 'seed9', 'seedNull', 'seed10'],
            $this->getSortedNames($responseSort, ['language' => 'desc'])
        );
    }

    /**
     * @testdox The submit date sorts by date, incomplete responses last
     * @return void
     */
    public function testSortBySubmitDate(): void
    {
        $responseSort = $this->makeResponseSort();
        $this->assertSame(
            ['seedEmpty', 'seedNull', 'seed9', 'seed100', 'seed10'],
            $this->getSortedNames($responseSort, ['submitDate' => 'asc'])
        );
        $this->assertSame(
            ['seed10', 'seed100', 'seedNull', 'seed9', 'seedEmpty'],
            $this->getSortedNames($responseSort, ['submitDate' => 'desc'])
        );
    }

    /**
     * @testdox A question column sorts with the first sorter that handles it, followed by the newest first
     * @return void
     */
    public function testQuestionColumnUsesSorter(): void
    {
        $qid = (string) $this->getAllSurveyQuestions()['q1']->qid;
        $sorter = $this->createMock(QuestionSorterInterface::class);
        $sorter->method('canSort')->willReturn(true);
        // Sorts by the seed, so the result shows that the sorter's order is used
        $sorter->method('getOrder')->willReturnCallback(
            static fn(array $fields, bool $descending) => SortSql::forKind(
                SortSql::column('seed'),
                SortSql::KIND_NUMERIC_TEXT,
                $descending
            )
        );
        $responseSort = $this->makeResponseSort([$sorter]);

        $this->assertContains($qid, $responseSort->getSortableKeys());
        $this->assertSame(
            ['seed9', 'seed10', 'seed100', 'seedEmpty', 'seedNull'],
            $this->getSortedNames($responseSort, [$qid => 'asc'])
        );
    }

    /**
     * @testdox Questions without a sorter, or with encrypted values, can't be sorted
     * @return void
     */
    public function testQuestionColumnsWithoutSorterOrEncrypted(): void
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $fieldMap = createFieldMap($survey, 'full', true, false, $survey->language);
        $qid = (string) $this->getAllSurveyQuestions()['q1']->qid;
        $sorter = $this->createMock(QuestionSorterInterface::class);
        $sorter->method('canSort')->willReturn(true);

        $this->assertNotContains($qid, (new ResponseSort($fieldMap))->getSortableKeys());

        foreach ($fieldMap as $key => $field) {
            if ((string) $field['qid'] === $qid) {
                $fieldMap[$key]['encrypted'] = 'Y';
            }
        }
        $responseSort = new ResponseSort($fieldMap, [$sorter]);
        $this->assertNotContains($qid, $responseSort->getSortableKeys());
        $this->assertSame(SortSql::newestFirst(), $responseSort->getOrder([$qid => 'asc']));
    }

    /**
     * @testdox Metadata columns are sortable when the survey has them, participant columns only when allowed
     * @return void
     */
    public function testSortableKeys(): void
    {
        $survey = \Survey::model()->findByPk(self::$surveyId);
        $fieldMap = createFieldMap($survey, 'full', true, false, $survey->language);

        $keys = (new ResponseSort($fieldMap))->getSortableKeys();
        foreach (['id', 'seed', 'language', 'submitDate'] as $key) {
            $this->assertContains($key, $keys);
        }
        // The survey has no date stamps and is not sorted by "completed"
        $this->assertNotContains('dateLastAction', $keys);
        $this->assertNotContains('completed', $keys);
        $this->assertNotContains('firstName', $keys);

        $keys = (new ResponseSort($fieldMap, [], '{{tokens_' . self::$surveyId . '}}', ['firstName', 'email']))
            ->getSortableKeys();
        $this->assertContains('firstName', $keys);
        $this->assertContains('email', $keys);
        $this->assertNotContains('lastName', $keys);

        $keys = (new ResponseSort($fieldMap, [], null, ['firstName']))->getSortableKeys();
        $this->assertNotContains('firstName', $keys);
    }
}
