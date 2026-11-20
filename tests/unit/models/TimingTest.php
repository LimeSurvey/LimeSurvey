<?php

namespace ls\tests;

/**
 * Tests for the Timing model: The timings of the responses of a survey.
 */
class TimingTest extends TestBaseClass
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
     * Insert a response together with its timings
     *
     * @param float $interviewTime Total time in seconds
     * @param boolean $completed
     * @return integer The response ID
     */
    private function insertResponseWithTiming($interviewTime, $completed = true)
    {
        $responseId = \Response::model(self::$surveyId)->insertRecords(array_filter([
            'startlanguage' => 'en',
            'submitdate' => $completed ? date('Y-m-d H:i:s') : null,
        ]));
        $timingId = \Timing::model(self::$surveyId)->insertRecords(['id' => $responseId, 'interviewtime' => $interviewTime]);
        $this->assertEquals($responseId, $timingId, 'The timings should be saved with the ID of their response.');
        return (int) $responseId;
    }

    /**
     * The model uses the timings table of its survey.
     *
     * @return void
     */
    public function testModelUsesTheTimingsTableOfTheSurvey()
    {
        $model = \Timing::model(self::$surveyId);

        $this->assertInstanceOf('Timing', $model);
        $this->assertSame('{{timings_' . self::$surveyId . '}}', $model->tableName());
        $this->assertEquals(self::$surveyId, $model->getSurveyId());
        $this->assertInstanceOf('Timing', \Timing::create(self::$surveyId));
    }

    /**
     * The statistics and the grid only use the completed responses.
     *
     * @return void
     */
    public function testStatisticsOfCompletedResponses()
    {
        \Response::model(self::$surveyId)->deleteAll();
        \Timing::model(self::$surveyId)->deleteAll();
        $this->assertSame(['count' => 0], \Timing::model(self::$surveyId)->statistics());

        $this->insertResponseWithTiming(60);
        $this->insertResponseWithTiming(90);
        $this->insertResponseWithTiming(150);
        $this->insertResponseWithTiming(1000, false);

        $statistics = \Timing::model(self::$surveyId)->statistics();
        $this->assertEquals(3, $statistics['count']);
        $this->assertSame(1, $statistics['avgmin'], 'The average of 60, 90 and 150 seconds is 1 minute 40 seconds.');
        $this->assertSame(40, $statistics['avgsec']);
        $this->assertSame(1, $statistics['allmin'], 'The median of three values is the mean of the middle one and the one before: 75 seconds.');
        $this->assertSame(15, $statistics['allsec']);

        $this->assertEquals(3, \Timing::model(self::$surveyId)->search()->getTotalItemCount());
    }

    /**
     * Deleting a response deletes its timings too.
     *
     * @return void
     */
    public function testTimingsAreDeletedWithTheResponse()
    {
        $responseId = $this->insertResponseWithTiming(30);
        $this->assertNotNull(\Timing::model(self::$surveyId)->findByPk($responseId));

        \Response::model(self::$surveyId)->findByPk($responseId)->delete();

        $this->assertNull(\Timing::model(self::$surveyId)->findByPk($responseId));
    }
}
