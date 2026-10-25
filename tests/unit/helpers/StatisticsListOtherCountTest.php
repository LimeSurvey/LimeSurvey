<?php

namespace ls\tests\unit\helpers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that the admin statistics count the "Other" option of a list question
 * and keep empty answers apart from not displayed ones (mantis #19463).
 */
class StatisticsListOtherCountTest extends TestBaseClass
{
    /** @var \Question List question with "Other" option of the test survey */
    private static $listQuestion;

    public static function setUpBeforeClass(): void
    {
        Yii::app()->loadHelper('admin.statistics');
        Yii::app()->loadHelper('common');
        Yii::app()->setController(new DummyController('dummyid'));

        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_PrefillBeforeSurveyPolicy.lss');
        self::$testHelper->activateSurvey(self::$surveyId);

        self::$listQuestion = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'Q00']);
        StatisticsListOtherCountData::insertResponses(self::$surveyId, self::$listQuestion);
    }

    /**
     * "Other" counts all responses that selected it, with or without other text, and
     * not displayed (NULL) answers are not counted as "No answer".
     */
    public function testListQuestionCounts()
    {
        $fieldname = 'Q' . self::$listQuestion->qid;

        $html = (new \statistics_helper())->generate_statistics(self::$surveyId, [$fieldname], [$fieldname], 0, 'html', 'DD', 'en', false);
        $counts = StatisticsListOtherCountData::countsFromHtml($html);

        // The answer options of the test survey have no text, so only the code is shown.
        $this->assertSame(2, $counts['(A1)'] ?? null, 'Wrong count for answer option A1');
        $this->assertSame(2, $counts[gT('Other')] ?? null, 'Wrong count for "Other"');
        $this->assertSame(3, $counts[gT('No answer')] ?? null, 'Wrong count for "No answer"');
        $this->assertSame(2, $counts[gT('Not completed or not displayed')] ?? null, 'Wrong count for not displayed answers');
    }

    /**
     * The simple statistics count "Other" the same way.
     */
    public function testListQuestionSimpleStatistics()
    {
        $fieldname = 'Q' . self::$listQuestion->qid;

        $html = (new \statistics_helper())->generate_simple_statistics(self::$surveyId, [$fieldname], [$fieldname], 1, 'html', 'DD');

        // Chart data in answer option sort order (A1, A3, A2), then "Other" and "No answer"
        $this->assertStringContainsString('grawdata : [2,0,0,2,3]', $html, 'The statistics values are not correct.');
    }
}
