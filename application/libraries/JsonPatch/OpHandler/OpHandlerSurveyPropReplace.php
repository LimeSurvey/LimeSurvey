<?php

namespace LimeSurvey\JsonPatch\OpHandler;

use Survey;
use LimeSurvey\JsonPatch\OpType\OpTypeInterface;
use LimeSurvey\JsonPatch\OpType\OpTypeReplace;
use LimeSurvey\JsonPatch\Pattern\PatternInterface;
use LimeSurvey\JsonPatch\Pattern\PatternSimple;

class OpHandlerSurveyPropUpdate implements OpHandlerInterface
{
    public function applyOperation($params, $value)
    {
        $model = Survey::model()->findByPk(
            $params['surveyId']
        );
        if (!$model) {
            throw new OpHandlerException(
                printf(
                    'Survey with id "%s" not found',
                    $params['surveyId']
                )
            );
        }

        $model->{$params['prop']} = $value;
        $model->save();
    }

    public function getPattern(): PatternInterface
    {
        return new PatternSimple('/$prop');
    }

    public function getOpType(): OpTypeInterface
    {
        return new OpTypeReplace;
    }
}
