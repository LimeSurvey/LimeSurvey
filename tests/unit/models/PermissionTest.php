<?php

/* Temporary disable each test : need a way to return findByPk or hasPermission (but hasPermission is the function tested …) ? */
namespace ls\tests;

use Permission;
use SurveysGroups;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    public static function setupBeforeClass()
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::import('application.helpers.globalsettings_helper', true);
    }

    /**
     * User 1 has access to everything.
     */
    public function testSuperAdmin()
    {
        $surveysGroup = $this
            ->getMockBuilder(SurveysGroups::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $surveysGroup->method('save')->willReturn(true);
        $surveysGroup->method('attributes')->willReturn([]);

        $perm = $this
            ->getMockBuilder(Permission::class)
            ->setMethods(['getUserId'])
            ->getMock();
        $perm->method('getUserId')->willReturn(1);

        $surveysGroupsId = 999;
        $this->assertTrue($surveysGroup->hasPermission('permission', 'create'));
    }

    /**
     * User is not superadmin and survey group is owned by this user.
     */
    public function testOwnershipSuccess()
    {
        // NB: Not 1 (superadmin).
        $userId = 2;
        $surveysGroup = $this
            ->getMockBuilder(SurveysGroups::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $surveysGroup->method('save')->willReturn(true);
        $surveysGroup->method('attributes')->willReturn([
            'owner_id'
        ]);
        $surveysGroup->owner_id = $userId;

        $perm = $this
            ->getMockBuilder(Permission::class)
            ->setMethods(['getUserId', 'getEntity'])
            ->getMock();
        $perm->method('getUserId')->willReturn($userId);
        $perm->method('getEntity')->willReturn($surveysGroup);

        $surveysGroupsId = 999;
        $this->assertTrue($surveysGroup->hasPermission('permission', 'create'));
    }

    /**
     * User is not superadmin and survey group is owned by other user.
     */
    public function testOwnershipFailure()
    {
        // NB: Not 1 (superadmin).
        $userId = 2;
        $surveysGroup = $this
            ->getMockBuilder(SurveysGroups::class)
            ->setMethods(['save', 'attributes'])
            ->getMock();
        $surveysGroup->method('save')->willReturn(true);
        $surveysGroup->method('attributes')->willReturn([
            'owner_id'
        ]);
        $surveysGroup->owner_id = $userId + 1;

        $perm = $this
            ->getMockBuilder(Permission::class)
            ->setMethods(['getUserId', 'getEntity'])
            ->getMock();
        $perm->method('getUserId')->willReturn($userId);
        $perm->method('getEntity')->willReturn($surveysGroup);

        $surveysGroupsId = 999;
        $this->assertFalse($surveysGroup->hasPermission('permission', 'create'));
    }
}
