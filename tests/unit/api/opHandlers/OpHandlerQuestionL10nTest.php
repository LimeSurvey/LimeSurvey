<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionL10nUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerQuestionL10nUpdate
 */
class OpHandlerQuestionL10nUpdateTest extends TestBaseClass
{
    protected OpInterface $op;

    /**
     * @testdox throws exception when no valid values are provided
     */
    public function testOpQuestionL10nThrowsNoValuesException()
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
     * @testdox can handle a questionL10n update
     */
    public function testOpQuestionL10nUpdateCanHandle()
    {
        $this->initializePatcher(
            $this->getCorrectPropsArray()
        );

        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @testdox cannot handle a questionL10n create
     */
    public function testOpQuestionL10nUpdateCanNotHandle()
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
            'questionL10n',
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
            'en' => [
                'question' => 'test',
                'help'     => 'help'
            ],
            'de' => [
                'question' => 'Frage',
                'help'     => 'Hilfe'
            ],
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
     * @return OpHandlerQuestionL10nUpdate
     */
    private function getOpHandler()
    {
        $mockQuestionL10nService = \Mockery::mock(
            QuestionAggregateService\L10nService::class
        )->makePartial();
        return new OpHandlerQuestionL10nUpdate(
            $mockQuestionL10nService,
            new TransformerInputQuestionL10ns()
        );
    }
}
