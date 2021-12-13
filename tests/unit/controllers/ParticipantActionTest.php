<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;

/**
 * Test the participantsaction controller class.
 */
class ParticipantActionTest extends TestBaseClass
{
    /**
     * @group pp
     */
    public function testUpdateEncryption()
    {
        \Yii::import('application.controllers.admin.ParticipantsAction', true);
        \Yii::import('application.helpers.admin.ajax_helper', true);
        \Yii::app()->session['loginID'] = 1;

        /** @var participantsaction */
        $participantController = new \ParticipantsAction('dummy');

        // TODO: Use PHPUnit dataset instead? https://phpunit.de/manual/6.5/en/database.html
        $attrName = new \ParticipantAttributeName();
        $attrName->attribute_type = 'TB';
        $attrName->defaultname    = 'encrypted';
        $attrName->visible        = 'TRUE';
        $attrName->encrypted      = 'Y';
        $attrName->core_attribute = 'N';
        $this->assertTrue($attrName->save());

        $attrName2 = new \ParticipantAttributeName();
        $attrName2->attribute_type = 'TB';
        $attrName2->defaultname    = 'not_ecrypted';
        $attrName2->visible        = 'TRUE';
        $attrName2->encrypted      = 'N';
        $attrName2->core_attribute = 'N';
        $this->assertTrue($attrName2->save());

        $part = new \Participant();
        $part->participant_id = $part->genUuid();
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
            'ea_' . $attrName->attribute_id => 'Some encrypted value',
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
                'attribute_id'   => $attrName->attribute_id
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

        $this->assertTrue($attrName->delete());
        $this->assertTrue($attrName2->delete());
        $this->assertTrue($attrValue->delete());
        $this->assertTrue($attrValue2->delete());
        $this->assertTrue($part->delete());
    }
}
