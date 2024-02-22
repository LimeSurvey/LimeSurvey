<?php

namespace ls\tests\unit\objectpatch;

use ls\tests\TestBaseClass;

use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Patcher;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;

/**
 * @testdox Patcher
 */
class PatcherTest extends TestBaseClass
{
    /**
     * @testdox applyPatch() should execute matched operation
     */
    public function testApplyPatchExecuteMatchedOperation()
    {
        $patch = [
            [
                'entity' => 'person',
                'op' => 'create',
                'id' => 1,
                'props' => ['name' => 'John']
            ]
        ];

        $opHandler = \Mockery::mock(
            OpHandlerInterface::class
        );
        $opHandler->shouldReceive('canHandle')
            ->andReturn(true);
        $opHandler->shouldReceive('handle')
            ->andReturn(true);
        $opHandler->shouldReceive('validateOperation')
            ->andReturn([]);

        $patcher = new Patcher();
        $patcher->addOpHandler($opHandler);
        $returnedData = $patcher->applyPatch($patch);
        $operationsApplied = $returnedData['operationsApplied'];

        $this->assertEquals(1, $operationsApplied);
    }

    /**
     * @testdox applyPatch() throws ObjectPatchException on missing op handler
     */
    public function testApplyPatchThrowsObjectPatchExceptionOnMissingOpHandler()
    {
        $this->expectException(ObjectPatchException::class);

        $patch = [
            [
                'entity' => 'person',
                'op' => 'create',
                'id' => 1,
                'props' => ['name' => 'John']
            ]
        ];

        $patcher = new Patcher();
        $returnedData = $patcher->applyPatch($patch);
        $operationsApplied = $returnedData['operationsApplied'];

        $this->assertEquals(0, $operationsApplied);
    }

    /**
     * @testdox applyPatch() throws ObjectPatchException on missing op
     */
    public function testApplyPatchThrowsObjectPatchExceptionOnMissingOp()
    {
        $this->expectException(ObjectPatchException::class);
        $patch = [
            [
                'entity' => 'person',
                'id' => 1,
                'props' => ['name' => 'John']
            ]
        ];

        $patcher = new Patcher();
        $patcher->applyPatch($patch);
    }
}
