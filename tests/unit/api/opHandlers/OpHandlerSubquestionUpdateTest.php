<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSubQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSubQuestion;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    Op\OpStandard,
};
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerSubQuestion
 */
class OpHandlerSubquestionUpdateTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox can handle a subquestion update
     */
    public function testOpSubquestionUpdateCanHandle()
    {
        $op = $this->getOp(
            $this->getDefaultProps()
        );
        self::assertTrue($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox can handle a subquestion create
     */
    public function testOpSubquestionCreateCanHandle()
    {
        $op = $this->getOp(
            $this->getDefaultProps(),
            'create'
        );
        self::assertTrue($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox can not handle a subquestion delete
     */
    public function testOpSubquestionCanNotHandle()
    {
        $op = $this->getOp(
            $this->getDefaultProps(),
            'delete'
        );
        self::assertFalse($this->getOpHandler()->canHandle($op));
    }

    private function getOp(
        array $propsArray,
        string $type = 'update'
    ) {
        return OpStandard::factory(
            'subquestion',
            $type,
            123,
            $propsArray,
            [
                'id' => 123456
            ]
        );
    }

    private function getDefaultProps()
    {
        return [
            '0' => [
                'qid' => 126,
                'oldCode' => 'SQ001',
                'title' => 'SQ001new',
                'l10ns' => [
                    'en' => [
                        'question' => 'sub 1'
                    ],
                    'de' => [
                        'question' => 'subger 1'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return OpHandlerSubQuestion
     */
    private function getOpHandler()
    {
        /** @var \LimeSurvey\Models\Services\QuestionAggregateService */
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        )->makePartial();
        /** @var \LimeSurvey\Models\Services\QuestionAggregateService\SubQuestionsService */
        $mockSubQuestionsService = \Mockery::mock(
            QuestionAggregateService\SubQuestionsService::class
        )->makePartial();
        /** @var \LimeSurvey\Models\Services\QuestionAggregateService\QuestionService */
        $mockQuestionService = \Mockery::mock(
            QuestionAggregateService\QuestionService::class
        )->makePartial();

        /** @var \LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSubQuestion */
        $transformerInputSubQuestion = \Mockery::mock(
            TransformerInputSubQuestion::class
        )->makePartial();

        return new OpHandlerSubQuestion(
            $mockQuestionAggregateService,
            $mockSubQuestionsService,
            $mockQuestionService,
            $transformerInputSubQuestion
        );
    }
}
