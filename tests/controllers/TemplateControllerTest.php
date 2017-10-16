<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * @since 2017-10-15
 * @group tempcontr
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
        @exec('rm -r ' . $newdirname);

        // Simulate a POST.
        $_POST['newname'] = $newname;
        $_POST['copydir'] = 'default';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $contr = new \templates(new DummyController('dummyid'));
        $contr->templatecopy();
        $template = \Template::model()->find('name = \'foobartest\'');
        $this->assertNotEmpty($template);
        $this->assertEquals('foobartest', $template->name);
    }

    /**
     * @todo Copy template folder that does not exist.
     */
    public function testCopyWrongFolder()
    {
    }
}
