<?php

namespace ls\tests;

use Permission;

/**
 * Tests for Permission::renameThemePermissions(): Users keep their theme permissions when a theme is renamed.
 */
class PermissionRenameThemeTest extends TestBaseClass
{
    /** @var string Prefix of all theme names used in these tests */
    private $sPrefix;

    /**
     * Create a unique theme name prefix for this test.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->sPrefix = 'renamepermtest' . uniqid();
    }

    /**
     * Remove all permissions created by this test.
     */
    public function tearDown(): void
    {
        Permission::model()->deleteAll(
            "entity = 'template' AND permission LIKE :prefix",
            [':prefix' => $this->sPrefix . '%']
        );
        parent::tearDown();
    }

    /**
     * Add a theme permission for a user.
     *
     * @param int $iUserId The user id
     * @param string $sThemeName The theme name
     * @param int $iRead The read permission value
     * @return void
     */
    private function addThemePermission($iUserId, $sThemeName, $iRead = 1)
    {
        $oPermission = new Permission();
        $oPermission->uid = $iUserId;
        $oPermission->entity = 'template';
        $oPermission->entity_id = 0;
        $oPermission->permission = $sThemeName;
        $oPermission->read_p = $iRead;
        $this->assertTrue($oPermission->save());
    }

    /**
     * Get the read permission value a user has for a theme name, null if there is none.
     *
     * @param int $iUserId The user id
     * @param string $sThemeName The theme name
     * @return int|null
     */
    private function getReadPermission($iUserId, $sThemeName)
    {
        $oPermission = Permission::model()->find(
            "entity = 'template' AND uid = :uid AND permission = :name",
            [':uid' => $iUserId, ':name' => $sThemeName]
        );
        return $oPermission ? (int) $oPermission->read_p : null;
    }

    /**
     * The permissions of all users are moved to the new name, other themes are left alone.
     */
    public function testPermissionsAreMovedToNewName()
    {
        $sOldName = $this->sPrefix . 'old';
        $sNewName = $this->sPrefix . 'new';
        $sOtherName = $this->sPrefix . 'other';
        $this->addThemePermission(1001, $sOldName);
        $this->addThemePermission(1002, $sOldName);
        $this->addThemePermission(1001, $sOtherName);

        Permission::renameThemePermissions($sOldName, $sNewName);

        $this->assertSame(1, $this->getReadPermission(1001, $sNewName));
        $this->assertSame(1, $this->getReadPermission(1002, $sNewName));
        $this->assertSame(1, $this->getReadPermission(1001, $sOtherName));
        $this->assertNull($this->getReadPermission(1001, $sOldName));
        $this->assertNull($this->getReadPermission(1002, $sOldName));
    }

    /**
     * A rename that only changes the case keeps the permissions.
     */
    public function testCaseOnlyRenameKeepsPermissions()
    {
        $sOldName = $this->sPrefix . 'case';
        $sNewName = $this->sPrefix . 'Case';
        $this->addThemePermission(1001, $sOldName);

        Permission::renameThemePermissions($sOldName, $sNewName);

        $oPermission = Permission::model()->find(
            "entity = 'template' AND uid = :uid AND permission = :name",
            [':uid' => 1001, ':name' => $sNewName]
        );
        $this->assertNotNull($oPermission);
        $this->assertSame($sNewName, $oPermission->permission);
        $this->assertSame(1, (int) $oPermission->read_p);
    }

    /**
     * A leftover permission with the new name does not prevent moving the permissions.
     */
    public function testLeftoverPermissionWithNewNameIsReplaced()
    {
        $sOldName = $this->sPrefix . 'old';
        $sNewName = $this->sPrefix . 'new';
        $this->addThemePermission(1001, $sOldName, 1);
        $this->addThemePermission(1001, $sNewName, 0);

        Permission::renameThemePermissions($sOldName, $sNewName);

        $this->assertSame(1, $this->getReadPermission(1001, $sNewName));
        $this->assertNull($this->getReadPermission(1001, $sOldName));
    }
}
