<?php

namespace LimeSurvey\JsonPatch\OpHandler;

use Survey;
use LimeSurvey\JsonPatch\OpType\OpTypeInterface;
use LimeSurvey\JsonPatch\OpType\OpTypeReplace;
use LimeSurvey\JsonPatch\Pattern\PatternInterface;
use LimeSurvey\JsonPatch\Pattern\PatternSimple;
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;

class OpHandlerSurveyPropReplace implements OpHandlerInterface
{
    public function applyOperation($params, $value)
    {
        $transformer = new TransformerInputSurvey();
        $data = [];
        $data[$params['prop']] = $value;
        $data = $transformer->transform($data);

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

        $model->{$params['prop']} = $data[$params['prop']];
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
