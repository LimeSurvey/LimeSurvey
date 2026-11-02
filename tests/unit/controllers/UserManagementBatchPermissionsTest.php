<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;

/**
 * The mass edit of user permissions only changes the permissions that were changed in the dialog (#18313).
 */
class UserManagementBatchPermissionsTest extends TestBaseClass
{
    /** @var \User[] */
    private static $users = [];

    /**
     * Creates the test users.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.controllers.UserManagementController', true);
        \Yii::app()->session['loginID'] = 1;
        \Yii::app()->user->setId(1);
        foreach (['t18313a', 't18313b'] as $userName) {
            self::$users[$userName] = self::createUserWithPermissions(
                [
                    'users_name' => $userName,
                    'full_name' => $userName,
                    'email' => $userName . '@example.org',
                    'parent_id' => 1,
                ],
                [
                    'surveys' => ['create' => 'on', 'read' => 'on'],
                    'participantpanel' => ['read' => 'on'],
                    'users' => ['update' => 'on'],
                ]
            );
        }
        self::$users['t18313c'] = self::createUserWithPermissions([
            'users_name' => 't18313c',
            'full_name' => 't18313c',
            'email' => 't18313c@example.org',
            'parent_id' => self::$users['t18313a']->uid,
        ]);
    }

    /**
     * Deletes the test users and their permissions.
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        foreach (self::$users as $user) {
            \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
            $user->delete();
        }
        parent::tearDownAfterClass();
    }

    /**
     * Calls a not public method of the controller.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    private function callController(string $method, array $args)
    {
        $controller = new \UserManagementController('userManagement');
        $reflectionMethod = new \ReflectionMethod($controller, $method);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($controller, $args);
    }

    /**
     * Returns the stored global permission of a user.
     * @param int $userId
     * @param string $permission
     * @return \Permission|null
     */
    private function getPermission(int $userId, string $permission): ?\Permission
    {
        return \Permission::model()->findByAttributes([
            'uid' => $userId,
            'entity' => 'global',
            'entity_id' => 0,
            'permission' => $permission,
        ]);
    }

    /**
     * Only the given permissions are changed, all other stored permissions stay and no other rows are created.
     * @return void
     */
    public function testApplyOnlyGivenPermissions()
    {
        $userId = (int) self::$users['t18313a']->uid;
        $this->callController('applyPermissionFromArray', [
            $userId,
            [
                'surveys' => ['create' => '0'],
                'templates' => ['read' => '1'],
            ],
            true
        ]);

        $surveys = $this->getPermission($userId, 'surveys');
        $this->assertSame(0, (int) $surveys->create_p);
        $this->assertSame(1, (int) $surveys->read_p);
        $this->assertSame(1, (int) $this->getPermission($userId, 'participantpanel')->read_p);
        $templates = $this->getPermission($userId, 'templates');
        $this->assertSame(1, (int) $templates->read_p);
        $this->assertSame(0, (int) $templates->update_p);
        $this->assertNull($this->getPermission($userId, 'labelsets'));
    }

    /**
     * The users whose permissions can be changed never include the current user or not existing users.
     * @return void
     */
    public function testPermissionAssignableUserIds()
    {
        $userIds = [(int) self::$users['t18313a']->uid, (int) self::$users['t18313b']->uid];
        $assignableUserIds = $this->callController(
            'getPermissionAssignableUserIds',
            [array_merge([1, 99999999], $userIds)]
        );
        sort($assignableUserIds);
        $this->assertSame($userIds, $assignableUserIds);
    }

    /**
     * An admin without superadmin permission can only change the permissions of the users they created.
     * @return void
     */
    public function testPermissionAssignableUserIdsOfNotSuperadmin()
    {
        $userId = (int) self::$users['t18313a']->uid;
        \Yii::app()->session['loginID'] = $userId;
        \Yii::app()->user->setId($userId);
        try {
            $assignableUserIds = $this->callController(
                'getPermissionAssignableUserIds',
                [[$userId, (int) self::$users['t18313b']->uid, (int) self::$users['t18313c']->uid]]
            );
        } finally {
            \Yii::app()->session['loginID'] = 1;
            \Yii::app()->user->setId(1);
        }
        $this->assertSame([(int) self::$users['t18313c']->uid], $assignableUserIds);
    }
}
