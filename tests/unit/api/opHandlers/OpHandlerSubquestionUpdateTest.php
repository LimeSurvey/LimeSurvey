<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSubQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSubQuestion;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerSubQuestion
 */
class OpHandlerSubquestionUpdateTest extends TestBaseClass
{
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

    /**
     * @testdox validation hits
     */
    public function testOpValidationFailure()
    {
        $opHandler = $this->getOpHandler();
        $op = $this->getOp(
            $this->getWrongProps(true),
        );
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
        $op = $this->getOp(
            $this->getWrongProps()
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
            $this->getDefaultProps()
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
     * @throws ObjectPatchException
     */
    private function getOp(array $props = [], $type = 'update'): OpStandard
    {
        return OpStandard::factory(
            'subquestion',
            $type,
            123,
            $props,
            [
                'id' => 123456
            ]
        );
    }

    /**
     * @param string $operation
     * @return array
     */
    private function getDefaultProps(string $operation = 'update'): array
    {
        $props = [
            '0' => [
                'qid' => 126,
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
        if ($operation === 'create') {
            $props[0]['tempId'] = 'XXX125';
            unset($props[0]['qid']);
        }
        return $props;
    }

    private function getWrongProps($wrongIndex = false, $operation = 'create'): array
    {
        $props = $this->getDefaultProps($operation);
        if ($wrongIndex) {
            $props['alphabetic'] = $props[0];
        }
        return $props;
    }

    /**
     * @return OpHandlerSubQuestion
     */
    private function getOpHandler(): OpHandlerSubQuestion
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

        return new OpHandlerSubQuestion(
            $mockQuestionAggregateService,
            $mockSubQuestionsService,
            $mockQuestionService,
            DI::getContainer()->get(TransformerInputSubQuestion::class)
        );
    }
}
