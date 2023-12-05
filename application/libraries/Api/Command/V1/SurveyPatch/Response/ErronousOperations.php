<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

class ErronousOperations
{
    private array $operationItems = [];

    /**
     * Adds an erronousOperationItem containing the error message from the
     * exception and the operation data.
     * @param ErronousOperationItem $operationItem
     * @return void
     */
    public function addErronousOperationItem(
        ErronousOperationItem $operationItem
    ): void {
        $this->operationItems[] = $operationItem;
    }

    /**
     * Returns the whole response array including all the added tempId mappings
     * and the number of applied operations.
     * @return array
     */
    public function getErronousOperationsObject(): array
    {
        return $this->operationItems;
    }
}
