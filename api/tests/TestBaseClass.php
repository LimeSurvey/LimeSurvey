<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Db\Connection\ConnectionInterface;

class TestBaseClass extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }
}
