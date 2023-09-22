<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyPatch\OpHandlerAnswerDelete;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\{
    ObjectPatchException,
    Op\OpInterface,
    Op\OpStandard
};
use ls\tests\TestBaseClass;

class OpHandlerAnswerDeleteTest extends TestBaseClass
{
    protected OpInterface $op;

    public function testCanHandleAnswer()
    {
        $this->initializePatcher('answer');
        $opHandler = $this->getOpHandler();
        $this->assertTrue($opHandler->canHandle($this->op));
    }

    public function testCanNotHandleAnswer()
    {
        $this->initializePatcher('question');
        $opHandler = $this->getOpHandler();
        $this->assertFalse($opHandler->canHandle($this->op));
    }

    /**
     * @param string $entityType
     * @param string $operation
     * @return void
     * @throws ObjectPatchException
     */
    private function initializePatcher(
        string $entityType = 'answer',
        string $operation = 'delete'
    ){
        $this->op = OpStandard::factory(
            $entityType,
            $operation,
            "77",
            [],
            ['id' => 666]
        );
    }

    /**
     * @return OpHandlerAnswerDelete
     */
    private function getOpHandler()
    {
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        )->makePartial();
        return new OpHandlerAnswerDelete($mockQuestionAggregateService);
    }
}
