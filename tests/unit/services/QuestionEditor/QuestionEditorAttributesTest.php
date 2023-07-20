<?php

namespace ls\tests\unit\services\QuestionEditor;

use Mockery;
use Question;
use QuestionAttribute;

use ls\tests\TestBaseClass;

use LimeSurvey\Models\Services\QuestionEditor\QuestionEditorAttributes;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException
};

/**
 * @group services
 */
class QuestionEditorAttributesTest extends TestBaseClass
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

        $questionEditorAttributes = new QuestionEditorAttributes(
            $modelQuestionAttribute
        );

        $questionEditorAttributes->saveAdvanced($question, []);
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

        $questionEditorAttributes = new QuestionEditorAttributes(
            $modelQuestionAttribute
        );

        $questionEditorAttributes->save($question, []);
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

        $questionEditorAttributes = new QuestionEditorAttributes(
            $modelQuestionAttribute
        );

        $questionEditorAttributes->save($question, [
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

        $questionEditorAttributes = new QuestionEditorAttributes(
            $modelQuestionAttribute
        );

        $questionEditorAttributes->save($question, [
            'some-attribute' => 'some value'
        ]);
    }
}
