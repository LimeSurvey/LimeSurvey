<?php

namespace ls\tests\Services\SurveyPatch;

use ls\tests\TestBaseClass;
use LimeSurvey\Model\Service\SurveyPatch\Path;
use LimeSurvey\Model\Service\SurveyPatch\Meta;

/**
 * @testdox SurveyPatch Path
 */
class PathTest extends TestBaseClass
{
    /**
     * @testdox match() returns meta on success
     */
    public function testMatchReturnsMetaOnSuccess()
    {
        $path = new Path('/a/b/c');
        $meta = $path->match('/a/b/c');

        $this->assertInstanceOf( Meta::class, $meta);
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
     * @testdox match() returns meta with variables on success
     */
    public function testMatchReturnsMetaWithVariablesOnSuccess()
    {
        $path = new Path('/a/b/$variable1/$variable2');
        $meta = $path->match('/a/b/c/d');

        $this->assertEquals(
            ['variable1' => 'c', 'variable2' => 'd'],
            $meta->getVariables()
        );
    }
}
