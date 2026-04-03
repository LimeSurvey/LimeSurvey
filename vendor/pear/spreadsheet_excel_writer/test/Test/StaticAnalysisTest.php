<?php

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * @copyright 2025, Alexey Kopytko
 * @coversNothing
 */
class StaticAnalysisTest extends TestCase
{
    /**
     * @return iterable
     */
    public function provideClasses()
    {
        $files = require 'vendor/composer/autoload_classmap.php';

        foreach ($files as $class => $filename) {
            $path = str_replace(getcwd(), '.', $filename);

            if (strpos($path, './vendor/') === 0) {
                continue;
            }

            yield $class => [str_replace(getcwd(), '.', $filename), $class];
        }
    }

    /**
     * @dataProvider provideClasses
     */
    public function testClassExists($filename, $class)
    {
        $this->assertTrue(class_exists($class) || trait_exists($class) || interface_exists($class));
    }
}
