<?php

namespace ls\tests;

use Permission;
use SurveysGroups;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestBaseClass
{
    protected $modelClassName = Permission::class;
    private static $user;

    public static function setupBeforeClass(): void
    {
        \Yii::import('application.helpers.common_helper', true);
        \Yii::import('application.helpers.globalsettings_helper', true);

        // Create user
        $userName = \Yii::app()->securityManager->generateRandomString(8);
        $password = createPassword();

        $userData = array(
            'users_name' => $userName,
            'full_name' => $userName,
            'email' => $userName . '@example.com',
            'lang' => 'auto',
            'password' => $password
        );

        $permissions = array(
            'surveys' => array(
                'read' => false
            )
        );

        $user = self::createUserWithPermissions($userData, $permissions);

        self::$user = $user;
    }

    public static function tearDownAfterClass(): void
    {
        self::$user->delete();
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
        $userId = self::$user->uid;
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
        $userId = self::$user->uid;
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
