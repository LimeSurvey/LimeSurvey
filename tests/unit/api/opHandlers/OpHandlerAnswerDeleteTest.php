<?php

namespace ls\tests\unit\api\opHandlers;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerQuestionUpdate;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Libraries\Api\Command\V1\SurveyPatch\OpHandlerAnswerDelete;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use ls\tests\TestBaseClass;

class OpHandlerAnswerDeleteTest extends TestBaseClass
{
    protected OpInterface $op;

    public function tesCanHandleAnswer()
    {
        $this->initializePatcher();
        $opHandler = $this->getOpHandler();
        $this->assertTrue($opHandler->canHandle($this->op));
    }

    /**
     * @param string $entityType
     * @param string $operation
     * @return void
     * @throws ObjectPatchException
     */
    private function initializePatcher(string $entityType = 'answer', string $operation = 'delete')
    {
        $this->op = OpStandard::factory(
            $entityType,
            $operation,
            "77",
        );
    }

    /**
     * @return OpHandlerAnswerDelete
     */
    private function getOpHandler()
    {
        $mockQuestionAggregateService = \Mockery::mock(
            QuestionAggregateService::class
        )->makePartial();
        return new OpHandlerAnswerDelete($mockQuestionAggregateService);
    }
}
