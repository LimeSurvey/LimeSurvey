<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * @since 2017-11-24
 */
class InstallationControllerTest extends TestBaseClassWeb
{
    /**
     * Setup
     */
    public static function setupBeforeClass(): void
    {
        if (getenv('LOCAL_TEST')) {
            self::markTestSkipped();
        }
        // NB: Does not call parent, because there might not
        // be a database (happens if this test is run multiple
        // times with failures).
        self::$testHelper = new TestHelper();
        self::$webDriver = self::$testHelper->getWebDriver();
        self::$domain = getenv('DOMAIN');
    }

    /**
     *
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $configFile = \Yii::app()->getBasePath() . '/config/config.php';
        if (file_exists($configFile)) {
            self::$testHelper->connectToOriginalDatabase();
        }
    }

    /**
     *
     * @throws \CException
     */
    public function testBasic()
    {
        //$this->checkFolders();

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
            $dbpwd = 'root'; // See https://github.com/actions/virtual-environments/blob/main/images/linux/Ubuntu1804-README.md#mysql
            echo 'Default to database password "root". Use DBPASSWORD=... from command-line to override this.' . PHP_EOL;
        }
        $dbLocation = getenv('DBLOCATION');
        if (!$dbLocation) {
            $dbLocation = 'localhost';
            echo 'Default to database location "localhost". Use DBLOCATION=... from command-line to override this.' . PHP_EOL;
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
        
        exec("sudo chmod -R 777 ./tmp"); // Add chmod 777, needed for CI pipeline

        // Run installer.
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl('');
        \Yii::import('application.helpers.common_helper', true);
        $installerForm = new \InstallerConfigForm();
        $installerForm->dbtype = \InstallerConfigForm::DB_TYPE_MYSQL;

        try {
            // Installer start page.
            self::$webDriver->get($url);

            // Click "Start installation".
            $start = self::$webDriver->findElement(WebDriverBy::id('ls-start-installation'));
            self::$webDriver->click($start);

            // Accept license.
            $accept = self::$webDriver->findElement(WebDriverBy::id('ls-accept-license'));
            self::$webDriver->click($accept);

            // Click next at pre-check.
            $next = self::$webDriver->wait(120)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::id('ls-next')
                )
            );
            self::$webDriver->click($next);

            // Fill in database form.
            $dbuserDbType = self::$webDriver->findElement(WebDriverBy::cssSelector('select[name="InstallerConfigForm[dbtype]"] option[value="'.$installerForm->dbtype.'"]'));
            $dbuserInput = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbuser]"]'));
            $dbpwdInput  = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbpwd]"]'));
            $dbnameInput = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbname]"]'));
            $dbLocationInput = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dblocation]"]'));
            $dbEngine = self::$webDriver->findElement(WebDriverBy::cssSelector('select[name="InstallerConfigForm[dbengine]"] option[value="'.$installerForm->dbengine.'"]'));

            self::$webDriver->click($dbuserDbType);
            self::$webDriver->click($dbEngine);
            $dbuserInput->clear()->sendKeys($dbuser);
            $dbpwdInput->clear()->sendKeys($dbpwd);
            $dbLocationInput->clear()->sendKeys($dbLocation);
            $dbnameInput->sendKeys($databaseName);

            // Click next.
            $next = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::id('ls-next')
                )
            );
            self::$webDriver->click($next);

            // Click "Create database".
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::cssSelector('input[type="submit"]')
                )
            );
            self::$webDriver->click($button);


            // Click "Populate".
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::cssSelector('input[type="submit"]')
                )
            );
            self::$webDriver->click($button);

            // Fill in admin username/password.
            $adminLoginName = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::cssSelector('input[name="InstallerConfigForm[adminLoginName]"]')
                )
            );
            $adminLoginPwd  = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[adminLoginPwd]"]'));
            $confirmPwd     = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[confirmPwd]"]'));
            $adminLoginName->clear()->sendKeys($username);
            $adminLoginPwd->clear()->sendKeys($password);
            $confirmPwd->clear()->sendKeys($password);


            // Confirm optional settings (admin password etc).
            $button = self::$webDriver->wait(10)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::cssSelector('input[type="submit"]')
                )
            );
            self::$webDriver->click($button);

            // Go to administration.
            $button = self::$webDriver->findElement(WebDriverBy::id('ls-administration'));
            self::$webDriver->click($button);

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

            $this->assertTrue(true, 'We made it!');
        } catch (NoSuchElementException $ex) {
            self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
            $this->assertFalse(
                true,
                self::$testHelper->javaTrace($ex)
            );
        }
    }
}
