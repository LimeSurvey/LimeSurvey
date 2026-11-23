<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;

/**
 * The "Add to user group" mass action in user management only allows groups the user owns,
 * unless the user is a superadmin.
 */
class UserManagementBatchAddGroupTest extends TestBaseClass
{
    /** @var \User[] */
    private static $users = [];

    /** @var \UserGroup[] */
    private static $groups = [];

    /**
     * Creates the test users and user groups.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.controllers.UserManagementController', true);
        \Yii::app()->session['loginID'] = 1;
        \Yii::app()->user->setId(1);
        self::$users['admin'] = self::createUserWithPermissions(
            [
                'users_name' => 'tbatchgroupadmin',
                'full_name' => 'tbatchgroupadmin',
                'email' => 'tbatchgroupadmin@example.org',
                'parent_id' => 1,
            ],
            [
                'users' => ['read' => 'on', 'update' => 'on'],
                'usergroups' => ['read' => 'on', 'update' => 'on'],
            ]
        );
        self::$users['member'] = self::createUserWithPermissions([
            'users_name' => 'tbatchgroupmember',
            'full_name' => 'tbatchgroupmember',
            'email' => 'tbatchgroupmember@example.org',
            'parent_id' => 1,
        ]);
        $owners = [
            'tbatchgroupown' => (int) self::$users['admin']->uid,
            'tbatchgroupother' => 1,
        ];
        foreach ($owners as $groupName => $ownerId) {
            // A leftover group of an aborted run would fail the unique rule
            \UserGroup::model()->deleteAllByAttributes(['name' => $groupName]);
            $group = new \UserGroup();
            $group->name = $groupName;
            $group->description = '';
            $group->owner_id = $ownerId;
            if (!$group->save()) {
                throw new \Exception('The test user group ' . $groupName . ' could not be saved.');
            }
            self::$groups[$groupName] = $group;
        }
    }

    /**
     * Deletes the test users, their permissions and the test user groups.
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        \Yii::app()->session['loginID'] = 1;
        \Yii::app()->user->setId(1);
        foreach (self::$groups as $group) {
            $group->delete();
        }
        foreach (self::$users as $user) {
            \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
            $user->delete();
        }
        parent::tearDownAfterClass();
    }

    /**
     * Runs the mass action as the test admin and returns the HTTP status code of a thrown exception (null if none).
     * @param \UserGroup $group
     * @return int|null
     */
    private function runBatchAddGroup(\UserGroup $group): ?int
    {
        $userId = (int) self::$users['admin']->uid;
        \Yii::app()->session['loginID'] = $userId;
        \Yii::app()->user->setId($userId);
        $_POST['sItems'] = json_encode([(int) self::$users['member']->uid]);
        $_POST['addtousergroup'] = $group->ugid;
        $controller = new \UserManagementController('userManagement');
        $originalController = \Yii::app()->getController();
        \Yii::app()->setController($controller);
        ob_start();
        try {
            $controller->actionBatchAddGroup();
            return null;
        } catch (\CHttpException $exception) {
            return $exception->statusCode;
        } finally {
            ob_end_clean();
            \Yii::app()->setController($originalController);
            unset($_POST['sItems'], $_POST['addtousergroup']);
            \Yii::app()->session['loginID'] = 1;
            \Yii::app()->user->setId(1);
        }
    }

    /**
     * An admin without superadmin permission can add users to a group they own.
     * @return void
     */
    public function testOwnerCanAddUsers()
    {
        $group = self::$groups['tbatchgroupown'];
        $this->assertNull($this->runBatchAddGroup($group));
        $this->assertTrue($group->hasUser(self::$users['member']->uid));
    }

    /**
     * An admin without superadmin permission can not add users to a group of another user,
     * even with the global user and user group update permissions.
     * @return void
     */
    public function testNotOwnerCanNotAddUsers()
    {
        $group = self::$groups['tbatchgroupother'];
        $this->assertSame(403, $this->runBatchAddGroup($group));
        $this->assertFalse($group->hasUser(self::$users['member']->uid));
    }
}
