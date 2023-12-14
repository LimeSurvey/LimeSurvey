<?php

namespace ls\tests\unit\api\opHandlers;

use Mockery;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\QuestionGroupService;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionGroup;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionGroupAggregate;
use LimeSurvey\ObjectPatch\{
    Op\OpStandard,
    OpHandler\OpHandlerException
};
use ls\tests\TestBaseClass;
use ls\tests\unit\services\QuestionGroup\QuestionGroupMockSetFactory;

class OpHandlerQuestionGroupTest extends TestBaseClass
{
    public function testOpQuestionGroupThrowsNoValuesException()
    {
        $this->expectException(
            OpHandlerException::class
        );
        $op = $this->getOpWrongPropsPatcher();
        $opHandler = $this->getOpHandler();
        $opHandler->setOperationTypes($op);
        $opHandler->handle($op);
    }

    public function testOpQuestionGroupCanHandle()
    {
        $op = $this->getOpPatcher();
        self::assertTrue($this->getOpHandler()->canHandle($op));
    }

    public function testOpQuestionGroupCanNotHandle()
    {
        $op = $this->getOpWrongEntityPatcher();
        self::assertFalse($this->getOpHandler()->canHandle($op));
    }

    private function getOpPatcher()
    {
        return OpStandard::factory(
            'questionGroup',
            'update',
            12345,
            [
                'groupOrder' => '1000'
            ],
            [
                'id' => 123456
            ]
        );
    }

    private function getOpWrongEntityPatcher()
    {
        return OpStandard::factory(
            'survey',
            'update',
            null,
            [
                'unknownA' => '2020-01-01 00:00',
                'unknownB' => true,
            ],
            [
                'id' => 123456
            ]
        );
    }

    private function getOpWrongPropsPatcher()
    {
        return OpStandard::factory(
            'questionGroup',
            'update',
            12345,
            [
                'unknownA' => '2020-01-01 00:00',
                'unknownB' => true,
            ],
            [
                'id' => 123456
            ]
        );
    }

    /**
     * @return OpHandlerQuestionGroup
     */
    private function getOpHandler()
    {
        $mockSet = (new QuestionGroupMockSetFactory())->make();
        /** @var QuestionGroupService */
        $mockQuestionGroupService = Mockery::mock(QuestionGroupService::class)
            ->makePartial();
        return new OpHandlerQuestionGroup(
            $mockSet->modelQuestionGroup,
            $mockQuestionGroupService,
            DI::getContainer()->get(TransformerInputQuestionGroupAggregate::class)
        );
    }
}
