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
        // times with failures).
        self::$testHelper = new TestHelper();
        self::$webDriver = self::$testHelper->getWebDriver();
        self::$domain = getenv('DOMAIN');
    }

    /**
     *
     */
    public static function teardownAfterClass()
    {
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
            $dbpwd = '';
            echo 'Default to empty database password. Use DBPASSWORD=... from command-line to override this.' . PHP_EOL;
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

        try {

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
            $dbuserDbType = self::$webDriver->findElement(WebDriverBy::cssSelector('select[name="InstallerConfigForm[dbtype]"] option[value="mysql"]'));
            $dbuserInput = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbuser]"]'));
            $dbpwdInput  = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbpwd]"]'));
            $dbnameInput = self::$webDriver->findElement(WebDriverBy::cssSelector('input[name="InstallerConfigForm[dbname]"]'));
            
            $dbuserDbType->click();
            $dbuserInput->clear()->sendKeys($dbuser);
            $dbpwdInput->clear()->sendKeys($dbpwd);
            $dbnameInput->sendKeys($databaseName);

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

    /**
     * Check that upload/tmp folders are writable.
     * @todo Does not work.
     */
    public function checkFolders()
    {
        $instContr = new \InstallerController('dummyvalue');
        $data = [];
        $folder = \Yii::app()->getConfig('tempdir') . '/';
        $tempdirIsWritable = $instContr->checkDirectoryWriteable(
            $folder,
            $data,
            'tmpdir',
            'tperror',
            true
        );
        $this->assertTrue($tempdirIsWritable, 'Can write to tmp/');

        $folder = \Yii::app()->getConfig('uploaddir') . '/';
        $uploadIsWritable = $instContr->checkDirectoryWriteable(
            $folder,
            $data,
            'uploaddir',
            'uerror',
            true
        );
        $this->assertTrue($uploadIsWritable, 'Can write to upload/');
    }
}
