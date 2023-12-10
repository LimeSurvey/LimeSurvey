<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAggregate;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerQuestionUpdate
 */
class OpHandlerQuestionUpdateTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox throws exception when no valid values are provided
     */
    public function testOpQuestionUpdateThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $this->initializePatcher(
            $this->getWrongPropsArray()
        );
        $opHandler = $this->getOpHandler();
        $opHandler->handle($this->op);
    }

    /**
     * @testdox getPreparedData() is expected to return a certain data structure
     */
    public function testOpQuestionUpdateDataStructure()
    {
        $this->initializePatcher(
            $this->getCorrectPropsArray()
        );
        $opHandler = $this->getOpHandler();
        $preparedData = $opHandler->getPreparedData($this->op);
        $this->assertArrayHasKey('question', $preparedData);
        $this->assertArrayHasKey('qid', $preparedData['question']);
        $this->assertEquals(77, $preparedData['question']['qid']);
    }

    /**
     * @testdox can handle a question update
     */
    public function testOpQuestionUpdateCanHandle()
    {
        $this->initializePatcher(
            $this->getCorrectPropsArray()
        );

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox cannot handle a question create
     */
    public function testOpQuestionUpdateCanNotHandle()
    {
        $this->initializePatcher(
            $this->getCorrectPropsArray(),
            'create'
        );

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    /**
     * @param array $props
     * @param string $type
     * @return void
     * @throws ObjectPatchException
     */
    private function initializePatcher(array $props, string $type = 'update')
    {
        $this->op = OpStandard::factory(
            'question',
            $type,
            "77",
            $props,
            [
                'id' => 666
            ]
        );
    }

    /**
     * @return array
     */
    private function getCorrectPropsArray()
    {
        return [
            'question' => [
                'title' => 'test title',
                'mandatory' => true,
                'type' => 'S',
                'tempId' => 1
            ],
            'questionL10n' => []
        ];
    }

    /**
     * @return array
     */
    private function getWrongPropsArray()
    {
        return [
            'xxx' => 'test title',
            'yyy' => true,
        ];
    }

    /**
     * @return OpHandlerQuestionUpdate
     */
    private function getOpHandler()
    {
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        )->makePartial();
        return new OpHandlerQuestionUpdate(
            $mockQuestionAggregateService,
            DI::getContainer()->get(TransformerInputQuestionAggregate::class)
        );
    }
}
