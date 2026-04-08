<?php

namespace api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch\OpHandlerResponsesDelete;
use LimeSurvey\Models\Services\SurveyResponseService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerResponsesDelete
 */
class OpHandlerResponsesDeleteTest extends TestBaseClass
{
    /**
     * @testdox can handle delete
     */
    public function testCanHandleResponse()
    {
        $op = $this->getOp();
        $opHandler = $this->getOpHandler();
        $this->assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox can not handle response delete
     */
    public function testCanNotHandleResponse()
    {
        $op = $this->getOp('question');
        $opHandler = $this->getOpHandler();
        $this->assertFalse($opHandler->canHandle($op));
    }

    /**
     * @testdox validation hits when entityId is missing
     */
    public function testOpValidationFailure()
    {
        $op = $this->getOp(
            'response',
            'create'
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
    }

    /**
     * @testdox validation doesn't hit when everything is fine
     */
    public function testOpValidationSuccess()
    {
        $op = $this->getOp(
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertEmpty($validation);
    }

    /**
     * @param string $entityType
     * @param string $operation
     * @return OpStandard
     * @throws ObjectPatchException
     */
    private function getOp(
        string $entityType = 'response',
        string $operation = 'delete'
    ): OpStandard {
        return OpStandard::factory(
            $entityType,
            $operation,
            $operation !== 'delete' ? null : "77",
            [],
            ['id' => 77]
        );
    }

    /**
     * @return OpHandlerResponsesDelete
     */
    private function getOpHandler(): OpHandlerResponsesDelete
    {
        /** @var SurveyResponseService $mockSurveyResponseService */
        $mockSurveyResponseService = \Mockery::mock(
            SurveyResponseService::class
        )->makePartial();
        return new OpHandlerResponsesDelete(
            $mockSurveyResponseService
        );
    }
}
