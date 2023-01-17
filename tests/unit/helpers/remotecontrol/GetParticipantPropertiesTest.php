<?php

namespace ls\tests;

class GetParticipantPropertiesTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey
        $filename = self::$surveysFolder . '/survey_archive_getParticipantPropertiesTest.lsa';
        self::importSurvey($filename);
    }

    public function testGetParticipantPropertiesByTid()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 1);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertEquals('Participant 1', $result['firstname'], '$result = ' . json_encode($result));
    }

    public function testGetParticipantPropertiesByAttribute()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $queryAttributes = ['firstname' => 'Participant 2'];
        $result = $this->handler->get_participant_properties($sessionKey, self::$surveyId, $queryAttributes);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertEquals(2, $result['tid'], '$result = ' . json_encode($result));
    }

    public function testGetInexistentParticipantProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 3);
        $this->assertArrayHasKey('status', $result);
    }
}
