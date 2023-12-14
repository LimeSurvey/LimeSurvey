<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSubquestionDelete;
use LimeSurvey\Models\Services\QuestionAggregateService\SubQuestionsService;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerSubquestionDelete
 */
class OpHandlerSubquesDeleteTest extends TestBaseClass
{

    /**
     * @testdox Can handle a subquestion delete
     */
    public function testCanHandleAnswer()
    {
        $op = $this->getOp('subquestion');
        $opHandler = $this->getOpHandler();
        $this->assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox Can not handle a question delete
     */
    public function testCanNotHandleAnswer()
    {
        $op = $this->getOp('question');
        $opHandler = $this->getOpHandler();
        $this->assertFalse($opHandler->canHandle($op));
    }

    private function getOp(
        string $entityType = 'subquestion',
        string $operation = 'delete'
    ) {
        return OpStandard::factory(
            $entityType,
            $operation,
            "77",
            [],
            ['id' => 666]
        );
    }

    /**
     * @return OpHandlerSubquestionDelete
     */
    private function getOpHandler()
    {
        /** @var \LimeSurvey\Models\Services\QuestionAggregateService\SubQuestionsService */
        $mockQuestionAggregateService = \Mockery::mock(
            SubQuestionsService::class
        )->makePartial();
        return new OpHandlerSubquestionDelete($mockQuestionAggregateService);
    }
}
