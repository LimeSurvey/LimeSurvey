<?php

namespace SPSS\Sav\Record\Info;

use SPSS\Buffer;
use SPSS\Sav\Record\Info;

class CharacterEncoding extends Info
{
    const SUBTYPE = 20;

    /**
     * @var string
     */
    public $value;

    /** @noinspection MagicMethodsValidityInspection */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function read(Buffer $buffer)
    {
        parent::read($buffer);
        $this->value = $buffer->readString($this->dataSize * $this->dataCount);
    }

    public function write(Buffer $buffer)
    {
        $this->dataCount = \strlen($this->value);
        parent::write($buffer);
        $buffer->writeString($this->value);
    }
}
