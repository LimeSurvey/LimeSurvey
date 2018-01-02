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

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\TimeOutException;
use User;

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

    /** @var \User $user current user */
    protected static $user;

    /** @var int $superUserId */
    public static $superUserId = 1;

    /** @var string $noPermissionsUserPassword */
    protected static $noPermissionsUserPassword = 'myHardPassword';

    /** @var string $superUserUsername */
    public static $superUserUsername = 'admin';

    /** @var string $noPermissionsUserUsername */
    public static $noPermissionsUserUsername = 'noPermissionsUser';

    /** @var User $noPermissionsUser */
    public static $noPermissionsUser;

    /**
     * @var WebDriver $webDriver
     */
    protected static $webDriver;

    /**
     * @var string
     */
    protected static $domain;

    /** @var  string $url current url */
    protected $url;

    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $domain = getenv('DOMAIN');
        if (empty($domain)) {
            echo 'Must specify DOMAIN environment variable to run this test, like "DOMAIN=localhost/limesurvey" or "DOMAIN=limesurvey.localhost".';
            exit(12);
        }

        self::$domain = getenv('DOMAIN');

        self::setUpWebDriver();
        self::tearDownTestUsers();
        self::setUpNoPermissionsUser();
        self::deleteLoginTimeout();
    }

    /**
     * @throws \CDbException
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::$webDriver->quit();
        self::tearDownTestUsers();
    }

    /**
     * @throws \CDbException
     */
    private static function tearDownTestUsers(){
        $noPermissionsUser = User::findByUsername(self::$noPermissionsUserUsername);
        if($noPermissionsUser){
            $noPermissionsUser->delete();
        }
    }
    /**
     * @throws \Exception
     */
    private static function setUpNoPermissionsUser(){
        $user = new User();
        $user->users_name =self::$noPermissionsUserUsername;
        $user->email = 'no-permissions@example.com';
        $user->full_name = 'Iha Veno Permissioons';
        $user->setPassword(self::$noPermissionsUserPassword);
        if($user->save()){
            self::$noPermissionsUser = $user;
        }else{
            throw new \Exception('Could not create User: '.serialize($user->errors));
        }
    }

    /**
     * @throws \Exception
     */
    private static function setUpWebDriver(){
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
     * @param string $url
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
     * @throws \Exception
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
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
            self::$user = User::findByUsername($userName);
        } catch (TimeOutException $ex) {
            //$name =__DIR__ . '/_output/loginfailed.png';
            $shotName = self::takeScreenShot('FailedLogin');
            self::assertTrue(
                false,
                'Screenshot in '.$shotName . PHP_EOL .
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
            $shotName = self::takeScreenShot('FailedLogin');
            self::assertTrue(
                false,
                ' Screenshot in ' . $shotName . PHP_EOL .
                'Found no welcome jumbotron after login.'
            );
        }
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

    /**
     * @param WebDriverBy $selector
     * @param int $waitSecondsUntil
     * @return mixed
     * @throws \Exception
     */
    protected static function findAndClick($selector,$waitSecondsUntil = 2){
        try {
            $clickable = self::$webDriver->wait($waitSecondsUntil)->until(
                WebDriverExpectedCondition::elementToBeClickable($selector)
            );
            $clickable->click();
            sleep(1);
            self::takeScreenShot('AfterGoodClick');
            return $clickable;
        } catch (\Exception $ex) {
            self::takeScreenShot('FailedClick');
        }

    }

    /**
     * @param WebDriverBy $selector
     * @param int $waitSecondsUntil
     * @return WebDriverElement
     * @throws \Exception
     */
    protected static function find($selector,$waitSecondsUntil = 1){
        try {
            self::$webDriver->wait($waitSecondsUntil)->until(
                WebDriverExpectedCondition::presenceOfElementLocated($selector)
            );
            $element = self::$webDriver->findElement($selector);
            return $element;
        } catch (\Exception $ex) {
            self::takeScreenShot('FailedFindElement');
            throw $ex;
        }

    }

    /**
     * @param $name
     * @param $view
     * @return WebDriverElement
     * @throws \Exception
     */
    protected function openAndFindViewTag($name, $view){
        $this->url = $this->getUrl($view);
        $this->openView($this->url);
        return $this->findViewTag($name);
    }

    /**
     * @param string $name
     * @return WebDriverElement
     * @throws \Exception
     */
    protected function findViewTag($name)
    {
        $element = self::find(WebDriverBy::id('action::' . $name));
        return $element;
    }

    public static function takeScreenShot($name){
        $screenshot = self::$webDriver->takeScreenshot();
        $filename = self::$screenshotsFolder .'/'.microtime(true).'_'.$name.'.png';
        file_put_contents($filename, $screenshot);
        return $filename;
    }
}
