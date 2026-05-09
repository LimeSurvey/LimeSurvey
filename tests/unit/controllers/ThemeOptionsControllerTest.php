<?php

namespace ls\tests\controllers;

use ThemeOptionsController;
use TemplateConfiguration;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

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
        $expected = 'off';
        $json = json_encode(['ajaxmode' => 'on']);

        $this->templateConfiguration = new TemplateConfiguration();
        $this->templateConfiguration->setAttribute('options', (string) $json);
        $this->templateConfiguration->setAttribute('surveyid', 1);

        // Use reflection to call the protected method
        $reflectionMethod = new ReflectionMethod($this->controller, 'turnAjaxmodeOffAsDefault');
        $reflectionMethod->setAccessible(true);
        $actual = $reflectionMethod->invoke($this->controller, $this->templateConfiguration);
        $actualOptions = json_decode($actual->getAttribute('options'), true);

        $this->assertEquals($expected, $actualOptions['ajaxmode']);
    }
}
