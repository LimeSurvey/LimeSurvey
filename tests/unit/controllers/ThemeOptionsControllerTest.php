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

}
