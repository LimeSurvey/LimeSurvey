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
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * @package ls\tests
 */
class TestBaseClassView extends TestBaseClassWeb
{

    /**
     * 
     */
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        $username = getenv('ADMINUSERNAME');
        if (!$username) {
            $username = 'admin';
        }

        $password = getenv('PASSWORD');
        if (!$password) {
            $password = 'password';
        }

        self::adminLogin($username, $password);
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
        $filename = null;

        try {
            $element = self::$webDriver->wait(2)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::id('action::' . $name)
                )
            );
        } catch (\Exception $e) {
            //throw new Exception($e->getMessage());
            $screenshot = self::$webDriver->takeScreenshot();
            file_put_contents(self::$screenshotsFolder. '/'.$name.'.png', $screenshot);
        }
        $body = self::$webDriver->findElement(WebDriverBy::tagName('body'));
        var_dump($body->getText());
        $this->assertNotEmpty(
            $element,
            'Possible screenshot at ' . $filename . PHP_EOL .
            sprintf(
                'FAILED viewing %s on route %s, full url %s',
                $name,
                $view['route'],
                $url
            )
        );
    }
}
