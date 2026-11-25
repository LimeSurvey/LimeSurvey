<?php

namespace ls\tests\unit\helpers\remotecontrol;

use ls\tests\TestBaseClass;

/**
 * Permission checks of the cpd_importParticipants remote control function:
 * creating and updating central participants with and without permission.
 */
class CPDImportParticipantsPermissionTest extends TestBaseClass
{
    /** @var string ID of the participant that is created by the tests */
    private const NEW_PARTICIPANT_ID = '1b2c3d4e-5f60-4172-8394-a5b6c7d8e9f0';

    /** @var string ID of the participant that exists before the tests run */
    private const EXISTING_PARTICIPANT_ID = '2c3d4e5f-6071-4283-94a5-b6c7d8e9f0a1';

    /** @var array<string, array> Test users: login name => global permissions */
    private const USERS = [
        'rccpdnoperm' => [],
        'rccpdcreate' => ['participantpanel' => ['create' => 'on']],
        'rccpdupdate' => ['participantpanel' => ['update' => 'on']],
    ];

    /** @var \remotecontrol_handle */
    protected static $handler;

    /** @var array<string, int> Test users: login name => user ID */
    protected static $userIds = [];

    /**
     * Activates the Authdb plugin and creates the test users.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);

        // Make sure the Authdb is in database (might not be the case if no browser login attempt has been made).
        $plugin = \Plugin::model()->findByAttributes(array('name' => 'Authdb'));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = 'Authdb';
        }
        $plugin->active = 1;
        $plugin->save();
        App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);
        \Yii::app()->getDb()->createCommand('DELETE FROM {{failed_login_attempts}}')->execute();

        foreach (self::USERS as $userName => $permissions) {
            $user = self::createUserWithPermissions(
                [
                    'users_name' => $userName,
                    'full_name' => $userName,
                    'email' => $userName . '@example.com',
                    'lang' => 'auto',
                    'password' => $userName,
                ],
                // Login permission only, plus the CPDB permissions of the user
                array_merge(['auth_db' => ['read' => 'on']], $permissions)
            );
            self::$userIds[$userName] = (int) $user->uid;
        }
        self::$handler = new \remotecontrol_handle(new \AdminController('dummyid'));
    }

    /**
     * Creates the existing participant, owned by the superadmin.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->deleteTestParticipants();
        \Yii::app()->db->createCommand()->insert('{{participants}}', [
            'participant_id' => self::EXISTING_PARTICIPANT_ID,
            'firstname' => 'Max',
            'lastname' => 'Original',
            'email' => 'max.original@example.com',
            'language' => 'en',
            'blacklisted' => 'N',
            'owner_uid' => 1,
            'created_by' => 1,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Deletes the test participants and logs the superadmin back in.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->deleteTestParticipants();
        \Yii::app()->session['loginID'] = 1;
        \Yii::app()->user->setId(1);
        parent::tearDown();
    }

    /**
     * Deletes the test users and their permissions.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        foreach (self::$userIds as $userId) {
            \Permission::model()->deleteAllByAttributes(['uid' => $userId]);
            \User::model()->deleteByPk($userId);
        }
        parent::tearDownAfterClass();
    }

    /**
     * A user without the global CPDB create permission cannot create a participant.
     *
     * @return void
     */
    public function testCreateWithoutPermissionIsSkipped()
    {
        $result = $this->importAs('rccpdnoperm', [$this->getNewParticipant()]);

        $this->assertSame(0, $result['ImportCount']);
        $this->assertSame(0, $result['UpdateCount']);
        $this->assertNull(\Participant::model()->findByPk(self::NEW_PARTICIPANT_ID));
    }

    /**
     * A user with the global CPDB create permission creates a participant and owns it.
     *
     * @return void
     */
    public function testCreateWithPermission()
    {
        $result = $this->importAs('rccpdcreate', [$this->getNewParticipant()]);

        $this->assertSame(1, $result['ImportCount']);
        $participant = \Participant::model()->findByPk(self::NEW_PARTICIPANT_ID);
        $this->assertInstanceOf(\Participant::class, $participant);
        $this->assertEquals(self::$userIds['rccpdcreate'], $participant->owner_uid);
        $this->assertEquals(self::$userIds['rccpdcreate'], $participant->created_by);
    }

    /**
     * Users without the global CPDB update permission cannot update a participant of another user,
     * the global CPDB create permission is not enough.
     *
     * @dataProvider usersWithoutUpdatePermissionProvider
     * @param string $userName Login name of the test user
     * @return void
     */
    public function testUpdateWithoutPermissionIsSkipped(string $userName)
    {
        $result = $this->importAs($userName, [$this->getUpdatedParticipant()], true);

        $this->assertSame(0, $result['ImportCount']);
        $this->assertSame(0, $result['UpdateCount']);
        $this->assertSame('Original', $this->getExistingParticipantLastname());
    }

    /**
     * Users that have no global CPDB update permission.
     *
     * @return array
     */
    public static function usersWithoutUpdatePermissionProvider(): array
    {
        return [
            'no CPDB permission' => ['rccpdnoperm'],
            'CPDB create permission only' => ['rccpdcreate'],
        ];
    }

