<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyTrait;
use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeCreate;
use LimeSurvey\ObjectPatch\OpType\OpTypeDelete;

class OpHandlerAnswerDelete implements OpHandlerInterface
{

    use OpHandlerSurveyTrait; //todo: not sure if this one will be used

    /*
    protected QuestionAggregateService $questionAggregateService;

    public function __construct(
        QuestionAggregateService $questionAggregateService
    ) {
        $this->questionAggregateService = $questionAggregateService;
    }
    */

    public function canHandle(OpInterface $op): bool
    {
        $isDeleteOperation = $op->getType()->getId() === OpTypeDelete::ID;
        $isAnswerEntity = $op->getEntityType() === 'answer';

        return $isAnswerEntity && $isDeleteOperation;
    }

    /**
     * Deletes an answer from the question.
     * This is the expected structure:
     * "patch": [
     *          {
     *              "entity": "answer",
     *              "op": "delete",
     *              "id": "12345",
     *         }
     *  ]
     *
     * @param OpInterface $op
     * @return void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    public function handle(OpInterface $op)
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionService = $diContainer->get(
            QuestionAggregateService::class
        );

        /**
        $this->questionAggregateService->deleteAnswer(
            $this->getSurveyIdFromContext($op),
            $op->getEntityId()
        ); **/

        $questionService->deleteAnswer($this->getSurveyIdFromContext(), $op->getEntityId());
    }
}
