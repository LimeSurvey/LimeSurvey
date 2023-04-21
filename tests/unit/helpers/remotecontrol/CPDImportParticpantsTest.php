<?php

namespace ls\tests;

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
        $result = \ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'defaultname' => 'website',
            'visible' => 'TRUE',
            'attribute_name' => 'Webseite',
            'encrypted'      => 'N',
            'core_attribute' => 'N'
        ));
        $this->assertTrue(intval($result) > 0);

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

        $attribute = $max->getParticipantAttribute('ea_1');
        $this->assertEquals('http://www.example.com', $attribute);
    }

    public function testParticipantUpdatedSuccessfullyWhenUpdateTrue()
    {
        \Yii::app()->session['adminlang'] = 'de';
        $this->assertTrue(empty(\ParticipantAttributeName::model()->findAll()));
        $result = \ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'defaultname' => 'website',
            'visible' => 'TRUE',
            'attribute_name' => 'Webseite',
            'encrypted'      => 'N',
            'core_attribute' => 'N'
        ));
        $this->assertTrue(intval($result) > 0);

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

        $attribute = $max->getParticipantAttribute('ea_1');
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
        $result = \ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'defaultname' => 'passport',
            'visible' => 'TRUE',
            'attribute_name' => 'Passport',
            'encrypted'      => 'Y',
            'core_attribute' => 'N'
        ));
        $this->assertTrue(intval($result) > 0);

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

        $attribute = $max->getParticipantAttribute('ea_1');
        $this->assertEquals('123456789', $attribute);
    }
}
