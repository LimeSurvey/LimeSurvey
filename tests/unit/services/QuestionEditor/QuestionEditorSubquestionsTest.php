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
use LimeSurvey\Models\Services\QuestionEditor;

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

        $questionEditorL10n = Mockery::mock(QuestionEditorL10n::class)
            ->makePartial();

        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();

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

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('settAttributes')
            ->passthru();
        $question->setAttributes(['qid' => 1], false);
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        $question->addRelatedRecord(
            'survey',
            (object)(['active' => 'N']),
            false
        );
        $question->addRelatedRecord(
            'questionType',
            (object)(['subquestions' => 1]),
            false
        );
        $question->shouldReceive('deleteAllSubquestions')->once();

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

        $questionEditorL10n = Mockery::mock(QuestionEditorL10n::class)
            ->makePartial();

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

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('settAttributes')
            ->passthru();
        $question->setAttributes(['qid' => 1], false);
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        $question->addRelatedRecord(
            'survey',
            (object)(['active' => 'Y']),
            false
        );
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
