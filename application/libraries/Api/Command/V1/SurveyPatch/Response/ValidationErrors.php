<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

/**
 * This class is responsible for handling and returning
 * transformer validation errors of an operation
 * for the response of PatcherSurvey
 */
class ValidationErrors
{
    private array $validationErrorItems = [];

    /**
     * Adds an validationErrorItem containing the error message(s) from the
     * failed transformation and the depending operation data.
     * @param ValidationErrorItem $operationItem
     * @return void
     */
    public function addValidationErrorItem(
        ValidationErrorItem $operationItem
    ): void {
        $this->validationErrorItems['validationErrors'][] = $operationItem;
    }

    /**
     * Returns the whole response array including all validation errors.
     * @return array
     */
    public function getValidationErrorsObject(): array
    {
        return $this->validationErrorItems;
    }
}
