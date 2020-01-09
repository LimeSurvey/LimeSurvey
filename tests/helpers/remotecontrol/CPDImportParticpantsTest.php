<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseTest.php';

class CPDImportParticpantsTest extends BaseTest
{
    public function setUp()
    {
        Yii::app()->db->createCommand()->truncateTable('{{participants}}');
        Yii::app()->db->createCommand()->truncateTable('{{participant_attribute}}');
        Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_names}}');
        Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_names_lang}}');
        Yii::app()->db->createCommand()->truncateTable('{{participant_attribute_values}}');
        Yii::app()->db->createCommand()->truncateTable('{{participant_shares}}');
        parent::setUp();
    }

    public function testOneParticipantImportedSuccessfully()
    {
        $participants = array(
            array(
                'firstname' => 'Marko',
                'lastname' => 'Bischof',
                'email' => 'marko.bischof@gmail.com',
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
                'firstname' => 'Marko',
                'lastname' => 'Bischof',
                'email' => 'marko.bischof@gmail.com',
                'language' => 'de',
                'blacklisted' => 'Y'
            ),
            array(
                'firstname' => 'Marcel',
                'lastname' => 'minke',
                'email' => 'marcel.minke@survey-consulting.com',
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
                'participant_id' => 'mbi',
                'firstname' => 'Marko',
                'lastname' => 'Bischof',
                'email' => 'marko.bischof@gmail.com',
                'language' => 'de',
                'blacklisted' => 'Y'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);

        $mbi = Participant::model()->findByPk('mbi');
        $this->assertInstanceOf('Participant', $mbi);

    }

    public function testImportingParticipantFailsDueToSameFirstnameLastnameEmail()
    {
        $participants = array(
            array(
                'firstname' => 'Marko',
                'lastname' => 'Bischof',
                'email' => 'marko.bischof@gmail.com',
                'language' => 'de',
                'blacklisted' => 'Y'
            ),
            array(
                'firstname' => 'Marko',
                'lastname' => 'Bischof',
                'email' => 'marko.bischof@gmail.com',
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
        Yii::app()->session['adminlang'] = 'de';
        $this->assertTrue(empty(ParticipantAttributeName::model()->findAll()));
        $result = ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'defaultname' => 'website',
            'visible' => 'TRUE',
            'attribute_name' => 'Webseite'
        ));
        $this->assertTrue(intval($result) > 0);

        $participants = array(
            array(
                'participant_id' => 'mbi',
                'firstname' => 'Marko',
                'lastname' => 'Bischof',
                'email' => 'marko.bischof@gmail.com',
                'language' => 'de',
                'blacklisted' => 'Y',
                'website' => 'http://www.hello-world.de'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);

        $mbi = Participant::model()->findByPk('mbi');
        $this->assertInstanceOf(Participant::class, $mbi);

        $attribute = $mbi->getParticipantAttribute('ea_1');
        $this->assertEquals('http://www.hello-world.de', $attribute);
    }

    public function testParticipantUpdatedSuccessfullyWhenUpdateTrue()
    {
        Yii::app()->session['adminlang'] = 'de';
        $this->assertTrue(empty(ParticipantAttributeName::model()->findAll()));
        $result = ParticipantAttributeName::model()->storeAttribute(array(
            'attribute_type' => 'TB',
            'defaultname' => 'website',
            'visible' => 'TRUE',
            'attribute_name' => 'Webseite'
        ));
        $this->assertTrue(intval($result) > 0);

        $participants = array(
            array(
                'participant_id' => 'mbi',
                'firstname' => 'Marko',
                'lastname' => 'Bischof',
                'email' => 'marko.bischof@gmail.com',
                'language' => 'de',
                'blacklisted' => 'Y',
                'website' => 'http://www.hello-world.de'
            ),
            array(
                'id' => 'mbi',
                'firstname' => 'Marko',
                'lastname' => 'Bischof',
                'email' => 'marko.bischof@gmail.com',
                'language' => 'de',
                'blacklisted' => 'N',
                'website' => 'http://www.example.de'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->cpd_importParticipants($sessionKey, $participants, true);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertEquals(1, $result['ImportCount']);
        $this->assertArrayHasKey('UpdateCount', $result);
        $this->assertEquals(1, $result['UpdateCount']);

        $mbi = Participant::model()->findByPk('mbi');
        $this->assertInstanceOf(Participant::class, $mbi);

        $attribute = $mbi->getParticipantAttribute('ea_1');
        $this->assertEquals('http://www.example.de', $attribute);
    }
}
