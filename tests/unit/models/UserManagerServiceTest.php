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
        \Yii::app()->session['loginID'] = $userId;
        $groupId = \UserGroup::model()->addGroup('Test', 'A test user group.');

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
}
