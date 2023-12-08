<?php

namespace ls\tests;

use Throwable;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class UserStatusTest extends TestBaseClassWeb
{
    // TODO: 
    // Check that user 1 cannot be deactivated
    // Check that you cannot deactive yourself
    // Deactivate massive action
    // Activate massive action
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
            $dropdownButton = $web->findByCss('.btn.btn-sm.btn-outline-secondary.ls-dropdown-toggle');
            $dropdownButton->click();

            // TODO: How to find the right dropdown menu for this dropdown button?
            $deactivateButton = $web->findByCss('.ri-user-unfollow-fill.text-danger');
        } catch (Throwable $e) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            echo $e->getMessage();
            debug_print_backtrace();
            $this->assertFalse(true);
        }
    }
}
