<?php

namespace ls\tests;

use Throwable;
use User;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

class UserStatusTest extends TestBaseClassWeb
{
    // TODO: 
    // Check that you cannot deactive yourself (even when not superadmin)
    //   Create new user with permission to edit users
    //   Login as new user
    //   Go to user management
    //   Click on my own action button
    //   "Deactivate" should be disabled
    // Activate massive action
    // Deactivate user you do not own?
    // Try to login as not-active
    // Try to login as active

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;

        // Browser login.
        self::adminLogin($username, $password, $wait = false);
    }

    public function testCannotDeactiveSuperadmin()
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $web = self::$webDriver;

        try {
            // Go to User Management page
            $url = $urlMan->createUrl('userManagement/index');
            $web->get($url);

            // Click on action dropdown
            $dropdownButton = $web->findByCss('.dropdown.ls-action_dropdown');
            $dropdownButton->click();
            // @var string Something like dropdown_3
            $id = $dropdownButton->getAttribute('id');
            $parts = explode('_', $id);
            $this->assertCount(2, $parts);

            // Get belonging <ul>
            $dropdownMenuItems = $web->findManyByCss('#dropdownmenu_' . (int) $parts[1] . ' li');
            $deactiveElement = $dropdownMenuItems[1];
            $deactiveElementAnchor = $deactiveElement
                ->findElement(
                    WebDriverBy::cssSelector('a')
                );

            $this->assertEquals('Deactivate', $deactiveElement->getText(), 'Text is Deactivate');
            $this->assertTrue($deactiveElement->isDisplayed(), 'Element is displayed');
            $this->assertEquals('#', $deactiveElementAnchor->getAttribute('href'), 'Anchor href is #');
        } catch (Throwable $e) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            echo $e->getMessage();
            debug_print_backtrace();
            $this->assertFalse(true);
        }
    }

    public function testCanDeactivateNewUser()
    {
        // Delete all users but superadmin
        User::model()->deleteAll('uid NOT IN (1)');
        // Insert new user
        $uid = User::insertUser(
            $new_user = 'newuser',
            $new_pass = 'asd',
            $new_full_name = 'New user',
            $parent_user = 1,
            $new_email = 'new@user.com'
        );
        $user = User::model()->findByPk($uid);
        $this->assertEquals(1, (int) $user->user_status, 'User status is 1');

        // Go to User Management page
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $web = self::$webDriver;
        $url = $urlMan->createUrl('userManagement/index');
        $web->get($url);

        // Find row for new user
        $uidTds = $web->findManyByCss('.uid');
        $this->assertCount(2, $uidTds, 'Found exactly two uids');

        // Get parent, which is the table row
        $row = $uidTds[1]->findElement(WebDriverBy::xpath('..'));

        // Find action button
        $dropdownButton = $row->findElement(WebDriverBy::cssSelector('.dropdown.ls-action_dropdown'));
        $dropdownButton->click();

        // Find ul
        $id = $dropdownButton->getAttribute('id');
        $parts = explode('_', $id);
        $this->assertCount(2, $parts);

        // Get belonging <ul>
        $dropdownMenuItems = $web->findManyByCss('#dropdownmenu_' . (int) $parts[1] . ' li');
        $deactiveElement = $dropdownMenuItems[1];
        $deactiveElementAnchor = $deactiveElement
            ->findElement(
                WebDriverBy::cssSelector('a')
            );

        // Click on "Deactivate"
        $deactiveElementAnchor->click();

        // Wait for modal
        $web->waitById('UserManagement-action-modal');

        // Click on "Save"
        $modal = $web->findById('UserManagement-action-modal');
        $saveButton = $modal->findElement(WebDriverBy::cssSelector('.modal-footer .btn.btn-primary'));
        $saveButton->click();

        // Check database
        $user = User::model()->findByPk($uid);
        $this->assertEquals(0, (int) $user->user_status, 'User status is 0');
    }

    public function testMassiveActionDeactivate()
    {
        // Delete all users but superadmin
        User::model()->deleteAll('uid NOT IN (1)');
        // Insert new user
        $uid = User::insertUser(
            $new_user = 'newuser',
            $new_pass = 'asd',
            $new_full_name = 'New user',
            $parent_user = 1,
            $new_email = 'new@user.com'
        );
        $user = User::model()->findByPk($uid);
        $this->assertEquals(1, (int) $user->user_status, 'User status is 1');

        // Go to User Management page
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $web = self::$webDriver;
        $url = $urlMan->createUrl('userManagement/index');
        $web->get($url);

        // Find row for new user
        $uidTds = $web->findManyByCss('.uid');
        $this->assertCount(2, $uidTds, 'Found exactly two uids');

        // Get parent, which is the table row
        $row = $uidTds[1]->findElement(WebDriverBy::xpath('..'));

        $checkbox = $row->findElement(WebDriverBy::cssSelector('.usermanagement--selector-userCheckbox'));
        $checkbox->click();

        // Open massive action menu
        $web->findByCss('.massiveAction')->click();

        // Click "Edit status"
        $web->findByLinkText('Edit status')->click();

        // Wait for modal to show
        $web->waitById('massive-actions-modal-usermanagement--identity-gridPanel-batchStatus-3');

        // Choose "Deactivate" in dropdown
        (new WebDriverSelect($web->findByCss('select[name=status_selector]')))->selectByValue('deactivate');

        // Click "Apply"
        $web->findByLinkText('Apply')->click();

        // Check database for result
        $user = User::model()->findByPk($uid);
        $this->assertEquals(0, (int) $user->user_status, 'User status is 0');
    }
}
