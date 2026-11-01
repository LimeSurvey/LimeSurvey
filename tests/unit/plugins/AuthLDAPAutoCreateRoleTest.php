<?php

namespace ls\tests;

/**
 * Tests for the role assigned by AuthLDAP to automatically created users
 */
class AuthLDAPAutoCreateRoleTest extends TestBaseClass
{
    /** @var \AuthLDAP */
    private static $plugin;

    /** @var boolean */
    private static $wasActive;

    /** @var integer[] */
    private static $roleIds = [];

    /** @var integer[] */
    private static $userIds = [];

    /**
     * Activate and load the AuthLDAP plugin
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $plugin = \Plugin::model()->findByAttributes(['name' => 'AuthLDAP']);
        self::$wasActive = $plugin && $plugin->active;
        $plugin = self::installAndActivatePlugin('AuthLDAP');
        // Pass the plugin id: without it, loadPlugin() only returns an already instantiated plugin
        self::$plugin = App()->getPluginManager()->loadPlugin('AuthLDAP', $plugin->id);
    }

    /**
     * Remove the created roles and users, reset the setting and restore the plugin state
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        self::$plugin->saveSettings(['autocreaterole' => '']);
        foreach (self::$userIds as $userId) {
            \UserInPermissionrole::model()->deleteAllByAttributes(['uid' => $userId]);
            \Permission::model()->deleteAllByAttributes(['uid' => $userId]);
            \User::model()->deleteByPk($userId);
        }
        foreach (self::$roleIds as $roleId) {
            \Permission::model()->deleteAllByAttributes(['entity' => 'role', 'entity_id' => $roleId]);
            \Permissiontemplates::model()->deleteByPk($roleId);
        }
        if (!self::$wasActive) {
            self::deActivatePlugin('AuthLDAP');
        }
        parent::tearDownAfterClass();
    }

    /**
     * Only roles with the "Use LDAP authentication" permission can be selected
     *
     * @return void
     */
    public function testOnlyLdapRolesAreOptions()
    {
        $ldapRoleId = $this->createRole(true);
        $otherRoleId = $this->createRole(false);

        $options = self::$plugin->getPluginSettings(false)['autocreaterole']['options'];

        $this->assertSame('None', $options['']);
        $this->assertArrayHasKey($ldapRoleId, $options);
        $this->assertArrayNotHasKey($otherRoleId, $options);
    }

    /**
     * The selected role is assigned and lets the user log in with LDAP
     *
     * @return void
     */
    public function testRoleIsAssigned()
    {
        $roleId = $this->createRole(true);
        $userId = $this->createUser();
        self::$plugin->saveSettings(['autocreaterole' => $roleId]);

        $this->assignAutoCreateRole($userId);

        $this->assertNotNull(\UserInPermissionrole::model()->findByPk(['ptid' => $roleId, 'uid' => $userId]));
        $this->assertTrue(\Permission::model()->hasGlobalPermission('auth_ldap', 'read', $userId));
    }

    /**
     * A role without the "Use LDAP authentication" permission is not assigned
     *
     * @return void
     */
    public function testRoleWithoutLdapPermissionIsNotAssigned()
    {
        $roleId = $this->createRole(false);
        $userId = $this->createUser();
        self::$plugin->saveSettings(['autocreaterole' => $roleId]);

        $this->assignAutoCreateRole($userId);

        $this->assertEmpty(\UserInPermissionrole::model()->findAllByAttributes(['uid' => $userId]));
    }

    /**
     * No role is assigned when none is selected or the selected role was deleted
     *
     * @return void
     */
    public function testNoOrDeletedRoleIsNotAssigned()
    {
        $userId = $this->createUser();

        self::$plugin->saveSettings(['autocreaterole' => '']);
        $this->assignAutoCreateRole($userId);
        $this->assertEmpty(\UserInPermissionrole::model()->findAllByAttributes(['uid' => $userId]));

        $roleId = $this->createRole(true);
        \Permissiontemplates::model()->deleteByPk($roleId);
        self::$plugin->saveSettings(['autocreaterole' => $roleId]);
        $this->assignAutoCreateRole($userId);
        $this->assertEmpty(\UserInPermissionrole::model()->findAllByAttributes(['uid' => $userId]));
    }

    /**
     * Call the private AuthLDAP::assignAutoCreateRole()
     *
     * @param integer $userId
     * @return void
     */
    private function assignAutoCreateRole($userId)
    {
        $method = new \ReflectionMethod(\AuthLDAP::class, 'assignAutoCreateRole');
        $method->setAccessible(true);
        $method->invoke(self::$plugin, $userId);
    }

    /**
     * Create a role
     *
     * @param boolean $withLdapPermission Grant the "Use LDAP authentication" permission to the role
     * @return integer The role ID
     */
    private function createRole($withLdapPermission)
    {
        $role = new \Permissiontemplates();
        $role->name = \Yii::app()->securityManager->generateRandomString(12);
        $role->description = $role->name;
        $role->renewed_last = date('Y-m-d H:i:s');
        $role->created_at = date('Y-m-d H:i:s');
        $role->created_by = 1;
        $this->assertTrue($role->save(), json_encode($role->getErrors()));
        self::$roleIds[] = (int) $role->ptid;

        $permission = new \Permission();
        $permission->entity = 'role';
        $permission->entity_id = $role->ptid;
        $permission->uid = 0;
        $permission->permission = $withLdapPermission ? 'auth_ldap' : 'surveys';
        $permission->read_p = 1;
        $this->assertTrue($permission->save(), json_encode($permission->getErrors()));

        return (int) $role->ptid;
    }

    /**
     * Create a user like AuthLDAP does for automatically created users
     *
     * @return integer The user ID
     */
    private function createUser()
    {
        $userName = \Yii::app()->securityManager->generateRandomString(28);
        $userId = \User::insertUser($userName, createPassword(), 'John Doe', 1, $userName . '@example.org');
        $this->assertFalse($userId instanceof \User, 'Failed to create user');
        $userId = (int) $userId;
        self::$userIds[] = $userId;
        \Permission::model()->setGlobalPermission($userId, 'auth_ldap');

        return $userId;
    }
}
