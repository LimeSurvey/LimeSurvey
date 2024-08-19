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

use Exception;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\UnrecognizedExceptionException;

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
    public static $webPort = 4444;

    /**
     * @var LimeSurveyWebDriver $webDriver
     */
    protected static $webDriver;

    /**
     * @var string
     */
    protected static $domain;

    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $domain = getenv('DOMAIN');
        if (empty($domain)) {
            echo 'Must specify DOMAIN environment variable to run this test, like "DOMAIN=localhost/limesurvey" or "DOMAIN=limesurvey.localhost".';
            exit(12);
        }

        self::$domain = getenv('DOMAIN');

        self::$webDriver = self::$testHelper->getWebDriver();

        if (empty(self::$webDriver)) {
            throw new \Exception('Could not connect to remote web driver');
        }

        // Implicit timout so we don't have to wait manually.
        self::$webDriver->manage()->timeouts()->implicitlyWait(5);

        // Anyone can preview surveys.
        self::$testHelper->enablePreview();
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
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
        //this is for testing new controllers (REFACTORING Controllers)
        if(isset($view['noAdminInFront']) && $view['noAdminInFront']){
            $url = $urlMan->createUrl($view['route']);
        }else {
            $url = $urlMan->createUrl('admin/' . $view['route']);
        }
        return $url;
    }

    /**
     * @return string
     */
    protected function getSurveyUrl($lang = 'en')
    {
        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
        $url = $urlMan->createUrl(
            'survey/index',
            [
                'sid' => self::$surveyId,
                'newtest' => 'Y',
                'lang' => $lang
            ]
        );
        return $url;
    }

    /**
     * @param string $userName
     * @param string $password
     * @param boolean $wait If true, wait for and disregard popups at first login; useful to set to false during local testing and development
     * @return void
     * @throws \Exception
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
    public static function adminLogin($userName, $password, $wait = true)
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
        self::$webDriver->click($submit);

        if ($wait) {
            self::$webDriver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(
                    WebDriverBy::className('welcome')
                )
            );
            self::ignoreWelcomeModal();
            self::ignoreAdminNotification();
            sleep(3);
            self::ignoreAdminNotification();
        }

        /*
        try {
            sleep(1);
            self::$webDriver->wait(5)->until(
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
         */
    }

    /**
     * Delete failed login attempts.
     * @throws \CDbException
     */
    protected static function deleteLoginTimeout()
    {
        $dbo = \Yii::app()->getDb();
        $dbo
            ->createCommand('DELETE FROM {{failed_login_attempts}}')
            ->execute();
    }

    protected function waitForElementShim(&$driver, $CSSelementSelectorString, $timeout = 10) {
        $element = false;
        $timeoutCounter = 0;
        do {
            try{
                $element = $driver->findElement(WebDriverBy::cssSelector($CSSelementSelectorString));
            } catch(NoSuchElementException $exception) {
                $timeoutCounter++;
                sleep(1);
            }
        } while($element === false && $timeoutCounter < $timeout);

        if($element === false) {
            throw new NoSuchElementException("Element not in scope after ".$timeout." seconds");
        }

        return $element;
    }

    /**
     * @return void
     */
    protected static function ignoreAdminNotification()
    {
        // Ignore password warning.
        try {
            try {
                self::$webDriver->wait(3)->until(
                    WebDriverExpectedCondition::visibilityOfElementLocated(
                        WebDriverBy::id('admin-notification-modal')
                    )
                );
            } catch (TimeoutException $ex) {
                // ignore
                return;
            }
            $button = self::$webDriver->wait()->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::cssSelector('#admin-notification-modal button.btn-outline-secondary')
                )
            );
            // modal fade in is 1 second.
            sleep(1);
            self::$webDriver->click($button);
        } catch (Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder . '/ignoreAdminNotification.png';
            file_put_contents($filename, $screenshot);
            self::assertTrue(
                false,
                'Screenshot in ' . $filename . PHP_EOL . $ex->getMessage()
            );
        }
    }

    /**
     * Closes the welcome modal if present
     * @return void
     */
    protected static function ignoreWelcomeModal()
    {
        try {
            try {
                self::$webDriver->wait(3)->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::id('welcomeModal')
                    )
                );
            } catch (TimeoutException $ex) {
                // ignore
                return;
            }
            $button = self::$webDriver->wait()->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::cssSelector('#welcomeModal button.btn-outline-secondary')
                )
            );
            // modal fade in is 1 second.
            sleep(1);
            self::$webDriver->click($button);
        } catch (Exception $ex) {
            $screenshot = self::$webDriver->takeScreenshot();
            $filename = self::$screenshotsFolder . '/ignoreWelcomeModal.png';
            file_put_contents($filename, $screenshot);
            self::assertTrue(
                false,
                'Screenshot in ' . $filename . PHP_EOL . $ex->getMessage()
            );
        }
    }
}
