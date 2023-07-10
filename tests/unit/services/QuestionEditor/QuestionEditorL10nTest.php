<?php

namespace ls\tests\unit\services\QuestionEditor;

use Mockery;
use QuestionL10n;

use LimeSurvey\DI;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\QuestionEditor\QuestionEditorL10n;

use ls\tests\unit\services\QuestionEditor\Question\{
    QuestionMockSet,
    QuestionMockSetFactory,
    QuestionFactory
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException
};

/**
 * @group services
 */
class QuestionEditorL10nTest extends TestBaseClass
{
    /**
     * @testdox save() throws PersistErrorException on create failure
     */
    public function testThrowsExceptionPersistErrorOnCreateFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $questionL10n = Mockery::mock(QuestionL10n::class)
            ->makePartial();
        $questionL10n->shouldReceive('settAttributes');
        $questionL10n->shouldReceive('save')
            ->andReturn(false);

        $modelQuestionL10n = Mockery::mock(QuestionL10n::class)
            ->makePartial();
        $modelQuestionL10n->shouldReceive('findByAttributes')
            ->andReturn(false);

        DI::getContainer()->set(
            QuestionL10n::class,
            function () use ($questionL10n) {
                return $questionL10n;
            }
        );

        $questionEditorL10n = new QuestionEditorL10n(
            $modelQuestionL10n
        );

        $questionEditorL10n->save(1, [
            'en' => []
        ]);
    }

    /**
     * @testdox save() throws PersistErrorException on update failure
     */
    public function testThrowsExceptionPersistErrorOnUpdateFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $questionL10n = Mockery::mock(QuestionL10n::class)
            ->makePartial();
        $questionL10n->shouldReceive('save')
            ->andReturn(false);

        $modelQuestionL10n = Mockery::mock(QuestionL10n::class)
            ->makePartial();
        $modelQuestionL10n->shouldReceive('findByAttributes')
            ->andReturn($questionL10n);

        $questionEditorL10n = new QuestionEditorL10n(
            $modelQuestionL10n
        );

        $questionEditorL10n->save(1, [
            'en' => []
        ]);
    }


    /**
     * @testdox save() throws NotFoundException
     */
    public function testThrowsExceptionNotFound()
    {
        $this->expectException(
            NotFoundException::class
        );

        $modelQuestionL10n = Mockery::mock(QuestionL10n::class)
            ->makePartial();
        $modelQuestionL10n->shouldReceive('findByAttributes')
            ->andReturn(false);

        $questionEditorL10n = new QuestionEditorL10n(
            $modelQuestionL10n
        );

        $questionEditorL10n->save(
            1,
            ['en' => []],
            false
        );
    }
}
