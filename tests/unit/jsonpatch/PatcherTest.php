<?php

namespace ls\tests\unit\jsonpatch;

use ls\tests\TestBaseClass;

use LimeSurvey\JsonPatch\Patcher;
use LimeSurvey\JsonPatch\Op\OpAdd;
use LimeSurvey\JsonPatch\Pattern\PatternRaw;

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
                'op' => 'add',
                'path' => '/hello',
                'value' => 'World'
            ]
        ];

        $opHandler = \Mockery::mock(
            'LimeSurvey\JsonPatch\OpHandler\OpHandlerInterface'
        );
        $opHandler->shouldReceive('getOp')
            ->andReturn(new OpAdd);
        $opHandler->shouldReceive('getPattern')
            ->andReturn(new PatternRaw($patch[0]['path']));
        $opHandler->shouldReceive('applyOperation')
            ->withArgs([[], $patch[0]['value']]);

        $patcher = new Patcher();
        $patcher->addOpHandler($opHandler);
        $operationsApplied = $patcher->applyPatch($patch);

        $this->assertEquals(1, $operationsApplied);
    }
}
