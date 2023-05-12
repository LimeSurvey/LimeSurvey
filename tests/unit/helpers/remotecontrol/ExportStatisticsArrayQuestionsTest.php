<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlExportStatisticsArrayQuestionsTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Yii::app()->setController(new DummyController('dummyid'));
    }

    public function testSurveyWithOneQuestion()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_statistics_three.lsa';
        self::importSurvey($filename);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $htmlStatistics = $this->handler->export_statistics($sessionKey, self::$surveyId, 'html');
        $htmlStatistics = base64_decode($htmlStatistics);

        $this->assertIsString($htmlStatistics);
    }
}
