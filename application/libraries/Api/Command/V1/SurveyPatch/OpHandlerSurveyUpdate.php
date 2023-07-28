<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use CModel;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeUpdate;
use Survey;

class OpHandlerSurveyUpdate implements OpHandlerInterface
{
    protected ?TransformerInterface $transformer = null;
    protected string $entity;
    protected CModel $model;

    public function __construct(string $entity, CModel $model, TransformerInterface $transformer = null)
    {
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
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isSurveyEntity = $op->getEntityType() === 'survey';

        return $isUpdateOperation && $isSurveyEntity;
    }

    /**
     * Saves the changes to the database.
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     */
    public function handle(OpInterface $op): void
    {
        $survey = $this->model->findByPk($op->getEntityId());
        //here we should get the props from the op
        $props = $op ->getProps();
        if ($props === null) {
            throw new OpHandlerException(
                printf(
                    'No values to update for entity %s',
                    $op->getEntityType()
                )
            );
        }
        $survey->setAttributes($props);
        if (!$survey->save()) {
            throw new OpHandlerException(
                printf(
                    'Could not update survey (id: %s)',
                    $op->getEntityId()
                )
            );
        }
    }
}
