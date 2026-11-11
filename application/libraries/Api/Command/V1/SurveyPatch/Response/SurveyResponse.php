<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

/**
 * This class is responsible for preparing and
 * building the whole response for PatcherSurvey
 */
class SurveyResponse
{
    private int $operationsApplied = 0;
    private bool $isValidOperation = true;
    protected TempIdMapping $tempIdMapping;
    protected ValidationErrors $validationErrors;
    protected ExceptionErrors $exceptionErrors;
    protected array $additional = [];

    /**
     * @param TempIdMapping $tempIdMapping
     * @param ValidationErrors $validationErrors
     * @param ExceptionErrors $exceptionErrors
     */
    public function __construct(
        TempIdMapping $tempIdMapping,
        ValidationErrors $validationErrors,
        ExceptionErrors $exceptionErrors
    ) {
        $this->tempIdMapping = $tempIdMapping;
        $this->validationErrors = $validationErrors;
        $this->exceptionErrors = $exceptionErrors;
    }

    /**
     * @param array $response
     * @return void
     */
    public function handleResponse(array $response): void
    {
        $this->extractTempIdMapping($response);
        $this->extractValidationErrors($response);
        $this->extractAdditional($response);
        if ($this->isValidOperation) {
            $this->incrementOperationsApplied();
        }
    }

    /**
     * @param \Exception $e
     * @param OpInterface $patchOpData
     * @return void
     */
    public function handleException(\Exception $e, OpInterface $patchOpData): void
    {
        // add error message and full operation info to ErrorItemList
        $exceptionErrorItem = new ExceptionErrorItem(
            $this->getExceptionMessage($e),
            (int)$e->getCode(),
            $patchOpData
        );
        $this->exceptionErrors->addExceptionErrorItem(
            $exceptionErrorItem
        );
    }

    /**
     * Returns the exception message, extended by the validation errors of the
     * model attached to the exception (if any), so the editor can show the
     * actual reason (e.g. free plan content restrictions).
     *
     * @param \Exception $e
     * @return string
     */
    private function getExceptionMessage(\Exception $e): string
    {
        $message = $e->getMessage();
        if (
            !($e instanceof \LimeSurvey\Models\Services\Exception)
            || !$e->getErrorModel()
        ) {
            return $message;
        }
        $modelErrors = array_unique(
            array_merge(...array_values($e->getErrorModel()->getErrors()))
        );
        if (empty($modelErrors)) {
            return $message;
        }
        return $message . ': ' . implode(' ', $modelErrors);
    }

    public function incrementOperationsApplied(): void
    {
        $this->operationsApplied++;
    }

    /**
     * Extracts possible tempIdMapping from the $operationData array
     * @param array $handleResponse
     * @return void
     * @throws OpHandlerException
     */
    public function extractTempIdMapping(array $handleResponse)
    {
        if (array_key_exists('tempIdMapping', $handleResponse)) {
            foreach ($handleResponse['tempIdMapping'] as $groupName => $mappingItem) {
                $this->tempIdMapping->addTempIdMapItems($mappingItem, $groupName);
            }
        }
    }

    /**
     * Extracts possible validationErrors from the $operationData array
     * @param array $handleResponse
     * @return void
     * @throws OpHandlerException
     */
    public function extractValidationErrors(array $handleResponse)
    {
        $this->isValidOperation = true;
        if (array_key_exists('validationErrors', $handleResponse)) {
            foreach ($handleResponse['validationErrors'] as $validationErrorItem) {
                $this->validationErrors->addValidationErrorItem(
                    $validationErrorItem
                );
            }
            $this->isValidOperation = false;
        }
    }

    /**
     * Extracts additional fields into 'extras'.
     * The additional data of every operation in the patch is merged recursively,
     * so keyed data (e.g. questionRelevance by qid) of earlier operations is kept.
     * Additional values must be scalars or keyed maps, not lists: Lists are
     * merged by index (array_replace_recursive), so a later operation's list
     * would overwrite the items of an earlier one instead of being appended.
     * @param array $handleResponse the response of one operation handler,
     *   whose 'additional' entry (if any) is merged into 'extras'
     * @return void nothing: The merged data is kept in $this->additional
     */
    public function extractAdditional(array $handleResponse)
    {
        if (array_key_exists('additional', $handleResponse)) {
            $this->additional['extras'] = array_replace_recursive(
                $this->additional['extras'] ?? [],
                $handleResponse['additional']
            );
        }
    }

    /**
     * Whether the patch created questions or subquestions, i.e. new response
     * fields: Their field names are then returned (setQuestionFieldnames).
     *
     * @return bool True when the temp ID mapping has questions or subquestions
     */
    public function hasCreatedQuestionFields(): bool
    {
        $mapping = $this->tempIdMapping->getMappingResponseObject()['tempIdMapping'] ?? [];
        return !empty($mapping['questionsMap']) || !empty($mapping['subquestionsMap']);
    }

    /**
     * Adds the field names of the survey's questions to the response
     * ('extras.questionFieldnames'), so the editor knows those of the new
     * questions without fetching them again.
     *
     * @param array $questionFieldnames The fields of each question, keyed by question ID
     *   (see QuestionFieldnames::getBySurvey())
     * @return void
     */
    public function setQuestionFieldnames(array $questionFieldnames): void
    {
        if (!empty($questionFieldnames)) {
            $this->additional['extras']['questionFieldnames'] = $questionFieldnames;
        }
    }

    /**
     * Builds the response of the whole patch.
     *
     * @return array The operations applied, temp ID mappings, errors and extras
     */
    public function buildResponseObject(): array
    {
        return array_merge(
            [
                'operationsApplied' => $this->operationsApplied,
            ],
            $this->tempIdMapping->getMappingResponseObject(),
            $this->validationErrors->getValidationErrorsObject(),
            $this->exceptionErrors->getExceptionErrorsObject(),
            $this->additional
        );
    }
}
