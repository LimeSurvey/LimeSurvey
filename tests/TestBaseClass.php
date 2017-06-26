<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

class TestBaseClass extends \PHPUnit_Framework_TestCase
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