    /**
     * A user with the global CPDB update permission updates a participant of another user,
     * and the participant keeps its owner.
     *
     * @return void
     */
    public function testUpdateWithPermission()
    {
        $result = $this->importAs('rccpdupdate', [$this->getUpdatedParticipant()], true);

        $this->assertSame(0, $result['ImportCount']);
        $this->assertSame(1, $result['UpdateCount']);
        $this->assertSame('Updated', $this->getExistingParticipantLastname());
        $this->assertEquals(1, \Participant::model()->findByPk(self::EXISTING_PARTICIPANT_ID)->owner_uid);
    }

    /**
     * A participant is not updated when the update parameter is not set, even with the update permission.
     *
     * @return void
     */
    public function testUpdateWithPermissionNeedsUpdateParameter()
    {
        $result = $this->importAs('rccpdupdate', [$this->getUpdatedParticipant()]);

        $this->assertSame(0, $result['ImportCount']);
        $this->assertSame(0, $result['UpdateCount']);
        $this->assertSame('Original', $this->getExistingParticipantLastname());
    }

    /**
     * The owner of a participant can update it without the global CPDB update permission.
     *
     * @return void
     */
    public function testOwnerCanUpdateOwnParticipant()
    {
        \Participant::model()->updateByPk(
            self::EXISTING_PARTICIPANT_ID,
            ['owner_uid' => self::$userIds['rccpdnoperm']]
        );

        $result = $this->importAs('rccpdnoperm', [$this->getUpdatedParticipant()], true);

        $this->assertSame(1, $result['UpdateCount']);
        $this->assertSame('Updated', $this->getExistingParticipantLastname());
    }

    /**
     * A user can update a participant shared with them only when the share allows editing.
     *
     * @dataProvider shareProvider
     * @param string $canEdit The can_edit value of the share
     * @param int $expectedUpdateCount Expected number of updated participants
     * @param string $expectedLastname Expected last name of the participant after the import
     * @return void
     */
    public function testSharedParticipantUpdate(string $canEdit, int $expectedUpdateCount, string $expectedLastname)
    {
        \Yii::app()->db->createCommand()->insert('{{participant_shares}}', [
            'participant_id' => self::EXISTING_PARTICIPANT_ID,
            'share_uid' => self::$userIds['rccpdnoperm'],
            'date_added' => date('Y-m-d H:i:s'),
            'can_edit' => $canEdit,
        ]);

        $result = $this->importAs('rccpdnoperm', [$this->getUpdatedParticipant()], true);

        $this->assertSame($expectedUpdateCount, $result['UpdateCount']);
        $this->assertSame($expectedLastname, $this->getExistingParticipantLastname());
    }

    /**
     * Participant shares with and without edit rights.
     *
     * @return array
     */
    public static function shareProvider(): array
    {
        return [
            'share with edit rights' => ['1', 1, 'Updated'],
            'share without edit rights' => ['0', 0, 'Original'],
        ];
    }

    /**
     * Logs in a test user with remote control and imports participants.
     *
     * @param string $userName Login name of the test user (the password is the same)
     * @param array $participants Participants to import
     * @param bool $update Whether existing participants are updated
     * @return array Result of cpd_importParticipants
     */
    private function importAs(string $userName, array $participants, bool $update = false): array
    {
        $sessionKey = self::$handler->get_session_key($userName, $userName);
        $this->assertIsString($sessionKey, 'Remote control login failed for ' . $userName);
        $result = self::$handler->cpd_importParticipants($sessionKey, $participants, $update);
        $this->assertArrayHasKey('ImportCount', $result);
        $this->assertArrayHasKey('UpdateCount', $result);
        return $result;
    }

    /**
     * Returns the data of a participant that does not exist yet.
     *
     * @return array
     */
    private function getNewParticipant(): array
    {
        return [
            'participant_id' => self::NEW_PARTICIPANT_ID,
            'firstname' => 'Erika',
            'lastname' => 'Created',
            'email' => 'erika.created@example.com',
        ];
    }

    /**
     * Returns new data for the existing participant, with its ID in the 'id' key.
     *
     * @return array
     */
    private function getUpdatedParticipant(): array
    {
        return [
            'id' => self::EXISTING_PARTICIPANT_ID,
            'firstname' => 'Max',
            'lastname' => 'Updated',
            'email' => 'max.updated@example.com',
        ];
    }

    /**
     * Returns the decrypted last name of the existing participant.
     *
     * @return string
     */
    private function getExistingParticipantLastname(): string
    {
        $participant = \Participant::model()->findByPk(self::EXISTING_PARTICIPANT_ID);
        $participant->decrypt();
        return $participant->lastname;
    }

    /**
     * Deletes the test participants and their shares.
     *
     * @return void
     */
    private function deleteTestParticipants(): void
    {
        $participantIds = [self::NEW_PARTICIPANT_ID, self::EXISTING_PARTICIPANT_ID];
        \ParticipantShare::model()->deleteAllByAttributes(['participant_id' => $participantIds]);
        \Participant::model()->deleteAllByAttributes(['participant_id' => $participantIds]);
    }
}
