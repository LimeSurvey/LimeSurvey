<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Models\Services\{
    QuestionAggregateService\AnswersService,
    QuestionAggregateService\QuestionService
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    Op\OpStandard
};
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerAnswer
 */
class OpHandlerAnswerTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox can handle create
     */
    public function testAnswerCanHandle()
    {
        $this->initializePatcher(
            $this->getCorrectProps()
        );

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox can handle update
     */
    public function testAnswerCanHandleUpdate()
    {
        $this->initializePatcher(
            $this->getCorrectProps(),
            'update'
        );

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox can not handle delete
     */
    public function testAnswerCanNotHandle()
    {
        $this->initializePatcher(
            $this->getCorrectProps(),
            'delete'
        );

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    /**
     * @testdox scale_id is used as second index of produced array
     * @todo this should be a transformer test
     */
    /*
    public function testAnswerDataStructure()
    {
        $this->initializePatcher(
            $this->getCorrectProps()
        );

        $opHandler = $this->getOpHandler();
        $data = $this->op->getProps();
        $preparedData = $opHandler->prepareAnswers(
            $this->op,
            $data,
            new TransformerInputAnswer(),
            new TransformerInputAnswerL10ns(),
            ['answer', 'answerL10n']
        );

        self::assertIsArray($preparedData);
        self::assertArrayHasKey(0, $preparedData);
        self::assertArrayHasKey(1, $preparedData);
        self::assertArrayHasKey(0, $preparedData[0]);
        self::assertArrayHasKey(1, $preparedData[1]);
    }
    */

    /**
     * @param array $props
     * @param string $type
     * @return void
     * @throws \LimeSurvey\ObjectPatch\ObjectPatchException
     */
    private function initializePatcher(array $props, string $type = 'create')
    {
        $this->op = OpStandard::factory(
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
        $mockAnswersService = \Mockery::mock(
            AnswersService::class
        )->makePartial();
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
