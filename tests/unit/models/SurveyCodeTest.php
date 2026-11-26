<?php

namespace ls\tests;

/**
 * The internal title (code) of a survey: Stored as typed, HTML-encoded in the survey list, sortable there
 */
class SurveyCodeTest extends TestBaseClass
{
    /** @var int|null Survey group holding the surveys of these tests */
    private static $groupId = null;

    /**
     * Creates the survey group holding the surveys of these tests
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->session['loginID'] = 1;

        $group = new \SurveysGroups();
        $group->name = 'SurveyCodeTest';
        $group->title = 'Survey code test';
        $group->sortorder = 1;
        $group->owner_id = 1;
        $group->created_by = 1;
        if (!$group->save()) {
            throw new \Exception('Could not create the survey group: ' . print_r($group->getErrors(), true));
        }
        self::$groupId = (int) $group->gsid;

        $groupSettings = new \SurveysGroupsettings();
        $groupSettings->gsid = self::$groupId;
        $groupSettings->setToInherit();
        $groupSettings->save();
    }

    /**
     * Deletes the surveys and the survey group of these tests
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->session['loginID'] = 1;
        unset($_GET['sort']);
        \Yii::app()->user->setState('surveyListSort', null);
        if (self::$groupId !== null) {
            foreach (\Survey::model()->findAllByAttributes(['gsid' => self::$groupId]) as $survey) {
                \Survey::model()->deleteSurvey($survey->sid);
            }
            \SurveysGroupsettings::model()->deleteAllByAttributes(['gsid' => self::$groupId]);
            \SurveysGroups::model()->deleteByPk(self::$groupId);
        }
        parent::tearDownAfterClass();
    }

    /**
     * A "<" followed by a letter doesn't cut off the rest of the internal title
     *
     * @return void
     */
    public function testTextAfterLessThanSignIsKept(): void
    {
        $sid = $this->createSurvey('  Q1<Q2 test  ');

        $this->assertSame('Q1<Q2 test', $this->reloadSurvey($sid)->code);
    }

    /**
     * Markup is stored as typed, and the survey list shows it as text
     *
     * @return void
     */
    public function testMarkupIsStoredAsTypedAndEncodedInSurveyList(): void
    {
        $sid = $this->createSurvey('Test <b>"quoted"</b> & more');
        $survey = $this->reloadSurvey($sid);

        $this->assertSame('Test <b>"quoted"</b> & more', $survey->code);

        \Yii::import('zii.widgets.grid.CGridView');
        $grid = new \CGridView();
        $grid->dataProvider = new \CArrayDataProvider([$survey], ['keyField' => 'sid']);
        $column = new \TbDataColumn($grid);
        foreach ($survey->getAdditionalColumns()['code'] as $property => $value) {
            $column->$property = $value;
        }
        $cell = $column->getDataCellContent(0);

        $this->assertStringContainsString('&lt;b&gt;', $cell);
        $this->assertStringContainsString('&amp; more', $cell);
        $this->assertStringNotContainsString('<b>', $cell);
    }

    /**
     * The survey list sorts by internal title in both directions, surveys without one count as empty
     *
     * @return void
     */
    public function testSurveyListSortsByInternalTitle(): void
    {
        // Only the surveys of this test in the group
        foreach (\Survey::model()->findAllByAttributes(['gsid' => self::$groupId]) as $survey) {
            \Survey::model()->deleteSurvey($survey->sid);
        }
        $secondCode = $this->createSurvey('B code');
        $withoutCode = $this->createSurvey(null);
        $withEmptyCode = $this->createSurvey(null);
        \Survey::model()->updateByPk($withEmptyCode, ['code' => '']);
        $firstCode = $this->createSurvey('A code');

        $empties = [$withoutCode, $withEmptyCode];
        sort($empties);

        $this->assertSame(array_merge($empties, [$firstCode, $secondCode]), $this->searchSorted('code'));
        $this->assertSame(array_merge([$secondCode, $firstCode], array_reverse($empties)), $this->searchSorted('code.desc'));
    }

    /**
     * Creates a survey in the test group
     *
     * @param string|null $code The internal title, null for none
     * @return int The survey ID
     */
    private function createSurvey(?string $code): int
    {
        $survey = \Survey::model()->insertNewSurvey(['gsid' => self::$groupId, 'code' => $code]);
        $this->assertNotNull($survey->sid, 'Could not create the survey: ' . print_r($survey->getErrors(), true));
        return (int) $survey->sid;
    }

    /**
     * Reads a survey from the database, not from the model cache
     *
     * @param int $sid The survey ID
     * @return \Survey
     */
    private function reloadSurvey(int $sid): \Survey
    {
        \Survey::model()->resetCache();
        $survey = \Survey::model()->findByPk($sid);
        $this->assertNotNull($survey);
        return $survey;
    }

    /**
     * Searches the surveys of the test group like the survey list does with the given sort parameter
     *
     * @param string $sort The sort parameter of the survey list, e.g. "code.desc"
     * @return int[] The survey IDs in the listed order
     */
    private function searchSorted(string $sort): array
    {
        $_GET['sort'] = $sort;
        try {
            $search = new \Survey('search');
            $search->gsid = self::$groupId;
            $dataProvider = $search->search(['skipCacheFlush' => true]);
            $dataProvider->pagination = false;
            return array_map(
                function ($survey) {
                    return (int) $survey->sid;
                },
                $dataProvider->getData()
            );
        } finally {
            unset($_GET['sort']);
            \Yii::app()->user->setState('surveyListSort', null);
        }
    }
}
