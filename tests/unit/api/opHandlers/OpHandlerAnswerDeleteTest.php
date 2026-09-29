<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerAnswerDelete;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerAnswerDelete
 */
class OpHandlerAnswerDeleteTest extends TestBaseClass
{
    /**
     * @testdox can handle delete
     */
    public function testCanHandleAnswer()
    {
        $op = $this->getOp();
        $opHandler = $this->getOpHandler();
        $this->assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox can not handle question delete
     */
    public function testCanNotHandleAnswer()
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
            'answer',
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
        string $entityType = 'answer',
        string $operation = 'delete'
    ): OpStandard {
        $entityId = $operation !== 'delete' ? null : "77";
        return OpStandard::factory(
            $entityType,
            $operation,
            $entityId,
            [],
            ['id' => 666]
        );
    }

    /**
     * @return OpHandlerAnswerDelete
     */
    private function getOpHandler(): OpHandlerAnswerDelete
    {
        /** @var \LimeSurvey\Models\Services\QuestionAggregateService */
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        )->makePartial();
        return new OpHandlerAnswerDelete(
            $mockQuestionAggregateService
        );
    }
}
