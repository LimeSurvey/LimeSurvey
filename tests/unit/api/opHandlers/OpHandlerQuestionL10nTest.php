<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionL10nUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionL10ns;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\{
    QuestionAggregateService,
    QuestionAggregateService\L10nService
};
use LimeSurvey\ObjectPatch\{
    ObjectPatchException,
    Op\OpStandard,
};
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerQuestionL10nUpdate
 */
class OpHandlerQuestionL10nUpdateTest extends TestBaseClass
{
    /**
     * @testdox can handle a questionL10n update
     */
    public function testOpQuestionL10nUpdateCanHandle()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray()
        );
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox cannot handle a questionL10n create
     */
    public function testOpQuestionL10nUpdateCanNotHandle()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray(),
            'create'
        );
        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($op));
    }

    /**
     * @testdox validation hits
     */
    public function testOpQuestionGroupValidationFailure()
    {
        $op = $this->getOp(
            $this->getWrongPropsArray()
        );
        $opHandler = $this->getOpHandler();
        $validation = $opHandler->validateOperation($op);
        $this->assertIsArray($validation);
        $this->assertNotEmpty($validation);
    }

    /**
     * @testdox validation doesn't hit when everything is fine
     */
    public function testOpQuestionGroupValidationSuccess()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray()
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
    private function getOp(array $props, string $type = 'update')
    {
        return OpStandard::factory(
            'questionL10n',
            $type,
            '77',
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
                'help' => 'help'
            ],
            'de' => [
                'question' => 'Frage',
                'help' => 'Hilfe'
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
        /** @var \LimeSurvey\Models\Services\QuestionAggregateService\L10nService */
        $mockQuestionL10nService = \Mockery::mock(
            L10nService::class
        )->makePartial();
        /** @var QuestionAggregateService */
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        );
        return new OpHandlerQuestionL10nUpdate(
            $mockQuestionL10nService,
            DI::getContainer()->get(TransformerInputQuestionL10ns::class),
            $mockQuestionAggregateService
        );
    }
}
