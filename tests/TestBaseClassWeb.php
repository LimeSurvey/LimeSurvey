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
    protected $webPort = 4444;

    /**
     * @var WebDriver $webDriver
     */
    protected $webDriver;

    public function setUp()
    {
        parent::setUp();

        if (empty(getenv('DOMAIN'))) {
            die('Must specify DOMAIN environment variable to run this test, like "DOMAIN=localhost/limesurvey" or "DOMAIN=limesurvey.localhost".');
        }

        $capabilities = DesiredCapabilities::phantomjs();
        $this->webDriver = RemoteWebDriver::create("http://localhost:{$this->webPort}/", $capabilities);
        $this->webDriver->manage()->window()->maximize();
    }

    /**
     * Tear down fixture.
     */
    public function tearDown()
    {
        // Close Firefox.
        $this->webDriver->quit();
    }

    /**
     * @param array $view
     * @return WebDriver
     */
    public function openView($url)
    {
        if (!is_string($url)) {
            throw new \Exception('$url must be a string, is ' . json_encode($url));
        }
        return $this->webDriver->get($url);
    }

    /**
     * Get URL to admin view.
     * @param array $view
     * @return string
     */
    public function getUrl(array $view)
    {
        $domain = getenv('DOMAIN');
        if (empty($domain)) {
            $domain = '';
        }

        $urlMan = \Yii::app()->urlManager;
        $urlMan->setBaseUrl('http://' . $domain . '/index.php');
        $url = $urlMan->createUrl('admin/' . $view['route']);
        return $url;
    }

    /**
     * @param string $userName
     * @param string $password
     * @return void
     */
    public function adminLogin($userName, $password)
    {
        $url = $this->getUrl(['route'=>'authentication/sa/login']);
        $this->openView($url);

        try {
            $this->webDriver->wait(2)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::id('user')
                )
            );
        } catch (TimeOutException $ex) {
            //$name =__DIR__ . '/_output/loginfailed.png';
            $screenshot = $this->webDriver->takeScreenshot();
            file_put_contents(__DIR__ .'/tmp.png', $screenshot);
            $this->assertTrue(
                false,
                sprintf(
                    'Could not login on url %s: Could not find element with id "user".',
                    $url
                )
            );
        }

        $userNameField = $this->webDriver->findElement(WebDriverBy::id("user"));
        $userNameField->clear()->sendKeys($userName);
        $passWordField = $this->webDriver->findElement(WebDriverBy::id("password"));
        $passWordField->clear()->sendKeys($password);

        $submit = $this->webDriver->findElement(WebDriverBy::name('login_submit'));
        $submit->click();
        try {
            $this->webDriver->wait(2)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::id('welcome-jumbotron')
                )
            );
        } catch (TimeOutException $ex) {
            $screenshot = $this->webDriver->takeScreenshot();
            file_put_contents(__DIR__ .'/tmp.png', $screenshot);
            $this->assertTrue(
                false,
                'Found no welcome jumbotron after login.'
            );
        }
    }
}
