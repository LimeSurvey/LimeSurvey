<?php

namespace ls\tests;

use TemplateConfiguration;

/**
 * @since 2017-06-13
 * @group tempconf
 * @group template
 */
class TemplateConfigurationTest extends TestBaseClass
{

    /**
     * @var TemplateConfiguration
     */
    private $templateConfiguration;

    /**
     * Issue #12795.
     * @throws \CException
     */
    public function testCopyMinimalTemplate()
    {
        \Yii::import('application.helpers.globalsettings_helper', true);
        $tempConf = \TemplateConfiguration::getInstanceFromTemplateName('default');
        $tempConf->prepareTemplateRendering('default');

        // No PHP notices.
        $this->assertTrue(true);
    }
}
