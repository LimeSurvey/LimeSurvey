<?php

namespace ls\tests\unit\helpers\remotecontrol;

use ls\tests\TestBaseClass;

/**
 * Tests for the list_surveys remote API function.
 */
class RemoteControlListSurveysTest extends TestBaseClass
{
    /** @var string */
    private const USERNAME = 'rclistsurveysuser';

    /** @var string */
    private const PASSWORD = 'rclistsurveysuser';

    /** @var \remotecontrol_handle */
    protected static $handler;

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

        self::createUserWithPermissions(
            [
                'users_name' => self::USERNAME,
                'full_name' => self::USERNAME,
                'email' => self::USERNAME . '@example.com',
                'lang' => 'auto',
                'password' => self::PASSWORD,
            ],
            ['auth_db' => ['read' => 'on']]
        );
        self::$handler = new \remotecontrol_handle(new \AdminController('dummyid'));
    }

    /**
     * An unknown username gets the same answer as an existing user
     * without surveys, so it does not reveal which usernames exist.
     */
    public function testUnknownUsernameGivesSameAnswerAsUserWithoutSurveys()
    {
        $sessionKey = self::$handler->get_session_key(self::USERNAME, self::PASSWORD);
        $this->assertIsString($sessionKey);

        $noSurveys = array('status' => 'No surveys found', 'error_code' => \remotecontrol_handle::ERR_NO_DATA);
        $this->assertEquals($noSurveys, self::$handler->list_surveys($sessionKey, 'admin'));
        $this->assertEquals($noSurveys, self::$handler->list_surveys($sessionKey, 'rcnonexistinguser'));
    }

    public static function tearDownAfterClass(): void
    {
        \User::model()->deleteAllByAttributes(['users_name' => self::USERNAME]);
        parent::tearDownAfterClass();
    }
}
