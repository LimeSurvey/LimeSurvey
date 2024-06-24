<?php

namespace ls\tests;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use User;

/**
 * Manage users.
 * @since 2021-09-19
 * @group createuser
 */
class UserManagementTest extends TestBaseClassWeb
{
    protected function setUp(): void
    {
        parent::setUp();
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
        self::adminLogin($username, $password);
    }

    protected function tearDown(): void
    {
        $deleteCondition = App()->db->getCommandBuilder()->createInCondition('{{users}}', 'users_name', ['testuser1', 'testuser2', 'testuser3']);
        User::model()->deleteAll($deleteCondition);
        self::adminLogout();
        parent::tearDown();
    }

    /**
     * Create a user
     */
    public function testCreateUser()
    {
        $username = "testuser1";
        $fullname = "Test User 1";
        $email = "testuser1@example.com";

        try {
            $urlMan = \Yii::app()->urlManager;
            $web = self::$webDriver;

            // Go to User Management page
            $url = $urlMan->createUrl('userManagement/index');
            $web->get($url);

            // Click on "Add user" button.
            $addUserButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//*[text()[contains(.,'Add user')]][self::button or self::a]")
                )
            );
            // Even though the "wait until elementToBeClickable" considers the button
            // is clickable, it seems sometimes the modal backdrop is still there. So we wait a second.
            sleep(1);
            $addUserButton->click();

            // Wait for "Add user" modal
            $this->waitForModal('Add user');

            // Fill in basic data.
            $this->fillInputById("User_Form_users_name", $username);
            $this->fillInputById("User_Form_full_name", $fullname);
            $this->fillInputById("User_Form_email", $email);

            // Enable "Set password now" to avoid mailing errors
            $setPasswordSwitch = self::$webDriver->findElement(
                WebDriverBy::cssSelector('label[for="utility_set_password_yes"]')
            );
            $setPasswordSwitch->click();

