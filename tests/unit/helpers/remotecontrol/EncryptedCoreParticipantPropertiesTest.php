<?php

namespace ls\tests;

class EncryptedCoreParticipantPropertiesTest extends BaseTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey, the email core property is encrypted.
        $filename = self::$surveysFolder . '/limesurvey_survey_get_participant_properties.lsa';
        self::importSurvey($filename);
    }

    /**
     * Get properties from a previously added participant.
     */
    public function testGetEncryptedParticipantPropertiesByTokenId()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        //Get participant's data from database.
        $participant = \Token::model(self::$surveyId)->findByAttributes(array('firstname' => 'participant', 'lastname' => 'one'));
        $tid = (int)$participant->tid;

        $result = $this->handler->get_participant_properties($sessionKey, self::$surveyId, $tid);

        $this->assertArrayNotHasKey('errors', $result);

        //Not equals since the email property is encrypted in the database.
        $this->assertNotEquals($participant->email, $result['email'], 'The returned email should not match the encrypted user email.');

        //Asserting it's a valid email.
        $this->assertEquals('p1@mail.com', $result['email'], 'Not the email address set.');
    }

    /**
     * Set new encrypted email.
     */
    public function testSetAndGetEncryptedCorePropertyByTokenId()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        //Get participant's data from database.
        $participant = \Token::model(self::$surveyId)->findByAttributes(array('firstname' => 'participant', 'lastname' => 'two'));
        $tid = (int)$participant->tid;

        $dataToChange = array(
            'email' => 'mj@mail.com'
        );

        $savedProperties = $this->handler->set_participant_properties($sessionKey, self::$surveyId, $tid, $dataToChange);
        $participantNewData = $this->handler->get_participant_properties($sessionKey, self::$surveyId, $tid);

        $encryptedMail = \LSActiveRecord::encryptSingle($dataToChange['email']);

        //Asserting the mail in database was changed.
        $this->assertNotEquals($participant->email, $savedProperties['email'], 'Apparently the mail was not correctly encrypted and saved.');
        $this->assertEquals($savedProperties['email'], $encryptedMail, 'Apparently the mail was not correctly encrypted and saved.');

        //Not equals since the email property is encrypted in the database.
        $this->assertNotEquals($savedProperties['email'], $participantNewData['email'], 'The returned email should not match the encrypted user email.');

        //Email was set correctly.
        $this->assertEquals($participantNewData['email'], $dataToChange['email'], 'The data retrieved does not correspond with the data set.');
    }

    /**
     * Create a new participant, set new data and get the updated data.
     */
    public function testSetAndGetEncryptedCorePropertyByParticipantProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $newParticipants = array(
            array(
                'firstname' => 'Johnny',
                'lastname'  => 'Ramone',
            ),
        );

        //Create new participant.
        $participantsData = $this->handler->add_participants($sessionKey, self::$surveyId, $newParticipants);
        $tid = (int)$participantsData[0]['tid'];

        $queryAttributes = ['firstname' => 'Johnny', 'lastname' => 'Ramone'];

        $dataToChange = array(
            'email' => 'jr@mail.com'
        );

        $savedProperties = $this->handler->set_participant_properties($sessionKey, self::$surveyId, $queryAttributes, $dataToChange);

        $participant = $this->handler->get_participant_properties($sessionKey, self::$surveyId, $tid);

        //Assert the returned mail is correct.
        $this->assertEquals($participant['email'], $dataToChange['email'], 'The data retrieved does not correspond with the data set.');

        //Assert saved mail is encrypted.
        $this->assertNotEquals($savedProperties['email'], $dataToChange['email'], 'The returned email should not match the encrypted user email.');
    }
}
