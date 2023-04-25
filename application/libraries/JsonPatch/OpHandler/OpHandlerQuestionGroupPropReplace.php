<?php

namespace LimeSurvey\JsonPatch\OpHandler;

use QuestionGroup;
use LimeSurvey\JsonPatch\OpType\OpTypeInterface;
use LimeSurvey\JsonPatch\OpType\OpTypeReplace;
use LimeSurvey\JsonPatch\Pattern\PatternInterface;
use LimeSurvey\JsonPatch\Pattern\PatternSimple;

class OpHandlerQuestionGroupPropReplace implements OpHandlerInterface
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

    public function getOpType(): OpTypeInterface
    {
        return new OpTypeReplace;
    }
}
