<?php
namespace ls\expressionmanager;


class Token {
    const STRING    = 1;
    const WS        = 2;
    const RP        = 3;
    const LP        = 4;
    const SEPARATOR = 5;
    const EQ_OP     = 6;
    const MULTI_OP  = 7;
    const ADD_OP    = 8;
    const SGQA      = 9;
    const BOOL      = 10;
    const UN_OP     = 11;
    const LOGIC_OP  = 12;
    const NUMBER    = 13;
    const APPLY     = 14;
    const WORD      = 15;
    const ASSIGN    = 16;
    const UNKNOWN   = 17;

    public $type;
    public $value;
    public $context;

    public function __construct($value, $type) {
        $this->type = $type;
        $this->value = $this->normalize($value);
    }

    protected function normalize($value) {
        switch ($this->type) {
            case self::NUMBER:
                return $this->normalizeNumber($value);
            case self::STRING:
                return  $this->normalizeString($value);
            case self::BOOL:
                return $this->normalizeBool($value);
            default:
                return $value;
        }

    }

    protected function normalizeString($value) {
        return substr($value, 1, -1);
    }

    protected function normalizeNumber($value) {
        if (strpos($value, '.') !== false) {
            $result = (float) $value;
        } else {
            $result = (int) $value;
        }
        return $result;
    }

    protected function normalizeBool($value) {
        return strcasecmp('true', $value) == 0;
    }

    static public function getName($type) {
        static $map;
        if (!isset($map)) {
            $map = array_flip((new \ReflectionClass(__CLASS__))->getConstants());
        }
        return $map[$type];

    }

    public function dump() {
        return "'{$this->value}' ({$this->getName($this->type)})";

    }

}