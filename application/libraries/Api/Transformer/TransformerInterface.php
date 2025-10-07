<?php

namespace LimeSurvey\Api\Transformer;

interface TransformerInterface
{
    /**
     * @param ?mixed $data
     * @param ?array $options
     * @return ?mixed
     */
    public function transform($data, $options = []);

    /**
     * @param array $collection
     * @param ?array $options
     * @return array
     */
    public function transformAll($collection, $options = []);

    /**
     * @param ?mixed $data
     * @param ?array $options
     * @return boolean|array
     */
    public function validate($data, $options = []);

    /**
     * @param array $collection
     * @param ?array $options
     * @return boolean|array
     */
    public function validateAll($collection, $options = []);
}
