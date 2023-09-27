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
        $this->initializePatcher('subquestion');
        $opHandler = $this->getOpHandler();
        $this->assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox Can not handle a question delete
     */
    public function testCanNotHandleAnswer()
    {
        $this->initializePatcher('question');
        $opHandler = $this->getOpHandler();
        $this->assertFalse($opHandler->canHandle($this->op));
    }

    private function initializePatcher(
        string $entityType = 'subquestion',
        string $operation = 'delete'
    ) {
        $this->op = OpStandard::factory(
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
        $mockQuestionAggregateService = \Mockery::mock(
            SubQuestionsService::class
        )->makePartial();
        return new OpHandlerSubquestionDelete($mockQuestionAggregateService);
    }
}
