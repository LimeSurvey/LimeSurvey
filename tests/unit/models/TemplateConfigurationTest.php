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

    /**
     * This test will turn of the ajaxmode.
     */
    public function testTurnOffAjaxMode()
    {
        $this->templateConfiguration = new TemplateConfiguration();
        $this->templateConfiguration->setAttribute('options', ['ajaxmode' => 'on']);

        $this->templateConfiguration->setAttribute('options', ['ajaxmode' => 'off']);

        $this->assertEquals('off', $this->templateConfiguration->getAttribute('options')['ajaxmode']);
    }

    /**
     * This test will turn on the ajaxmode.
     */
    public function testTurnOnAjaxMode()
    {
        $this->templateConfiguration = new TemplateConfiguration();
        $this->templateConfiguration->setAttribute('options', ['ajaxmode' => 'off']);

        $this->templateConfiguration->setAttribute('options', ['ajaxmode' => 'on']);

        $this->assertEquals('on', $this->templateConfiguration->getAttribute('options')['ajaxmode']);
    }
}
