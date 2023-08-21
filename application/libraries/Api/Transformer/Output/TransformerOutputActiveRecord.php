<?php

namespace LimeSurvey\Api\Transformer\Output;

use CActiveRecord;
use LimeSurvey\Api\Transformer\Transformer;

abstract class TransformerOutputActiveRecord extends Transformer
{
    /**
     * @param ?mixed $data
     * @return ?mixed
     */
    public function transform($data)
    {
        return parent::transform(
            $data instanceof CActiveRecord
            ? $data->attributes
            : $data
        );
    }
}
