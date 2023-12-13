<?php

namespace ls\tests;

use Throwable;
use User;
use UserManagementController;
use Yii;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

/**
 * https://docs.phpunit.de/en/10.5/annotations.html#backupglobals
 *
 * @backupGlobals enabled
 */
class UserStatusControllerTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Permission to everything.
        \Yii::app()->session['loginID'] = 1;
    }

    public function testActionUserActivateDeactivate()
    {
        // Delete all users but superadmin
        User::model()->deleteAll('uid NOT IN (1)');
        // Insert new user
        $uid = User::insertUser(
            $new_user = 'newuser',
            $new_pass = 'newuser',
            $new_full_name = 'New user',
            $parent_user = 1,
            $new_email = 'new@user.com'
        );

        // Not good, should inject the request object instead.
        $_GET['userid'] = $uid;

        $controller = $this
            // Get mock of controller
            ->getMockBuilder(UserManagementController::class)
            // Only mock the renderPartial method
            ->onlyMethods(['renderPartial'])
            // Disable __construct
            ->disableOriginalConstructor()
            ->getMock();

        $controller
            ->method('renderPartial')
            // renderPartial just returns the $data argument
            ->willReturnCallback(fn ($a, $b) => $b);

        // Run the controller action.
        $data = $controller->actionUserActivateDeactivate();
        // Run assert on the result.
        $this->assertTrue($data['data']['success']);

        $user = User::model()->findByPk($uid);
        $this->assertEquals(0, (int) $user->user_status, 'User status is 0');
    }
}
