<?php

namespace LimeSurvey\Transformer\Output;

use CActiveRecord;
use LimeSurvey\Transformer\Transformer;

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
