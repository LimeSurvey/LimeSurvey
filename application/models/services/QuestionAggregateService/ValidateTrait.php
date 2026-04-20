<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use LimeSurvey\Models\Services\{
    Exception\PersistErrorException,
};

/**
 * Question Aggregate Validate Trait
 */
trait ValidateTrait
{
    /**
     * Validate subquestion/answer codes.
     *
     * @param array $requestDataArray Data from request.
     * @return void
     * @throws PersistErrorException
     */
    public function validateCodes($requestDataArray)
    {
        // ensure uniqueness of codes
        $codes = [];
        foreach ($requestDataArray as $dataArray) {
            foreach ($dataArray as $scaleId => $data) {
                if (!isset($codes[$scaleId])) {
                    $codes[$scaleId] = [];
                }
                if (
                    isset($data['code'])
                    && isset($codes[$scaleId])
                    && in_array(
                        $data['code'],
                        $codes[$scaleId]
                    )
                ) {
                    throw new PersistErrorException(
                        'Subquestion/Answer codes must be unique'
                    );
                }
                if (isset($data['code'])) {
                    $codes[$scaleId][] = $data['code'];
                }
            }
        }
    }
}
