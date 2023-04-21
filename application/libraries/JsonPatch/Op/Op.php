<?php

namespace LimeSurvey\JsonPatch\Op;

class Op implements OpInterface
{
    private const CODE = '';

    public function getId()
    {
        return static::CODE;
    }

    public function __toString()
    {
        return static::CODE;
    }
}
