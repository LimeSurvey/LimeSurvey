<?php

namespace SPSS\Sav\Record;

use SPSS\Buffer;
use SPSS\Sav\Record;

class InfoCollection
{
    /**
     * @var array
     */
    public static $classMap = [
        Record\Info\MachineInteger::class,
        Record\Info\MachineFloatingPoint::class,
        Record\Info\VariableDisplayParam::class,
        Record\Info\LongVariableNames::class,
        Record\Info\VeryLongString::class,
        Record\Info\ExtendedNumberOfCases::class,
        Record\Info\DataFileAttributes::class,
        Record\Info\VariableAttributes::class,
        Record\Info\CharacterEncoding::class,
        Record\Info\LongStringValueLabels::class,
        Record\Info\LongStringMissingValues::class,
    ];

    /**
     * @var array
     */
    public $data = [];

    /**
     * @param int $subtype
     *
     * @return string
     */
    protected static function getClassBySubtype($subtype)
    {
        foreach (self::$classMap as $class) {
            if ($subtype === $class::SUBTYPE && is_subclass_of($class, Record\Info::class)) {
                return $class;
            }
        }

        return Record\Info\Unknown::class;
    }

    /**
     * @param  Buffer  $buffer
     *
     * @return array|Record
     */
    public function fill(Buffer $buffer)
    {
        $subtype              = $buffer->readInt();
        $this->data[$subtype] = \call_user_func(self::getClassBySubtype($subtype) . '::fill', $buffer);

        return $this->data;
    }
}
