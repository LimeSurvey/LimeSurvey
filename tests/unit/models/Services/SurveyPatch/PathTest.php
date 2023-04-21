<?php

namespace ls\tests\Services\JsonPatch;

use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\JsonPatch\Path;
use LimeSurvey\Models\Services\JsonPatch\PathMatch;

/**
 * @testdox JsonPatch Path
 */
class PathTest extends TestBaseClass
{
    /**
     * @testdox match() returns PathMatch on success
     */
    public function testMatchReturnsPathMatchOnSuccess()
    {
        $path = new Path('/a/b/c');
        $meta = $path->match('/a/b/c');

        $this->assertInstanceOf(PathMatch::class, $meta);
    }

    /**
     * @testdox match() returns false on failure
     */
    public function testMatchReturnsFalseOnFailure()
    {
        $path = new Path('/a/b/c');
        $result = $path->match('/d/e/f');

        $this->assertFalse($result);
    }

    /**
     * @testdox match() returns PathMatch with variables on success
     */
    public function testMatchReturnsPathMatchWithVariablesOnSuccess()
    {
        $path = new Path('/a/b/$variable1/$variable2');
        $meta = $path->match('/a/b/c/d');

        $this->assertEquals(
            ['variable1' => 'c', 'variable2' => 'd'],
            $meta->getVariables()
        );
    }
}
