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

namespace ls\tests\acceptance\admin;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * @since 2017-10-15
 * @group tempcontr
 * @group template
 */
class TemplateControllerTest extends TestBaseClass
{

    /**
     * Test copy a template.
     * @group copytemplate
     */
    public function testCopyTemplate()
    {
        \Yii::app()->session['loginID'] = 1;
        \Yii::import('application.controllers.admin.themes', true);
        \Yii::import('application.helpers.globalsettings_helper', true);

        // Clean up from last test.
        $templateName = 'foobartest';
        \TemplateConfiguration::uninstall($templateName);
        \Template::model()->deleteAll('name = \'foobartest\'');
        \Permission::model()->deleteAllByAttributes(array('permission' => $templateName,'entity' => 'template'));

        // Remove folder from last test.
        $newname = 'foobartest';
        $newdirname  = \Yii::app()->getConfig('userthemerootdir') . "/" . $newname;
        if (file_exists($newdirname)) {
            exec('rm -r ' . $newdirname);
        }

        $config = require(\Yii::app()->getBasePath() . '/config/config-defaults.php');
        // Simulate a POST.
        $_POST['newname'] = $newname;
        // NB: If default theme is not installed, this test will fail.
        $_POST['copydir'] = $config['defaulttheme'];
        $_SERVER['SERVER_NAME'] = 'localhost';

        $contr = new \themes(new DummyController('dummyid'));
        $contr->templatecopy();

        $flashes = \Yii::app()->user->getFlashes();
        $this->assertEmpty($flashes, 'No flash messages');

        $template = \Template::model()->find(
            sprintf(
                'name = \'%s\'',
                $templateName
            )
        );
        $this->assertNotEmpty($template);
        $this->assertEquals($templateName, $template->name);



        // Clean up. //TODO tearDown
        \Template::model()->deleteAll('name = \'foobartest\'');
    }

    /**
     * @todo Copy template folder that does not exist.
     */
    /*
    public function testCopyWrongFolder()
    {
    }
     */
}
