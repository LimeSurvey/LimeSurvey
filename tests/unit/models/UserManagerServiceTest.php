<?php

namespace ls\tests;

use LSWebUser;
use LimeSurvey\Models\Services\UserManager;

class UserManagerServiceTest extends \ls\tests\TestBaseClass
{
    /**
     * Testing that the canAssignPermissions function
     * returns false if no targe user is set.
     */
    public function testCanAssignPermissisonsFunctionWithNoTargetUser()
    {
        $managingUser = new LSWebUser();

        $userManager = new userManager($managingUser);

        $assignPermission = $userManager->canAssignPermissions();

        $this->assertFalse($assignPermission, 'The canAssignPermissions function needs a target user.');
    }

    /**
     * Testing that a user with superadmin / read permissions
     * can assign permissions to any user.
     */
    public function testSuperadminCanAssignPermissions()
    {
        // Creating managing user with superadmin / read permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com',
        );

        $managingUserPermissions = array(
            'superadmin' => array(
                'read'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canAssignPermissions();

        $this->assertTrue($assignPermission, 'A user with superadmin / read permissions should be able to assign permissions on any user.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user without superadmin / read permissions can't
     * assign permissions to a user.
     */
    public function testSuperadminCanNotAssignPermissions()
    {
        // Creating managing user with superadmin / import permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'superadmin' => array(
                'import'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canAssignPermissions();

        $this->assertFalse($assignPermission, 'A user without superadmin / read permissions should not be able to assign permissions on any user.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user with users / update permissions
     * can assign permissions to any of its child users.
     */
    public function testUserCanAssignPermissionsToChildUser()
    {
        // Creating user with users / update permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'users' => array(
                'update'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
            'parent_id'  => $userManagingUser->uid
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canAssignPermissions();

        $this->assertTrue($assignPermission, 'A user with users / update permissions should be able to assign permissions to any of its child users.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user with no users / update permissions
     * can not assign permissions to any of its child users.
     */
    public function testUserCanNotAssignPermissionsToChildUser()
    {
        // Creating user with users / update permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'users' => array(
                'read'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
            'parent_id'  => $userManagingUser->uid
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canAssignPermissions();

        $this->assertFalse($assignPermission, 'A user without users / update permissions should not be able to assign permissions to any of its child users.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user with users / update permissions
     * can not assign permissions to a user that is not its child.
     */
    public function testUserCanNotAssignPermissionsToAnyUser()
    {
        // Creating user with users / update permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'users' => array(
                'update'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canAssignPermissions();

        $this->assertFalse($assignPermission, 'A user with users / update permissions should not be able to assign permissions to a user that is not its child.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that the canAssignRole function
     * returns false if no targe user is set.
     */
    public function testCanAssignRoleFunctionWithNoTargetUser()
    {
        $managingUser = new LSWebUser();

        $userManager = new userManager($managingUser);

        $assignPermission = $userManager->canAssignRole();

        $this->assertFalse($assignPermission, 'The canAssignRole function needs a target user.');
    }

    /**
     * Testing that a user with superadmin / read permissions
     * can assign roles to any user.
     */
    public function testSuperadminCanAssignRole()
    {
        // Creating managing user with superadmin / read permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'superadmin' => array(
                'read'   => true,
                'create' => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canAssignRole();

        $this->assertTrue($assignPermission, 'A user with superadmin / create permissions should be able to assign roles to any user.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user without superadmin / read permissions can't
     * assign roles to a user.
     */
    public function testSuperadminCanNotAssignRoles()
    {
        // Creating managing user with superadmin / import permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'superadmin' => array(
                'read'   => true,
                'import'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canAssignRole();

        $this->assertFalse($assignPermission, 'A user without superadmin / create permissions should not be able to assign roles to any user.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that the canEdit function
     * returns false if no targe user is set.
     */
    public function testCanEditFunctionWithNoTargetUser()
    {
        $managingUser = new LSWebUser();

        $userManager = new userManager($managingUser);

        $assignPermission = $userManager->canEdit();

        $this->assertFalse($assignPermission, 'The canEdit function needs a target user.');
    }

    /**
     * Testing that a user with superadmin / read permissions
     * can edit any user.
     */
    public function testSuperadminCanEditUser()
    {
        // Creating managing user with superadmin / read permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'superadmin' => array(
                'read'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canEdit();

        $this->assertTrue($assignPermission, 'A user with superadmin / read permissions should be able to edit any user.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user without superadmin / read permissions can't
     * edit a user.
     */
    public function testSuperadminCanNotEditUser()
    {
        // Creating managing user with superadmin / import permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'superadmin' => array(
                'import'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canEdit();

        $this->assertFalse($assignPermission, 'A user without superadmin / read permissions should not be able edit any user.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user with users / update permissions
     * can edit any of its child users.
     */
    public function testUserCanEditChildUser()
    {
        // Creating user with users / update permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'users' => array(
                'update'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
            'parent_id'  => $userManagingUser->uid
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canEdit();

        $this->assertTrue($assignPermission, 'A user with users / update permissions should be able to assign permissions to any of its child users.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user with no users / update permissions
     * can not edit any of its child users.
     */
    public function testUserCanNotEditUser()
    {
        // Creating user with users / update permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'users' => array(
                'read'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
            'parent_id'  => $userManagingUser->uid
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canEdit();

        $this->assertFalse($assignPermission, 'A user without users / update permissions should not be able to edit any of its child users.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }

    /**
     * Testing that a user with users / update permissions
     * can not edit a user that is not its child.
     */
    public function testUserCanNotEditAnyUser()
    {
        // Creating user with users / update permissions.
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);

        $managingUserData = array(
            'users_name' => $managingUserName,
            'full_name'  => $managingUserName,
            'email'      => $managingUserName . '@example.com'
        );

        $managingUserPermissions = array(
            'users' => array(
                'update'   => true,
            )
        );

        $userManagingUser = self::createUserWithPermissions($managingUserData, $managingUserPermissions);
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);

        $userManager = new userManager($managingUser, $targetUser);

        $assignPermission = $userManager->canEdit();

        $this->assertFalse($assignPermission, 'A user with users / update permissions should not be able edit a user that is not its child.');

        //Delete managing user.
        $userManagingUser->delete();

        //Delete managing permissions.
        $managingUserCriteria = new \CDbCriteria();
        $managingUserCriteria->compare('uid', $userManagingUser->uid);
        \Permission::model()->deleteAll($managingUserCriteria);

        //Delete target user.
        $targetUser->delete();
    }
}
