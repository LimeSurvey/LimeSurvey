<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionAttributeUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\{
    QuestionAggregateService,
    QuestionAggregateService\AttributesService,
    QuestionAggregateService\QuestionService
};
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerQuestionAttributeUpdate
 */
class OpHandlerQuestionAttributeUpdateTest extends TestBaseClass
{
    /**
     * @testdox can handle
     */
    public function testQuestionAttributeUpdateCanHandle()
    {
        $op = $this->getOp(
            $this->getCorrectProps()
        );
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox can not handle
     */
    public function testQuestionAttributeUpdateCanNotHandle()
    {
        $op = $this->getOp(
            $this->getCorrectProps(),
            'create'
        );
        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($op));
    }

    /**
     * @param array $props
     * @param string $type
     * @return OpStandard
     * @throws \LimeSurvey\ObjectPatch\OpHandlerException
     */
    private function getOp(array $props, string $type = 'update')
    {
        return OpStandard::factory(
            'questionAttribute',
            $type,
            123,
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
            "public_statistics" => [
                '' => [
                    'value' => '1'
                ]
            ],
            'dualscale_headerA' => [
                'en' => [
                    'value' => 'Header Text'
                ],
                'de' => [
                    'value' => 'Kopf Text'
                ]
            ]
        ];
    }

    /**
     * @return OpHandlerQuestionAttributeUpdate
     */
    private function getOpHandler()
    {
        $transformer = DI::getContainer()->get(TransformerInputQuestionAttribute::class);
        /** @var AttributesService */
        $mockAttributesService = \Mockery::mock(AttributesService::class)
            ->makePartial();
        /** @var QuestionService */
        $mockQuestionService = \Mockery::mock(QuestionService::class)
            ->makePartial();
        /** @var QuestionAggregateService */
        $mockQuestionAggregateService = \Mockery::mock(QuestionAggregateService::class)
            ->makePartial();
        return new OpHandlerQuestionAttributeUpdate(
            $mockAttributesService,
            $mockQuestionService,
            $mockQuestionAggregateService,
            $transformer
        );
    }
}
