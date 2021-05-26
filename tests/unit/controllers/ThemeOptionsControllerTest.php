<?php

namespace ls\tests\controllers;

use ThemeOptionsController;
use TemplateConfiguration;
use PHPUnit\Framework\TestCase;

class ThemeOptionsControllerTest extends TestCase
{
    /**
     * @var ThemeOptionsController
     */
    private $controller;

    /**
     * @var TemplateConfiguration
     */
    private $templateConfiguration;

    public function setUp(): void
    {
        \Yii::import('application.controllers.ThemeOptionsController', true);
        $this->controller = new ThemeOptionsController('moo');
    }

    public function tearDown(): void
    {
        $this->controller = null;
    }

    /**
     * This test will check if the ajaxmode will be turned off.
     */
    public function testTurnAjaxModeOffAsDefault()
    {
        $this->markTestSkipped();
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
