<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Mockery;
use Question;
use Survey;
use Permission;

use LimeSurvey\DI;
use ls\tests\TestBaseClass;
use LimeSurvey\Models\Services\QuestionAggregateService\{
    SubQuestionsService,
    L10nService
};
use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    BadRequestException,
    NotFoundException
};

/**
 * @group services
 */
class SubQuestionsServiceTest extends TestBaseClass
{
    /**
     * @testdox save() throws PersistErrorException on create failure
     */
    public function testSaveThrowsExceptionPersistErrorOnCreateFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        // Model question is a required dependency
        // but is not relevant to this test
        $l10nService = Mockery::mock(
            L10nService::class
        )->makePartial();

        // Model question is a required dependency
        // but is not executed for this test
        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();

        // The code under test creates a new Question
        // - using dependency injection.
        // Configure DI to return a mock question
        $newQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $newQuestion
            ->shouldReceive('save')
            ->andReturn(false);
        DI::getContainer()->set(
            Question::class,
            function () use ($newQuestion) {
                return $newQuestion;
            }
        );

        // Create a mock of the question we are editing
        $question = Mockery::mock(Question::class)
            ->makePartial();
        // Question id (qid) must be set
        $question->shouldReceive('settAttributes');
        $question->setAttributes(['qid' => 1], false);
        // $question->survey->active must be N
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        $question->addRelatedRecord(
            'survey',
            (object)(['active' => 'N']),
            false
        );
        // $question->questionType->subquestions must be > 0
        $question->addRelatedRecord(
            'questionType',
            (object)(['subquestions' => 1]),
            false
        );

        $subquestionsService = new SubQuestionsService(
            $l10nService,
            $modelQuestion,
            $modelSurvey,
            $modelPermission
        );

        $subquestions = [
            [
                123 => [
                    'code' => 'ABC123',
                    'relevance' => 1
                ]
            ]
        ];
        $subquestionsService->save(
            $question,
            $subquestions
        );
    }

    /**
     * @testdox save() throws PersistErrorException on update failure
     */
    public function testSaveThrowsExceptionPersistErrorOnUpdateFailure()
    {
        $this->expectException(
            NotFoundException::class
        );

        // Model question is a required dependency
        // but is not relevant to this test
        $l10nService = Mockery::mock(
            L10nService::class
        )->makePartial();

        // The code under test updates a Question.
        // Configure mock model question to return null
        // to simulate subquestion not found
        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion
            ->shouldReceive('findByAttributes')
            ->andReturn(null);

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();

        // Create a mock of the question we are editing
        $question = Mockery::mock(Question::class)
            ->makePartial();
        // Question id (qid) must be set
        $question->shouldReceive('settAttributes')
            ->passthru();
        $question->setAttributes(['qid' => 1], false);
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        // $question->survey->active must be Y
        $question->addRelatedRecord(
            'survey',
            (object)(['active' => 'Y']),
            false
        );
        // $question->questionType->subquestions must be > 0
        $question->addRelatedRecord(
            'questionType',
            (object)(['subquestions' => 1]),
            false
        );

        $subquestionsService = new SubQuestionsService(
            $l10nService,
            $modelQuestion,
            $modelSurvey,
            $modelPermission
        );

        $subquestions = [
            [
                123 => [
                    'code' => 'ABC123',
                    'relevance' => 1
                ]
            ]
        ];
        $subquestionsService->save(
            $question,
            $subquestions
        );
    }

    /**
     * @testdox save() throws NotFoundException on subquestion not found
     */
    public function testSaveThrowsNotFoundExceptionOnSubquestionNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        // Model question is a required dependency
        // but is not relevant to this test
        $l10nService = Mockery::mock(
            L10nService::class
        )->makePartial();

        // findByAttributes should return null
        // - to simulate subquestion not found
        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion
            ->shouldReceive('findByAttributes')
            ->andReturn(null);

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();

        // Create a mock of the question we are editing
        $question = Mockery::mock(Question::class)
            ->makePartial();
        // Question id (qid) must be set
        $question->shouldReceive('settAttributes')
            ->passthru();
        $question->setAttributes(['qid' => 1], false);
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        // $question->survey->active must be Y
        $question->addRelatedRecord(
            'survey',
            (object)(['active' => 'Y']),
            false
        );
        // $question->questionType->subquestions must be > 0
        $question->addRelatedRecord(
            'questionType',
            (object)(['subquestions' => 1]),
            false
        );

        $subquestionsService = new SubQuestionsService(
            $l10nService,
            $modelQuestion,
            $modelSurvey,
            $modelPermission
        );

        $subquestions = [
            [
                123 => [
                    'code' => 'ABC123',
                    'relevance' => 1
                ]
            ]
        ];
        $subquestionsService->save(
            $question,
            $subquestions
        );
    }

    /**
     * @testdox save() throws BadRequestException with missing subquestion code on create
     */
    public function testSaveThrowsBadRequestExceptionWithMissingSubquestionCodeOnCreate()
    {
        $this->expectException(
            BadRequestException::class
        );

        // Model question is a required dependency
        // but is not relevant to this test
        $l10nService = Mockery::mock(
            L10nService::class
        )->makePartial();

        // Model question is a required dependency
        // but is not executed for this test
        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();

        // Create a mock of the question we are editing
        $question = Mockery::mock(Question::class)
            ->makePartial();
        // Question id (qid) must be set
        $question->shouldReceive('settAttributes');
        $question->setAttributes(['qid' => 1], false);
        // $question->survey->active must be N
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        $question->addRelatedRecord(
            'survey',
            (object)(['active' => 'N']),
            false
        );
        // $question->questionType->subquestions must be > 0
        $question->addRelatedRecord(
            'questionType',
            (object)(['subquestions' => 1]),
            false
        );

        $subquestionsService = new SubQuestionsService(
            $l10nService,
            $modelQuestion,
            $modelSurvey,
            $modelPermission
        );

        $subquestions = [
            [
                123 => [
                    'relevance' => 1
                ]
            ]
        ];
        $subquestionsService->save(
            $question,
            $subquestions
        );
    }

    /**
     * @testdox save() throws BadRequestException with missing subquestion code on update
     */
    public function testSaveThrowsBadRequestExceptionWithMissingSubquestionCodeOnUpdate()
    {
        $this->expectException(
            BadRequestException::class
        );

        // Model question is a required dependency
        // but is not relevant to this test
        $l10nService = Mockery::mock(
            L10nService::class
        )->makePartial();

        // The code under test updates a Question.
        // Configure mock model question to return mock
        // - question for update
        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion
            ->shouldReceive('findByAttributes')
            ->andReturn(Mockery::mock(Question::class));

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $modelPermission = Mockery::mock(Permission::class)
            ->makePartial();

        // Create a mock of the question we are editing
        $question = Mockery::mock(Question::class)
            ->makePartial();
        // Question id (qid) must be set
        $question->shouldReceive('settAttributes')
            ->passthru();
        $question->setAttributes(['qid' => 1], false);
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        // $question->survey->active must be Y
        $question->addRelatedRecord(
            'survey',
            (object)(['active' => 'Y']),
            false
        );
        // $question->questionType->subquestions must be > 0
        $question->addRelatedRecord(
            'questionType',
            (object)(['subquestions' => 1]),
            false
        );

        $subquestionsService = new SubQuestionsService(
            $l10nService,
            $modelQuestion,
            $modelSurvey,
            $modelPermission
        );

        $subquestions = [
            [
                123 => [
                    'relevance' => 1
                ]
            ]
        ];
        $subquestionsService->save(
            $question,
            $subquestions
        );
    }
}
