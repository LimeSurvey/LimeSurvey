<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Models\Services\{
    QuestionAggregateService\AnswersService,
    QuestionAggregateService\QuestionService
};
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerAnswer
 */
class OpHandlerAnswerTest extends TestBaseClass
{
    /**
     * @testdox can handle create
     */
    public function testAnswerCanHandle()
    {
        $op = $this->getOp(
            $this->getCorrectProps()
        );
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox can handle update
     */
    public function testAnswerCanHandleUpdate()
    {
        $op = $this->getOp(
            $this->getCorrectProps(),
            'update'
        );
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox can not handle delete
     */
    public function testAnswerCanNotHandle()
    {
        $op = $this->getOp(
            $this->getCorrectProps(),
            'delete'
        );
        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($op));
    }

    /**
     * @param array $props
     * @param string $type
     * @return OpStandard
     */
    private function getOp(array $props, string $type = 'create')
    {
        return OpStandard::factory(
            'answer',
            $type,
            0,
            $props,
            [
                'id' => 123456,
            ]
        );
    }

    /**
     * @return array
     */
    private function getCorrectProps()
    {
        return [
            '0' => [
                'code'     => 'AO01',
                'scaleId' => '0',
                'l10ns'    => [
                    'en' => [
                        'answer' => 'answer'
                    ],
                    'de' => [
                        'answer' => 'answerger'
                    ]
                ]
            ],
            '1' => [
                'code'     => 'AO01',
                'scaleId' => '1',
                'l10ns'    => [
                    'en' => [
                        'answer' => 'answer'
                    ],
                    'de' => [
                        'answer' => 'answerger'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return OpHandlerAnswer
     */
    private function getOpHandler(): OpHandlerAnswer
    {
        /** @var AnswersService */
        $mockAnswersService = \Mockery::mock(
            AnswersService::class
        )->makePartial();
        /** @var QuestionService */
        $mockQuestionService = \Mockery::mock(
            QuestionService::class
        )->makePartial();
        return new OpHandlerAnswer(
            DI::getContainer()->get(TransformerInputAnswer::class),
            $mockAnswersService,
            $mockQuestionService
        );
    }
}
