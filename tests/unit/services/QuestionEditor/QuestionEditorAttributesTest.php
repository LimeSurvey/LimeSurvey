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
     * @testdox saveAdvanced() throws PersistErrorException on create failure
     */
    public function testSaveAdvancedThrowsExceptionPersistErrorOnCreateFailure()
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
     * @testdox save() throws PersistErrorException on create failure
     */
    public function testSaveThrowsExceptionPersistErrorOnCreateFailure()
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
}
