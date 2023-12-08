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
}
