<?php

namespace SPSS\Sav\Record;

use SPSS\Buffer;
use SPSS\Sav\Record;

class Document extends Record implements \ArrayAccess
{
    const TYPE   = 6;
    const LENGTH = 80;

    /**
     * @var array
     */
    protected $lines = [];

    public function read(Buffer $buffer)
    {
        $count = $buffer->readInt();
        for ($i = 0; $i < $count; $i++) {
            $this->lines[] = trim($buffer->readString(self::LENGTH));
        }
    }

    public function write(Buffer $buffer)
    {
        $buffer->writeInt(self::TYPE);
        $buffer->writeInt(\count($this->lines));
        foreach ($this->lines as $line) {
            $buffer->writeString((string) $line, self::LENGTH);
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->lines;
    }

    /**
     * @param array $lines
     */
    public function append($lines)
    {
        foreach ($lines as $line) {
            $this->lines[] = $line;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->lines[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->lines[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->lines[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->lines[$offset]);
    }
}
