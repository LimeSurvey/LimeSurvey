<?php

namespace ls\tests\unit\services\QuestionEditor;

use Mockery;
use Question;

use ls\tests\TestBaseClass;

use ls\tests\unit\services\QuestionEditor\Question\{
    QuestionMockSet,
    QuestionMockSetFactory,
    QuestionFactory
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException
};

/**
 * @group services
 */
class QuestionEditorQuestionTest extends TestBaseClass
{
    /**
     * @testdox save() throws PersistErrorException
     */
    public function testThrowsExceptionPersistError()
    {
        $this->expectException(
            PersistErrorException::class
        );

        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('save')
            ->andReturn(false);

        $modelQuestion = Mockery::mock(Question::class)
            ->makePartial();
        $modelQuestion->shouldReceive('findByPk')
            ->andReturn($question);

        $mockSetInit = new QuestionMockSet();
        $mockSetInit->modelQuestion = $modelQuestion;

        $mockSet = (new QuestionMockSetFactory)->make($mockSetInit);

        $questionEditor = (new QuestionFactory)->make($mockSet);

        $questionEditor->save([
            'question' => [
                'qid' => 1
            ]
        ]);
    }
}
