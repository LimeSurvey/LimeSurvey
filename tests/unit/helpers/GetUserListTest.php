<?php

namespace ls\tests;

/**
 * Tests for getUserList() with the usercontrolSameGroupPolicy setting (#18468).
 */
class GetUserListTest extends TestBaseClass
{
    /** @var \User User with the global permission to view users */
    private $userViewer;

    /** @var \User User without any permission */
    private $plainUser;

    /** @var \User User which is in no user group with any of the others */
    private $otherUser;

    /** @var mixed Original value of usercontrolSameGroupPolicy */
    private $usercontrolSameGroupPolicy;

    /**
     * Creates three users which are not in any user group and enables
     * usercontrolSameGroupPolicy.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Yii::app()->loadHelper('common');
        $this->usercontrolSameGroupPolicy = \Yii::app()->getConfig('usercontrolSameGroupPolicy');
        \Yii::app()->setConfig('usercontrolSameGroupPolicy', true);

        $this->userViewer = $this->createTestUser('userlist_viewer', ['users' => ['read' => 'on']]);
        $this->plainUser = $this->createTestUser('userlist_plain');
        $this->otherUser = $this->createTestUser('userlist_other');
    }

    /**
     * Deletes the test users and their permissions and restores the session
     * user and the usercontrolSameGroupPolicy setting.
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->setConfig('usercontrolSameGroupPolicy', $this->usercontrolSameGroupPolicy);
        \Yii::app()->session['loginID'] = 1;
        foreach ([$this->userViewer, $this->plainUser, $this->otherUser] as $user) {
            if ($user) {
                \Permission::model()->deleteAllByAttributes(['uid' => $user->uid]);
                $user->delete();
            }
        }
        parent::tearDown();
    }

    /**
     * A user with the global permission to view users gets all users,
     * even if they share no user group.
     * @return void
     */
    public function testUserWithUsersReadPermissionSeesAllUsers()
    {
        \Yii::app()->session['loginID'] = $this->userViewer->uid;

        $userIds = array_map('intval', getUserList('onlyuidarray'));

        $this->assertContains((int) $this->otherUser->uid, $userIds);
        $this->assertContains((int) $this->plainUser->uid, $userIds);
    }

    /**
     * A user without the permission to view users still only gets
     * users from the own user groups.
     * @return void
     */
    public function testUserWithoutUsersReadPermissionSeesOnlyOwnGroup()
    {
        \Yii::app()->session['loginID'] = $this->plainUser->uid;

        $userIds = array_map('intval', getUserList('onlyuidarray'));

        $this->assertNotContains((int) $this->otherUser->uid, $userIds);
        $this->assertNotContains((int) $this->userViewer->uid, $userIds);
        $this->assertContains((int) $this->plainUser->uid, $userIds);
    }

    /**
     * Without usercontrolSameGroupPolicy every user gets all users.
     * @return void
     */
    public function testWithoutSameGroupPolicyAllUsersAreListed()
    {
        \Yii::app()->setConfig('usercontrolSameGroupPolicy', false);
        \Yii::app()->session['loginID'] = $this->plainUser->uid;

        $userIds = array_map('intval', getUserList('onlyuidarray'));

        $this->assertContains((int) $this->otherUser->uid, $userIds);
    }

    /**
     * Creates a user with a random name.
     * @param string $prefix
     * @param array $permissions Global permissions, see TestBaseClass::addUserPermissions()
     * @return \User
     */
    private function createTestUser($prefix, array $permissions = [])
    {
        $userName = $prefix . '_' . \Yii::app()->securityManager->generateRandomString(8);
        return self::createUserWithPermissions([
            'users_name' => $userName,
            'full_name' => $userName,
            'email' => $userName . '@example.org',
            'lang' => 'auto',
            'password' => 'testpassword123',
        ], $permissions);
    }
}
