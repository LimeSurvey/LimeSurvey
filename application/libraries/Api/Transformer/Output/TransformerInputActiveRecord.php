<?php

namespace LimeSurvey\Api\Transformer\Input;

use CActiveRecord;
use LimeSurvey\Api\Transformer\Transformer;

abstract class TransformerOutputActiveRecord extends Transformer
{
    public function transform($data)
    {
        return parent::transform(
            $data instanceof CActiveRecord
            ? $data->attributes
            : $data
        );
    }
}
