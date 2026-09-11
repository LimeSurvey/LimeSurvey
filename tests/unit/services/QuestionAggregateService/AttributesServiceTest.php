<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use LimeSurvey\Models\Services\QuestionAttributeHelper;
use Mockery;
use Question;
use QuestionAttribute;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\QuestionAggregateService\AttributesService;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException
};
use Survey;

/**
 * @group services
 */
class AttributesServiceTest extends TestBaseClass
{
    public function testSaveUserDefaultsPersistsDefaultsForQuestionType()
    {
        $modelQuestionAttribute = Mockery::mock(QuestionAttribute::class)
            ->makePartial();
        $modelQuestionAttribute
            ->shouldReceive('setQuestionAttributeWithLanguage')
            ->with(123, 'random_order', '1', '')
            ->once()
            ->andReturn(true);

        $questionAttrHelper = Mockery::mock(QuestionAttributeHelper::class);
        $questionAttrHelper->shouldReceive('getUserDefaultsForQuestionType')
            ->with('L')
            ->once()
            ->andReturn(['random_order' => ['' => '1']]);

        $question = Mockery::mock(Question::class)->makePartial();
        $question->qid = 123;
        $question->type = 'L';
        $question->shouldReceive('getAttributes')->andReturn([]);
        $question->shouldReceive('save')->once()->andReturn(true);
        $question->shouldReceive('refresh')->once();

        $attributesService = new AttributesService(
            $modelQuestionAttribute,
            $questionAttrHelper,
            Mockery::mock(Survey::class)->makePartial()
        );

        $attributesService->saveUserDefaults($question);
    }

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

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $questionAttrHelper = Mockery::mock(QuestionAttributeHelper::class)
            ->makePartial();

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('save')
            ->andReturn(false);

        $attributesServices = new AttributesService(
            $modelQuestionAttribute,
            $questionAttrHelper,
            $modelSurvey
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

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();
        $questionAttrHelper = Mockery::mock(QuestionAttributeHelper::class);


        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('save')
           ->andReturn(false);

        $attributesServices = new AttributesService(
            $modelQuestionAttribute,
            $questionAttrHelper,
            $modelSurvey
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

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $questionAttrHelper = Mockery::mock(QuestionAttributeHelper::class);

        $question = Mockery::mock(Question::class)
            ->makePartial();

        $attributesServices = new AttributesService(
            $modelQuestionAttribute,
            $questionAttrHelper,
            $modelSurvey
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

        $modelSurvey = Mockery::mock(Survey::class)
            ->makePartial();

        $questionAttrHelper = Mockery::mock(QuestionAttributeHelper::class);

        $question = Mockery::mock(Question::class)
            ->makePartial();

        $attributesServices = new AttributesService(
            $modelQuestionAttribute,
            $questionAttrHelper,
            $modelSurvey
        );

        $attributesServices->save($question, [
            'some-attribute' => 'some value'
        ]);
    }
}
