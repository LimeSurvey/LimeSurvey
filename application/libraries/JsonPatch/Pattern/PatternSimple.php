<?php

namespace LimeSurvey\JsonPatch\Pattern;

class PatternSimple implements PatternInterface
{
    private $pattern = '';

    public function __construct($pattern)
    {
        $this->pattern = $this->getPattern($pattern);
    }

    public function getRaw()
    {
        return $this->pattern;
    }

    protected function getPattern($path)
    {
        $parts = array_map(function($part){
            if (empty($part)) {
                return '';
            }
            return ($part[0] == '$')
                ? '(?<' . substr($part, 1). '>[^\/]+)'
                : $part;
        }, explode('/', $path));
        return '^' . implode('/', $parts) . '$';
    }
}
