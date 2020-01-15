<?php

namespace ls\tests\controllers;

use themeoptions;
use TemplateConfiguration;
use PHPUnit\Framework\TestCase;

class ThemeOptionsControllerTest extends TestCase
{
    /**
     * @var themeoptions
     */
    private $controller;

    /**
     * @var TemplateConfiguration
     */
    private $templateConfiguration;

    public function setUp()
    {
        \Yii::import('application.controllers.admin.themeoptions', true);
        $this->controller = new themeoptions();

    }

    public function tearDown()
    {
        $this->controller = null;
    }

    /**
     * This test will check if the ajaxmode will be turned off.
     */
    public function testTurnAjaxModeOffAsDefault()
    {
        $expected = 'off';
        $json = json_encode(['ajaxmode' => 'on']);

        $this->templateConfiguration = new TemplateConfiguration();
        $this->templateConfiguration->setAttribute('options', (string) $json);
        $this->templateConfiguration->setAttribute('surveyid', 1);

        $actual = $this->controller->turnAjaxmodeOffAsDefault($this->templateConfiguration);
        $actualOptions = json_decode($actual->getAttribute('options'), true);

        $this->assertEquals($expected, $actualOptions['ajaxmode']);
    }
}
