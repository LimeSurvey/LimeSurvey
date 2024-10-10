<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

/**
 * This class is responsible for organizing and return of error messages
 * from thrown exceptions during a patch operation
 * for the response of PatcherSurvey.
 */
class ExceptionErrors
{
    private array $operationItems = [];

    /**
     * Adds an exceptionErrorItem containing the error message from the
     * exception and the operation data.
     * @param ExceptionErrorItem $operationItem
     * @return void
     */
    public function addExceptionErrorItem(
        ExceptionErrorItem $operationItem
    ): void {
        $this->operationItems['exceptionErrors'][] = $operationItem;
    }

    /**
     * Returns the whole response array including all the added exception errors.
     * @return array
     */
    public function getExceptionErrorsObject(): array
    {
        return $this->operationItems;
    }
}
