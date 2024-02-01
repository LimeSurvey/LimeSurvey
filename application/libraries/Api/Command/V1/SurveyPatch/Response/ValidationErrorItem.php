<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

use LimeSurvey\ObjectPatch\Op\OpStandard;

class ValidationErrorItem
{
    /**
     * @var array|null the error message(s) from the validation error
     */
    public array $errorMessages = [];
    /**
     * @var array the operation data which came from the client
     */
    public array $patchOpData = [];

    /**
     * @param array|null $errorMessages
     * @param OpStandard $patchOpData
     */
    public function __construct(
        ?array $errorMessages,
        OpStandard $patchOpData
    ) {
        $this->errorMessages = $errorMessages;
        $this->patchOpData['entity'] = $patchOpData->getEntityType();
        $this->patchOpData['op'] = $patchOpData->getType()->getId();
        $this->patchOpData['id'] = $patchOpData->getEntityId();
    }
}
