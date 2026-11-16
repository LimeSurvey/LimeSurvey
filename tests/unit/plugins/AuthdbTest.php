<?php

namespace ls\tests;

/**
 * Tests for the Authdb core plugin
 */
class AuthdbTest extends TestBaseClass
{
    /** @var string Username of the user created by the tests */
    private const USERNAME = 'AuthdbCreateTest';

    /** @var \Authdb */
    private static $plugin;

    /**
     * Load the plugin and log in as the initial superadmin
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->session['loginID'] = 1;
        $pluginRow = \Plugin::model()->findByAttributes(['name' => 'Authdb']);
        self::$plugin = App()->getPluginManager()->loadPlugin('Authdb', $pluginRow->id);
        self::deleteTestUser();
    }

    /**
     * Delete the test user
     */
    public static function tearDownAfterClass(): void
    {
        self::deleteTestUser();
        parent::tearDownAfterClass();
    }

    /**
     * Delete the user created by the tests, if any
     *
     * @return void
     */
    private static function deleteTestUser(): void
    {
        $user = \User::model()->findByAttributes(['users_name' => self::USERNAME]);
        if ($user) {
            $user->delete();
        }
    }

    /**
     * Dispatch the createNewUser event to the plugin only
     *
     * @param string|null $userType The user_type of the new user, null to leave it out
     * @return \PluginEvent
     */
    private function createUser(?string $userType): \PluginEvent
    {
        $user = [
            'users_name' => self::USERNAME,
            'full_name' => 'Authdb create test',
            'email' => 'authdbcreatetest@example.com',
            'password' => createPassword(),
        ];
        if ($userType !== null) {
            $user['user_type'] = $userType;
        }
        $event = new \PluginEvent('createNewUser');
        $event->set('preCollectedUserArray', $user);
        self::$plugin->setEvent($event)->createNewUser();
        return $event;
    }

    /**
     * Users of another type (e.g. LDAP) are left to their authentication plugin
     */
    public function testUserOfOtherTypeIsNotCreated()
    {
        $event = $this->createUser('LDAP');
        $this->assertNull($event->get('newUserID'));
        $this->assertNull(\User::model()->findByAttributes(['users_name' => self::USERNAME]));
    }

    /**
     * Database users, and users without a type (e.g. imported ones), are created with the database login
     *
     * @dataProvider databaseUserTypeProvider
     * @param string|null $userType
     */
    public function testDatabaseUserIsCreated(?string $userType)
    {
        self::deleteTestUser();
        $event = $this->createUser($userType);
        $this->assertSame(\Authdb::ERROR_NONE, $event->get('errorCode'));
        $newUserId = $event->get('newUserID');
        $this->assertNotEmpty($newUserId);
        $this->assertTrue(\Permission::model()->hasGlobalPermission('auth_db', 'read', $newUserId));
    }

    /**
     * User types created by Authdb
     *
     * @return array
     */
    public static function databaseUserTypeProvider(): array
    {
        return [
            'DB' => ['DB'],
            'no type' => [null],
        ];
    }
}
