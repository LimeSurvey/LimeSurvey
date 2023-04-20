<?php

namespace LimeSurvey\SurveyPatch\OpHandler;

use Survey;
use LimeSurvey\SurveyPatch\Op\OpInterface;
use LimeSurvey\SurveyPatch\Op\OpUpdate;
use LimeSurvey\SurveyPatch\Pattern\PatternInterface;
use LimeSurvey\SurveyPatch\Pattern\PatternSimple;

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

    public function getOp(): OpInterface
    {
        return new OpUpdate;
    }
}
