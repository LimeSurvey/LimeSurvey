<?php

namespace api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch\OpHandlerResponsesUpdate;
use LimeSurvey\Models\Services\SurveyResponseService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerResponsesUpdate
 */
class OpHandlerResponsesUpdateTest extends TestBaseClass
{
    /**
     * @testdox can handle a response update
     */
    public function testOpResponseUpdateCanHandle()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray()
        );
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox cannot handle a response create
     */
    public function testOpQuestionUpdateCanNotHandle()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray(),
            'response',
            'create'
        );
        $opHandler = $this->getOpHandler();
        self::assertFalse($opHandler->canHandle($op));
    }

    /**
     * @testdox validation hits
     */
    public function testOpResponseValidationFailure()
    {
        $op = $this->getOp(
            $this->getCorrectPropsArray(),
            'question',
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
    public function testOpResponseValidationSuccess()
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
    private function getOp(array $props, string $entity = 'response', string $type = 'update')
    {
        return OpStandard::factory(
            $entity,
            $type,
            $type !== 'update' ? null : "77",
            $props,
            [
                'id' => 77
            ]
        );
    }

    /**
     * @return array
     */
    private function getCorrectPropsArray(): array
    {
        return [
            'token' => 'test',
            'startlanguage' => "ar"
        ];
    }

    /**
     * @return OpHandlerResponsesUpdate
     */
    private function getOpHandler(): OpHandlerResponsesUpdate
    {
        /** @var SurveyResponseService $mockSurveyResponseService */
        $mockSurveyResponseService = \Mockery::mock(
            SurveyResponseService::class
        )->makePartial();
        return new OpHandlerResponsesUpdate(
            $mockSurveyResponseService
        );
    }
}
