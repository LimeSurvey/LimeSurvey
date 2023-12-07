<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

class ErronousOperationItem
{
    /**
     * @var string|null the error message from the exception
     */
    public ?string $errorMessage;
    /**
     * @var array|null the operation data which came from the client
     */
    public ?array $patchOpData;

    /**
     * @param string|null $errorMessage
     * @param array|null $patchOpData
     */
    public function __construct(
        ?string $errorMessage,
        ?array $patchOpData
    ) {
        $this->errorMessage = $errorMessage;
        $this->patchOpData = $patchOpData;
    }
}
