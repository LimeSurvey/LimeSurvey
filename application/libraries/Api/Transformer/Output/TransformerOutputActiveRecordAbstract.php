<?php

namespace LimeSurvey\Api\Transformer\Output;

use CActiveRecord;

abstract class TransformerOutputActiveRecordAbstract extends TransformerOutputAbstract
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
