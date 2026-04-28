<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Mockery;
use Question;

use LimeSurvey\DI;

use ls\tests\TestBaseClass;

use ls\tests\unit\services\QuestionAggregateService\Question\{
    QuestionMockSet,
    QuestionMockSetFactory,
    QuestionFactory
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException
};

/**
 * @group services
 */
class QuestionServiceTest extends TestBaseClass
{
    /**
     * @testdox save() throws PersistErrorException on create failure
     */
    public function testSaveThrowsExceptionPersistErrorOnCreateFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('settAttributes');
        $question->shouldReceive('save')
            ->andReturn(false);

        DI::getContainer()->set(
            Question::class,
            function () use ($question) {
                return $question;
            }
        );

        $mockSet = (new QuestionMockSetFactory)->make();
        $questionService = (new QuestionFactory)->make($mockSet);

        $questionService->save([
            'question' => [
                'sid' => 1
            ]
        ]);
    }

    /**
     * @testdox save() throws PersistErrorException on update failure
     */
    public function testDeleteThrowsExceptionPersistErrorOnUpdateFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('save')
            ->andReturn(false);

        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion->shouldReceive('findByAttributes')
            ->andReturn($question);

        $mockSetInit = new QuestionMockSet();
        $mockSetInit->modelQuestion = $modelQuestion;

        $mockSet = (new QuestionMockSetFactory)->make($mockSetInit);

        $questionService = (new QuestionFactory)->make($mockSet);

        $questionService->save([
            'question' => [
                'qid' => 1,
                'sid' => 1
            ]
        ]);
    }
}
