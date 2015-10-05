<?php

trait SmartColumnTypeTrait
{

    /**
     * Splits a column type into 3 parts:
     * - The base type.
     * - Any parameters that must be passed between brackets (brackets are ommitted).
     * - Any arguments that must be appended.
     * @param string $type
     */
    public function splitColumnType($type) {
        $regex = '/^([a-zA-Z]+)\s*(\(.+\))?\s*(.*)$/';
        if (preg_match($regex, $type, $matches)) {
            return [
                'base' => trim($matches[1]),
                'arguments' => substr($matches[2], 1, -1),
                'suffix' => $matches[3]
            ];
        }
        throw new \Exception("Could not parse type.");
    }

    public function splitArguments($type) {
        $regex = '/^([^()]*)(\(.*\))?(.*)$/';
        if (preg_match($regex, $type, $matches)) {
            return [
                'base' => trim($matches[1]),
                'arguments' => substr($matches[2], 1, -1),
                'suffix' => $matches[3]
            ];
        }
        throw new \Exception("Could not parse type.");
    }


    protected function parseType($type, callable $baseParser)
    {
        $parts = $this->splitColumnType($type);
        if (!empty($parts['arguments'])) {
            $baseParts = $this->splitArguments($baseParser($parts['base']));
            $result = trim($baseParts['base'] . "({$parts['arguments']}) " .  $parts['suffix']);
        } else {
            $result = $baseParser($type);
        }

        return $result;
    }
    /**
     * Adds support for replacing default arguments.
     * @param string $type
     * @return string
     */
    public function getColumnType($type)
    {
        // This is bad practice, it assumes knowledge about the traits' parent.
        return $this->parseType($type, function($type) { return parent::getColumnType($type); });
    }
}