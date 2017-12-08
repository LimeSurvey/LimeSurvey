<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\ElementNotVisibleException;

/**
 * @since 2017-11-24
 * @group inst
 */
class InstallationControllerTest extends TestBaseClassWeb
{
    /**
     * 
     */
    public static function setupBeforeClass()
    {
        parent::setUpBeforeClass();

        if (empty(getenv('DBPASSWORD'))) {
            self::assertTrue(
                false,
                'Must specify DBPASSWORD environment variable to run this test'
            );
        }

        if (empty(getenv('DBUSER'))) {
            self::assertTrue(
                false,
                'Must specify DBUSER environment variable to run this test'
            );
        }
    }
    /**
     * 
     */
    public function testBasic()
    {
        $configFile = \Yii::app()->getBasePath() . '/config/config.php';
        $databaseName = 'limesurvey';

        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }
        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        if (file_exists($configFile)) {
            // Delete possible previous database.
            try {
                $dbo = \Yii::app()->getDb();
                $dbo->createCommand('DROP DATABASE ' . $databaseName)->execute();
            } catch (\CDbException $ex) {
                $msg = $ex->getMessage();
                // Only this error is OK.
                self::assertTrue(
                    strpos($msg, "database doesn't exist") !== false,
                    'Could drop database. Error message: ' . $msg
                );
            }

            // Remove config.php if present.
            $result = unlink($configFile);
            $this->assertTrue($result, 'Could unlink config.php');
        }

        // Run installer.
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl('');

        // Installer start page.
        self::$webDriver->get($url);

        // Click "Start installation".
        $start = self::$webDriver->findElement(WebDriverBy::id('ls-start-installation'));
        $start->click();

        // Accept license.
        $accept = self::$webDriver->findElement(WebDriverBy::id('ls-accept-license'));
        $accept->click();

        // Click next at pre-check.
        $next = self::$webDriver->findElement(WebDriverBy::id('ls-next'));
        $next->click();

        // Fill in database form.
        $dbuser = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbuser]"]'));
        $dbpwd  = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbpwd]"]'));
        $dbname = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbname]"]'));
        $dbuser->sendKeys(getenv('DBUSER'));
        $dbpwd->sendKeys(getenv('DBPASSWORD'));
        $dbname->sendKeys($databaseName);

        // Click next.
        $next = self::$webDriver->findElement(WebDriverBy::id('ls-next'));
        $next->click();

        // Click "Create database".
        $button = self::$webDriver->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $button->click();

        // Click "Populate".
        $button = self::$webDriver->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $button->click();

        // Fill in admin username/password.
        $adminLoginName = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[adminLoginName]"]'));
        $adminLoginPwd  = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[adminLoginPwd]"]'));
        $confirmPwd     = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[confirmPwd]"]'));
        $adminLoginName->clear()->sendKeys($username);
        $adminLoginPwd->clear()->sendKeys($password);
        $confirmPwd->clear()->sendKeys($password);

        // Confirm optional settings (admin password etc).
        $button = self::$webDriver->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $button->click();

        // Go to administration.
        $button = self::$webDriver->findElement(WebDriverBy::id('ls-administration'));
        $button->click();

        // Login.
        self::adminLogin($username, $password);

        self::$testHelper->connectToOriginalDatabase();
    }
}
