<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSubquestionDelete;
use LimeSurvey\Models\Services\QuestionAggregateService\SubQuestionsService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;
use Mockery;

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

    /**
     * @testdox validation hits when entityId is missing
     */
    public function testOpValidationFailure()
    {
        $op = $this->getOp(
            'failure'
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
     * @param string $entity
     * @param string $type
     * @return OpStandard
     * @throws OpHandlerException|ObjectPatchException
     */
    private function getOp(
        string $entity = 'subquestion',
        string $type = 'delete'
    ) {
        $entityId = $entity !== 'subquestion' ? null : 77;
        return OpStandard::factory(
            $entity,
            $type,
            $entityId,
            [],
            ['id' => 666]
        );
    }

    /**
     * @return OpHandlerSubquestionDelete
     */
    private function getOpHandler()
    {
        /** @var SubQuestionsService */
        $mockQuestionAggregateService = Mockery::mock(
            SubQuestionsService::class
        )->makePartial();
        return new OpHandlerSubquestionDelete($mockQuestionAggregateService);
    }
}
