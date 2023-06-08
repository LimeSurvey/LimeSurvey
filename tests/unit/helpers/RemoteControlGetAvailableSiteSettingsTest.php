<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlGetAvailableSiteSettingsTest extends TestBaseClass
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();

        self::$username = getenv('ADMINUSERNAME');
        if (!self::$username) {
            self::$username = 'admin';
        }

        self::$password = getenv('PASSWORD');
        if (!self::$password) {
            self::$password = 'password';
        }
    }

    /**
     * Test the get_available_site_settings API call
     */
    public function testGetAvailableSiteSettings()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );

        $settings = $handler->get_available_site_settings($sessionKey);
        $this->assertNotEmpty($settings);
        $this->assertArrayHasKey('sitename', $settings);
        $this->assertArrayHasKey('defaultlang', $settings);
    }
}
