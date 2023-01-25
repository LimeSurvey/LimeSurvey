<?php

namespace LimeSurvey\Models\Services\SurveyPatch\OperationHandler;

use Survey;
use LimeSurvey\Models\Services\SurveyPatch\Path;
use LimeSurvey\Models\Services\SurveyPatch\PathMatch;
use LimeSurvey\Models\Services\SurveyPatch\Operation;
use LimeSurvey\Models\Services\SurveyPatch\OperationHandlerInterface;


/**
 * Operation handler survey update
 *
 */
class OperationHandlerSurveyUpdate implements OperationHandlerInterface
{
    /**
     * Get model class
     *
     * @return string
     */
    public function getModelClass()
    {
        return Survey::class;
    }

    /**
     * Get path type
     *
     * @return string
     */
    public function getPathType()
    {
        return Path::PATH_TYPE_PROP;
    }

    /**
     * Get operation type
     *
     * @return string
     */
    public function getOperationType()
    {
        return Operation::OPERATION_TYPE_UPDATE;
    }

    /**
     * Apply patch
     *
     * @param Operation $operation
     * @param PathMatch $pathMatch
     * @param array $context
     * @return void
     */
    public function applyPatch(Operation $operation, PathMatch $pathMatch, $context)
    {
        $updateValue = $operation->getValue();

        $survey = Survey::find($context['surveyId']);

        foreach ($updateValue as $prop => $value) {
            $survey->$prop = $value;
        }

        $survey->save();
    }
}
