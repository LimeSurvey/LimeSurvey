<?php

namespace ls\tests\unit\helpers\remotecontrol;

class CPDImportParticpantsTest extends BaseTest
{
    /** @var string Participant ID used by the tests that set their own ID */
    private const PARTICIPANT_ID = '0a1b2c3d-4e5f-4071-8293-a4b5c6d7e8f9';

    public function setUp(): void
    {
        \Yii::app()->db->createCommand()->truncateTable('{{participants}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_names}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_names_lang}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_values}}');
        \Yii::app()->db->createCommand()->truncateTable('{{participant_shares}}');
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
                'participant_id' => self::PARTICIPANT_ID,
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

        $max = \Participant::model()->findByPk(self::PARTICIPANT_ID);
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
                'participant_id' => self::PARTICIPANT_ID,
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

        $max = \Participant::model()->findByPk(self::PARTICIPANT_ID);
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
                'participant_id' => self::PARTICIPANT_ID,
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
                'language' => 'de',
                'blacklisted' => 'Y',
                'website' => 'http://www.example.com'
            ),
            array(
                'id' => self::PARTICIPANT_ID,
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

        $max = \Participant::model()->findByPk(self::PARTICIPANT_ID);
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
                'participant_id' => self::PARTICIPANT_ID,
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

        $max = \Participant::model()->findByPk(self::PARTICIPANT_ID);
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
                'participant_id' => self::PARTICIPANT_ID,
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

        $max = \Participant::model()->findByPk(self::PARTICIPANT_ID);
        $this->assertInstanceOf(\Participant::class, $max);

        $attribute = $max->getParticipantAttribute('ea_' . $attributeId);
        $this->assertEquals('123456789', $attribute);
    }

    /**
     * A new participant with a participant ID that is not a UUID is not imported, and the ID is reported.
     *
     * @return void
     */
    public function testParticipantWithInvalidIdIsNotImported()
    {
        $participants = array(
            array(
                'participant_id' => 'a"b<c',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'email' => 'max.mustermann@example.com',
            ),
            array(
                'participant_id' => self::PARTICIPANT_ID,
                'firstname' => 'Erika',
                'lastname' => 'Mustermann',
                'email' => 'erika.mustermann@example.com',
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertEquals(1, $result['ImportCount']);
        $this->assertSame(['a"b<c'], $result['InvalidParticipantIds']);
        $this->assertNull(\Participant::model()->findByPk('a"b<c'));
        $this->assertInstanceOf(\Participant::class, \Participant::model()->findByPk(self::PARTICIPANT_ID));
    }

    /**
     * An existing participant with an older participant ID that is not a UUID can still be updated.
     *
     * @return void
     */
    public function testExistingParticipantWithLegacyIdIsUpdated()
    {
        \Yii::app()->db->createCommand()->insert('{{participants}}', [
            'participant_id' => 'legacy-id-12345',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'email' => 'max.mustermann@example.com',
            'language' => 'en',
            'blacklisted' => 'N',
            'owner_uid' => 1,
            'created_by' => 1,
            'created' => date('Y-m-d H:i:s'),
        ]);
        $participants = array(
            array(
                'participant_id' => 'legacy-id-12345',
                'firstname' => 'Max',
                'lastname' => 'Musterfrau',
                'email' => 'max.mustermann@example.com',
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        try {
            $result = $this->handler->cpd_importParticipants($sessionKey, $participants, true);
            $this->assertEquals(0, $result['ImportCount']);
            $this->assertEquals(1, $result['UpdateCount']);
            $this->assertSame([], $result['InvalidParticipantIds']);
            $participant = \Participant::model()->findByPk('legacy-id-12345');
            $participant->decrypt();
            $this->assertEquals('Musterfrau', $participant->lastname);
        } finally {
            \Participant::model()->deleteByPk('legacy-id-12345');
        }
    }
}
