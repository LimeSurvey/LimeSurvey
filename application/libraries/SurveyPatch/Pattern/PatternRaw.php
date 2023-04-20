<?php

namespace LimeSurvey\SurveyPatch\Pattern;

class PatternRaw implements PatternInterface
{
    private $pattern = '';

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function getRaw()
    {
        return $this->pattern;
    }
}
