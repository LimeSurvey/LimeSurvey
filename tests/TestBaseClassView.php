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
 * Class TestBaseClassWeb
 * this is the base class for functional tests that need browser simulation
 * @package ls\tests
 */
class TestBaseClassView extends TestBaseClassWeb
{
    private static $importId;

    public function setUp()
    {
        parent::setUp();
        $this->adminLogin('admin','password');
    }

    /**
     * @param string $name
     * @param array $view
     */
    protected function findViewTag($name,$view){
        $this->openView($view);
        $element = null;

        $screenshot = $this->webDriver->takeScreenshot();
        file_put_contents(__DIR__ . '/_output/'.$name.'.png', $screenshot);
        try{
            $element = $this->webDriver->findElement(WebDriverBy::id('action::'.$name));
        } catch (\Exception $e){
            //throw new Exception($e->getMessage());
        }
        $this->assertNotEmpty($element,sprintf('FAILED viewing %s on route %s',$name,$view['route']));


    }

}
