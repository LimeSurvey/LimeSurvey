<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Manage users.
 * @since 2021-09-19
 * @group createuser
 */
class UserManagementTest extends TestBaseClassWeb
{
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
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

            self::ignoreWelcomeModal();
            self::ignoreAdminNotification();

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
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//*[text()[contains(.,'Add user')]][contains(@class, 'modal-title')]")
                )
            );

            // Fill in the username.
            $usernameInput = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id("User_Form_users_name")
                )
            );
            $usernameInput->clear()->sendKeys($username);

            // Fill in the full name.
            $fullnameInput = self::$webDriver->findElement(
                WebDriverBy::id("User_Form_full_name")
            );
            $fullnameInput->clear()->sendKeys($fullname);

            // Fill in the email.
            $emailInput = self::$webDriver->findElement(
                WebDriverBy::id("User_Form_email")
            );
            $emailInput->clear()->sendKeys($email);

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

            // Fill in the password.
            $passwordInput = self::$webDriver->findElement(
                WebDriverBy::id("User_Form_password")
            );
            $passwordInput->clear()->sendKeys($suggestedPassword);

            // Fill in the password confirmation.
            $passwordConfirmationInput = self::$webDriver->findElement(
                WebDriverBy::id("password_repeat")
            );
            $passwordConfirmationInput->clear()->sendKeys($suggestedPassword);

            // Click "Add".
            $save = self::$webDriver->findElement(WebDriverBy::id('submitForm'));
            $save->click();

            // Wait for "Edit Permissions" modal
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//*[text()[contains(.,'Edit permissions')]][contains(@class, 'modal-title')]")
                )
            );

            // Click "Save".
            $save = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('permission-modal-submitForm')
                )
            );
            $save->click();

            // Wait for "Saved successfully" modal
            self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::xpath("//*[text()[contains(.,'Saved successfully')]][contains(@class, 'modal-header')]")
                )
            );

            // Make sure the user was saved in database.
            $users = \User::model()->findAllByAttributes(['users_name' => $username]);
            $this->assertCount(1, $users);

            $user = $users[0];

            // Check basic attributes
            $this->assertEquals($fullname, $user->full_name);
            $this->assertEquals($email, $user->email);
        } catch (\Throwable $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
