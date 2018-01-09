<?php
namespace ls\tests\acceptance\admin;

<<<<<<< HEAD:tests/models/TemplateConfigurationTest.php
namespace ls\tests;
=======
use ls\tests\TestBaseClass;
>>>>>>> 10b0e2b... DEV: restructure tests: move Models tests:tests/acceptance/admin/TemplateConfigurationTest.php

/**
 * @since 2017-06-13
 * @group tempconf
 * @group template
 */
class TemplateConfigurationTest extends TestBaseClass
{
    /**
     * Issue #12795.
     * @throws \CException
     */
    public function testCopyMinimalTemplate()
    {
        \Yii::import('application.helpers.globalsettings_helper', true);
        $tempConf = \TemplateConfiguration::getInstanceFromTemplateName('default');
        $tempConf->prepareTemplateRendering('default');

        // FIXME

        // No PHP notices.
        $this->assertTrue(true);
    }
}
