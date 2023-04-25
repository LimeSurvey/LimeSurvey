<?php

namespace ls\tests\unit\jsonpatch;

use ls\tests\TestBaseClass;

use LimeSurvey\JsonPatch\JsonPatchException;
use LimeSurvey\JsonPatch\OpType\OpType;

/**
 * @testdox OpType
 */
class OpTypeTest extends TestBaseClass
{
    /**
     * @testdox factory() throws JsonPatchException on invalid op string
     */
    public function testFactoryThrowsExceptionOnInValidOpString()
    {
        $this->expectException(JsonPatchException::class);

        OpType::factory('invalid');
    }
}
