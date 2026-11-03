<?php

namespace SPSS\Sav;

use SPSS\Buffer;

abstract class Record implements RecordInterface
{
    /**
     * @var int Position where the record start
     */
    protected $startPos = -1;

    /**
     * Record constructor.
     *
     * @param array $data
     */
    public function __construct($data = [], $startPos = -1)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
        $this->startPos = $startPos;
    }

    /**
     * @param  Buffer  $buffer
     * @param  array  $data
     *
     * @return static
     */
    public static function fill(Buffer $buffer, $data = [])
    {
        $record = new static($data);
        $record->read($buffer);

        return $record;
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public static function create($data = [], $startPos = -1)
    {
        return new static($data, $startPos);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [];
    }
}
