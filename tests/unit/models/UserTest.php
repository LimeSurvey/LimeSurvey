<?php

namespace ls\tests;

class UserTest extends BaseModelTestCase
{
    protected $modelClassName = \User::class;

    /**
    * Test for users with superadmin permissions.
    */
    public function testGetUsersWithSuperAdminPermissions()
    {
        //Create user.
        $newPassword = createPassword();
        $userId = \User::insertUser('test_user', $newPassword, 'John Doe', 1, 'jd@mail.com');

        //Assign superadmin permissions.
        \Permission::model()->setGlobalPermission($userId, 'superadmin', array('read_p'));

        $superAdminsBefore = \User::model()->getSuperAdmins();

        //Deselect the super administrator permissions for the user.
        $permission = \Permission::model()->findByAttributes(array('uid' => $userId));
        $permission->read_p = 0;
        $permission->save();

        $superAdminsAfter = \User::model()->getSuperAdmins();

        $this->assertNotSameSize($superAdminsBefore, $superAdminsAfter, 'The new user should not have superadmin permissions anymore.');

        //Delete user.
        $user = \User::model()->findByPk($userId);
        $user->delete();
        $permission->delete();
    }

    /**
     * Test get users that will always be super admins, no matter their permissions.
     * See config-defaluts.php.
     */
    public function testGetUsersWithForcedSuperAdminPermissionsByDefault()
    {
        $superAdminsBefore = \User::model()->getSuperAdmins();

        //Get forced super admin array. By default user id 1 is a forcedsuperadmin.
        $forcedSuperAdmins = App()->getConfig('forcedsuperadmin');

        //Deselect the super administrator permissions for the forcedsuperadmin user.
        $permission = \Permission::model()->findByAttributes(array('uid' => $forcedSuperAdmins[0]));
        $temp_read_p = $permission->read_p;
        $permission->read_p = 0;
        $permission->save();

        $isForced = \Permission::isForcedSuperAdmin($forcedSuperAdmins[0]);
        $superAdminsAfter = \User::model()->getSuperAdmins();

        $this->assertTrue($isForced, 'The forced super admin user should still be a super admin.');
        $this->assertSameSize($superAdminsBefore, $superAdminsAfter, 'The forced super admin user should still be a super admin.');

        //Restore forcedsuperadmin read_p status.
        $permission->read_p = $temp_read_p;
        $permission->save();
    }

    /**
     * Make a new user forcedsuperadmin.
     */
    public function testGetUsersWithForcedSuperAdminPermissionsOnNewUser()
    {
        $superAdminsBefore = \User::model()->getSuperAdmins();

        //Create user.
        $newPassword = createPassword();
        $userId = \User::insertUser('test_user', $newPassword, 'John Doe', 1, 'jd@mail.com');

        //Add new user id to forcedsuperadmin array.
        $tempForcedSuperAdmins = App()->getConfig('forcedsuperadmin');
        $newForcedSuperAdmins = array_merge($tempForcedSuperAdmins, array( (int)$userId ));
        App()->setConfig('forcedsuperadmin', $newForcedSuperAdmins);
        $forcedSuperAdmins = App()->getConfig('forcedsuperadmin');

        $superAdminsAfter = \User::model()->getSuperAdmins();
        $isForced = \Permission::isForcedSuperAdmin($userId);

        $this->assertTrue($isForced, 'The forced super admin user should still be a super admin.');
        $this->assertNotSameSize($superAdminsBefore, $superAdminsAfter, 'The new user have superadmin permissions.');

        //Delete user.
        $user = \User::model()->findByPk($userId);
        $user->delete();

        //Restore forcedsuperadmin original array.
        App()->setConfig('forcedsuperadmin', $tempForcedSuperAdmins);
    }
}
