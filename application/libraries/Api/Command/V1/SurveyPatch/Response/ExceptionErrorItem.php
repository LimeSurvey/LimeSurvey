<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

use LimeSurvey\ObjectPatch\Op\OpInterface;

class ExceptionErrorItem
{
    /**
     * @var string|null the error message from the exception
     */
    public ?string $error;

    /**
     * @var string the name of the entity of that operation
     */
    public string $entity;

    /**
     * @var string|null the id of that operation
     */
    public ?string $id;

    /**
     * @var string the name of the op of that operation
     */
    public string $op;

    /**
     * @var array the context of the whole patch
     */
    public array $context;

    /**
     * @param string|null $errorMessage
     * @param OpInterface $patchOpData
     */
    public function __construct(
        ?string $errorMessage,
        OpInterface $patchOpData
    ) {
        $this->error = $errorMessage;
        $this->entity = $patchOpData->getEntityType();
        $this->op = $patchOpData->getType()->getId();
        $this->id = $patchOpData->getEntityId();
        $this->context = $patchOpData->getContext();
    }
}
