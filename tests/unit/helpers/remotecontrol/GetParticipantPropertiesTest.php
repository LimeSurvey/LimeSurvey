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

    public function testGetCommonParticipantProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $participant_1 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 1);
        $participant_2 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 2);

        $this->assertSame($participant_1['attribute_1'], 'Test attribute for Participant 1', 'The user attributes do not match.');
        $this->assertSame($participant_2['attribute_1'], 'Test attribute for Participant 2', 'The user attributes do not match.');
    }

    public function testGetEncryptedParticipantProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $participant_1 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 1);
        $participant_2 = $this->handler->get_participant_properties($sessionKey, self::$surveyId, 2);

        $this->assertSame($participant_1['attribute_2'], 'Encrypted attribute for Participant 1', 'The encrypted user attributes do not match.');
        $this->assertSame($participant_2['attribute_2'], 'Encrypted attribute for Participant 2', 'The encrypted user attributes do not match.');
    }

    public function testSetByTokenIdAndGetParticipantProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $newParticipants = array(
            array(
                'firstname' => 'John',
                'email'     => 'john@mail.com',
            ),
        );

        $participantsData = $this->handler->add_participants($sessionKey, self::$surveyId, $newParticipants);
        $tid = (int)$participantsData[0]['tid'];

        $dataToChange = array(
            'lastname' => 'Lennon'
        );

        $this->handler->set_participant_properties($sessionKey, self::$surveyId, $tid, $dataToChange);
        $participant = $this->handler->get_participant_properties($sessionKey, self::$surveyId, $tid);

        $this->assertSame($participant['lastname'], $dataToChange['lastname'], 'The data retrieved does not correspond with the data set.');
    }

    public function testSetByQueryAttributesAndGetParticipantProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $newParticipants = array(
            array(
                'firstname' => 'George',
                'email'     => 'gh@mail.com',
            ),
        );

        $participantsData = $this->handler->add_participants($sessionKey, self::$surveyId, $newParticipants);
        $tid = (int)$participantsData[0]['tid'];

        $queryAttributes = ['email' => 'gh@mail.com'];

        $dataToChange = array(
            'lastname' => 'Harrison'
        );

        $this->handler->set_participant_properties($sessionKey, self::$surveyId, $queryAttributes, $dataToChange);
        $participant = $this->handler->get_participant_properties($sessionKey, self::$surveyId, $tid);

        $this->assertSame($participant['lastname'], $dataToChange['lastname'], 'The data retrieved does not correspond with the data set.');
    }

    public function testTryToSetInexistentParticipantProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $dataToChange = array(
            'email' => 'new_mail@mail.com'
        );

        $response = $this->handler->set_participant_properties($sessionKey, self::$surveyId, 999999, $dataToChange);

        $this->assertIsArray($response, 'The error response should be an array.');
        $this->assertArrayHasKey('status', $response, 'The error response array does not contain a status key.');
        $this->assertSame($response['status'], 'Error: Invalid tokenid', 'An invalid token id error was expected.');
    }

    public function testSetAndGetTokenAttribute()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $tokenAttributeToSet = array(
            'attribute_1' => 'Test attribute'
        );

        //Get a previously created participant.
        $oldParticipant = \Token::model(self::$surveyId)->findByAttributes(array('firstname' => 'John', 'lastname' => 'Lennon'));
        $tid = (int)$oldParticipant->tid;

        $this->handler->set_participant_properties($sessionKey, self::$surveyId, $tid, $tokenAttributeToSet);

        $participant = $this->handler->get_participant_properties($sessionKey, self::$surveyId, $tid);
        $this->assertSame($participant['attribute_1'], $tokenAttributeToSet['attribute_1'], 'The data retrieved does not correspond with the data set.');
    }

    public function testSetAndGetEncryptedTokenAttribute()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $tokenAttributeToSet = array(
            'attribute_2' => 'Test attribute'
        );

        //Get a previously created participant.
        $oldParticipant = \Token::model(self::$surveyId)->findByAttributes(array('firstname' => 'George', 'lastname' => 'Harrison'));
        $tid = (int)$oldParticipant->tid;

        $savedProperties = $this->handler->set_participant_properties($sessionKey, self::$surveyId, $tid, $tokenAttributeToSet);

        $participant = $this->handler->get_participant_properties($sessionKey, self::$surveyId, $tid);
        $this->assertSame($participant['attribute_2'], $tokenAttributeToSet['attribute_2'], 'The data retrieved does not correspond with the data set.');

        //Asserting the saved attribute was encrypted.
        $this->assertNotSame($savedProperties['attribute_2'], $participant['attribute_2'], 'The attribute should be encrypted.');
    }
}
