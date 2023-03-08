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

    public function testGetCommonParticipantProperties(): void
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $participant_1 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 1);
        $participant_2 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 2);

        $this->assertSame($participant_1['attribute_1'], 'Test attribute for Participant 1', 'The user attributes do not match.');
        $this->assertSame($participant_2['attribute_1'], 'Test attribute for Participant 2', 'The user attributes do not match.');
    }

    public function testGetEncryptedParticipantProperties(): void
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $participant_1 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 1);
        $participant_2 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 2);

        $this->assertSame($participant_1['attribute_2'], 'Encrypted attribute for Participant 1', 'The encrypted user attributes do not match.');
        $this->assertSame($participant_2['attribute_2'], 'Encrypted attribute for Participant 2', 'The encrypted user attributes do not match.');
    }

    public function testSetByTokenIdAndGetParticipantProperties(): void
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $newParticipants = array(
            array(
                'firstname' => 'John',
                'email'     => 'john@mail.com',
            ),
        );

        $participantsData = $this->handler->add_participants($sessionKey, self::$surveyId, $newParticipants);

        $dataToChange = array(
            'lastname' => 'Lennon'
        );

        $this->handler->set_participant_properties($sessionKey, self::$surveyId, $participantsData[0]['tid'], $dataToChange);
        $participant_3 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 3);

        $this->assertSame($participant_3['lastname'], $dataToChange['lastname'], 'The data retrieved does not correspond with the data set.');
    }

    public function testSetByQueryAttributesAndGetParticipantProperties(): void
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $newParticipants = array(
            array(
                'firstname' => 'George',
                'email'     => 'gh@mail.com',
            ),
        );

        $participantsData = $this->handler->add_participants($sessionKey, self::$surveyId, $newParticipants);

        $queryAttributes = ['email' => 'gh@mail.com'];

        $dataToChange = array(
            'lastname' => 'Harrison'
        );

        $this->handler->set_participant_properties($sessionKey, self::$surveyId, $queryAttributes, $dataToChange);
        $participant_4 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 4);

        $this->assertSame($participant_4['lastname'], $dataToChange['lastname'], 'The data retrieved does not correspond with the data set.');
    }
}
