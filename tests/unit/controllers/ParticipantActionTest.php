<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;
use PHPUnit\DbUnit\TestCaseTrait;

/**
 * Test the participantsaction controller class.
 */
class ParticipantActionTest extends TestBaseClass
{
    use TestCaseTrait;

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $config = include(APPPATH . '/config/config.php');
        $dsn = 'mysql:dbname=limesurvey;host=localhost';
        $user = $config['components']['db']['username'];
        $password = $config['components']['db']['password'];
        $pdo = new \PDO($dsn, $user, $password);
        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        libxml_disable_entity_loader(false);
        return $this->createFlatXMLDataSet(ROOT . '/tests/data/datasets/participantattributes.xml');
    }

    /**
     * @group pp
     */
    public function testUpdateEncryption()
    {
        \Yii::import('application.controllers.admin.participantsaction', true);
        \Yii::import('application.helpers.admin.ajax_helper', true);
        \Yii::app()->session['loginID'] = 1;

        /** @var participantsaction */
        $participantController = new \participantsaction('dummy');

        // TODO: Use PHPUnit dataset instead? https://phpunit.de/manual/6.5/en/database.html
        $attribute_id = 10;

        $attrName2 = new \ParticipantAttributeName();
        $attrName2->attribute_type = 'TB';
        $attrName2->defaultname    = 'not_ecrypted';
        $attrName2->visible        = 'TRUE';
        $attrName2->encrypted      = 'N';
        $attrName2->core_attribute = 'N';
        $this->assertTrue($attrName2->save());

        $part = new \Participant();
        $part->participant_id = $part->gen_uuid();
        $part->blacklisted = 'N';
        $part->owner_uid   = 1;
        $part->created_by  = 1;
        $this->assertTrue($part->save(), 'Saved participant');

        /** @var array<string, string> */
        $data = [
            'participant_id' => $part->participant_id,
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'language' => '',
            'blacklisted' => 'N',
            'owner_uid' => '1'
        ];

        /** @var array<string, string> */
        $extraAttributes = [
            'ea_' . $attribute_id => 'Some encrypted value',
            'ea_' . $attrName2->attribute_id => 'Some value'
        ];

        /** @var AjaxHelper */
        $dummyAjaxHelper = new class() extends \ls\ajax\AjaxHelper
        {
            public static $called = null;
            public static function outputSuccess($msg)
            {
                self::$called = 'outputSuccess';
            }
            public static function outputNoPermission()
            {
                self::$called = 'outputNoPermission';
            }
            public static function outputError($msg, $code = 0)
            {
                self::$called = 'outputError';
            }
        };

        // Inject our dummy AjaxHelper into the controller.
        $participantController->setAjaxHelper($dummyAjaxHelper);

        // Thanks to dummy AjaxHelper, this will not die.
        $participantController->updateParticipant($data, $extraAttributes);

        $this->assertEquals('outputSuccess', $dummyAjaxHelper::$called);

        $attrValue = \ParticipantAttribute::model()->findByAttributes(
            [
                'participant_id' => $part->participant_id,
                'attribute_id'   => $attribute_id
            ]
        );

        $this->assertNotEmpty($attrValue);
        // Not equal, because it is encrypted.
        $this->assertNotEquals('Some encrypted value', $attrValue->value);

        $attrValue2 = \ParticipantAttribute::model()->findByAttributes(
            [
                'participant_id' => $part->participant_id,
                'attribute_id'   => $attrName2->attribute_id
            ]
        );

        $this->assertNotEmpty($attrValue2);
        // Equal, because it is NOT encrypted.
        $this->assertEquals('Some value', $attrValue2->value);

        $this->assertTrue($attrName2->delete());
        $this->assertTrue($attrValue->delete());
        $this->assertTrue($attrValue2->delete());
        $this->assertTrue($part->delete());
    }
}
