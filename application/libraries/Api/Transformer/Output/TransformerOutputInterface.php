<?php

namespace LimeSurvey\Api\Transformer\Output;

interface TransformerOutputInterface
{
    public function transform($data);
    public function transformAll(array $array);
}
