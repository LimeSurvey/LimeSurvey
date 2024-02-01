<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

/**
 * This class is responsible for organizing and return of error messages
 * from thrown exceptions during a patch operation
 * for the response of PatcherSurvey.
 */
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
        $this->operationItems['erronousOperations'][] = $operationItem;
    }

    /**
     * Returns the whole response array including all the added erronous
     * operations and the number of applied operations.
     * @return array
     */
    public function getErronousOperationsObject(): array
    {
        return $this->operationItems;
    }
}
