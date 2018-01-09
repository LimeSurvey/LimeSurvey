<?php
namespace ls\tests\acceptance\admin;

use ls\tests\TestBaseClass;

/**
 * @since 2017-06-13
 * @group tempconf
 * @group template
 */
class TemplateConfigurationTest extends TestBaseClass
{
    /**
     * Issue #12795.
     */
    public function testCopyMinimalTemplate()
    {
        $tempConf = \TemplateConfiguration::getInstanceFromTemplateName('default');
        $tempConf->prepareTemplateRendering('default');

        // FIXME

        // No PHP notices.
        $this->assertTrue(true);
    }
}
