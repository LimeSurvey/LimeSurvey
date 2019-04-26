<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 *
 * @since 2019-04-26
 */
class RemoteControlTest extends TestBaseClass
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    public static function setupBeforeClass()
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
     * Test the add_response API call.
     */
    public function testAddResponse()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle');

        // Import survey
        $filename = self::$surveysFolder . '/limesurvey_survey_666368.lss';
        self::importSurvey($filename);

        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );

        //var_dump($sessionKey);

        // Add response
        // Check result
        // Cleanup
    }
}
