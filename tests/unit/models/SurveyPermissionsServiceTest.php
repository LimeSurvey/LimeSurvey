<?php

namespace ls\tests;

use LimeSurvey\Models\Services\SurveyPermissions;

class SurveyPermissionsServiceTest extends \ls\tests\TestBaseClass
{
    public static $userIds = [];
    public static $userGroupId = null;

    public static function createSomeTestUsers()
    {
        //add 3 users for group
        $oUser = self::createUserWithPermissions(
            [
                "users_name" => "user1group",
                "full_name" => "user1group",
                "email" => "user1group@example.com",
                "lang" => "auto",
                "password" => "user1group"
            ],
            [
                'auth_db' => [
                    'read' => 'on'
                ],
            ]
        );
        self::$userIds[] = $oUser->uid;
        $oUser = self::createUserWithPermissions(
            [
                "users_name" => "user2group",
                "full_name" => "user2group",
                "email" => "user2group@example.com",
                "lang" => "auto",
                "password" => "user2group"
            ],
            [
                'auth_db' => [
                    'read' => 'on'
                ],
            ]
        );
        self::$userIds[] = $oUser->uid;
        $oUser = self::createUserWithPermissions(
            [
                "users_name" => "user3group",
                "full_name" => "user3group",
                "email" => "user3group@example.com",
                "lang" => "auto",
                "password" => "user3group"
            ],
            [
                'auth_db' => [
                    'read' => 'on'
                ],
            ]
        );
        self::$userIds[] = $oUser->uid;
        //add 1 user with normal permissions
        $oUser = self::createUserWithPermissions(
            [
                "users_name" => "normaluser1",
                "full_name" => "normaluser1",
                "email" => "normal1@example.com",
                "lang" => "auto",
                "password" => "normaluser1"
            ],
            [
                'surveys' => [
                    'read' => 'on'
                ],
            ]
        );
        self::$userIds[] = $oUser->uid;
        //add 1 user with global survey permissions
        $oUser = self::createUserWithPermissions(
            [
                "users_name" => "userGlobalSurvey",
                "full_name" => "userGlobalSurvey",
                "email" => "userGlobalSurvey@example.com",
                "lang" => "auto",
                "password" => "userGlobalSurvey"
            ],
            [
                'surveys' => [
                    'read' => 'on'
                ],
            ]
        );
        self::$userIds[] = $oUser->uid;
    }

    /**
     * Create a user group and add 3 users too it.
     *
     * @return bool true if group could be saved
     */
    private static function createTestUserGroup()
    {
        //add a testusergroup
        $result = false;
        $userGroup = new \UserGroup();
        $userGroup->name = 'TestUserGroup';
        $userGroup->description = 'some nice description';
        $userGroup->owner_id = 1; //admin owns this group

        if ($userGroup->save()) {
            self::$userGroupId = $userGroup->ugid;
            //add 3 users into group
            //only add users if they are already inserted ...
            if (self::$userIds !== null && count(self::$userIds) > 2) {
                for ($cnt = 0; $cnt < 3; $cnt++) {
                    $userInGroup = new \UserInGroup();
                    $userInGroup->ugid = self::$userGroupId;
                    $userInGroup->uid = self::$userIds[$cnt];
                    $userInGroup->save();
                }
                $result = true;
            }
        }
        return $result;
    }

    public static function deleteUsers()
    {
        foreach (self::$userIds as $uid) {
            \User::model()->findByPk($uid)->delete();
        }
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_268886_testSurveyPermissions.lss';
        self::importSurvey($surveyFile);
        //add 5 Test users with different permissions
        self::createSomeTestUsers();
        if (!self::createTestUserGroup()) {
            throw new \Exception(
                "Could not create group: "
            );
        }
    }

    /**
     * @return void
     */
    public function testUnknownUser()
    {
        $userId = 500;
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);

        self::assertFalse($oSurveyPermissions->addUserToSurveyPermission($userId));
    }

    /**
     * Test how many users are there to add to survey permissions.
     *
     * @return void
     */
    public function testCntValidUsers()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);
        self::assertEquals(5, count($oSurveyPermissions->getSurveyUserList()));
    }

    /**
     * Add a normal user to survey permission (not survey owner)
     *
     * @return void
     */
    public function testAddUserToSurveyPermission()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);

        self::assertTrue($oSurveyPermissions->addUserToSurveyPermission(self::$userIds[0]));
    }

    /**
     * Test how many users are there to add to survey permissions.
     * (** test again after one possible user has already been inserted for survey permissions)
     *
     * @return void
     */
    public function testCntValidUsersNotAll()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);
        self::assertEquals(4, count($oSurveyPermissions->getSurveyUserList()));
    }

    /**
     * Test valid user groups (how many user groups could still be inserted for
     * survey permissions)
     *
     * @return void
     */
    public function testCntValidUserGroups()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);
        self::assertEquals(1, count($oSurveyPermissions->getSurveyUserGroupList()));
    }

    /**
     * Test add user group. One user from group has already been inserted.
     *
     * @return void
     */
    public function testAddUserGroupToSurveyPermission()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);

        $twoUsersAdded = $oSurveyPermissions->addUserGroupToSurveyPermissions((int)self::$userGroupId);
        self::assertEquals(2, $twoUsersAdded);
    }

    /**
     * Test no valid user groups (how many user groups could still be inserted for
     * survey permissions)
     *
     * @return void
     */
    public function testNoValidUserGroups()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);
        self::assertEquals(0, count($oSurveyPermissions->getSurveyUserGroupList()));
    }

    public function testSaveUserPermissions()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);
        $permissions = [
            'statistics' => [
                'create' => 1,
                'update' => 1,
                'delete' => 0,
            ],
            'assessments' => [
                'import' => 1,
                'export' => 0,
            ],
            'quotas' => [
                'import' => 1,
                'export' => 1,
            ],
            'surveyactivation' => [
                'read' => 1,
            ],
        ];
        self::assertTrue($oSurveyPermissions->saveUserPermissions(self::$userIds[0], $permissions));
    }

    public function testSaveUserGroupPermissions()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);
        $permissions = [
            'tokens' => [
                'create' => 1,
                'read' => 1,
                'update' => 0,
            ],
        ];
        self::assertTrue($oSurveyPermissions->saveUserGroupPermissions((int)self::$userGroupId, $permissions));
    }

    public function testDeleteUsersSurveyPermissions()
    {
        $oSurveyPermissions = new SurveyPermissions(self::$testSurvey, true);

        self::assertEquals(0, $oSurveyPermissions->deleteUserPermissions(self::$userIds[4]));
        self::assertLessThan($oSurveyPermissions->deleteUserPermissions(self::$userIds[0]), 0);
    }

    public static function tearDownAfterClass(): void
    {
        //delete users from group
        \UserInGroup::model()->deleteAll();
        //delete group
        \UserGroup::model()->findByPk(self::$userGroupId)->delete();
        //delete users
        self::deleteUsers();

        parent::tearDownAfterClass();
    }
}
