<?php

namespace ls\tests;

/**
 * Tests for the time statistics of the responses of a survey.
 */
class SurveyTimingDynamicStatisticsTest extends TestBaseClass
{
    /**
     * Import a survey and activate it with timings.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss');
        self::$testSurvey->savetimings = 'Y';
        self::$testSurvey->save();
        \Survey::model()->resetCache();
        self::$testSurvey = \Survey::model()->findByPk(self::$surveyId);

        $activator = new \SurveyActivator(self::$testSurvey);
        $activator->activate();
    }

    /**
     * Replace the responses of the survey by completed responses with the given interview times
     *
     * @param float[] $interviewTimes Total time of each response in seconds
     * @return void
     */
    private function setCompletedResponses(array $interviewTimes)
    {
        \SurveyDynamic::model(self::$surveyId)->deleteAll();
        \SurveyTimingDynamic::model(self::$surveyId)->deleteAll();
        foreach ($interviewTimes as $interviewTime) {
            $responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords([
                'startlanguage' => 'en',
                'submitdate' => date('Y-m-d H:i:s'),
            ]);
            \SurveyTimingDynamic::model(self::$surveyId)->insertRecords(['id' => $responseId, 'interviewtime' => $interviewTime]);
        }
    }

    /**
     * Interview times and the median expected for them
     *
     * @return array[]
     */
    public function medianProvider()
    {
        return [
            'one value' => [[70], 70],
            'two values' => [[60, 90], 75],
            'three values' => [[60, 90, 150], 90],
            'four values' => [[150, 60, 90, 100], 95],
            'five values' => [[10, 20, 30, 400, 500], 30],
        ];
    }

    /**
     * The median is the middle value, or the mean of the two middle values for an even number of responses.
     *
     * @dataProvider medianProvider
     * @param float[] $interviewTimes
     * @param integer $expectedMedian
     * @return void
     */
    public function testMedian(array $interviewTimes, $expectedMedian)
    {
        $this->setCompletedResponses($interviewTimes);

        $statistics = \SurveyTimingDynamic::model(self::$surveyId)->statistics();

        $this->assertEquals(count($interviewTimes), $statistics['count']);
        $this->assertSame($expectedMedian, $statistics['median']);
        $this->assertSame(intdiv($expectedMedian, 60), $statistics['allmin']);
        $this->assertSame($expectedMedian % 60, $statistics['allsec']);
    }

    /**
     * Incomplete responses are not part of the statistics.
     *
     * @return void
     */
    public function testIncompleteResponsesAreIgnored()
    {
        $this->setCompletedResponses([60, 90, 150]);
        $responseId = \SurveyDynamic::model(self::$surveyId)->insertRecords(['startlanguage' => 'en']);
        \SurveyTimingDynamic::model(self::$surveyId)->insertRecords(['id' => $responseId, 'interviewtime' => 5000]);

        $statistics = \SurveyTimingDynamic::model(self::$surveyId)->statistics();

        $this->assertEquals(3, $statistics['count']);
        $this->assertSame(90, $statistics['median']);
        $this->assertSame(1, $statistics['avgmin'], 'The average of 60, 90 and 150 seconds is 1 minute 40 seconds.');
        $this->assertSame(40, $statistics['avgsec']);
    }
}
