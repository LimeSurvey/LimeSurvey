<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSubQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
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
        $this->initializePatcher(
            $this->getDefaultProps()
        );

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox can handle a subquestion create
     */
    public function testOpSubquestionCreateCanHandle()
    {
        $this->initializePatcher(
            $this->getDefaultProps(),
            'create'
        );

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    /**
     * @testdox can not handle a subquestion delete
     */
    public function testOpSubquestionCanNotHandle()
    {
        $this->initializePatcher(
            $this->getDefaultProps(),
            'delete'
        );

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    private function initializePatcher(
        array $propsArray,
        string $type = 'update'
    ) {
        $this->op = OpStandard::factory(
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
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        )->makePartial();
        $mockSubQuestionsService = \Mockery::mock(
            QuestionAggregateService\SubQuestionsService::class
        )->makePartial();
        $mockQuestionService = \Mockery::mock(
            QuestionAggregateService\QuestionService::class
        )->makePartial();

        return new OpHandlerSubQuestion(
            $mockQuestionAggregateService,
            $mockSubQuestionsService,
            $mockQuestionService,
            new TransformerInputQuestionL10ns(),
            new TransformerInputSubQuestion()
        );
    }
}
