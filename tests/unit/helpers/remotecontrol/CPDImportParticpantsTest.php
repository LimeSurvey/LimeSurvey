<?php

namespace ls\tests\unit\helpers\remotecontrol;

class CPDImportParticpantsTest extends BaseTest
{
    public function setUp(): void
    {
        \Yii::app()->db->createCommand()->truncateTable('{{participants}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_names}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_names_lang}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_values}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_shares}}');
        \Yii::app()->db->createCommand()->truncateTable('{{survey_links}}');
        parent::setUp();
    }

    public function testOneParticipantImportedSuccessfully()
    {
        $participants = array(
            array(
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);
    }

    public function testTwoParticipantImportedSuccessfully()
    {
        $participants = array(
            array(
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y'
            ),
            array(
                'firstname' => 'Max',
                'lastname' => 'Mustermann2',
                'email' => 'max.mustermann2@example.com',
                'language' => 'de',
                'blacklisted' => 'N'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(2, $result['ImportCount']);
    }

    public function testOneParticipantWithOwnIdImportedSuccessfully()
    {
        $participants = array(
            array(
                'participant_id' => 'max',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);

        $max = \Participant::model()->findByPk('max');
        $this->assertInstanceOf('Participant', $max);
    }

    public function testImportingParticipantFailsDueToSameFirstnameLastnameEmail()
    {
        $participants = array(
            array(
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y'
            ),
            array(
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'en',
                'blacklisted' => 'N'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(0, $result['UpdateCount']);
    }

    public function testParticipantWithOneAttributeImportedSucessfully()
    {
        \Yii::app()->session['adminlang'] = 'de';
        $this->assertTrue(empty(\ParticipantAttributeName::model()->findAll()));
        $attributeId = \ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'defaultname' => 'website',
            'visible' => 'TRUE',
            'attribute_name' => 'Webseite',
            'encrypted'      => 'N',
            'core_attribute' => 'N'
        ));
        $this->assertTrue(intval($attributeId) > 0);

        $participants = array(
            array(
                'participant_id' => 'max',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y',
                'website' => 'http://www.example.com'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);

        $max = \Participant::model()->findByPk('max');
        $this->assertInstanceOf(\Participant::class, $max);

        $attribute = $max->getParticipantAttribute('ea_' . $attributeId);
        $this->assertEquals('http://www.example.com', $attribute);
    }

    public function testParticipantUpdatedSuccessfullyWhenUpdateTrue()
    {
        \Yii::app()->session['adminlang'] = 'de';
        $this->assertTrue(empty(\ParticipantAttributeName::model()->findAll()));
        $attributeId = \ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'defaultname' => 'website',
            'visible' => 'TRUE',
            'attribute_name' => 'Webseite',
            'encrypted'      => 'N',
            'core_attribute' => 'N'
        ));
        $this->assertTrue(intval($attributeId) > 0);

        $participants = array(
            array(
                'participant_id' => 'max',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y',
                'website' => 'http://www.example.com'
            ),
            array(
                'id' => 'max',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'N',
                'website' => 'http://www.example.org'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants, true);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);
        $this->assertArrayHasKey('UpdateCount', $result);
        $this->assertEquals(1, $result['UpdateCount']);

        $max = \Participant::model()->findByPk('max');
        $this->assertInstanceOf(\Participant::class, $max);

        $attribute = $max->getParticipantAttribute('ea_' . $attributeId);
        $this->assertEquals('http://www.example.org', $attribute);
    }

    public function testOneParticipantWithEncryptedCoreAttributesImportedSuccessfully()
    {
        \Yii::app()->session['adminlang'] = 'de';
        $this->assertTrue(empty(\ParticipantAttributeName::model()->findAll()));

        //Setting email attribute to be encrypted.
        $result = \ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'attribute_name' => 'email',
            'defaultname' => 'email',
            'visible' => 'TRUE',
            'encrypted'      => 'Y',
            'core_attribute' => 'Y'
        ));
        $this->assertTrue(intval($result) > 0);

        //Setting lastname attribute to be encrypted.
        $result = \ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'attribute_name' => 'lastname',
            'defaultname' => 'lastname',
            'visible' => 'TRUE',
            'encrypted'      => 'Y',
            'core_attribute' => 'Y'
        ));
        $this->assertTrue(intval($result) > 0);

