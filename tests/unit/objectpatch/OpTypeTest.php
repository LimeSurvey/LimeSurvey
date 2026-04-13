<?php

namespace ls\tests\unit\objectpatch;

use ls\tests\TestBaseClass;

use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\OpType\OpType;

/**
 * @testdox OpType
 */
class OpTypeTest extends TestBaseClass
{
    /**
     * @testdox factory() throws ObjectPatchException on invalid op string
     */
    public function testFactoryThrowsExceptionOnInValidOpString()
    {
        $this->expectException(ObjectPatchException::class);

        OpType::factory('invalid');
    }
}
