<?php

namespace ls\tests;

/**
 * Tests for the AuthLDAP core plugin which don't need an LDAP server.
 */
class AuthLDAPTest extends TestBaseClass
{
    /** @var \AuthLDAP */
    private static $plugin;

    /** @var \User */
    private static $user;

    /** @var \Plugin|null Plugin row created by the test, deleted afterwards */
    private static $createdPluginRow;

    /**
     * Load the plugin (without activating it) and create a user with a mixed case username
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $pluginRow = \Plugin::model()->findByAttributes(['name' => 'AuthLDAP']);
        if (!$pluginRow) {
            $pluginRow = new \Plugin();
            $pluginRow->name = 'AuthLDAP';
            $pluginRow->active = 0;
            $pluginRow->save();
            self::$createdPluginRow = $pluginRow;
        }
        self::$plugin = App()->getPluginManager()->loadPlugin('AuthLDAP', $pluginRow->id);
        $userId = \User::insertUser('AuthLdapCaseTest', createPassword(), 'AuthLDAP case test', 1, 'authldapcasetest@example.com');
        self::$user = \User::model()->findByPk($userId);
    }

    /**
     * Delete the test user and the plugin row if created by the test
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$user) {
            self::$user->delete();
        }
        if (self::$createdPluginRow) {
            self::$createdPluginRow->delete();
        }
        parent::tearDownAfterClass();
    }

    /**
     * Call a private method of the plugin
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    private function callPrivate($method, array $args)
    {
        $reflectionMethod = new \ReflectionMethod(self::$plugin, $method);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs(self::$plugin, $args);
    }

    /**
     * The existing user is found whatever the case of the given username
     */
    public function testUserIsFoundCaseInsensitive()
    {
        foreach (['AuthLdapCaseTest', 'authldapcasetest', 'AUTHLDAPCASETEST'] as $username) {
            $user = $this->callPrivate('getUserByNameCaseInsensitive', [$username]);
            $this->assertNotNull($user, "User not found with username $username");
            $this->assertSame(self::$user->uid, $user->uid);
        }
        $this->assertNull($this->callPrivate('getUserByNameCaseInsensitive', ['AuthLdapCaseTestOther']));
    }

    /**
     * No second user is created for a username differing only by case
     */
    public function testUserIsNotCreatedTwice()
    {
        $event = new \PluginEvent('newUserSession');
        $newUserId = $this->callPrivate('ldapCreateNewUser', [$event, 'authldapcasetest']);
        $this->assertNull($newUserId);
        $this->assertSame(\AuthLDAP::ERROR_ALREADY_EXISTING_USER, $event->get('errorCode'));
        $this->assertSame(1, (int) \User::model()->count('LOWER(users_name) = :name', [':name' => 'authldapcasetest']));
    }
}
