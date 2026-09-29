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
     * @testdox getDefaultAttributeValues() delegates to QuestionAttributeHelper
     */
    public function testGetDefaultAttributeValuesReturnsFlattenedDefaults()
    {
        $questionAttributeHelper = Mockery::mock(
            \LimeSurvey\Models\Services\QuestionAttributeHelper::class
        );
        $questionAttributeHelper->shouldReceive('getUserDefaultsForQuestionType')
            ->with('L')
            ->once()
            ->andReturn([
                'random_order' => ['' => '1'],
                'min_answers' => ['' => '2'],
            ]);

        $mockSetInit = new QuestionMockSet();
        $mockSetInit->questionAttributeHelper = $questionAttributeHelper;
        $questionService = (new QuestionFactory())->make($mockSetInit);

        $this->assertSame(
            [
                'random_order' => ['' => '1'],
                'min_answers' => ['' => '2'],
            ],
            $questionService->getDefaultAttributeValues('L')
        );
    }

    /**
     * @testdox getDefaultAttributeValuesByQuestionType() delegates to QuestionAttributeHelper
     */
    public function testGetDefaultAttributeValuesByQuestionTypeDelegatesToHelper()
    {
        $questionAttributeHelper = Mockery::mock(
            \LimeSurvey\Models\Services\QuestionAttributeHelper::class
        );
        $questionAttributeHelper->shouldReceive('getUserDefaultsByQuestionType')
            ->once()
            ->andReturn([
                'L' => [
                    'random_order' => ['' => '1'],
                ],
            ]);

        $mockSetInit = new QuestionMockSet();
        $mockSetInit->questionAttributeHelper = $questionAttributeHelper;
        $questionService = (new QuestionFactory())->make($mockSetInit);

        $this->assertSame(
            [
                'L' => [
                    'random_order' => ['' => '1'],
                ],
            ],
            $questionService->getDefaultAttributeValuesByQuestionType()
        );
    }

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
