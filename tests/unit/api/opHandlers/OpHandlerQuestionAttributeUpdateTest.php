<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionAttributeUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\Models\Services\QuestionAggregateService\AttributesService;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerQuestionAttributeUpdate
 */
class OpHandlerQuestionAttributeUpdateTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox can handle
     */
    public function testQuestionAttributeUpdateCanHandle()
    {
        $this->initializePatcher(
            $this->getCorrectProps()
        );
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox can not handle
     */
    public function testQuestionAttributeUpdateCanNotHandle()
    {
        $this->initializePatcher(
            $this->getCorrectProps(),
            'create'
        );

        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($this->op));
    }

    /**
     * @param array $props
     * @param string $type
     * @return void
     * @throws \LimeSurvey\ObjectPatch\ObjectPatchException
     */
    private function initializePatcher(array $props, string $type = 'update')
    {
        $this->op = OpStandard::factory(
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
        $mockAttributesService = \Mockery::mock(AttributesService::class)
            ->makePartial();
        $mockQuestionService = \Mockery::mock(QuestionService::class)
            ->makePartial();
        $mockQuestionAggregateService = \Mockery::mock(QuestionAggregateService::class)
            ->makePartial();

        return new OpHandlerQuestionAttributeUpdate(
            $mockAttributesService,
            $mockQuestionService,
            $mockQuestionAggregateService,
            new TransformerInputQuestionAttribute()
        );
    }
}
