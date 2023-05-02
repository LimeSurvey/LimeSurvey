<?php

namespace LimeSurvey\JsonPatch\Pattern;

class PatternRaw implements PatternInterface
{
    private $regex = '';

    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    public function getRegex()
    {
        return $this->regex;
    }
}
