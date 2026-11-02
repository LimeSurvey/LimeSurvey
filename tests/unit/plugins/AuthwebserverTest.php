<?php

namespace ls\tests;

/**
 * Tests for the Authwebserver core plugin: the web server identity is re-checked before every action.
 */
class AuthwebserverTest extends TestBaseClass
{
    /** @var \Authwebserver */
    private static $plugin;

    /** @var \Plugin Plugin row, created by the test when missing */
    private static $pluginRow;

    /** @var bool Whether the plugin row was created by the test and must be deleted */
    private static $createdPluginRow = false;

    /** @var \User The user whose session is checked */
    private static $user;

    /** @var \User Another user allowed to use web server authentication */
    private static $otherUser;

    /** @var string|null Value of REMOTE_USER before the test */
    private static $originalRemoteUser;

    /**
     * Load the plugin (without activating it), point it to REMOTE_USER and create two users
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$originalRemoteUser = $_SERVER['REMOTE_USER'] ?? null;

        self::$pluginRow = \Plugin::model()->findByAttributes(['name' => 'Authwebserver']);
        if (!self::$pluginRow) {
            self::$pluginRow = new \Plugin();
            self::$pluginRow->name = 'Authwebserver';
            self::$pluginRow->active = 0;
            self::$pluginRow->save();
            self::$createdPluginRow = true;
        }
        self::$plugin = App()->getPluginManager()->loadPlugin('Authwebserver', self::$pluginRow->id);
        self::$plugin->saveSettings(['serverkey' => 'REMOTE_USER', 'strip_domain' => '0']);

        self::$user = self::createWebserverUser('AuthwebserverTestUser');
        self::$otherUser = self::createWebserverUser('AuthwebserverTestOther');
    }

    /**
     * Delete the users, the plugin settings and the plugin row if created by the test
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$originalRemoteUser === null) {
            unset($_SERVER['REMOTE_USER']);
        } else {
            $_SERVER['REMOTE_USER'] = self::$originalRemoteUser;
        }
        foreach ([self::$user, self::$otherUser] as $user) {
            if ($user) {
                \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
                $user->delete();
            }
        }
        \PluginSetting::model()->deleteAllByAttributes(['plugin_id' => self::$pluginRow->id]);
        if (self::$createdPluginRow) {
            self::$pluginRow->delete();
        }
        parent::tearDownAfterClass();
    }

    /**
     * Create a user allowed to log in through the plugin
     *
     * @param string $userName
     * @return \User
     */
    private static function createWebserverUser($userName)
    {
        $userId = \User::insertUser($userName, createPassword(), $userName, 1, $userName . '@example.com');
        \Permission::model()->setGlobalPermission($userId, 'auth_webserver');
        return \User::model()->findByPk($userId);
    }

    /**
     * Open a session for the user as if logged in through the given plugin
     *
     * @param \User $user
     * @param string $loginPlugin
     * @return void
     */
    private function logInAs(\User $user, $loginPlugin = 'Authwebserver')
    {
        // A logout destroys the session, make sure there is one again
        App()->session->open();
        App()->user->setId($user->uid);
        App()->session['user'] = $user->users_name;
        App()->user->setState('plugin', $loginPlugin);
    }

    /**
     * Hand the beforeControllerAction event to the plugin like the plugin manager does
     *
     * Other loaded plugins need a controller for this event, so it is not dispatched through the manager.
     *
     * @return \PluginEvent
     */
    private function dispatchBeforeControllerAction()
    {
        $event = new \PluginEvent('beforeControllerAction');
        $event->set('controller', 'surveyAdministration');
        $event->set('action', 'listsurveys');
        self::$plugin->setEvent($event);
        self::$plugin->beforeControllerAction();
        return $event;
    }

    /**
     * The session stays when the web server still names the logged in user
     */
    public function testSameUserKeepsSession()
    {
        $this->logInAs(self::$user);
        $_SERVER['REMOTE_USER'] = 'AuthwebserverTestUser';
        $this->assertFalse(self::$plugin->hasWebserverUserChanged());
        $event = $this->dispatchBeforeControllerAction();
        $this->assertFalse(App()->user->isGuest);
        $this->assertTrue($event->get('run', true));
    }

    /**
     * Nothing happens when the web server passes no username
     */
    public function testMissingServerUserKeepsSession()
    {
        $this->logInAs(self::$user);
        unset($_SERVER['REMOTE_USER']);
        $this->assertFalse(self::$plugin->hasWebserverUserChanged());
        $this->dispatchBeforeControllerAction();
        $this->assertFalse(App()->user->isGuest);
    }

    /**
     * Sessions opened through another authentication plugin are not checked
     */
    public function testOtherLoginPluginIsIgnored()
    {
        $this->logInAs(self::$user, 'Authdb');
        $_SERVER['REMOTE_USER'] = 'AuthwebserverTestOther';
        $this->assertFalse(self::$plugin->hasWebserverUserChanged());
        $this->dispatchBeforeControllerAction();
        $this->assertFalse(App()->user->isGuest);
    }

    /**
     * The domain part is stripped before comparing when the setting is on
     */
    public function testStripDomainIsApplied()
    {
        $this->logInAs(self::$user);
        self::$plugin->saveSettings(['strip_domain' => '1']);
        $_SERVER['REMOTE_USER'] = 'EXAMPLE\\AuthwebserverTestUser';
        $this->assertFalse(self::$plugin->hasWebserverUserChanged());
        $_SERVER['REMOTE_USER'] = 'AuthwebserverTestUser@example.com';
        $this->assertFalse(self::$plugin->hasWebserverUserChanged());
        self::$plugin->saveSettings(['strip_domain' => '0']);
        $_SERVER['REMOTE_USER'] = 'EXAMPLE\\AuthwebserverTestUser';
        $this->assertTrue(self::$plugin->hasWebserverUserChanged());
    }

    /**
     * The user is logged out and the action is not run when the web server names somebody else
     */
    public function testOtherUserIsLoggedOut()
    {
        $this->logInAs(self::$user);
        $_SERVER['REMOTE_USER'] = 'AuthwebserverTestOther';
        $this->assertTrue(self::$plugin->hasWebserverUserChanged());
        $event = $this->dispatchBeforeControllerAction();
        $this->assertTrue(App()->user->isGuest);
        $this->assertFalse($event->get('run', true));
    }

    /**
     * An unknown web server user ends the session as well
     */
    public function testUnknownUserIsLoggedOut()
    {
        $this->logInAs(self::$user);
        $_SERVER['REMOTE_USER'] = 'AuthwebserverTestNobody';
        $this->assertTrue(self::$plugin->hasWebserverUserChanged());
        $this->dispatchBeforeControllerAction();
        $this->assertTrue(App()->user->isGuest);
    }
}
