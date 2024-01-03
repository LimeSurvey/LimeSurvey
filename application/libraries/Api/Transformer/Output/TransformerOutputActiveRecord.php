<?php

namespace LimeSurvey\Api\Transformer\Output;

use CActiveRecord;
use LimeSurvey\Api\Transformer\Transformer;

abstract class TransformerOutputActiveRecord extends Transformer
{
    public function transform($data, $options = [])
    {
        $options = $options ?? [];
        return parent::transform(
            $data instanceof CActiveRecord
            ? $data->attributes
            : $data,
            $options
        );
    }
}
