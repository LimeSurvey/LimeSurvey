<?php

namespace LimeSurvey\Api\Transformer;

interface TransformerInterface
{
    /**
     * @param ?mixed $data
     * @return ?mixed
     */
    public function transform($data);

    /**
     * @param ?array $array
     * @return ?array
     */
    public function transformAll($array);
}
