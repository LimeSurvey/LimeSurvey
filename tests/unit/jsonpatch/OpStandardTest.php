<?php

namespace ls\tests\unit\jsonpatch;

use ls\tests\TestBaseClass;

use LimeSurvey\JsonPatch\JsonPatchException;
use LimeSurvey\JsonPatch\OpType\OpType;

/**
 * @testdox OpStandard
 */
class OpStandardTest extends TestBaseClass
{
    /**
     * @testdox factory() throws JsonPatchException on missing path
     */
    public function testFactoryThrowsExceptionOnInValidPathString()
    {
        $this->expectException(JsonPatchException::class);

        OpType::factory(null);
    }

    /**
     * @testdox factory() throws JsonPatchException on invalid op string
     */
    public function testFactoryThrowsExceptionOnInValidOpString()
    {
        $this->expectException(JsonPatchException::class);

        OpType::factory('/hello', 'invalid');
    }
}
