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
     * Testing that a user with superadmin / read but without superadmin / create permissions
     * can assign roles to a user, but only the roles without superadmin permissions.
     */
    public function testSuperadminWithoutCreateCanAssignRolesWithoutSuperadminPermission()
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

        $this->assertTrue($assignPermission, 'A user with superadmin / read permissions should be able to assign roles to any user.');

        $superadminRole = self::createRole(['superadmin' => ['read' => 1]]);
        $surveysRole = self::createRole(['surveys' => ['create' => 1, 'read' => 1]]);
        $this->assertFalse($userManager->isRoleAssignable($superadminRole), 'A user without superadmin / create permissions should not be able to assign a role with superadmin permission.');
        $this->assertTrue($userManager->isRoleAssignable($surveysRole), 'A user with superadmin / read permissions should be able to assign a role without superadmin permission.');
        self::deleteRole($superadminRole);
        self::deleteRole($surveysRole);

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

    /**
     * Test deleting a user.
     */
    public function testSuccessfullyDeleteUser()
    {
        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);
        $userId = $targetUser->uid;

        // Checking that user exists.
        $existingUser = \User::model()->findByPk($userId);
        $this->assertThat($existingUser, $this->isInstanceOf('\User'), 'Unexpected. A user should have been found.');

        $userManager = new userManager();
        $operationResult = $userManager->deleteUser($existingUser);
        $message = $operationResult->getMessages()[0];

        // Checking that user no longer exists.
        $existingUser = \User::model()->findByPk($userId);
        $this->assertNull($existingUser, 'User should have been deleted.');

        // Checking messages.
        $this->assertTrue($operationResult->isSuccess(), 'The operation should have been successful.');
        $this->assertSame('success', $message->getType(), 'Unexpected message type for a successful operation.');
        $this->assertSame(gT('User successfully deleted.'), $message->getMessage(), 'Unexpected message for a successful operation.');
    }

    /**
     * Test deleting a user and its groups.
     */
    public function testDeleteUserAndGroups()
    {
        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);
        $userId = $targetUser->uid;

        // Checking that user exists.
        $existingUser = \User::model()->findByPk($userId);
        $this->assertThat($existingUser, $this->isInstanceOf('\User'), 'Unexpected. A user should have been found.');

        // Creating user group.
        $originalLoginId = \Yii::app()->session['loginID'] ?? null;
        try {
            \Yii::app()->session['loginID'] = $userId;
            $groupId = \UserGroup::model()->addGroup('Test', 'A test user group.');
        } finally {
            if ($originalLoginId === null) {
                unset(\Yii::app()->session['loginID']);
            } else {
                \Yii::app()->session['loginID'] = $originalLoginId;
            }
        }

        // Checking that a group for this user exists.
        $existingGroup = \UserGroup::model()->findByAttributes(array('owner_id' => $userId));
        $this->assertThat($existingGroup, $this->isInstanceOf('\UserGroup'), 'Unexpected. A user group should have been found.');

        $userManager = new userManager();
        $operationResult = $userManager->deleteUser($existingUser);
        $messages = $operationResult->getMessages();

        // Checking that user no longer exists.
        $existingUser = \User::model()->findByPk($userId);
        $this->assertNull($existingUser, 'User should have been deleted.');

        // Checking that the group exists but no longer belongs to the deleted user.
        $existingGroupAfterDeletion = \UserGroup::model()->findByPk($groupId);
        $this->assertThat($existingGroupAfterDeletion, $this->isInstanceOf('\UserGroup'), 'Unexpected. A user group should have been found.');
        $this->assertNotSame($existingGroupAfterDeletion->owner_id, $userId, 'The group should not belong to the deleted user anymore.');

        // Checking messages.
        $siteAdminName = \User::model()->findByPk(1)->users_name;

        $this->assertIsArray($messages, 'The operation result messages should be in an array.');
        $this->assertCount(2, $messages, 'There should be two messages.');
        $this->assertThat($messages, $this->containsOnlyInstancesOf('LimeSurvey\Datavalueobjects\TypedMessage'));
        $this->assertSame($messages[0]->getMessage(), sprintf(gT("All of the user's user groups were transferred to %s."), $siteAdminName));

        // Deleting group.
        $existingGroupAfterDeletion->delete();
    }

    /**
     * Test deleting a user and its participants.
     */
    public function testDeleteUserAndParticipants()
    {
        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);
        $userId = $targetUser->uid;

        // Creating participant.
        $participantName = \Yii::app()->securityManager->generateRandomString(8);
        $participantData = array(
            'participant_id' => 'participant_' . $participantName,
            'firstname' => $participantName,
            'email' => $participantName . '@example.com',
            'blacklisted' => 'N',
            'owner_uid' => $userId,
            'created_by' => $userId,
        );

        $participant = \Participant::model()->insertParticipant($participantData);
        $participantId = $participant->participant_id;

        // Checking that user exists.
        $existingUser = \User::model()->findByPk($userId);
        $this->assertThat($existingUser, $this->isInstanceOf('\User'), 'Unexpected. A user should have been found.');

        // Checking that a participant for this user exists.
        $existingParticipant = \Participant::model()->findByAttributes(array('owner_uid' => $userId));
        $this->assertThat($existingParticipant, $this->isInstanceOf('\Participant'), 'Unexpected. A participant should have been found.');

        $userManager = new userManager();
        $operationResult = $userManager->deleteUser($existingUser);
        $messages = $operationResult->getMessages();

        // Checking that user no longer exists.
        $existingUser = \User::model()->findByPk($userId);
        $this->assertNull($existingUser, 'User should have been deleted.');

        // Checking that the participant still exists but no longer belongs to the deleted user.
        $existingParticipantAfterDeletion = \Participant::model()->findByPk($participantId);
        $this->assertThat($existingParticipantAfterDeletion, $this->isInstanceOf('\Participant'), 'Unexpected. A participant should have been found.');
        $this->assertNotSame($existingParticipantAfterDeletion->owner_uid, $userId, 'The participant should not belong to the deleted user anymore.');

        // Checking messages.
        $siteAdminName = \User::model()->findByPk(1)->users_name;

        $this->assertIsArray($messages, 'The operation result messages should be in an array.');
        $this->assertCount(2, $messages, 'There should be two messages.');
        $this->assertThat($messages, $this->containsOnlyInstancesOf('LimeSurvey\Datavalueobjects\TypedMessage'));
        $this->assertSame($messages[0]->getMessage(), sprintf(gT("All participants owned by this user were transferred to %s."), $siteAdminName));

        // Delete participant.
        $participant->delete();
    }

    /**
     * Test deleting a user that owns a survey group and has participants shared with them.
     */
    public function testDeleteUserAndSurveyGroupsAndParticipantShares()
    {
        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);
        $userId = $targetUser->uid;

        // Creating survey group owned by the user.
        $surveysGroup = new \SurveysGroups();
        $surveysGroup->name = 'group_' . \Yii::app()->securityManager->generateRandomString(8);
        $surveysGroup->title = 'User deletion test group';
        $surveysGroup->sortorder = 1;
        $surveysGroup->created_by = 1;
        $surveysGroup->owner_id = $userId;
        $this->assertTrue($surveysGroup->save(false), 'Unexpected. The survey group could not be saved.');
        $groupId = $surveysGroup->gsid;

        // Creating participant owned by the site admin and shared with the user.
        $participantName = \Yii::app()->securityManager->generateRandomString(8);
        $participant = \Participant::model()->insertParticipant(array(
            'participant_id' => 'participant_' . $participantName,
            'firstname' => $participantName,
            'email' => $participantName . '@example.com',
            'blacklisted' => 'N',
            'owner_uid' => 1,
            'created_by' => 1,
        ));
        $participantId = $participant->participant_id;
        $participantShare = new \ParticipantShare();
        $participantShare->setAttributes(array(
            'participant_id' => $participantId,
            'share_uid' => $userId,
            'date_added' => date('Y-m-d H:i:s'),
            'can_edit' => 0,
        ));
        $this->assertTrue($participantShare->save(), 'Unexpected. The participant share could not be saved.');
        $this->assertNotNull(\ParticipantShare::model()->findByAttributes(['share_uid' => $userId]), 'Unexpected. A participant share should have been found.');

        $existingUser = \User::model()->findByPk($userId);
        $userManager = new userManager();
        $operationResult = $userManager->deleteUser($existingUser);
        $messages = $operationResult->getMessages();

        // Checking that user no longer exists.
        $this->assertTrue($operationResult->isSuccess(), 'The operation should have been successful.');
        $this->assertNull(\User::model()->findByPk($userId), 'User should have been deleted.');

        // Checking that the survey group still exists and now belongs to the site admin.
        $existingGroupAfterDeletion = \SurveysGroups::model()->findByPk($groupId);
        $this->assertThat($existingGroupAfterDeletion, $this->isInstanceOf('\SurveysGroups'), 'Unexpected. A survey group should have been found.');
        $this->assertSame(1, (int) $existingGroupAfterDeletion->owner_id, 'The survey group should have been transferred to the site admin.');

        // Checking that the participant share with the deleted user is gone, but the participant still exists.
        $this->assertNull(\ParticipantShare::model()->findByAttributes(['share_uid' => $userId]), 'The participant share with the deleted user should have been removed.');
        $this->assertNotNull(\Participant::model()->findByPk($participantId), 'The participant should still exist.');

        // Checking messages.
        $siteAdminName = \User::model()->findByPk(1)->users_name;
        $this->assertCount(2, $messages, 'There should be two messages.');
        $this->assertSame(sprintf(gT("All of the user's survey groups were transferred to %s."), $siteAdminName), $messages[0]->getMessage());

        // Cleanup.
        $existingGroupAfterDeletion->delete();
        \Participant::model()->deleteAll('participant_id = :participant_id', [':participant_id' => $participantId]);
    }

    /**
     * Test deleting a user in a group.
     */
    public function testDeleteAUserInAGroup()
    {
        // Creating target user.
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);

        $targetUserData = array(
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        );

        $targetUser = self::createUserWithPermissions($targetUserData);
        $userId = $targetUser->uid;

        // Creating user group.
        $groupId = \UserGroup::model()->addGroup('Test', 'A test user group.');

        // Add user to group.
        $userGroup = \UserGroup::model()->findByPk($groupId);
        $userGroup->addUser($userId);

        // Checking that user exists.
        $existingUser = \User::model()->findByPk($userId);
        $this->assertThat($existingUser, $this->isInstanceOf('\User'), 'Unexpected. A user should have been found.');

        // Checking that user belongs to group.
        $this->assertTrue($userGroup->hasUser($userId), 'The user should belong to the group.');

        $userManager = new userManager();
        $operationResult = $userManager->deleteUser($existingUser);
        $messages = $operationResult->getMessages();

        // Checking that user no longer exists.
        $existingUser = \User::model()->findByPk($userId);
        $this->assertNull($existingUser, 'User should have been deleted.');

        // Checking that user does not belong to group any more.
        $this->assertFalse($userGroup->hasUser($userId), 'The user should not belong to the group any more.');

        // Checking messages.
        $this->assertIsArray($messages, 'The operation result messages should be in an array.');
        $this->assertCount(1, $messages, 'There should be two messages.');
        $this->assertThat($messages, $this->containsOnlyInstancesOf('LimeSurvey\Datavalueobjects\TypedMessage'));

        // Deleting group.
        $userGroup->delete();
    }

    /**
     * Test for a failing operation.
     * The delete function will return false.
     */
    public function testErrorDeletingUser()
    {
        $userPartialMock = $this->createPartialMock(\User::class, ['delete']);
        $userPartialMock->uid = 9999;

        $userPartialMock->expects($this->once())
                                ->method('delete')
                                ->willReturn(false);

        $userManager = new userManager();
        $operationResult = $userManager->deleteUser($userPartialMock);
        $message = $operationResult->getMessages()[0];

        // Checking messages.
        $this->assertFalse($operationResult->isSuccess(), 'The operation should not have been successful.');
        $this->assertSame('error', $message->getType(), 'Unexpected message type for an unsuccessful operation.');
        $this->assertSame(gT("User could not be deleted."), $message->getMessage(), 'Unexpected message for an unsuccessful operation.');
    }

    /**
     * Test for a failing operation.
     * The delete function will throw an exception.
     */
    public function testExceptionDeletingUser()
    {
        $userPartialMock = $this->createPartialMock(\User::class, ['delete']);
        $userPartialMock->uid = 9999;

        $userPartialMock->expects($this->once())
                                ->method('delete')
                                ->willThrowException(new \Exception('Mock exception.'));

        $userManager = new userManager();
        $operationResult = $userManager->deleteUser($userPartialMock);
        $message = $operationResult->getMessages()[0];

        // Checking messages.
        $this->assertFalse($operationResult->isSuccess(), 'The operation should not have been successful.');
        $this->assertSame('error', $message->getType(), 'Unexpected message type for an unsuccessful operation.');
        $this->assertSame(gT("An error occurred while deleting the user."), $message->getMessage(), 'Unexpected message for an unsuccessful operation.');
    }

    /**
     * Testing that a user with users / update permission can assign to a child user
     * the roles with permissions they have, and only these roles.
     */
    public function testUserCanAssignOwnPermissionRolesToChildUser()
    {
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);
        $userManagingUser = self::createUserWithPermissions(
            [
                'users_name' => $managingUserName,
                'full_name'  => $managingUserName,
                'email'      => $managingUserName . '@example.com'
            ],
            [
                'users' => ['read' => true, 'update' => true],
                'surveys' => ['create' => true, 'read' => true],
            ]
        );
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);
        $targetUser = self::createUserWithPermissions([
            'users_name' => $targetUserName,
            'full_name'  => $targetUserName,
            'email'      => $targetUserName . '@example.com',
        ]);
        \User::model()->updateByPk($targetUser->uid, ['parent_id' => $userManagingUser->uid]);
        $targetUser->parent_id = $userManagingUser->uid;

        $ownPermissionsRole = self::createRole(['surveys' => ['create' => 1, 'read' => 1]]);
        $morePermissionsRole = self::createRole(['surveys' => ['read' => 1, 'delete' => 1]]);
        $superadminRole = self::createRole(['superadmin' => ['read' => 1]]);

        $userManager = new userManager($managingUser, $targetUser);
        $this->assertTrue($userManager->canAssignRole(), 'A user with users / update permission should be able to assign roles to a child user.');
        $assignableRoles = $userManager->getAssignableRoles();
        $this->assertArrayHasKey($ownPermissionsRole->ptid, $assignableRoles, 'A role with only permissions of the managing user should be assignable.');
        $this->assertArrayNotHasKey($morePermissionsRole->ptid, $assignableRoles, 'A role with a permission the managing user does not have should not be assignable.');
        $this->assertArrayNotHasKey($superadminRole->ptid, $assignableRoles, 'A role with superadmin permission should not be assignable without superadmin / create permission.');

        self::deleteRole($ownPermissionsRole);
        self::deleteRole($morePermissionsRole);
        self::deleteRole($superadminRole);
        \Permission::model()->deleteAllByAttributes(['uid' => $userManagingUser->uid]);
        $userManagingUser->delete();
        $targetUser->delete();
    }

    /**
     * Testing that a user with users / update permission can not assign roles
     * to a user who is not a child user, or to a superadmin child user.
     */
    public function testUserCanNotAssignRolesToOtherOrSuperadminUser()
    {
        $managingUserName = \Yii::app()->securityManager->generateRandomString(8);
        $userManagingUser = self::createUserWithPermissions(
            [
                'users_name' => $managingUserName,
                'full_name'  => $managingUserName,
                'email'      => $managingUserName . '@example.com'
            ],
            ['users' => ['read' => true, 'update' => true]]
        );
        $managingUser = new LSWebUser();
        $managingUser->id = $userManagingUser->uid;

        $otherUserName = \Yii::app()->securityManager->generateRandomString(8);
        $otherUser = self::createUserWithPermissions([
            'users_name' => $otherUserName,
            'full_name'  => $otherUserName,
            'email'      => $otherUserName . '@example.com',
        ]);
        $userManager = new userManager($managingUser, $otherUser);
        $this->assertFalse($userManager->canAssignRole(), 'A user with users / update permission should not be able to assign roles to a user who is not a child user.');

        $superadminUserName = \Yii::app()->securityManager->generateRandomString(8);
        $superadminUser = self::createUserWithPermissions(
            [
                'users_name' => $superadminUserName,
                'full_name'  => $superadminUserName,
                'email'      => $superadminUserName . '@example.com',
            ],
            ['superadmin' => ['read' => true]]
        );
        \User::model()->updateByPk($superadminUser->uid, ['parent_id' => $userManagingUser->uid]);
        $superadminUser->parent_id = $userManagingUser->uid;
        $userManager = new userManager($managingUser, $superadminUser);
        $this->assertFalse($userManager->canAssignRole(), 'A user without superadmin / create permission should not be able to assign roles to a superadmin.');

        \Permission::model()->deleteAllByAttributes(['uid' => [$userManagingUser->uid, $superadminUser->uid]]);
        $userManagingUser->delete();
        $otherUser->delete();
        $superadminUser->delete();
    }

    /**
     * Testing that setting roles removes the individual global permissions of the user,
     * so the user has no global permission after removing the last role.
     * Permissions on a survey are kept.
     */
    public function testSetRolesRemovesIndividualGlobalPermissions()
    {
        $targetUserName = \Yii::app()->securityManager->generateRandomString(8);
        $targetUser = self::createUserWithPermissions(
            [
                'users_name' => $targetUserName,
                'full_name'  => $targetUserName,
                'email'      => $targetUserName . '@example.com',
            ],
            ['surveys' => ['read' => true, 'create' => true]]
        );
        $surveyPermission = new \Permission();
        $surveyPermission->entity = 'survey';
        $surveyPermission->entity_id = 999999;
        $surveyPermission->uid = $targetUser->uid;
        $surveyPermission->permission = 'survey';
        $surveyPermission->read_p = 1;
        $surveyPermission->save();

        $role = self::createRole(['surveys' => ['read' => 1]]);
        $userManager = new userManager(null, $targetUser);

        // Setting roles on a user without roles keeps their individual permissions
        $this->assertTrue($userManager->setRoles([]));
        $this->assertSame('1', (string) \Permission::model()->countByAttributes(['uid' => $targetUser->uid, 'entity' => 'global']));
        $this->assertTrue($userManager->hasIndividualGlobalPermissions(), 'The user should have individual global permissions before a role is assigned.');

        $this->assertTrue($userManager->setRoles([$role->ptid]));
        $this->assertSame([(int) $role->ptid], array_values(array_map('intval', \CHtml::listData(\Permission::getUserRole($targetUser->uid), 'ptid', 'ptid'))));
        $this->assertSame('0', (string) \Permission::model()->countByAttributes(['uid' => $targetUser->uid, 'entity' => 'global']), 'Assigning a role should delete the individual global permissions.');
        $this->assertSame('1', (string) \Permission::model()->countByAttributes(['uid' => $targetUser->uid, 'entity' => 'survey']), 'Assigning a role should keep the survey permissions.');
        $this->assertFalse($userManager->hasIndividualGlobalPermissions(), 'Survey permissions are not individual global permissions.');

        $this->assertTrue($userManager->setRoles([]));
        $this->assertEmpty(\Permission::getUserRole($targetUser->uid));
        $this->assertFalse(\Permission::model()->hasGlobalPermission('surveys', 'create', $targetUser->uid), 'A user without roles should not get back their previous individual permissions.');

        self::deleteRole($role);
        \Permission::model()->deleteAllByAttributes(['uid' => $targetUser->uid]);
        $targetUser->delete();
    }

    /**
     * Create a role with the given global permissions
     * @param array $permissions permission name => [crud => 1]
     * @return \Permissiontemplates
     */
    private static function createRole(array $permissions): \Permissiontemplates
    {
        $roleName = \Yii::app()->securityManager->generateRandomString(12);
        $role = new \Permissiontemplates();
        $role->name = $roleName;
        $role->description = $roleName;
        $role->renewed_last = date("Y-m-d H:i:s");
        $role->created_at = date("Y-m-d H:i:s");
        $role->created_by = 1;
        $role->save();
        foreach ($permissions as $permissionName => $cruds) {
            $permission = new \Permission();
            $permission->entity = 'role';
            $permission->entity_id = $role->ptid;
            $permission->uid = 0;
            $permission->permission = $permissionName;
            foreach ($cruds as $crud => $value) {
                $permission->setAttribute($crud . '_p', $value);
            }
            $permission->save();
        }
        return $role;
    }

    /**
     * Delete a role, its permissions and its user assignments
     * @param \Permissiontemplates $role
     * @return void
     */
    private static function deleteRole(\Permissiontemplates $role)
    {
        \Permission::model()->deleteAllByAttributes(['entity' => 'role', 'entity_id' => $role->ptid]);
        \UserInPermissionrole::model()->deleteAllByAttributes(['ptid' => $role->ptid]);
        $role->delete();
    }
}
