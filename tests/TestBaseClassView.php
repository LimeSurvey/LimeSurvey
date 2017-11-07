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

use Facebook\WebDriver\WebDriverBy;

/**
 * @package ls\tests
 */
class TestBaseClassView extends TestBaseClassWeb
{

    /**
     * 
     */
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();

        $username = getenv('USERNAME');
        if (empty($username)) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (empty($password)) {
            $password = 'password';
        }

        $this->adminLogin($username, $password);
    }

    /**
     * @param string $name
     * @param array $view
     */
    protected function findViewTag($name, $view)
    {
        $url = $this->getUrl($view);
        $this->openView($url);
        $element = null;

        $screenshot = self::$webDriver->takeScreenshot();
        file_put_contents(self::$screenshotsFolder. '/'.$name.'.png', $screenshot);
        try {
            $element = self::$webDriver->findElement(WebDriverBy::id('action::'.$name));
        } catch (\Exception $e) {
            $screenshot = self::$webDriver->takeScreenshot();
            file_put_contents(__DIR__ . '/_output/'.$name.'.png', $screenshot);
            //throw new Exception($e->getMessage());
        }
        $this->assertNotEmpty(
            $element,
            sprintf(
                'FAILED viewing %s on route %s, full url %s',
                $name,
                $view['route'],
                $url
            )
        );
    }
}
