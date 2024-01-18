<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\{
    ObjectPatchException,
    Op\OpStandard,
    OpHandler\OpHandlerException
};
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerQuestionUpdate
 */
class OpHandlerQuestionUpdateTest extends TestBaseClass
{
    /**
     * @testdox throws exception on missing required parameters (tempId)
     */
    public function testOpQuestionUpdateThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $op = $this->getOp(
            $this->getWrongPropsArray(),
            'create'
        );
        $opHandler = $this->getOpHandler();
        $opHandler->handle($op);
    }

    /**
     * @testdox getPreparedData() is expected to return a certain data structure
     */
    public function testOpQuestionUpdateDataStructure()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray()
        );
        $opHandler = $this->getOpHandler();
        $preparedData = $opHandler->getPreparedData($op);
        $this->assertArrayHasKey('qid', $preparedData);
        $this->assertEquals(77, $preparedData['qid']);
    }

    /**
     * @testdox can handle a question update
     */
    public function testOpQuestionUpdateCanHandle()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray()
        );
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox cannot handle a question create
     */
    public function testOpQuestionUpdateCanNotHandle()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray(),
            'create'
        );
        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($op));
    }

    /**
     * @param array $props
     * @param string $type
     * @return OpStandard
     * @throws ObjectPatchException
     */
    private function getOp(array $props, string $type = 'update')
    {
        return OpStandard::factory(
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
            'title' => 'test title',
            'mandatory' => true,
            'type' => 'S',
            'tempId' => 1
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
        /** @var \LimeSurvey\Models\Services\QuestionAggregateService */
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        )->makePartial();
        return new OpHandlerQuestionUpdate(
            $mockQuestionAggregateService,
            DI::getContainer()->get(TransformerInputQuestion::class)
        );
    }
}
