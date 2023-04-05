<?php

namespace SPSS\Sav\Record\Info;

use SPSS\Buffer;
use SPSS\Sav\Record\Info;

class VariableAttributes extends Info
{
    const SUBTYPE = 18;

    public $data = [];

    public function read(Buffer $buffer)
    {
        parent::read($buffer);
        $data = $buffer->readString($this->dataSize * $this->dataCount);
        foreach (explode('/', $data) as $item) {
            list($var, $value) = explode(':', $item);
            if (preg_match_all('#(.+)\((.+)\)#Uis', $value, $matches)) {
                $this->data[$var] = [];
                foreach ($matches[1] as $key => $val) {
                    $this->data[$var][$val] = trim(trim($matches[2][$key]), '\'');
                }
            } else {
                $this->data[$var] = $value;
            }
        }
    }

    public function write(Buffer $buffer)
    {
        $lines = [];
        foreach ($this->data as $var => $value) {
            if (\is_array($value)) {
                $_tmpString = '';
                foreach ($value as $key => $val) {
                    $_tmpString .= sprintf("%s('%s'\n)", $key, $val);
                }
                $value = $_tmpString;
            }
            $lines[] = sprintf('%s:%s', $var, $value);
        }

        if ($lines !== []) {
            $data            = implode('/', $lines);
            $this->dataCount = mb_strlen($data);
            parent::write($buffer);
            $buffer->writeString($data);
        }
    }
}
