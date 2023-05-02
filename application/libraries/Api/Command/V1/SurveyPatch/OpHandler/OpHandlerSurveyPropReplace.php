<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\OpHandler;

use Survey;
use LimeSurvey\JsonPatch\{
    OpHandler\OpHandlerInterface,
    OpHandler\OpHandlerException,
    OpType\OpTypeInterface,
    OpType\OpTypeReplace,
    Pattern\PatternInterface,
    Pattern\PatternSimple
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;

class OpHandlerSurveyPropReplace implements OpHandlerInterface
{
    public function applyOperation($params, $values)
    {
        $transformer = new TransformerInputSurvey();
        $data = $transformer->transform($values);

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

        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }

        $model->save();
    }

    public function getPattern(): PatternInterface
    {
        return new PatternSimple('/$prop');
    }

    public function getGroupByParams(): array
    {
        return ['surveyId'];
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
