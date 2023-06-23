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
     * @param mixed $collection
     * @return mixed
     */
    public function transformAll($collection);
}
