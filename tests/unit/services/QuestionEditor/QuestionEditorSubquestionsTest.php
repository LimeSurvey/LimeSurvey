<?php

namespace ls\tests\unit\services\QuestionEditor;

use Mockery;
use Question;

use LimeSurvey\DI;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\QuestionEditor\{
    QuestionEditorSubquestions,
    QuestionEditorL10n
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    BadRequestException
};

/**
 * @group services
 */
class QuestionEditorSubquestionsTest extends TestBaseClass
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
        $questionEditorL10n = Mockery::mock(
            QuestionEditorL10n::class
        )->makePartial();

        // Model question is a required dependency
        // but is not executed for this test
        $modelQuestion = Mockery::mock(Question::class)
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

        $questionEditorSubquestions = new QuestionEditorSubquestions(
            $questionEditorL10n,
            $modelQuestion
        );

        $subquestions = [
            [
                123 => [
                    'code' => 'ABC123',
                    'relevance' => 1
                ]
            ]
        ];
        $questionEditorSubquestions->save(
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
            PersistErrorException::class
        );

        // Model question is a required dependency
        // but is not relevant to this test
        $questionEditorL10n = Mockery::mock(
            QuestionEditorL10n::class
        )->makePartial();

        // The code under test updates a Question.
        // Configure mock model question to return mock
        // - question for update
        $updateQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $updateQuestion
            ->shouldReceive('update')
            ->andReturn(false);
        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion
            ->shouldReceive('findByAttributes')
            ->andReturn($updateQuestion);

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

        $questionEditorSubquestions = new QuestionEditorSubquestions(
            $questionEditorL10n,
            $modelQuestion
        );

        $subquestions = [
            [
                123 => [
                    'code' => 'ABC123',
                    'relevance' => 1
                ]
            ]
        ];
        $questionEditorSubquestions->save(
            $question,
            $subquestions
        );
    }
}
