<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Mockery;
use Question;
use QuestionAttribute;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\QuestionAggregateService\AttributesService;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException
};

/**
 * @group services
 */
class AttributesServiceTest extends TestBaseClass
{
    /**
     * @testdox saveAdvanced() throws PersistErrorException on question save failure
     */
    public function testSaveAdvancedThrowsExceptionPersistErrorOnQuestionSaveFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $modelQuestionAttribute = Mockery::mock(QuestionAttribute::class)
            ->makePartial();

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('save')
            ->andReturn(false);

        $attributesServices = new AttributesService(
            $modelQuestionAttribute
        );

        $attributesServices->saveAdvanced($question, []);
    }

    /**
     * @testdox save() throws PersistErrorException on question save failure
     */
    public function testSaveThrowsExceptionPersistErrorOnQuestionSaveFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $modelQuestionAttribute = Mockery::mock(QuestionAttribute::class)
            ->makePartial();

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('save')
           ->andReturn(false);

        $attributesServices = new AttributesService(
            $modelQuestionAttribute
        );

        $attributesServices->save($question, []);
    }

    /**
     * @testdox save() throws PersistErrorException on language save failure
     */
    public function testSaveThrowsExceptionPersistErrorOnLangSaveFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $modelQuestionAttribute = Mockery::mock(QuestionAttribute::class)
            ->makePartial();
        $modelQuestionAttribute->shouldReceive('setQuestionAttributeWithLanguage')
            ->andReturn(false);

        $question = Mockery::mock(Question::class)
            ->makePartial();

        $attributesServices = new AttributesService(
            $modelQuestionAttribute
        );

        $attributesServices->save($question, [
            'some-attribute' => [
                'en' => 'some value',
                'de' => 'some other value'
            ]
        ]);
    }

    /**
     * @testdox save() throws PersistErrorException on attribute save failure
     */
    public function testSaveThrowsExceptionPersistErrorOnAttributeSaveFailure()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $modelQuestionAttribute = Mockery::mock(QuestionAttribute::class)
            ->makePartial();
        $modelQuestionAttribute->shouldReceive('setQuestionAttribute')
            ->andReturn(false);

        $question = Mockery::mock(Question::class)
            ->makePartial();

        $attributesServices = new AttributesService(
            $modelQuestionAttribute
        );

        $attributesServices->save($question, [
            'some-attribute' => 'some value'
        ]);
    }
}
