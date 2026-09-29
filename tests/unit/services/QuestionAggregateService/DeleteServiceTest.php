<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Mockery;
use Condition;
use Question;

use ls\tests\TestBaseClass;

use ls\tests\unit\services\QuestionAggregateService\Delete\{
    DeleteMockSet,
    DeleteMockSetFactory,
    DeleteFactory
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    QuestionHasConditionsException
};

/**
 * @group services
 */
class DeleteServiceTest extends TestBaseClass
{
    /**
     * @testdox delete() throws NotFoundException on question not found in survey
     */
    public function testDeleteThrowsNotFoundExceptionOnQuestionNotFoundInSurvey()
    {
        $this->expectException(
            NotFoundException::class
        );

        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion->shouldReceive('findByAttributes')
            ->andReturn(false);

        $mockSet = (new DeleteMockSetFactory)->make();
        $mockSet->modelQuestion = $modelQuestion;

        $deleteService = (new DeleteFactory)->make($mockSet);

        $deleteService->delete(1, 1);
    }

     /**
     * @testdox delete() throws QuestionHasConditionsException
     */
    public function testDeleteThrowsQuestionHasConditionsException()
    {
        $this->expectException(
            QuestionHasConditionsException::class
        );

        $modelCondition = Mockery::mock(Condition::class)
            ->makePartial();
        $modelCondition->shouldReceive('findAllByAttributes')
            ->andReturn([
                Mockery::mock(Condition::class)->makePartial()
            ]);

        $mockSet = (new DeleteMockSetFactory)->make();
        $mockSet->modelCondition = $modelCondition;

        $deleteService = (new DeleteFactory)->make($mockSet);

        $deleteService->delete(1, 1);
    }

    /**
     * @testdox delete() throws PersistErrorException on delete failure
     */
    public function testDeleteThrowsPersistErrorExceptionOnDeleteFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $question = Mockery::mock(Question::class)->makePartial();
        $question->shouldReceive('delete')
            ->andReturn(false);

        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion->shouldReceive('findByAttributes')
            ->andReturn($question);

        $mockSet = (new DeleteMockSetFactory)->make();
        $mockSet->modelQuestion = $modelQuestion;

        $deleteService = (new DeleteFactory)->make($mockSet);

        $deleteService->delete(1, 1);
    }
}
