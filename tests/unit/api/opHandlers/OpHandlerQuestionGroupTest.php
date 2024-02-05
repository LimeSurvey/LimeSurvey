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

/**
 * @testdox OpHandlerQuestionGroupTest
 */
class OpHandlerQuestionGroupTest extends TestBaseClass
{
    /**
     * @testdox can handle
     */
    public function testOpQuestionGroupCanHandle()
    {
        $op = $this->getOp($this->getPropsValid());
        self::assertTrue($this->getOpHandler()->canHandle($op));
    }

    /**
     * @testdox can not handle
     */
    public function testOpQuestionGroupCanNotHandle()
    {
        $op = $this->getOp(
            $this->getPropsValid(),
            'update',
            'not-questionGroup'
        );
        self::assertFalse($this->getOpHandler()->canHandle($op));
    }

    /**
     * @param array $props
     * @param string $type
     * @param string $entity
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\OpHandlerException
     */
    private function getOp($props = [], $type = 'update', $entity = 'questionGroup')
    {
        return OpStandard::factory(
            $entity,
            $type,
            12345,
            $props,
            [
                'id' => 123456
            ]
        );
    }

    /**
     * @return array
     */
    private function getPropsValid()
    {
        return [
            'groupOrder' => '1000'
        ];
    }

    /**
     * @return array
     */
    private function getPropsInvalid()
    {
        return [
            'unknownA' => '2020-01-01 00:00',
            'unknownB' => true,
        ];
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
