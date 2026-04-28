<?php

namespace SPSS\Sav\Record\Info;

use SPSS\Buffer;
use SPSS\Sav\Record\Info;

// Available as of PHP 7.2.0.
if (!\defined('PHP_FLOAT_MAX')) {
    \define('PHP_FLOAT_MAX', 1.7976931348623e+308);
}

/**
 * @see \SPSS\Tests\MachineFloatingPointTest
 */
class MachineFloatingPoint extends Info
{
    const SUBTYPE = 4;

    /**
     * @var float
     */
    public $sysmis;

    /**
     * @var float
     */
    public $highest;

    /**
     * @var float
     */
    public $lowest;

    /**
     * @var int always set to 8
     */
    protected $dataSize = 8;

    /**
     * @var int always set to 3
     */
    protected $dataCount = 3;

    public function read(Buffer $buffer)
    {
        parent::read($buffer);
        $this->sysmis  = $buffer->readDouble();
        $this->highest = $buffer->readDouble();
        $this->lowest  = $buffer->readDouble();
    }

    public function write(Buffer $buffer)
    {
        if (!isset($this->sysmis)) {
            $this->sysmis = -PHP_FLOAT_MAX;
        }

        if (!isset($this->highest)) {
            $this->highest = PHP_FLOAT_MAX;
        }

        if (!isset($this->lowest)) {
            $this->lowest = -PHP_FLOAT_MAX;
        }

        parent::write($buffer);
        $buffer->writeDouble($this->sysmis);
        $buffer->writeDouble($this->highest);
        $buffer->writeDouble($this->lowest);
    }
}
