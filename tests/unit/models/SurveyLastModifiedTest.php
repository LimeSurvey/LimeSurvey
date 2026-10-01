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
     * The survey list can be sorted by the last modified column.
     *
     * @return void
     */
    public function testSurveyListIsSortableByLastModified(): void
    {
        $survey = new Survey('search');
        $sort = $survey->search()->getSort();

        $this->assertSame(
            ['asc' => 't.lastmodified asc', 'desc' => 't.lastmodified desc'],
            $sort->resolveAttribute('lastModified')
        );
    }

    /**
     * Assert that the survey's lastmodified timestamp is no longer the old value set in setUp().
     *
     * @return void
     */
    private function assertSurveyLastModifiedUpdated(): void
    {
        Survey::model()->resetCache();
        $survey = Survey::model()->findByPk(self::$surveyId);
        $this->assertNotSame(self::OLD_TIMESTAMP, $survey->lastmodified, 'The survey lastmodified timestamp was not updated.');
    }
}
