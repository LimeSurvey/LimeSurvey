<?php

namespace LimeSurvey\Api\Transformer\Output;

use CActiveRecord;
use LimeSurvey\Api\Transformer\Transformer;

abstract class TransformerOutputActiveRecord extends Transformer
{
    /**
     * @param ?mixed $data
     * @param ?array $options
     * @return ?mixed
     */
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
