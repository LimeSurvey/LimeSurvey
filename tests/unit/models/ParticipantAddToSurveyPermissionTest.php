<?php

namespace ls\tests;

/**
 * Regression tests for issue #19405: users with read-only access to the central participant database
 * must be able to list the surveys of a participant, and with the export permission add it to a survey.
 */
class ParticipantAddToSurveyPermissionTest extends TestBaseClass
{
    /** @var \User[] */
    private static $users = [];

    /** @var \Participant */
    private static $participant;

    /**
     * Creates the participant owner and the users with different participant panel permissions.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->user->setId(1);

        $userPermissions = [
            'owner'      => [],
            'readOnly'   => ['participantpanel' => ['read' => 'on']],
            'readExport' => ['participantpanel' => ['read' => 'on', 'export' => 'on']],
            'exportOnly' => ['participantpanel' => ['export' => 'on']],
        ];
        foreach ($userPermissions as $key => $permissions) {
            self::$users[$key] = self::createUserWithPermissions(
                [
                    'users_name' => 'cpdbAddToSurvey19405' . $key,
                    'full_name'  => 'CPDB add to survey ' . $key,
                    'email'      => 'cpdbaddtosurvey19405' . strtolower($key) . '@example.org',
                    'lang'       => 'auto',
                    'password'   => 'testpassword123',
                ],
                $permissions
            );
        }

        self::$participant = new \Participant();
        self::$participant->participant_id = self::$participant->genUuid();
        self::$participant->blacklisted = 'N';
        self::$participant->owner_uid = self::$users['owner']->uid;
        self::$participant->created_by = self::$users['owner']->uid;
        if (!self::$participant->save()) {
            throw new \Exception('Could not save participant');
        }
    }

    /**
     * Deletes the participant, its shares and the test users.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->user->setId(1);
        \ParticipantShare::model()->deleteAllByAttributes(['participant_id' => self::$participant->participant_id]);
        self::$participant->delete();
        foreach (self::$users as $user) {
            \User::model()->deleteByPk($user->uid);
        }
        parent::tearDownAfterClass();
    }

    /**
     * Reset the logged in user after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->user->setId(1);
        parent::tearDown();
    }

    /**
     * Global read permission allows listing the surveys, but adding to a survey also needs the export permission.
     *
     * @return void
     */
    public function testReadOnlyUserCanListSurveysButNotAddToSurvey()
    {
        \Yii::app()->user->setId(self::$users['readOnly']->uid);
        $this->assertTrue(self::$participant->userHasPermissionToRead());
        $this->assertFalse(self::$participant->userHasPermissionToAddToSurvey());
        $this->assertSame(
            [],
            \Participant::model()->filterParticipantIdsAddableToSurvey([self::$participant->participant_id])
        );
    }

    /**
     * Global read and export permission allow adding participants of other users to a survey.
     *
     * @return void
     */
    public function testReadAndExportUserCanAddToSurvey()
    {
        \Yii::app()->user->setId(self::$users['readExport']->uid);
        $this->assertFalse(self::$participant->userHasPermissionToEdit());
        $this->assertTrue(self::$participant->userHasPermissionToAddToSurvey());
        $this->assertSame(
            [self::$participant->participant_id],
            \Participant::model()->filterParticipantIdsAddableToSurvey([self::$participant->participant_id, ''])
        );
    }

    /**
     * Export permission alone only covers participants shared with the user.
     *
     * @return void
     */
    public function testExportOnlyUserNeedsShareToAddToSurvey()
    {
        $participantId = self::$participant->participant_id;
        \Yii::app()->user->setId(self::$users['exportOnly']->uid);
        $this->assertFalse(self::$participant->userHasPermissionToAddToSurvey());
        $this->assertSame([], \Participant::model()->filterParticipantIdsAddableToSurvey([$participantId]));

        \Yii::app()->user->setId(1);
        $share = new \ParticipantShare();
        $share->participant_id = $participantId;
        $share->share_uid = self::$users['exportOnly']->uid;
        $share->date_added = date('Y-m-d H:i:s');
        $share->can_edit = '0';
        $this->assertTrue($share->save(), 'Saved read-only share');

        \Yii::app()->user->setId(self::$users['exportOnly']->uid);
        $this->assertTrue(self::$participant->userHasPermissionToAddToSurvey());
        $this->assertSame([$participantId], \Participant::model()->filterParticipantIdsAddableToSurvey([$participantId]));
    }

    /**
     * The owner can still add own participants to a survey without any global permission.
     *
     * @return void
     */
    public function testOwnerCanAddToSurvey()
    {
        \Yii::app()->user->setId(self::$users['owner']->uid);
        $this->assertTrue(self::$participant->userHasPermissionToAddToSurvey());
        $this->assertSame(
            [self::$participant->participant_id],
            \Participant::model()->filterParticipantIdsAddableToSurvey([self::$participant->participant_id])
        );
    }
}
