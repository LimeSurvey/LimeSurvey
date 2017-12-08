<?php
/**
 *  LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

namespace ls\tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\Firefox\FirefoxPreferences;
use Facebook\WebDriver\Exception\WebDriverCurlException;

/**
 * Class TestBaseClassWeb
 * this is the base class for functional tests that need browser simulation
 * @package ls\tests
 */
class TestBaseClassWeb extends TestBaseClass
{
    /**
     * @var int web server port
     * TODO this should be in configuration somewhere
     */
    protected static $webPort = 4444;

    /**
     * @var WebDriver $webDriver
     */
    protected static $webDriver;

    /**
     * @var string
     */
    protected static $domain;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (empty(getenv('DOMAIN'))) {
            echo 'Must specify DOMAIN environment variable to run this test, like "DOMAIN=localhost/limesurvey" or "DOMAIN=limesurvey.localhost".';
            exit(12);
        }

        self::$domain = getenv('DOMAIN');

        // NB: Travis might be slow, better try more than once to connect.
        $tries = 0;
        $success = false;
        do {
            try {
                $host = 'http://localhost:4444/wd/hub'; // this is the default
                $capabilities = DesiredCapabilities::firefox();
                $profile = new FirefoxProfile();
                $profile->setPreference(FirefoxPreferences::READER_PARSE_ON_LOAD_ENABLED, false);
                // Open target="_blank" in new tab.
                $profile->setPreference('browser.link.open_newwindow', 3);
                $capabilities->setCapability(FirefoxDriver::PROFILE, $profile);
                self::$webDriver = RemoteWebDriver::create($host, $capabilities, 5000);
                $success = true;
            } catch (WebDriverCurlException $ex) {
                $tries++;
                sleep(1);
            }
        } while (!$success && $tries < 5);

        if (empty(self::$webDriver)) {
            throw new \Exception(
                sprintf(
                    'Could not connect to remote web driver, tried %d times.',
                    $tries
                )
            );
        }

        // Implicit timout so we don't have to wait manually.
        self::$webDriver->manage()->timeouts()->implicitlyWait(5);
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::$webDriver->quit();
    }

    /**
     * @param $url
     * @return WebDriver
     * @throws \Exception
     * @internal param array $view
     */
    public static function openView($url)
    {
        if (!is_string($url)) {
            throw new \Exception('$url must be a string, is ' . json_encode($url));
        }
        return self::$webDriver->get($url);
    }

    /**
     * Get URL to admin view.
     * @param array $view
     * @return string
     * @todo Rename to getAdminUrl.
     */
    public static function getUrl(array $view)
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl('admin/' . $view['route']);
        return $url;
    }

    /**
     * @param string $userName
     * @param string $password
     * @return void
     */
    public static function adminLogin($userName, $password)
    {
        $url = self::getUrl(['login', 'route'=>'authentication/sa/login']);
        self::openView($url);
        try {
            self::$webDriver->wait(5)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::id('user')
                )
            );
        } catch (TimeOutException $ex) {
            //$name =__DIR__ . '/_output/loginfailed.png';
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder .'/FailedLogin.png';
            file_put_contents($filename, $screenshot);
            self::assertTrue(
                false,
                ' Screenshot in ' . $filename . PHP_EOL .
                sprintf(
                    'Could not login on url %s: Could not find element with id "user".',
                    $url
                )
            );
        }
        $userNameField = self::$webDriver->findElement(WebDriverBy::id("user"));
        $userNameField->clear()->sendKeys($userName);
        $passWordField = self::$webDriver->findElement(WebDriverBy::id("password"));
        $passWordField->clear()->sendKeys($password);

        $submit = self::$webDriver->findElement(WebDriverBy::name('login_submit'));
        $submit->click();
        try {
            self::$webDriver->wait(2)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::id('welcome-jumbotron')
                )
            );
        } catch (TimeOutException $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder .'/FailedLogin.png';
            file_put_contents($filename, $screenshot);
            self::assertTrue(
                false,
                ' Screenshot in ' . $filename . PHP_EOL .
                'Found no welcome jumbotron after login.'
            );
        }
    }

    /**
     * Delete failed login attempts.
     */
    protected static function deleteLoginTimeout()
    {
        $dbo = \Yii::app()->getDb();
        $dbo
            ->createCommand('DELETE FROM {{failed_login_attempts}}')
            ->execute();
    }
}
