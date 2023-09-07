<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Mockery;
use QuestionL10n;

use LimeSurvey\DI;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\QuestionAggregateService\L10nService;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException
};

/**
 * @group services
 */
class L10nServiceTest extends TestBaseClass
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

        $l10nService = new L10nService(
            $modelQuestionL10n
        );

        $l10nService->save(1, [
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

        $l10nService = new L10nService(
            $modelQuestionL10n
        );

        $l10nService->save(1, [
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

        $l10nService = new L10nService(
            $modelQuestionL10n
        );

        $l10nService->save(
            1,
            ['en' => []],
            false
        );
    }
}
