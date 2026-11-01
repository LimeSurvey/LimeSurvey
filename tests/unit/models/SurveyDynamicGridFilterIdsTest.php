<?php

namespace ls\tests;

/**
 * Tests for SurveyDynamic::getIdsByGridFilter(), used by "Select all" in the responses grid.
 *
 * @see https://bugs.limesurvey.org/view.php?id=15320
 */
class SurveyDynamicGridFilterIdsTest extends TestBaseClass
{
    /** @var int[] Ids of the completed responses */
    private static $completedIds = [];

    /** @var int[] Ids of the incomplete responses */
    private static $incompleteIds = [];

    /**
     * Import and activate a survey and add completed and incomplete responses.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss');
        (new \SurveyActivator(self::$testSurvey))->activate();

        $model = \SurveyDynamic::model(self::$surveyId);
        for ($i = 0; $i < 3; $i++) {
            self::$completedIds[] = (int) $model->insertRecords(['startlanguage' => 'en', 'submitdate' => '2026-01-01 00:00:00']);
            self::$incompleteIds[] = (int) $model->insertRecords(['startlanguage' => 'en']);
        }
    }

    /**
     * The grid filters limit the returned ids.
     *
     * @return void
     */
    public function testFilteredIds()
    {
        $this->assertSame(
            self::$completedIds,
            $this->getIds('SurveyDynamic[id]=&SurveyDynamic[completed_filter]=Y')
        );
        $this->assertSame(
            self::$incompleteIds,
            $this->getIds('SurveyDynamic[id]=&SurveyDynamic[completed_filter]=N')
        );
    }

    /**
     * Responses deselected by the user are left out.
     *
     * @return void
     */
    public function testExcludedIds()
    {
        $this->assertSame(
            [self::$completedIds[0], self::$completedIds[2]],
            $this->getIds('SurveyDynamic[id]=&SurveyDynamic[completed_filter]=Y', [(string) self::$completedIds[1]])
        );
    }

    /**
     * Without filters all responses are returned; a limit keeps the lowest ids.
     *
     * @return void
     */
    public function testLimit()
    {
        $allIds = array_merge(self::$completedIds, self::$incompleteIds);
        sort($allIds);

        $this->assertSame($allIds, $this->getIds('SurveyDynamic[id]=&SurveyDynamic[completed_filter]='));
        $this->assertSame(
            array_slice($allIds, 0, 2),
            $this->getIds('SurveyDynamic[id]=&SurveyDynamic[completed_filter]=', [], 2)
        );
    }

    /**
     * Returns the sorted ids matching the filter query, as integers.
     *
     * @param string $filterQuery
     * @param array $excludedIds
     * @param int|null $limit
     * @return int[]
     */
    private function getIds(string $filterQuery, array $excludedIds = [], ?int $limit = null): array
    {
        $ids = array_map('intval', \SurveyDynamic::getIdsByGridFilter(self::$surveyId, $filterQuery, $excludedIds, $limit));
        sort($ids);
        return $ids;
    }
}
