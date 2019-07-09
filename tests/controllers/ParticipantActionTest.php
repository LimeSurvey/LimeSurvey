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
        \Yii::import('application.controllers.admin.participantsaction', true);
        \Yii::import('application.helpers.admin.ajax_helper', true);
        \Yii::app()->session['loginID'] = 1;

        /** @var participantsaction */
        $pp = new \participantsaction('dummy');

        /** @var array<string, string> */
        $data = [
            'participant_id' => '16fbd492-ba69-421b-9c0f-a806629a5ff1',
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'language' => '',
            'blacklisted' => 'N',
            'owner_uid' => '1'
        ];

        /** @var array<string, string> */
        $extraAttributes = [
            'ea_11' => '',
            'ea_12' => '',
            'ea_13' => '',
            'ea_14' => ''
        ];

        /** @var AjaxHelper */
        $dummyAjaxHelper = new class() extends \ls\ajax\AjaxHelper {
            public static function outputSuccess($msg) { die($msg); }
            public static function outputNoPermission() { die('no permission'); }
            public static function outputError($msg, $code = 0) { die($msg); }
        };

        $pp->setAjaxHelper($dummyAjaxHelper);

        $pp->updateParticipant($data, $extraAttributes);
    }
}
