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
 * Class AdminViewsTest
 * This test loops through all basic admin view pages and cheks if they open withour errors
 *
 * @package ls\tests
 */
class AdminViewsTest extends TestBaseClassWeb
{

    public function testAdminViews(){
        $this->adminLogin('admin','password');
        foreach ($this->adminViews as $name => $view){
            if($name=='login'){
                continue;
            }
            $this->openView($view);
            $element = null;
            try{
                $element = $this->webDriver->findElement(WebDriverBy::id('action::'.$name));
            } catch (\Exception $e){
                $screenshot = $this->webDriver->takeScreenshot();
                file_put_contents(__DIR__ . '/tmp.png', $screenshot);

                //throw new Exception($e->getMessage());
            }
            $this->assertNotEmpty($element,sprintf('FAILED viewing %s on route admin/%s',$name,$view['route']));
        }
    }



}