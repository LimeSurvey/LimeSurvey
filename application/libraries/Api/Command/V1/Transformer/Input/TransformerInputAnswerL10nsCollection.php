<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

class TransformerInputAnswerL10nsCollection extends Transformer
{
    public function transformAll($collection, $options = [])
    {
        $data = parent::transformAll($collection, $options);
        $answers = [];
        foreach ($data as $index => $answer) {
            // second array index needs to be the scaleId
            $scaleId = array_key_exists(
                'scale_id',
                $answer
            ) ? $answer['scale_id'] : 0;
            $index = array_key_exists(
                'aid',
                $answer
            ) ? $answer['aid'] : $index;
            $answers[$index][$scaleId] = $answer;
        }
        return $answers;
    }
}
