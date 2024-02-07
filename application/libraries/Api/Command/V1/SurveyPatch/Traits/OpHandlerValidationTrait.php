<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Traits;

use LimeSurvey\Api\Command\V1\SurveyPatch\Response\ValidationErrorItem;
use LimeSurvey\ObjectPatch\Op\OpInterface;

trait OpHandlerValidationTrait
{
    /**
     * @param array $validationData
     * @param OpInterface $op
     * @return array|ValidationErrorItem[][]
     */
    public function getValidationReturn(
        string $error,
        array $validationData,
        OpInterface $op
    ): array {
        $validationErrors = [];
        if (!empty($validationData)) {
            $validationErrors = [
                'validationErrors' => [
                    new ValidationErrorItem(
                        $error,
                        $validationData,
                        $op
                    )
                ]
            ];
        }
        return $validationErrors;
    }

    /**
     * checks for entity id being there, otherwise adds error to validationData
     * @param OpInterface $op
     * @param array $validationData
     * @return array
     */
    public function validateEntityId(
        OpInterface $op,
        array $validationData
    ): array {
        $id = $op->getEntityId();
        $hasId = ((int)$id) > 0 || (is_string($id) && $id !== '');
        $error = $hasId ? true : 'No entity id provided';
        if (is_string($error)) {
            $validationData = $this->addErrorToValidationData(
                $error,
                $validationData
            );
        }
        return $validationData;
    }

    /**
     * checks incoming props if it's a collection
     * otherwise adds error to validationData.
     * Basically this only check for the incoming props being
     * a multidimensional array.
     * @param OpInterface $op
     * @param array $validationData
     * @return array
     */
    public function validateCollection(
        OpInterface $op,
        array $validationData
    ): array {
        $props = $op->getProps();
        if (is_array($props) && !empty($props)) {
            $error = is_array(
                $props[array_key_first($props)]
            ) ? true : "Props didn't come as collection";
            if (is_string($error)) {
                $validationData = $this->addErrorToValidationData(
                    $error,
                    $validationData
                );
            }
        }
        return $validationData;
    }

    /**
     * validates for collection first and then
     * checks the indexes to be numeric or alphabetic
     * dependent on the alphabetic flag
     * @param OpInterface $op
     * @param array $validationData
     * @param bool $alphabetic
     * @return array
     */
    public function validateCollectionIndex(
        OpInterface $op,
        array $validationData,
        bool $alphabetic = true
    ): array {
        $validCollectionData = $this->validateCollection($op, $validationData);
        if (empty(array_diff($validCollectionData, $validationData))) {
            if ($alphabetic) {
                $error = 'Index of collection is numeric';
            } else {
                $error = 'Index of collection is not numeric';
            }
            $keys = array_keys($op->getProps());
            foreach ($keys as $key) {
                $valid = $alphabetic ? !is_numeric($key) : is_numeric($key);
                if (!$valid) {
                    $validationData = $this->addErrorToValidationData(
                        $error,
                        $validationData
                    );
                }
            }
        } else {
            // validateCollection found errors
            $validationData = $validCollectionData;
        }
        return $validationData;
    }

    /**
     * Adds the error message to the validationData array
     * @param string $error
     * @param array $validationData
     * @return array
     */
    public function addErrorToValidationData(
        string $error,
        array $validationData
    ): array {
        $validationData[] = $error;

        return $validationData;
    }
}
