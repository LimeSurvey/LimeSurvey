<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerAnswerDelete;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

class OpHandlerAnswerDeleteTest extends TestBaseClass
{
    public function testCanHandleAnswer()
    {
        $op = $this->getOp('answer');
        $opHandler = $this->getOpHandler();
        $this->assertTrue($opHandler->canHandle($op));
    }

    public function testCanNotHandleAnswer()
    {
        $op = $this->getOp('question');
        $opHandler = $this->getOpHandler();
        $this->assertFalse($opHandler->canHandle($op));
    }

    /**
     * @param string $entityType
     * @param string $operation
     * @return OpStandard
     */
    private function getOp(
        string $entityType = 'answer',
        string $operation = 'delete'
    ) {
        return OpStandard::factory(
            $entityType,
            $operation,
            '77',
            [],
            ['id' => 666]
        );
    }

    /**
     * @return OpHandlerAnswerDelete
     */
    private function getOpHandler()
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
