<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 4/12/15
 * Time: 2:47 PM
 */

namespace ls\expressionmanager;


class Token {

    public $type;
    public $value;
    public $context;

    public function __construct($value, $type) {
        $this->type = $type;
        $this->value = $this->normalize($value);
    }

    protected function normalize($value) {
        switch ($this->type) {
            case 'NUMBER':
                return $this->normalizeNumber($value);
            case 'STRING':
                return  $this->normalizeString($value);
            case 'BOOL':
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

}