<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use CModel;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\Models\Services\QuestionGroupService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeUpdate;

class OpHandlerQuestionGroupL10n implements OpHandlerInterface
{
    protected TransformerInterface $transformer;
    protected string $entity;
    protected CModel $model;

    public function __construct(
        string $entity,
        CModel $model,
        TransformerInterface $transformer,
    ) {
        $this->entity = $entity;
        $this->model = $model;
        $this->transformer = $transformer;
    }

    /**
     * Checks if the operation is applicable for the given entity.
     *
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return
            $op->getType()->getId() === OpTypeUpdate::ID
            && $op->getEntityType() === 'questionGroupL10n';
    }

    /**
     * Saves the changes to the database.
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionGroupService = $diContainer->get(
            QuestionGroupService::class
        );
//        $questionGroupId = $op->getEntityId()['gid'];
//        $transformedProps = $this->transformer->transform($op->getProps());
//        $questionGroup = $questionGroupService->getQuestionGroupForUpdate($questionGroupId);
//        $questionGroupService->updateQuestionGroupLanguages($questionGroup, $transformedProps);
    }

}