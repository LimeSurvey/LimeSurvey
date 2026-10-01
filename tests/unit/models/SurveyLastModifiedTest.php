<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyDetailService;
use Question;
use QuestionGroup;
use Survey;

/**
 * Tests that changes to questions and question groups update the survey's lastmodified timestamp,
 * and that the survey list can be sorted by it.
 */
class SurveyLastModifiedTest extends TestBaseClass
{
    /** @var string An old timestamp the survey is reset to before each test */
    private const OLD_TIMESTAMP = '2000-01-01 00:00:00';

    /**
     * Import the test survey.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_594264_getGroupDescription.lss';
        self::importSurvey($surveyFile);
    }

    /**
     * Reset the survey's lastmodified timestamp and the per-request de-duplication of SurveyDetailService.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        (new \ReflectionProperty(SurveyDetailService::class, 'lastTouched'))->setValue(null, []);
        Survey::model()->updateByPk(self::$surveyId, ['lastmodified' => self::OLD_TIMESTAMP]);
        Survey::model()->resetCache();
    }

    /**
     * Saving a question group updates the survey's lastmodified timestamp.
     *
     * @return void
     */
    public function testSavingQuestionGroupUpdatesSurveyLastModified(): void
    {
        $questionGroup = QuestionGroup::model()->findByAttributes(['sid' => self::$surveyId]);
        $this->assertTrue($questionGroup->save(), 'The question group could not be saved.');

        $this->assertSurveyLastModifiedUpdated();
    }

    /**
     * Saving a question updates the survey's lastmodified timestamp.
     *
     * @return void
     */
    public function testSavingQuestionUpdatesSurveyLastModified(): void
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0]);
        $this->assertTrue($question->save(), 'The question could not be saved.');

        $this->assertSurveyLastModifiedUpdated();
    }

    /**
     * Moving questions to another group/position with the question list mass action updates the survey's
     * lastmodified timestamp.
     *
     * @return void
     */
    public function testMovingMultipleQuestionsUpdatesSurveyLastModified(): void
    {
        \Yii::import('application.controllers.QuestionAdministrationController', true);
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0]);
        $questionGroup = QuestionGroup::model()->find(
            'sid=:sid AND gid<>:gid',
            [':sid' => self::$surveyId, ':gid' => $question->gid]
        );

        \QuestionAdministrationController::changeMultipleQuestionPositionAndGroup([$question->qid], 1, $questionGroup);

        $this->assertSurveyLastModifiedUpdated();
    }

    /**
     * Reordering only the question groups updates the survey's lastmodified timestamp.
     *
     * @return void
     */
    public function testReorderingGroupsUpdatesSurveyLastModified(): void
    {
        $questionGroups = QuestionGroup::model()->findAllByAttributes(
            ['sid' => self::$surveyId],
            ['order' => 'group_order DESC']
        );
        $orgdata = [];
        foreach ($questionGroups as $questionGroup) {
            $orgdata['g' . $questionGroup->gid] = 'root';
        }

        $result = (new \LimeSurvey\Models\Services\GroupHelper())->reorderGroup(self::$surveyId, $orgdata);

        $this->assertSame('success', $result['type']);
        $this->assertSurveyLastModifiedUpdated();
    }

    /**
     * Deleting a question updates the survey's lastmodified timestamp.
     *
     * @return void
     */
    public function testDeletingQuestionUpdatesSurveyLastModified(): void
    {
        $question = Question::model()->findByAttributes(['sid' => self::$surveyId, 'parent_qid' => 0]);
        $this->assertTrue($question->delete(), 'The question could not be deleted.');

        $this->assertSurveyLastModifiedUpdated();
    }

    /**
     * Deleting a question group updates the survey's lastmodified timestamp.
     *
     * @return void
     */
    public function testDeletingQuestionGroupUpdatesSurveyLastModified(): void
    {
        $questionGroup = QuestionGroup::model()->findByAttributes(['sid' => self::$surveyId]);
        $this->assertSame(1, QuestionGroup::deleteWithDependency($questionGroup->gid));

        $this->assertSurveyLastModifiedUpdated();
    }

    /**
     * The survey list is ordered by the last modified date when sorting by the last modified column.
     *
     * @return void
     */
    public function testSurveyListIsSortableByLastModified(): void
    {
        $title = 'lastModifiedSortTest' . mt_rand();
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_594264_getGroupDescription.lss';
        $olderSurveyId = (int) \importSurveyFile($surveyFile, false, $title, null)['newsid'];
        $newerSurveyId = (int) \importSurveyFile($surveyFile, false, $title, null)['newsid'];
        $previousSort = $_GET['sort'] ?? null;
        try {
            Survey::model()->updateByPk($olderSurveyId, ['lastmodified' => '2001-01-01 00:00:00']);
            Survey::model()->updateByPk($newerSurveyId, ['lastmodified' => '2002-01-01 00:00:00']);
            Survey::model()->resetCache();

            $this->assertSame(
                [$olderSurveyId, $newerSurveyId],
                $this->getSurveyIdsSortedBy('lastModified', $title),
                'Surveys are not sorted by ascending last modified date.'
            );
            $this->assertSame(
                [$newerSurveyId, $olderSurveyId],
                $this->getSurveyIdsSortedBy('lastModified.desc', $title),
                'Surveys are not sorted by descending last modified date.'
            );
        } finally {
            if ($previousSort === null) {
                unset($_GET['sort']);
            } else {
                $_GET['sort'] = $previousSort;
            }
            Survey::model()->deleteSurvey($olderSurveyId);
            Survey::model()->deleteSurvey($newerSurveyId);
        }
    }

    /**
     * Search the survey list for the given title with the given sort parameter.
     *
     * @param string $sort Value of the sort request parameter, e.g. 'lastModified' or 'lastModified.desc'
     * @param string $title Survey title to filter by
     * @return int[] Survey IDs in the order returned by the search
     */
    private function getSurveyIdsSortedBy(string $sort, string $title): array
    {
        $_GET['sort'] = $sort;
        $survey = new Survey('search');
        $survey->searched_value = $title;
        return array_map(
            function ($survey) {
                return (int) $survey->sid;
            },
            $survey->search()->getData()
        );
    }

    /**
     * Assert that the survey's lastmodified timestamp is no longer the old value set in setUp().
     *
     * @return void
     */
    private function assertSurveyLastModifiedUpdated(): void
    {
        $survey = Survey::model()->findByPk(self::$surveyId);
        $this->assertNotSame(self::OLD_TIMESTAMP, $survey->lastmodified, 'The survey lastmodified timestamp was not updated.');
    }
}
