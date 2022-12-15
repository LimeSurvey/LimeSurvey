<?php

namespace LimeSurvey\Api\Transformer;

interface TransformerInterface
{
    public function transform($data);
    public function transformAll(array $array);
}