        $participants = array(
            array(
                'participant_id' => 'max',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);

        $max = \Participant::model()->findByPk('max');
        $this->assertInstanceOf(\Participant::class, $max);

        //Not equal since it's encrypted.
        $this->assertNotEquals($participants[0]['email'], $max->email);
        $this->assertNotEquals($participants[0]['lastname'], $max->lastname);
    }

    public function testParticipantWithOneEncryptedAttributeImportedSucessfully()
    {
        \Yii::app()->session['adminlang'] = 'de';
        $this->assertTrue(empty(\ParticipantAttributeName::model()->findAll()));
        $attributeId = \ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'defaultname' => 'passport',
            'visible' => 'TRUE',
            'attribute_name' => 'Passport',
            'encrypted'      => 'Y',
            'core_attribute' => 'N'
        ));
        $this->assertTrue(intval($attributeId) > 0);

        $participants = array(
            array(
                'participant_id' => 'max',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y',
                'passport' => '123456789',
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);

        $max = \Participant::model()->findByPk('max');
        $this->assertInstanceOf(\Participant::class, $max);

        $attribute = $max->getParticipantAttribute('ea_' . $attributeId);
        $this->assertEquals('123456789', $attribute);
    }

    /**
     * Participants not in the import list are removed, participants in the list
     * (identified by participant_id, id or firstname/lastname/email) are kept.
     */
    public function testParticipantsNotInListRemovedWhenRemoveTrue()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $existing = array(
            $this->makeParticipant('keep1', 'One'),
            $this->makeParticipant('keep2', 'Two'),
            $this->makeParticipant('keep3', 'Three'),
            $this->makeParticipant('gone', 'Away'),
        );
        $result = $this->handler->cpd_importParticipants($sessionKey, $existing);
        $this->assertEquals(4, $result['ImportCount']);
        \Yii::app()->db->createCommand()->insert('{{survey_links}}', array(
            'participant_id' => 'gone',
            'token_id' => 1,
            'survey_id' => 1,
            'date_created' => date('Y-m-d H:i:s')
        ));

        $keepById = $this->makeParticipant(null, 'Two');
        $keepById['id'] = 'keep2';
        $import = array(
            $this->makeParticipant('keep1', 'One'),
            $keepById,
            $this->makeParticipant(null, 'Three'),
            $this->makeParticipant('new', 'New'),
        );
        $result = $this->handler->cpd_importParticipants($sessionKey, $import, false, true);
        $this->assertEquals(1, $result['ImportCount']);
        $this->assertEquals(0, $result['UpdateCount']);
        $this->assertEquals(1, $result['RemoveCount']);

        foreach (array('keep1', 'keep2', 'keep3', 'new') as $participantId) {
            $this->assertNotNull(\Participant::model()->findByPk($participantId), $participantId);
        }
        $this->assertNull(\Participant::model()->findByPk('gone'));
        $this->assertEquals(0, \SurveyLink::model()->countByAttributes(array('participant_id' => 'gone')));
    }

    /**
     * Without the remove flag nothing is deleted.
     */
    public function testParticipantsNotRemovedWhenRemoveFalse()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $this->handler->cpd_importParticipants($sessionKey, array($this->makeParticipant('max', 'Max')));

        $result = $this->handler->cpd_importParticipants($sessionKey, array($this->makeParticipant('erika', 'Erika')));
        $this->assertEquals(0, $result['RemoveCount']);
        $this->assertNotNull(\Participant::model()->findByPk('max'));
    }

    /**
     * An empty import list with the remove flag is rejected and does not delete anything.
     */
    public function testEmptyImportListWithRemoveIsRejected()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $this->handler->cpd_importParticipants($sessionKey, array($this->makeParticipant('max', 'Max')));

        $result = $this->handler->cpd_importParticipants($sessionKey, array(), false, true);
        $this->assertEquals(\remotecontrol_handle::ERR_INVALID_PARAMETERS, $result['error_code']);
        $this->assertNotNull(\Participant::model()->findByPk('max'));
    }

    /**
     * Build a participant row for cpd_importParticipants
     *
     * @param string|null $participantId Participant ID, or null to identify the participant by name and email
     * @param string $lastname Last name, also used to build a unique email address
     * @return array
     */
    private function makeParticipant($participantId, $lastname)
    {
        $participant = array(
            'firstname' => 'Test',
            'lastname' => $lastname,
            'email' => strtolower($lastname) . '@example.com'
        );
        if ($participantId !== null) {
            $participant['participant_id'] = $participantId;
        }
        return $participant;
    }
}