            // Get suggested password
            $suggestedPasswordInput = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::name("random_example_password")
                )
            );
            $suggestedPassword = $suggestedPasswordInput->getAttribute('value');
            // TODO: Remove this when suggestions are fixed
            // Sometimes the suggestion doesn't have numbers, which makes the test fail
            $suggestedPassword = $suggestedPassword . "1";

            // Fill in the password and confirmation.
            $this->fillInputById("User_Form_password", $suggestedPassword);
            $this->fillInputById("password_repeat", $suggestedPassword);

            // Click "Add".
            $save = self::$webDriver->findElement(WebDriverBy::id('submitForm'));
            $save->click();

            // Wait for "Edit Permissions" modal
            $this->waitForModal('Edit permissions');

            // Click "Save".
            $save = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('permission-modal-submitForm')
                )
            );
            $save->click();
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#UserManagement-action-modal:not(.grid-view-loading)')
                )
            );
            // Make sure the user was saved in database.
            $users = User::model()->findAllByAttributes(['users_name' => $username]);
            $this->assertCount(1, $users);

            $user = $users[0];

            // Check basic attributes
            $this->assertEquals($fullname, $user->full_name);
            $this->assertEquals($email, $user->email);
            $this->assertEquals(1, (int) $user->user_status);

            // Test login
            self::adminLogout();
            self::adminLogin($username, $suggestedPassword);

            // Check that the user menu is present
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//a[text()[contains(.,'{$username}')]]")
                )
            );
        } catch (\Throwable $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Create a user with expiration date in the future
     */
    public function testCreateUserWithExpiration()
    {
        $username = "testuser2";
        $fullname = "Test User 2";
        $email = "testuser2@example.com";

        // Define expiration date in default frontend and db formats
        $dateformatdetails = getDateFormatData(1);
        $expiration = date($dateformatdetails['phpdate'] . ' H:i', strtotime("+1 day"));
        $datetimeobj = new \Date_Time_Converter($expiration, $dateformatdetails['phpdate'] . ' H:i');
        $expirationDbValue = $datetimeobj->convert("Y-m-d H:i:s");

        try {
            $urlMan = \Yii::app()->urlManager;
            $web = self::$webDriver;

            // Go to User Management page
            $url = $urlMan->createUrl('userManagement/index');
            $web->get($url);

            // Click on "Add user" button.
            $addUserButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//*[text()[contains(.,'Add user')]][self::button or self::a]")
                )
            );
            // Even though the "wait until elementToBeClickable" considers the button
            // is clickable, it seems sometimes the modal backdrop is still there. So we wait a second.
            sleep(1);
            $addUserButton->click();

            // Wait for "Add user" modal
            $this->waitForModal('Add user');

            // Fill in basic data.
            $this->fillInputById("User_Form_users_name", $username);
            $this->fillInputById("User_Form_full_name", $fullname);
            $this->fillInputById("User_Form_email", $email);

            // Fill in the expiration date.
            $this->fillDateById('expires', $expiration);

            // Enable "Set password now" to avoid mailing errors
            $setPasswordSwitch = self::$webDriver->findElement(
                WebDriverBy::cssSelector('label[for="utility_set_password_yes"]')
            );
            $setPasswordSwitch->click();

            // Get suggested password
            $suggestedPasswordInput = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::name("random_example_password")
                )
            );
            $suggestedPassword = $suggestedPasswordInput->getAttribute('value');
            // TODO: Remove this when suggestions are fixed
            // Sometimes the suggestion doesn't have numbers, which makes the test fail
            $suggestedPassword = $suggestedPassword . "1";

            // Fill in the password and confirmation.
            $this->fillInputById("User_Form_password", $suggestedPassword);
            $this->fillInputById("password_repeat", $suggestedPassword);

            // Click "Add".
            $save = self::$webDriver->findElement(WebDriverBy::id('submitForm'));
            $save->click();

            // Wait for "Edit Permissions" modal
            $this->waitForModal('Edit permissions');

            // Click "Save".
            $save = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('permission-modal-submitForm')
                )
            );
            $save->click();
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#UserManagement-action-modal:not(.grid-view-loading)')
                )
            );
            // Make sure the user was saved in database.
            $users = User::model()->findAllByAttributes(['users_name' => $username]);
            $this->assertCount(1, $users);

            $user = $users[0];

            // Check basic attributes
            $this->assertEquals($fullname, $user->full_name);
            $this->assertEquals($email, $user->email);
            $this->assertEquals($expirationDbValue, $user->expires);

            // Test login
            self::adminLogout();
            self::adminLogin($username, $suggestedPassword);

            // Check that the user menu is present
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//a[text()[contains(.,'{$username}')]]")
                )
            );
        } catch (\Throwable $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    /**
     * Create a user with expiration date in the past
     */
    public function testCreateUserExpired()
    {
        $username = "testuser3";
        $fullname = "Test User 3";
        $email = "testuser3@example.com";

        // Define expiration date in default frontend and db formats
        $dateformatdetails = getDateFormatData(1);
        $expiration = date($dateformatdetails['phpdate'] . ' H:i', strtotime("-1 day"));
        $datetimeobj = new \Date_Time_Converter($expiration, $dateformatdetails['phpdate'] . ' H:i');
        $expirationDbValue = $datetimeobj->convert("Y-m-d H:i:s");

        try {
            $urlMan = \Yii::app()->urlManager;
            $web = self::$webDriver;

            // Go to User Management page
            $url = $urlMan->createUrl('userManagement/index');
            $web->get($url);

            // Click on "Add user" button.
            $addUserButton = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::xpath("//*[text()[contains(.,'Add user')]][self::button or self::a]")
                )
            );
            // Even though the "wait until elementToBeClickable" considers the button
            // is clickable, it seems sometimes the modal backdrop is still there. So we wait a second.
            sleep(1);
            $addUserButton->click();

            // Wait for "Add user" modal
            $this->waitForModal('Add user');

            // Fill in basic data.
            $this->fillInputById("User_Form_users_name", $username);
            $this->fillInputById("User_Form_full_name", $fullname);
            $this->fillInputById("User_Form_email", $email);

            // Fill in the expiration date.
            $this->fillDateById('expires', $expiration);

            // Enable "Set password now" to avoid mailing errors
            $setPasswordSwitch = self::$webDriver->findElement(
                WebDriverBy::cssSelector('label[for="utility_set_password_yes"]')
            );
            $setPasswordSwitch->click();

            // Get suggested password
            $suggestedPasswordInput = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::name("random_example_password")
                )
            );
            $suggestedPassword = $suggestedPasswordInput->getAttribute('value');
            // TODO: Remove this when suggestions are fixed
            // Sometimes the suggestion doesn't have numbers, which makes the test fail
            $suggestedPassword = $suggestedPassword . "1";

            // Fill in the password and confirmation.
            $this->fillInputById("User_Form_password", $suggestedPassword);
            $this->fillInputById("password_repeat", $suggestedPassword);

            // Click "Add".
            $save = self::$webDriver->findElement(WebDriverBy::id('submitForm'));
            $save->click();

            // Wait for "Edit Permissions" modal
            $this->waitForModal('Edit permissions');

            // Click "Save".
            $save = self::$webDriver->wait()->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('permission-modal-submitForm')
                )
            );
            $save->click();
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::cssSelector('#UserManagement-action-modal:not(.grid-view-loading)')
                )
            );
            // Make sure the user was saved in database.
            $users = User::model()->findAllByAttributes(['users_name' => $username]);
            $this->assertCount(1, $users);

            $user = $users[0];

            // Check basic attributes
            $this->assertEquals($fullname, $user->full_name);
            $this->assertEquals($email, $user->email);
            $this->assertEquals($expirationDbValue, $user->expires);

            // Test login
            self::adminLogout();
            try {
                self::adminLogin($username, $suggestedPassword);
            } catch (Exception $e) {
                // Check that the login failed
                self::$webDriver->wait(5)->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::cssSelector('.login-panel')
                    )
                );
            }

        } catch (\Throwable $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }

    protected function fillInputById($id, $value, $timeout = 10)
    {
        $input = self::$webDriver->wait($timeout)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id($id)
            )
        );
        $input->clear()->sendKeys($value);
    }

    protected function fillDateById($id, $value, $timeout = 10)
    {
        $input = self::$webDriver->wait($timeout)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id($id)
            )
        );
        $input->click();
        $input->sendKeys(WebDriverKeys::DELETE);
        $otherInput = self::$webDriver->findElement(WebDriverBy::id('User_Form_full_name'));
        $input->clear()->sendKeys($value);
        // click on other input field to close the datepicker
        $otherInput->click();
    }

    protected function waitForModal($title, $timeout = 10)
    {
        self::$webDriver->wait($timeout)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::xpath("//*[text()[contains(.,'{$title}')]][contains(@class, 'modal-title') or contains(@class, 'modal-header')]")
            )
        );
    }

    protected static function adminLogout()
    {
        $url = self::getUrl(['login', 'route'=>'authentication/sa/logout']);
        self::openView($url);
    }
}
