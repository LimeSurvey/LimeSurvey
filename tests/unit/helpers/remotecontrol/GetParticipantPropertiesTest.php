<?php

namespace ls\tests;

class GetParticipantProperties extends BaseTest
{
    public function testGetParticipantPropertiesByTid()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_archive_getParticipantPropertiesTest.lsa';
        self::importSurvey($filename);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 1);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertEquals('Participant 1', $result['firstname'], '$result = ' . json_encode($result));
    }

    public function testGetParticipantPropertiesByAttribute()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_archive_getParticipantPropertiesTest.lsa';
        self::importSurvey($filename);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->get_participant_properties($sessionKey, self::$surveyId, ['firstname' => 'Participant 2']);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertEquals(2, $result['tid'], '$result = ' . json_encode($result));
    }

    public function testGetInexistentParticipantProperties()
    {
        // Import survey
        $filename = self::$surveysFolder . '/survey_archive_getParticipantPropertiesTest.lsa';
        self::importSurvey($filename);

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 3);
        $this->assertArrayHasKey('status', $result);
    }
}
