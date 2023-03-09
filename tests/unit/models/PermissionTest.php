<?php

namespace ls\tests;

use Permission;
use SurveysGroups;
use PHPUnit\Framework\TestCase;

class PermissionTest extends BaseModelTestCase
{
    protected $modelClassName = Permission::class;

    public static function setupBeforeClass(): void
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::import('application.helpers.globalsettings_helper', true);
    }

    /**
     * User 1 has access to everything.
     */
    public function testSuperAdmin()
    {
        $userId = 1;
        $surveysGroupGid = 999;

        $surveysGroup = $this
            ->getMockBuilder(SurveysGroups::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $surveysGroup->method('save')->willReturn(true);
        $surveysGroup->method('attributes')->willReturn([
            'gsid',
            'owner_id'
        ]);
        $surveysGroup->gsid = $surveysGroupGid;
        $surveysGroup->owner_id = $userId + 1;

        $perm = $this
            ->getMockBuilder(Permission::class)
            ->setMethods(['getUserId', 'getEntity'])
            ->getMock();
        $perm->method('getUserId')->willReturn($userId);
        $perm->method('getEntity')->willReturn($surveysGroup);

        $this->assertTrue($perm->hasPermission($surveysGroupGid, 'SurveysGroups', 'permission', 'create'));
    }

    /**
     * User is not superadmin and survey group is owned by this user.
     */
    public function testOwnershipSuccess()
    {
        // NB: Not 1 (superadmin).
        $userId = 2;
        $surveysGroupGid = 999;

        $surveysGroup = $this
            ->getMockBuilder(SurveysGroups::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $surveysGroup->method('save')->willReturn(true);
        $surveysGroup->method('attributes')->willReturn([
            'gsid',
            'owner_id'
        ]);
        $surveysGroup->gsid = $surveysGroupGid;
        $surveysGroup->owner_id = $userId;

        $perm = $this
            ->getMockBuilder(Permission::class)
            ->setMethods(['getUserId', 'getEntity'])
            ->getMock();
        $perm->method('getUserId')->willReturn($userId);
        $perm->method('getEntity')->willReturn($surveysGroup);

        $this->assertTrue($perm->hasPermission($surveysGroupGid, 'SurveysGroups', 'permission', 'create'));
    }

    /**
     * User is not superadmin and survey group is owned by other user.
     */
    public function testOwnershipFailure()
    {
        // NB: Not 1 (superadmin).
        $userId = 2;
        $surveysGroupGid = 999;

        $surveysGroup = $this
            ->getMockBuilder(SurveysGroups::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $surveysGroup->method('save')->willReturn(true);
        $surveysGroup->method('attributes')->willReturn([
            'gsid',
            'owner_id'
        ]);
        $surveysGroup->gsid = $surveysGroupGid;
        $surveysGroup->owner_id = $userId + 1;

        $perm = $this
            ->getMockBuilder(Permission::class)
            ->setMethods(['getUserId', 'getEntity'])
            ->getMock();
        $perm->method('getUserId')->willReturn($userId);
        $perm->method('getEntity')->willReturn($surveysGroup);

        $this->assertFalse($perm->hasPermission($surveysGroupGid, 'SurveysGroups', 'permission', 'create'));
    }

    /**
     * Test for users with superadmin permissions.
     */
    public function testGetUsersWithSuperAdminPermissions()
    {
        //Create user.
        $newPassword = createPassword();
        $userId = \User::insertUser('test_user', $newPassword, 'John Doe', 1, 'jd@mail.com');

        //Assign superadmin permissions.
        \Permission::model()->setGlobalPermission($userId, 'superadmin', array('create_p', 'read_p', 'update_p', 'delete_p', 'import_p', 'export_p'));

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
}
