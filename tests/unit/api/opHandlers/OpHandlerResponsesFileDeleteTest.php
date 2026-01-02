<?php

namespace api\opHandlers;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch\OpHandlerResponsesFileDelete;
use LimeSurvey\Models\Services\SurveyResponseService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

/**
 * @testdox OpHandlerResponsesUpdate
 */
class OpHandlerResponsesFileDeleteTest extends TestBaseClass
{
    /**
     * @testdox can handle a response-file delete
     */
    public function testOpResponseUpdateCanHandle()
    {
        $op = $this->getOp();
        $opHandler = $this->getOpHandler();
        self::assertTrue($opHandler->canHandle($op));
    }

    /**
     * @testdox cannot handle a response-file create
     */
    public function testOpQuestionUpdateCanNotHandle()
    {
        $op = $this->getOp(
            'response-file',
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
            'response-file',
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
        $op = $this->getOp();
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
    private function getOp(string $entity = 'response-file', string $type = 'delete')
    {
        return OpStandard::factory(
            $entity,
            $type,
            $type !== 'delete' ? null : "7",
            [],
            [
                'id' => 7
            ]
        );
    }

    /**
     * @return OpHandlerResponsesFileDelete
     */
    private function getOpHandler(): OpHandlerResponsesFileDelete
    {
        /** @var SurveyResponseService $mockSurveyResponseService */
        $mockSurveyResponseService = \Mockery::mock(
            SurveyResponseService::class
        )->makePartial();
        return new OpHandlerResponsesFileDelete(
            $mockSurveyResponseService
        );
    }
}
