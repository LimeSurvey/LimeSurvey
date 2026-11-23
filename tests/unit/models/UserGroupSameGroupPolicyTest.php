<?php

namespace ls\tests;

/**
 * Tests the user group filtering by the 'usercontrolSameGroupPolicy' setting in UserGroup.
 */
class UserGroupSameGroupPolicyTest extends TestBaseClass
{
    /** @var \User User whose visible groups are tested */
    private static $user;

    /** @var \User Owner of the groups the tested user does not own */
    private static $otherUser;

    /** @var int Group the tested user is a member of */
    private static $memberGroupId;

    /** @var int Group the tested user owns */
    private static $ownedGroupId;

    /** @var int Group the tested user neither owns nor is a member of */
    private static $otherGroupId;

    /** @var mixed Value of 'usercontrolSameGroupPolicy' before the tests */
    private static $originalPolicy;

    /**
     * Creates two users and three user groups.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$originalPolicy = \Yii::app()->getConfig('usercontrolSameGroupPolicy');
        self::$user = self::createUserWithPermissions([
            'users_name' => 'ugpolicyuser',
            'full_name' => 'ugpolicyuser',
            'email' => 'ugpolicyuser@example.com',
            'lang' => 'auto',
            'password' => 'ugpolicyuser',
        ], ['auth_db' => ['read' => 'on']]);
        self::$otherUser = self::createUserWithPermissions([
            'users_name' => 'ugpolicyother',
            'full_name' => 'ugpolicyother',
            'email' => 'ugpolicyother@example.com',
            'lang' => 'auto',
            'password' => 'ugpolicyother',
        ], ['auth_db' => ['read' => 'on']]);

        // Names are chosen so that the name order differs from the creation order
        self::$ownedGroupId = self::createGroup('ugpolicy_b_owned', self::$user->uid);
        self::$memberGroupId = self::createGroup('ugpolicy_a_member', self::$otherUser->uid);
        self::$otherGroupId = self::createGroup('ugpolicy_c_other', self::$otherUser->uid);

        $membership = new \UserInGroup();
        $membership->ugid = self::$memberGroupId;
        $membership->uid = self::$user->uid;
        $membership->save();
    }

    /**
     * Removes the users and groups and restores the session user and the setting.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        $groupIds = [self::$memberGroupId, self::$ownedGroupId, self::$otherGroupId];
        \UserInGroup::model()->deleteAllByAttributes(['ugid' => $groupIds]);
        \UserGroup::model()->deleteAllByAttributes(['ugid' => $groupIds]);
        self::$user->delete();
        self::$otherUser->delete();
        \Yii::app()->setConfig('usercontrolSameGroupPolicy', self::$originalPolicy);
        \Yii::app()->session['loginID'] = 1;
        parent::tearDownAfterClass();
    }

    /**
     * Restores the superadmin session user after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->session['loginID'] = 1;
        parent::tearDown();
    }

    /**
     * With the policy enabled, a user only sees the groups they are a member or the owner of, sorted by name.
     *
     * @return void
     */
    public function testPolicyLimitsGroupsToMembershipAndOwnership()
    {
        \Yii::app()->setConfig('usercontrolSameGroupPolicy', true);
        \Yii::app()->session['loginID'] = self::$user->uid;

        $userGroup = \UserGroup::model();
        $this->assertTrue($userGroup->isFilteredBySameGroupPolicy());
        $this->assertSame([self::$memberGroupId, self::$ownedGroupId], $userGroup->getAuthorizedGroupIds());
        $this->assertTrue($userGroup->isAuthorizedGroup((string) self::$memberGroupId));
        $this->assertTrue($userGroup->isAuthorizedGroup(self::$ownedGroupId));
        $this->assertFalse($userGroup->isAuthorizedGroup(self::$otherGroupId));
    }

    /**
     * With the policy disabled, a user sees all groups.
     *
     * @return void
     */
    public function testNoFilteringWhenPolicyIsDisabled()
    {
        \Yii::app()->setConfig('usercontrolSameGroupPolicy', false);
        \Yii::app()->session['loginID'] = self::$user->uid;

        $userGroup = \UserGroup::model();
        $this->assertFalse($userGroup->isFilteredBySameGroupPolicy());
        $groupIds = $userGroup->getAuthorizedGroupIds();
        $this->assertContains(self::$otherGroupId, $groupIds);
        $this->assertContains(self::$memberGroupId, $groupIds);
        $this->assertContains(self::$ownedGroupId, $groupIds);
        $this->assertTrue($userGroup->isAuthorizedGroup(self::$otherGroupId));
    }

    /**
     * A superadmin always sees all groups, even with the policy enabled.
     *
     * @return void
     */
    public function testNoFilteringForSuperadmin()
    {
        \Yii::app()->setConfig('usercontrolSameGroupPolicy', true);
        \Yii::app()->session['loginID'] = 1;

        $userGroup = \UserGroup::model();
        $this->assertFalse($userGroup->isFilteredBySameGroupPolicy());
        $this->assertContains(self::$otherGroupId, $userGroup->getAuthorizedGroupIds());
        $this->assertTrue($userGroup->isAuthorizedGroup(self::$otherGroupId));
    }

    /**
     * The deprecated global helpers return the same as the model methods.
     *
     * @return void
     */
    public function testDeprecatedHelpersUseModel()
    {
        \Yii::app()->setConfig('usercontrolSameGroupPolicy', true);
        \Yii::app()->session['loginID'] = self::$user->uid;

        $this->assertTrue(shouldFilterUserGroupList());
        $this->assertSame([self::$memberGroupId, self::$ownedGroupId], getUserGroupList());
    }

    /**
     * Creates a user group.
     *
     * @param string $name
     * @param int $ownerId
     * @return int id of the new group
     */
    private static function createGroup($name, $ownerId)
    {
        \UserGroup::model()->deleteAllByAttributes(['name' => $name]);
        $group = new \UserGroup();
        $group->name = $name;
        $group->description = '';
        $group->owner_id = $ownerId;
        if (!$group->save()) {
            throw new \Exception('Could not save user group: ' . print_r($group->getErrors(), true));
        }
        return (int) $group->ugid;
    }
}
