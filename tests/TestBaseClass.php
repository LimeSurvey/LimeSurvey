<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

class TestBaseClass extends TestCase
{
    /**
     * @var TestHelper
     */
    protected static $testHelper = null;

    public function __construct()
    {
        self::$testHelper = new TestHelper();

        self::$testHelper->importAll();

        parent::__construct();
    }
}
