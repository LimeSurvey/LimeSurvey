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

        // Clear login attempts.
        $dbo = \Yii::app()->getDb();
        $query = sprintf('DELETE FROM {{failed_login_attempts}}');
        $dbo->createCommand($query)->execute();

        /** @var string */
        $filename = self::$surveysFolder . '/survey_archive_265351_listParticipants.lsa';
        self::importSurvey($filename);

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

        // for MSSQL Server
        if (in_array(App()->db->driverName, ['mssql','sqlsrv'])) {
            $list[0]['validuntil'] = preg_replace('/\.000$/', '', $list[0]['validuntil']);
            $list[0]['validfrom'] = preg_replace('/\.000$/', '', $list[0]['validfrom']);
        }

        $this->assertEquals($expected, $list);
    }



    /**
     * Test so that validuntil works with IN operator.
     *
     * @return void
     */
    public function testConditionIn()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

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
            [],
            ['tid' => ["IN","1","2"]]
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
            ],
            [
                'tid' => "2",
                'token' => "e",
                'participant_info' => [
                    'firstname' => "q",
                    'lastname' => "w",
                    'email' => "q@q.com"
                ],
            ]

        ];

        $this->assertEquals($expected, $list);
    }


    /**
     * Test condition with empty return result.
     * 
     * @return void
     */
    public function testConditionEmptyResult()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

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
            ['validuntil' => null]
        );

        $expected = [
            'status' => 'No survey participants found.'
        ];

        $this->assertEquals($expected, $list);
    }

    /**
     * Test illegal operator '!'.
     *
     * @return void
     */
    public function testConditionIllegalOperator()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

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
            ['validuntil' => ['!', '2019']]
        );

        $expected = [
            'status' => 'Illegal operator: ! for column validuntil'
        ];

        $this->assertEquals($expected, $list);
    }

    /**
     * Test invalid columns 'extractvalue(1,concat(0x3a,(DATABASE())))'.
     *
     * @return void
     */
    public function testConditionInvalidColumn()
    {
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

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
            10,
            false,
            false,
            ['extractvalue(1,concat(0x3a,(DATABASE())))' => ['=', 1]]
        );

        $expected = [
            'status' => 'Invalid column name: extractvalue(1,concat(0x3a,(DATABASE())))'
        ];

        $this->assertEquals($expected, $list);
    }

    /**
     * Test higher-than operator, '>'.
     *
     * @return void
     */
    public function testConditionHigherThan()
    {
        //$oTokens = Token::model(self::$surveyId)
            //->findAllByAttributes($aAttributeValues, $oCriteria);


        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
        \Yii::import('application.helpers.viewHelper', true);
        \Yii::import('application.libraries.BigData', true);

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
            ['validuntil' => ['>', '2020-04-01 15:12:00']]
        );

        /** @var array */
        $expected = [
            'status' => 'No survey participants found.'
        ];

        $this->assertEquals($expected, $list);

        // Same as above but with >=.

        /** @var array */
        $list = $handler->list_participants(
            $sessionKey,
            self::$surveyId,
            0,
            999,
            false,
            ['validuntil', 'validfrom'],
            ['validuntil' => ['>=', '2020-04-01 15:12:00']]
        );

        /** @var array */
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

        // for MSSQL Server
        if (in_array(App()->db->driverName, ['mssql','sqlsrv'])) {
            $list[0]['validuntil'] = preg_replace('/\.000$/', '', $list[0]['validuntil']);
            $list[0]['validfrom'] = preg_replace('/\.000$/', '', $list[0]['validfrom']);
        }

        $this->assertEquals($expected, $list);

        // As above but with future date.

        /** @var array */
        $list = $handler->list_participants(
            $sessionKey,
            self::$surveyId,
            0,
            999,
            false,
            ['validuntil', 'validfrom'],
            ['validuntil' => ['>=', '2021-04-01 15:12:00']]
        );

        /** @var array */
        $expected = [
            'status' => 'No survey participants found.'
        ];

        $this->assertEquals($expected, $list);

        /** @var array */
        $list = $handler->list_participants(
            $sessionKey,
            self::$surveyId,
            0,
            999,
            false,
            ['validuntil', 'validfrom'],
            ['email' => ['LIKE', 'com']]
        );

        // Got exactly one participant with "com" in email.
        $this->assertCount(1, $list);
        $this->assertEquals('q@q.com', $list[0]['participant_info']['email']);
    }
}
