<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\OpHandler;

use QuestionGroup;
use LimeSurvey\JsonPatch\{
    OpHandler\OpHandlerInterface,
    OpHandler\OpHandlerException,
    OpType\OpTypeInterface,
    OpType\OpTypeReplace,
    Pattern\PatternInterface,
    Pattern\PatternSimple
};

class OpHandlerQuestionGroupPropReplace implements OpHandlerInterface
{
    public function applyOperation($params, $values)
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

        foreach ($values as $key => $value) {
            $model->{$key} = $value;
        }

        $model->save();
    }

    public function getPattern(): PatternInterface
    {
        return new PatternSimple(
            '/questionGroups/$questionGroupId/$prop'
        );
    }

    public function getGroupByParams(): array
    {
        return ['surveyId', 'questionGroupId'];
    }

    public function getValueKeyParam()
    {
        return 'prop';
    }

    public function getOpType(): OpTypeInterface
    {
        return new OpTypeReplace;
    }
}
