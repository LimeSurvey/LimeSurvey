<?php

namespace ls\tests;

use Facebook\WebDriver\WebDriver;
use PHPUnit\Framework\TestCase;

class TestBaseClass extends TestCase
{
    /**
     * @var TestHelper
     */
    protected static $testHelper = null;

    /**
     * @var WebDriver
     */
    protected $webDriver;

    public function __construct()
    {
        self::$testHelper = new TestHelper();

        self::$testHelper->importAll();

        parent::__construct();
    }
}
