<?php

namespace SPSS\Sav\Record\Info;

use SPSS\Buffer;
use SPSS\Sav\Record\Info;

class VeryLongString extends Info
{
    const SUBTYPE   = 14;
    const DELIMITER = "\t";

    public function read(Buffer $buffer)
    {
        parent::read($buffer);
        $data = rtrim($buffer->readString($this->dataSize * $this->dataCount));
        foreach (explode(self::DELIMITER, $data) as $item) {
            list($key, $value) = explode('=', $item);
            $this->data[$key] = (int) $value;
        }
    }

    public function write(Buffer $buffer)
    {
        if ($this->data) {
            $data = [];
            foreach ($this->data as $key => $value) {
                $data[] = sprintf('%s=%05d%c', $key, $value, 0);
            }
            $data = implode(self::DELIMITER, $data);
            $this->dataCount = \strlen($data);
            parent::write($buffer);
            $buffer->writeString($data);
        }
    }
}
