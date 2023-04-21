<?php

namespace LimeSurvey\JsonPatch\OpHandler;

use QuestionGroup;
use LimeSurvey\JsonPatch\Op\OpInterface;
use LimeSurvey\JsonPatch\Op\OpReplace;
use LimeSurvey\JsonPatch\Pattern\PatternInterface;
use LimeSurvey\JsonPatch\Pattern\PatternSimple;

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
        return new OpReplace;
    }
}
