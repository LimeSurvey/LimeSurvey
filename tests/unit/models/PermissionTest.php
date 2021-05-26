<?php

namespace ls\tests;

use Permission;
use SurveysGroups;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
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
}
