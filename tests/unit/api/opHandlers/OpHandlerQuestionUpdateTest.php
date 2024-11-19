<?php

namespace ls\tests\unit\api\opHandlers;

use DI\DependencyException;
use DI\NotFoundException;
use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\{
    ObjectPatchException,
    Op\OpStandard,
};
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerQuestionUpdate
 */
class OpHandlerQuestionUpdateTest extends TestBaseClass
{
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
     * @testdox validation hits
     */
    public function testOpQuestionGroupValidationFailure()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray(),
            'create'
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
        $entityId = $type !== 'update' ? null : "77";
        return OpStandard::factory(
            'question',
            $type,
            $entityId,
            $props,
            [
                'id' => 666
            ]
        );
    }

    /**
     * @return array
     */
    private function getCorrectPropsArray(): array
    {
        return [
            'title' => 'test title',
            'mandatory' => true,
            'type' => 'S',
        ];
    }

    /**
     * @return OpHandlerQuestionUpdate
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function getOpHandler(): OpHandlerQuestionUpdate
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
