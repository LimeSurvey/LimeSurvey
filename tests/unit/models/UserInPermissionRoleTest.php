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

        /* Create different user due to usage of static in Permission model */
        $userName = \Yii::app()->securityManager->generateRandomString(28);
        $userIdNoRoles = \User::insertUser($userName, $newPassword, 'John Doe', 1, $userName . '@example.org');
        $userName = \Yii::app()->securityManager->generateRandomString(28);
        $userIdSuperadminView = \User::insertUser($userName, $newPassword, 'John Doe', 1, $userName . '@example.org');
        $userName = \Yii::app()->securityManager->generateRandomString(28);
        $userIdSuperadminCreate = \User::insertUser($userName, $newPassword, 'John Doe', 1, $userName . '@example.org');

        // User don't have any peermission
        $this->assertFalse(\Permission::model()->hasGlobalPermission('auth_db', 'read', $userIdNoRoles));
        $this->assertFalse(\Permission::model()->hasGlobalPermission('superadmin', 'read', $userIdNoRoles));

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

        // Set the superadmin view roles
        $permission = new \Permission();
        $permission->entity = 'role';
        $permission->entity_id = $newRoleId;
        $permission->uid = 0;
        $permission->permission = 'superadmin';
        $permission->read_p = 1;
        $permission->save();
        $userInPermissionrole = new \UserInPermissionrole();
        $userInPermissionrole->ptid = $newRoleId;
        $userInPermissionrole->uid = $userIdSuperadminView;
        $userInPermissionrole->save();
        // User have all permission except create superadmin
        $this->assertTrue(\Permission::model()->hasGlobalPermission('auth_db', 'read', $userIdSuperadminView));
        $this->assertTrue(\Permission::model()->hasGlobalPermission('superadmin', 'read', $userIdSuperadminView));
        $this->assertFalse(\Permission::model()->hasGlobalPermission('superadmin', 'create', $userIdSuperadminView));

        // Add superadmin create
        $permission->create_p = 1;
        $permission->save();
        $userInPermissionrole = new \UserInPermissionrole();
        $userInPermissionrole->ptid = $newRoleId;
        $userInPermissionrole->uid = $userIdSuperadminCreate;
        $userInPermissionrole->save();
        // User have create superadmin
        $this->assertTrue(\Permission::model()->hasGlobalPermission('auth_db', 'read', $userIdSuperadminView));
        $this->assertTrue(\Permission::model()->hasGlobalPermission('superadmin', 'read', $userIdSuperadminView));
        $this->assertTrue(\Permission::model()->hasGlobalPermission('superadmin', 'create', $userIdSuperadminCreate));

        // Delete role
        \Permissiontemplates::model()->deleteByPk($newRoleId);
        \Permission::model()->deleteAll(
            "entity = :entity and entity_id = :entity_id",
            [':entity' => 'role', ':entity_id' => $newRoleId]
        );
        \UserInPermissionrole::model()->deleteAll("ptid = :ptid", [':ptid' => $newRoleId]);

        //Delete users
        \User::model()->deleteByPk($userIdNoRoles);
        \User::model()->deleteByPk($userIdSuperadminView);
        \User::model()->deleteByPk($userIdSuperadminCreate);
    }
}
