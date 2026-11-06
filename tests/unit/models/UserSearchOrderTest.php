<?php

namespace ls\tests;

/**
 * The user management list and the user group member list are sorted by user ID by default.
 * Without an ORDER BY, PostgreSQL returns an updated row after the others, so the lists were
 * shown in a changing order and paging could show a user twice and skip another one.
 */
class UserSearchOrderTest extends TestBaseClass
{
    /** @var string Prefix of the user names created by this test */
    private const USERNAME_PREFIX = 'searchordertest';

    /** @var int[] IDs of the created users, ascending */
    private static $userIds = [];

    /** @var \UserGroup */
    private static $userGroup;

    /**
     * Create three users in a user group, then update the first one, so PostgreSQL returns it last
     * when the query has no ORDER BY.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::app()->session['loginID'] = 1;

        self::$userGroup = new \UserGroup();
        self::$userGroup->name = self::USERNAME_PREFIX;
        self::$userGroup->description = '';
        self::$userGroup->owner_id = 1;
        self::assertTrue(self::$userGroup->save());

        for ($index = 1; $index <= 3; $index++) {
            $user = self::createUserWithPermissions([
                'users_name' => self::USERNAME_PREFIX . $index,
                'full_name' => self::USERNAME_PREFIX . $index,
                'email' => self::USERNAME_PREFIX . $index . '@example.org',
                'password' => 'password',
            ]);
            self::$userIds[] = (int) $user->uid;
            self::assertTrue(self::$userGroup->addUser($user->uid));
        }

        \User::model()->updateByPk(self::$userIds[0], ['full_name' => self::USERNAME_PREFIX . ' updated']);
    }

    /**
     * Delete the created users and the user group
     */
    public static function tearDownAfterClass(): void
    {
        \UserInGroup::model()->deleteAllByAttributes(['ugid' => self::$userGroup->ugid]);
        self::$userGroup->delete();
        foreach (self::$userIds as $userId) {
            \User::model()->deleteByPk($userId);
        }
        parent::tearDownAfterClass();
    }

    /**
     * The user management list is sorted by user ID
     */
    public function testUserListIsSortedByUserId()
    {
        $model = new \User('search');
        $model->users_name = self::USERNAME_PREFIX;
        $userIds = array_map(
            function ($user) {
                return (int) $user->uid;
            },
            $model->search()->getData()
        );

        $this->assertSame(self::$userIds, $userIds);
    }

    /**
     * The user group member list is sorted by user ID
     */
    public function testUserGroupMemberListIsSortedByUserId()
    {
        $model = new \User('search');
        $userIds = array_map(
            function ($user) {
                return (int) $user->uid;
            },
            $model->searchUserGroupMembers(self::$userGroup->ugid)->getData()
        );

        $this->assertSame(self::$userIds, $userIds);
    }
}
