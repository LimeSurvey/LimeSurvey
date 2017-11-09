<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClass;
use ls\tests\DummyController;

/**
 * @since 2017-10-15
 * @group tempcontr
 * @group template
 */
class TemplateControllerTest extends TestBaseClass
{

    /**
     * Test copy a template.
     */
    public function testCopyTemplate()
    {
        \Yii::app()->session['loginID'] = 1;
        \Yii::import('application.controllers.admin.templates', true);
        \Yii::import('application.helpers.globalsettings_helper', true);

        // Clean up from last test.
        \Template::model()->deleteAll('name = \'foobartest\'');

        // Remove folder from last test.
        $newname = 'foobartest';
        $newdirname  = \Yii::app()->getConfig('usertemplaterootdir') . "/" . $newname;
        if (file_exists($newdirname)) {
            exec('rm -r ' . $newdirname);
        }

        // Simulate a POST.
        $_POST['newname'] = $newname;
        $_POST['copydir'] = 'default';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $contr = new \templates(new \ls\tests\DummyController('dummyid'));
        $contr->templatecopy();
        $template = \Template::model()->find('name = \'foobartest\'');
        $this->assertNotEmpty($template);
        $this->assertEquals('foobartest', $template->name);

        // Clean up.
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
