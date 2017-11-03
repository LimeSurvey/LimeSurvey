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
     * @var array
     */
    protected $adminViews;

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
        $this->adminViews = require __DIR__."/data/adminViews.php";

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
    public function openView($view){
        $domain = getenv('DOMAIN');
        if (empty($domain)) {
            $domain = '';
        }
        $url = "http://{$domain}/index.php/admin/".$view['route'];
        return $this->webDriver->get($url);
    }

    public function adminLogin($userName,$passWord){
        $this->openView($this->adminViews['login']);
    }

}
