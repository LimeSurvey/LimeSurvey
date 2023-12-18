<?php

namespace ls\tests;

class UserInPermissionRoleTest extends BaseModelTestCase
{
    protected $modelClassName = \UserInPermissionrole::class;

    /**
     * User with roles have superamdin permission
     */
    public function testSuperadminRoles()
    {
        // Create an user without any permission
        $newPassword = createPassword();
        $userName = \Yii::app()->securityManager->generateRandomString(28);
        $userId = \User::insertUser($userName, $newPassword, 'John Doe', 1, $userName . '@example.org');
        // User don't have any peermission
        $this->assertFalse(\Permission::model()->hasGlobalPermission('auth_db', 'read', $userId));
        $this->assertFalse(\Permission::model()->hasGlobalPermission('superadmin', 'read', $userId));

        // Create a role superamin read
        $newRoleName = \Yii::app()->securityManager->generateRandomString(12);
        // Check if exist ? Near 0 possibility
        $permissiontemplates = new \Permissiontemplates();
        $permissiontemplates->name = $newRoleName;
        $permissiontemplates->description = $newRoleName;
        $permissiontemplates->renewed_last = date("Y-m-d");
        $permissiontemplates->created_at = date("Y-m-d");
        $permissiontemplates->created_by = 1;
        $permissiontemplates->save();
        $newRoleId = $permissiontemplates->ptid;
        // set the user to this role
        $userInPermissionrole = new \UserInPermissionrole();
        $userInPermissionrole->ptid = $newRoleId;
        $userInPermissionrole->uid = $userId;
        $userInPermissionrole->save();

        // Set the superadmin view roles
        $permission = new \Permission();
        $permission->entity = 'role';
        $permission->entity_id = $newRoleId;
        $permission->uid = 0;
        $permission->permission = 'superadmin';
        $permission->read_p = 1;
        $permission->save();
        // User have all permission except create superadmin
        $this->assertTrue(\Permission::model()->hasGlobalPermission('auth_db', 'read', $userId));
        $this->assertTrue(\Permission::model()->hasGlobalPermission('superadmin', 'read', $userId));
        $this->assertFalse(\Permission::model()->hasGlobalPermission('superadmin', 'create', $userId));
        // Add superadmin create
        $permission->create_p = 1;
        $permission->save();
        // User have create superadmin
        $this->assertTrue(\Permission::model()->hasGlobalPermission('superadmin', 'create', $userId));
        App()->end();
        // Delete roles
        \Permissiontemplates::model()->deleteByPk($newRoleId);
        \Permission::model()->deleteAll(
            "entity = :entity and entity_id = :entity_id",
            [':entity' => 'role', ':entity_id' => $newRoleId]
        );
        \UserInPermissionrole::model()->deleteAll("ptid = :ptid", [':ptid' => $newRoleId]);

        //Delete user.
        $user = \User::model()->findByPk($userId);
        $user->delete();
    }
}
