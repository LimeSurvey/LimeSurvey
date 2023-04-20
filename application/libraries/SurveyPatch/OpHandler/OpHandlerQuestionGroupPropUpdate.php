<?php

namespace LimeSurvey\SurveyPatch\OpHandler;

use QuestionGroup;
use LimeSurvey\SurveyPatch\Op\OpInterface;
use LimeSurvey\SurveyPatch\Op\OpUpdate;
use LimeSurvey\SurveyPatch\Pattern\PatternInterface;
use LimeSurvey\SurveyPatch\Pattern\PatternSimple;

class OpHandlerQuestionGroupProp implements OpHandlerInterface
{
    public function applyOperation($params, $value)
    {
        $model = QuestionGroup::model()->findByPk(
            $params['questionGroupId']
        );
        if (!$model) {
            throw new OpHandlerException(
                printf(
                    'Question group with id "%s" not found',
                    $params['questionGroupId']
                )
            );
        }
        $model->{$params['prop']} = $value;
        $model->save();
    }

    public function getPattern(): PatternInterface
    {
        return new PatternSimple(
            '/questionGroups/$questionGroupId/$prop'
        );
    }

    public function getOp(): OpInterface
    {
        return new OpUpdate;
    }
}
