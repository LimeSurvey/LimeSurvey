<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlListParticipantsTest extends TestBaseClass
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    /**
     * Setup.
     *
     * @return void
     */
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

        // Clear login attempts.
        $dbo = \Yii::app()->getDb();
        $query = sprintf('DELETE FROM {{failed_login_attempts}}');
        $dbo->createCommand($query)->execute();
    }

    /**
     * Test so that validuntil works with exact equality.
     *
     * @return void
     */
    public function testConditionEquality()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

        /** @var CDbConnection */
        $dbo = \Yii::app()->getDb();

        /** @var string */
        $filename = self::$surveysFolder . '/survey_archive_265351_listParticipants.lsa';
        self::importSurvey($filename);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        /** @var array */
        $list = $handler->list_participants(
            $sessionKey,
            self::$surveyId,
            0,
            999,
            false,
            ['validuntil', 'validfrom'],
            ['validuntil' => '2020-04-01 15:12:00']
        );

        $expected = [
            [
                'tid' => "1",
                'token' => "c",
                'participant_info' => [
                    'firstname' => "a",
                    'lastname' => "b",
                    'email' => "a@a.a"
                ],
                'validuntil' => "2020-04-01 15:12:00",
                'validfrom' => "2020-03-18 15:12:00"
            ]
        ];

        $this->assertEquals($expected, $list);
    }

    /**
     * Test condition with empty return result.
     * 
     * @return 
     */
    public function testConditionEmptyResult()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

        /** @var CDbConnection */
        $dbo = \Yii::app()->getDb();

        /** @var string */
        $filename = self::$surveysFolder . '/survey_archive_265351_listParticipants.lsa';
        self::importSurvey($filename);

        // Create handler.
        $admin   = new \AdminController('dummyid');
        $handler = new \remotecontrol_handle($admin);

        // Get session key.
        $sessionKey = $handler->get_session_key(
            self::$username,
            self::$password
        );
        $this->assertNotEquals(['status' => 'Invalid user name or password'], $sessionKey);

        /** @var array */
        $list = $handler->list_participants(
            $sessionKey,
            self::$surveyId,
            0,
            999,
            false,
            ['validuntil', 'validfrom'],
            ['validuntil' => '']
        );

        $expected = [
            'status' => 'No survey participants found.'
        ];

        $this->assertEquals($expected, $list);
    }
}
