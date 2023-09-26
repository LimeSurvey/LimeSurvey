<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroupL10n;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSubquestionUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupL10ns;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSubQuestion;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    Op\OpStandard,
    OpHandler\OpHandlerException
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

/**
 * @testdox OpHandlerSubquestionUpdate
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
     * @testdox can not handle a subquestion create
     */
    public function testOpSubquestionUpdateCanNotHandle()
    {
        $this->initializePatcher(
            $this->getDefaultProps(),
            'create'
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
                'title' => 'SQ001',
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
     * @return OpHandlerSubquestionUpdate
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

        return new OpHandlerSubquestionUpdate(
            $mockQuestionAggregateService,
            $mockSubQuestionsService,
            $mockQuestionService,
            new TransformerInputQuestionL10ns(),
            new TransformerInputSubQuestion()
        );
    }
}
