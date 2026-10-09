<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;

/**
 * Test the participantsaction controller class.
 */
class ParticipantActionTest extends TestBaseClass
{
    /** @var \User User with only the global participant panel create permission */
    private static $createOnlyUser;

    /**
     * Creates a user with only the global participant panel create permission.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$createOnlyUser = self::createUserWithPermissions(
            [
                'users_name' => 'cpdb_create_only',
                'full_name' => 'cpdb_create_only',
                'email' => 'cpdb_create_only@example.com',
                'lang' => 'auto',
                'password' => 'cpdb_create_only',
                'parent_id' => 1,
            ],
            [
                'auth_db' => ['read' => 'on'],
                'participantpanel' => ['create' => 'on'],
            ]
        );
    }

    /**
     * Removes the test user and any participants left behind by failed tests.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        \Participant::model()->deleteAllByAttributes(['owner_uid' => self::$createOnlyUser->uid]);
        \Permission::model()->deleteAllByAttributes(['uid' => self::$createOnlyUser->uid]);
        self::$createOnlyUser->delete();
        \Yii::app()->session['loginID'] = null;
    }

    /**
     * Owners without global delete permission can delete their own participants.
     *
     * @return void
     */
    public function testOwnerWithoutGlobalDeletePermissionCanDeleteOwnParticipant()
    {
        $participant = $this->createParticipant(self::$createOnlyUser->uid);

        \Yii::app()->session['loginID'] = self::$createOnlyUser->uid;
        $called = $this->runDeleteParticipant([
            'selectedoption' => 'po',
            'participant_id' => $participant->participant_id,
        ]);

        $this->assertEquals('outputSuccess', $called);
        $this->assertNull(\Participant::model()->findByPk($participant->participant_id));
    }

    /**
     * Users without global delete permission must not delete participants of other users.
     *
     * @dataProvider deleteOptionProvider
     * @param string $selectedOption
     * @return void
     */
    public function testUserWithoutGlobalDeletePermissionCannotDeleteForeignParticipant($selectedOption)
    {
        $participant = $this->createParticipant(1);

        \Yii::app()->session['loginID'] = self::$createOnlyUser->uid;
        $called = $this->runDeleteParticipant([
            'selectedoption' => $selectedOption,
            'participant_id' => $participant->participant_id,
        ]);

        $this->assertEquals('outputNoPermission', $called);
        $this->assertNotNull(\Participant::model()->findByPk($participant->participant_id));
        $this->assertTrue($participant->delete());
    }

    /**
     * Delete options of the participant delete action.
     *
     * @return array<string, string[]>
     */
    public static function deleteOptionProvider()
    {
        return [
            'central panel only' => ['po'],
            'central panel and surveys' => ['ptt'],
            'central panel, surveys and responses' => ['ptta'],
        ];
    }

    /**
     * A mass delete by a user without global delete permission only deletes the participants they own.
     *
     * @return void
     */
    public function testMassDeleteWithoutGlobalDeletePermissionOnlyDeletesOwnParticipants()
    {
        $ownParticipant = $this->createParticipant(self::$createOnlyUser->uid);
        $foreignParticipant = $this->createParticipant(1);

        \Yii::app()->session['loginID'] = self::$createOnlyUser->uid;
        $called = $this->runDeleteParticipant([
            'selectedoption' => 'po',
            'sItems' => json_encode([$ownParticipant->participant_id, $foreignParticipant->participant_id]),
        ]);

        $this->assertEquals('outputSuccess', $called);
        $this->assertNull(\Participant::model()->findByPk($ownParticipant->participant_id));
        $this->assertNotNull(\Participant::model()->findByPk($foreignParticipant->participant_id));
        $this->assertTrue($foreignParticipant->delete());
    }

    /**
     * Deleting participants from the central panel and their surveys must not delete participants of other users,
     * even if all of them are linked to a survey where the user may delete survey participants.
     *
     * @return void
     */
    public function testDeleteParticipantTokenOnlyDeletesOwnParticipants()
    {
        // The survey owner has all permissions on the survey, including deleting survey participants
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_143933.lss', self::$createOnlyUser->uid);
        \Token::createTable(self::$surveyId);

        $ownParticipant = $this->createParticipant(self::$createOnlyUser->uid);
        $foreignParticipant = $this->createParticipant(1);
        foreach ([$ownParticipant, $foreignParticipant] as $participant) {
            $surveyLink = new \SurveyLink();
            $surveyLink->participant_id = $participant->participant_id;
            $surveyLink->token_id = 0;
            $surveyLink->survey_id = self::$surveyId;
            $surveyLink->date_created = date('Y-m-d H:i:s');
            $this->assertTrue($surveyLink->save());
        }

        \Yii::app()->session['loginID'] = self::$createOnlyUser->uid;
        \Participant::model()->deleteParticipantToken(
            $ownParticipant->participant_id . ',' . $foreignParticipant->participant_id
        );

        $this->assertNull(\Participant::model()->findByPk($ownParticipant->participant_id));
        $this->assertNotNull(\Participant::model()->findByPk($foreignParticipant->participant_id));
        \SurveyLink::model()->deleteAllByAttributes(['survey_id' => self::$surveyId]);
        $this->assertTrue($foreignParticipant->delete());
    }

    /**
     * Creates a central participant.
     *
     * @param integer $ownerUid
     * @return \Participant
     */
    private function createParticipant($ownerUid)
    {
        $participant = new \Participant();
        $participant->participant_id = $participant->genUuid();
        $participant->blacklisted = 'N';
        $participant->owner_uid = $ownerUid;
        $participant->created_by = $ownerUid;
        $this->assertTrue($participant->save(), 'Saved participant');
        return $participant;
    }

    /**
     * Runs ParticipantsAction::deleteParticipant() with the given POST data.
     *
     * @param array<string, string> $post
     * @return string|null Name of the called AjaxHelper output method
     */
    private function runDeleteParticipant(array $post)
    {
        \Yii::import('application.controllers.admin.ParticipantsAction', true);
        \Yii::import('application.helpers.admin.ajax_helper', true);

        $dummyAjaxHelper = new class () extends \ls\ajax\AjaxHelper
        {
            public static $called = null;
            public static function outputSuccess($msg)
            {
                self::$called = 'outputSuccess';
            }
            public static function outputNoPermission()
            {
                // Like the real AjaxHelper, end the request here
                self::$called = 'outputNoPermission';
                throw new \CHttpException(403);
            }
            public static function outputError($msg, $code = 0)
            {
                self::$called = 'outputError';
            }
        };
        $dummyAjaxHelper::$called = null;

        $participantController = new \ParticipantsAction('dummy');
        $participantController->setAjaxHelper($dummyAjaxHelper);

        $_POST = $post;
        try {
            $participantController->deleteParticipant();
        } catch (\CHttpException $e) {
            // Thrown by the dummy outputNoPermission()
        } finally {
            $_POST = [];
        }

        return $dummyAjaxHelper::$called;
    }

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
