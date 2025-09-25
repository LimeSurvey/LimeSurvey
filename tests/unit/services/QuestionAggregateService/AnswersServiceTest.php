<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Answer;
use AnswerL10n;
use Mockery;
use Question;
use QuestionAttribute;

use LimeSurvey\DI;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\QuestionAggregateService\AnswersService;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    BadRequestException
};

/**
 * @group services
 */
class AnswersServiceTest extends TestBaseClass
{
    /**
     * @testdox save() throws BadRequestException missing code
     */
    public function testSaveThrowsExceptionBadRequestOnMissingCode()
    {
        $this->expectException(
            BadRequestException::class
        );

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('settAttributes')
            ->passthru();
        $question->setAttributes(['qid' => 1], false);
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        $question->addRelatedRecord(
            'questionType',
            (object)(['answerscales' => 100]),
            false
        );
        $question->shouldReceive('deleteAllAnswers')->once();

        $answersService = DI::getContainer()->get(
            AnswersService::class
        );

        $answersService->save($question, [
            [
                123 => ['not-code' => 'ABC123']
            ]
        ]);
    }

    /**
     * @testdox save() throws PersistErrorException on save answer failure
     */
    public function testSaveThrowsExceptionPersistErrorOnQuestionSaveAnswerFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('settAttributes')
            ->passthru();
        $question->setAttributes(['qid' => 1], false);
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        $question->addRelatedRecord(
            'questionType',
            (object)(['answerscales' => 100]),
            false
        );
        $question->shouldReceive('deleteAllAnswers')->once();

        $answer = Mockery::mock(Answer::class)
            ->makePartial();
        $answer->shouldReceive('settAttributes');
        $answer->shouldReceive('save')
            ->andReturn(false);

        $answersService = DI::getContainer()->get(
            AnswersService::class
        );

        $answersService->save($question, [
            [
                123 => ['code' => 'ABC123']
            ]
        ]);
    }

    /**
     * @testdox save() throws PersistErrorException on save answer L10n failure
     */
    public function testSaveThrowsExceptionPersistErrorOnQuestionSaveAnswerL10nFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('settAttributes')
            ->passthru();
        $question->setAttributes(['qid' => 1], false);
        $question->shouldReceive('addRelatedRecord')
            ->passthru();
        $question->addRelatedRecord(
            'questionType',
            (object)(['answerscales' => 100]),
            false
        );
        $question->shouldReceive('deleteAllAnswers')->once();

        $answer = Mockery::mock(Answer::class)
            ->makePartial();
        $answer->shouldReceive('settAttributes');
        $answer->shouldReceive('save')
            ->andReturn(true);

        $l10n = Mockery::mock(AnswerL10n::class)
            ->makePartial();
        $l10n->shouldReceive('settAttributes');
        $l10n->shouldReceive('save')
            ->andReturn(false);

        $answersService = DI::getContainer()->get(
            AnswersService::class
        );

        $answersService->save($question, [
            [
                123 => [
                    'code' => 'ABC123',
                    'answeroptionl10n' => [
                        'en' => 'test'
                    ]
                ]
            ]
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
