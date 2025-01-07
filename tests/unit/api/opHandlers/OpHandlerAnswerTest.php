<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputAnswer;
use LimeSurvey\Models\Services\{
    QuestionAggregateService,
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
     * @testdox validation hits
     */
    public function testOpValidationFailure()
    {
        $opHandler = $this->getOpHandler();
        $op = $this->getOp(
            $this->getWrongProps(true, 'update'),
            'update'
        );
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
        $op = $this->getOp(
            $this->getWrongProps(),
            'update'
        );
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
    }

    /**
     * @testdox validation doesn't hit when everything is fine
     */
    public function testOpValidationSuccess()
    {
        $op = $this->getOp(
            $this->getCorrectProps()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertEmpty($validation);
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
            888,
            $props,
            [
                'id' => 123456,
            ]
        );
    }

    /**
     * @param string $operation
     * @return array
     * @throws \Exception
     */
    private function getCorrectProps($operation = 'create'): array
    {
        $answer = [
            'code' => 'AO01',
            'scaleId' => '0',
            'l10ns' => [
                'en' => [
                    'answer' => 'answer',
                    'language' => 'en'
                ],
                'de' => [
                    'answer' => 'answerger',
                    'language' => 'de'
                ]
            ]
        ];
        if ($operation === 'create') {
            $answer['tempId'] = '222';
        } else {
            $answer['aid'] = random_int(1, 1000);
        }
        return [
            '0' => $answer,
        ];
    }

    private function getWrongProps(
        $wrongIndex = false,
        $operation = 'create'
    ): array {
        $props = $this->getCorrectProps($operation);
        if ($wrongIndex) {
            $props['alphabetic'] = $props[0];
        }
        return $props;
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
        /** @var QuestionAggregateService */
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        );
        return new OpHandlerAnswer(
            DI::getContainer()->get(TransformerInputAnswer::class),
            $mockAnswersService,
            $mockQuestionService,
            $mockQuestionAggregateService
        );
    }
}
