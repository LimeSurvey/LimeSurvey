<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that the public statistics count the "Other" option of a list question
 * and keep empty answers apart from not displayed ones (mantis #19463).
 *
 * The public and the admin statistics helpers declare the same global functions,
 * so this test cannot share a process with tests that load the admin helper.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class UserStatisticsListOtherCountTest extends TestBaseClass
{
    /** @var \Question List question with "Other" option of the test survey */
    private static $listQuestion;

    /** @var \Question Short free text question of the test survey */
    private static $textQuestion;

    /**
     * Loads the public statistics helper and imports the test survey with responses. This runs
     * in the isolated test process only, unlike setUpBeforeClass(), which also runs in the parent.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Yii::app()->loadHelper('userstatistics');
        Yii::app()->loadHelper('common');
        Yii::app()->setController(new DummyController('dummyid'));

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_PrefillBeforeSurveyPolicy.lss');
        self::$testHelper->activateSurvey(self::$surveyId);

        self::$listQuestion = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'Q00']);
        self::$textQuestion = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'uid']);
        StatisticsListOtherCountData::insertResponses(self::$surveyId, self::$listQuestion, self::$textQuestion);
    }

    /**
     * Deletes the test survey, as tearDownAfterClass() does not run in the isolated test process.
     */
    protected function tearDown(): void
    {
        self::tearDownAfterClass();

        parent::tearDown();
    }

    /**
     * "Other" counts all responses that selected it, with or without other text, and
     * not displayed (NULL) answers are not counted as "No answer".
     */
    public function testListQuestionCounts()
    {
        $fieldname = 'Q' . self::$listQuestion->qid;

        $html = (new \userstatistics_helper())->generate_statistics(self::$surveyId, [$fieldname], [$fieldname], 0, 'html', null, 'en', false);
        $counts = StatisticsListOtherCountData::countsFromHtml($html);

        // The answer options of the test survey have no text, so only the code is shown.
        $this->assertSame(2, $counts['(A1)'] ?? null, 'Wrong count for answer option A1');
        $this->assertSame(2, $counts[gT('Other')] ?? null, 'Wrong count for "Other"');
        $this->assertSame(3, $counts[gT('No answer')] ?? null, 'Wrong count for "No answer"');
        $this->assertSame(2, $counts[gT('Not completed or not displayed')] ?? null, 'Wrong count for not displayed answers');
    }

    /**
     * Text questions are shown, and not displayed (NULL) answers are not counted as "No answer".
     */
    public function testTextQuestionCounts()
    {
        $fieldname = 'TQ' . self::$textQuestion->qid;

        $html = (new \userstatistics_helper())->generate_statistics(self::$surveyId, [$fieldname], [$fieldname], 0, 'html', null, 'en', false);
        $counts = StatisticsListOtherCountData::countsFromHtml($html);

        $this->assertSame(1, $counts[gT('Answer')] ?? null, 'Wrong count for answered text');
        $this->assertSame(2, $counts[gT('No answer')] ?? null, 'Wrong count for "No answer"');
        $this->assertSame(6, $counts[gT('Not completed or not displayed')] ?? null, 'Wrong count for not displayed answers');
    }
}
