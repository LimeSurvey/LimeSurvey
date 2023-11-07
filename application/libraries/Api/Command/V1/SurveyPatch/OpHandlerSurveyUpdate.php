<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\Models\Services\{
    SurveyUpdater,
    Exception\PersistErrorException
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerSurveyUpdate implements OpHandlerInterface
{
    protected string $entity;
    protected Survey $model;
    protected TransformerInputSurvey $transformer;

    public function __construct(Survey $model, TransformerInputSurvey $transformer)
    {
        $this->entity = 'survey';
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
     * @throws PersistErrorException
     */
    public function handle(OpInterface $op): void
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyUpdater = $diContainer->get(
            SurveyUpdater::class
        );

        //here we should get the props from the op
        $props = $op->getProps();
        $transformedProps = $this->transformer->transform($props);

        if ($props === null || $transformedProps === null) {
            throw new OpHandlerException(
                sprintf(
                    'No values to update for entity %s',
                    $op->getEntityType()
                )
            );
        }

        $surveyUpdater->update(
            $op->getEntityId(),
            $transformedProps
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        // TODO: Implement isValidPatch() method.
        return true;
    }
}
