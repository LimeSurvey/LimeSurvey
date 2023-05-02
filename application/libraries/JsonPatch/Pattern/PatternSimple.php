<?php

namespace LimeSurvey\JsonPatch\Pattern;

class PatternSimple implements PatternInterface
{
    private $regex = '';

    public function __construct($pattern)
    {
        $this->regex = $this->toRegex($pattern);
    }

    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * To Regex
     *
     * @param string $path
     * @return string
     */
    protected function toRegex($path)
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
