<?php

namespace LimeSurvey\Api\Command\V2\Transformer\Output;

interface TransformerOutputInterface
{
    public function transform($data);
    public function transformAll(array $array);
}
