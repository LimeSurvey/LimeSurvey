<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;
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
        // NB: Does not call parent, because there might not
        // be a database (happens if this test is run multiple
        // times).
        self::$testHelper = new TestHelper();
        self::$webDriver = self::$testHelper->getWebDriver();
        self::$domain = getenv('DOMAIN');
    }

    public static function teardownAfterClass()
    {
        self::$testHelper->connectToOriginalDatabase();
    }

    /**
     *
     * @throws \CException
     * @throws \Exception
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

        $dbuser = getenv('DBUSER');
        if (!$dbuser) {
            $dbuser = 'root';
            echo 'Default to database user "root". Use DBUSER=... from command-line to override this.' . PHP_EOL;
        }
        $dbpwd = getenv('DBPASSWORD');
        if (!$dbpwd) {
            $dbpwd = '';
            echo 'Default to empty database password. Use DBPASSWORD=... from command-line to override this.' . PHP_EOL;
        }

        if (file_exists($configFile)) {
            // Delete possible previous database.
            try {
                $dbo = \Yii::app()->getDb();
                $dbo->createCommand('DROP DATABASE ' . $databaseName)->execute();
                $dbo->createCommand('CREATE DATABASE ' . $databaseName)->execute();
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

        try {

            // Installer start page.
            self::getUrl(['route'=>'']);

            // Click "Start installation".
            self::findAndClick(WebDriverBy::id('ls-start-installation'));

            // Accept license.
            self::findAndClick(WebDriverBy::id('ls-accept-license'));

            // Click next at pre-check.
            self::findAndClick(WebDriverBy::id('ls-next'));

            // Fill in database form.
            $dbuserInput = self::find(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbuser]"]'));
            $dbpwdInput  = self::find(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbpwd]"]'));
            $dbnameInput = self::find(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbname]"]'));

            $dbuserInput->clear()->sendKeys($dbuser);
            $dbpwdInput->clear()->sendKeys($dbpwd);
            $dbnameInput->sendKeys($databaseName);

            // Click next.
            self::findAndClick(WebDriverBy::id('ls-next'));

            // Click "Create database".
            self::findAndClick(WebDriverBy::cssSelector('input[type="submit"]'));

            // Click "Populate".
            self::findAndClick(WebDriverBy::cssSelector('input[type="submit"]'));

            // Fill in admin username/password.
            $adminLoginName = self::find(WebDriverBy::cssSelector('input[name="InstallerConfigForm[adminLoginName]"]'));
            $adminLoginPwd  = self::find(WebDriverBy::cssSelector('input[name="InstallerConfigForm[adminLoginPwd]"]'));
            $confirmPwd     = self::find(WebDriverBy::cssSelector('input[name="InstallerConfigForm[confirmPwd]"]'));
            $adminLoginName->clear()->sendKeys($username);
            $adminLoginPwd->clear()->sendKeys($password);
            $confirmPwd->clear()->sendKeys($password);

            // Confirm optional settings (admin password etc).
            self::findAndClick(WebDriverBy::cssSelector('input[type="submit"]'));

            // Go to administration.
            self::findAndClick(WebDriverBy::id('ls-administration'));

            // Set debug=2
            /* TODO: Can't write to config.php after installation.
            $configFile = \Yii::app()->getBasePath() . '/config/config.php';
            $data = file($configFile);
            $data = array_map(function($data) {
                  return stristr($data, "'debug'=>0") ? "'debug'=>2," : $data;
            }, $data);
            $output = [];
            exec('chmod 777 ' . $configFile, $output);
            var_dump($output);
            $result = file_put_contents($configFile, implode('', $data));
            $this->assertTrue($result > 0, 'Wrote config');
             */

            // Reset urlManager to adapt to latest config.
            $config = require($configFile);
            $urlMan = \Yii::app()->urlManager;
            $urlMan->setUrlFormat($config['components']['urlManager']['urlFormat']);

            // Login.
            self::adminLogin($username, $password);
        } catch (NoSuchElementException $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, (new \ReflectionClass($this))->getShortName() . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
