<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerAnswerCreate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswerL10ns;
use LimeSurvey\Models\Services\QuestionAggregateService\AnswersService;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerAnswerCreate
 */
class OpHandlerAnswerCreateTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox can handle correct patch
     */
    public function testAnswerCreateCanHandle()
    {
        $this->initializePatcher(
            $this->getCorrectProps()
        );

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox can not handle incorrect patch
     */
    public function testAnswerCreateCanNotHandle()
    {
        $this->initializePatcher(
            $this->getCorrectProps(),
            'update'
        );

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    /**
     * @testdox scaleId is used as second index of produced array
     */
    public function testAnswerCreateDataStructure()
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
//        $this->assertArrayHasKey('answer', $preparedData);
    }

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
                'scale_id' => '0',
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
                'scale_id' => '1',
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
     * @return array[]
     */
    private function getIncompleteProps()
    {
        return [
            'question' => [
                'title'               => 'G01Q01',
                'type'                => '1',
                'question_theme_name' => 'arrays\/dualscale',
                'gid'                 => '50',
                'mandatory'           => false
            ]
        ];
    }

    /**
     * @return OpHandlerAnswerCreate
     */
    private function getOpHandler(): OpHandlerAnswerCreate
    {
        $mockAnswersService = \Mockery::mock(
            AnswersService::class
        )->makePartial();
        $mockQuestionService = \Mockery::mock(
            QuestionService::class
        )->makePartial();
        return new OpHandlerAnswerCreate(
            new TransformerInputAnswer(),
            new TransformerInputAnswerL10ns(),
            $mockAnswersService,
            $mockQuestionService
        );
    }
}
