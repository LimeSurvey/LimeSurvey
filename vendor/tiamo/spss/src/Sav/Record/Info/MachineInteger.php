<?php

namespace SPSS\Sav\Record\Info;

use SPSS\Buffer;
use SPSS\Sav\Record\Info;

class MachineInteger extends Info
{
    const SUBTYPE = 3;

    /**
     * @var array [Major, Minor, Revision]
     */
    public $version = [1, 0, 0];

    /**
     * @var int machine code
     */
    public $machineCode = 0;

    /**
     * @var int Floating point representation code.
     *          For IEEE 754 systems this is 1.
     *          IBM 370 sets this to 2,
     *          and DEC VAX E to 3.
     */
    public $floatingPointRep = 1;

    /**
     * @var int Compression code.
     *          Always set to 1, regardless of whether or how the file is compressed.
     */
    public $compressionCode = 1;

    /**
     * @var int Machine endianness.
     *          1 indicates big-endian,
     *          2 indicates little-endian.
     */
    public $endianness = 2;

    /**
     * @var int Character code.
     *          The following values have been actually observed in system files:
     *          1        EBCDIC.
     *          2        7-bit ASCII.
     *          3        8-bit ASCII.
     *          4        DEC Kanji.
     *          1250     The windows-1250 code page for Central European and Eastern European languages.
     *          1252     The windows-1252 code page for Western European languages.
     *          28591    ISO 8859-1.
     *          65001    UTF-8.
     */
    public $characterCode = 65001;

    /**
     * @var int always set to 4
     */
    protected $dataSize = 4;

    /**
     * @var int always set to 8
     */
    protected $dataCount = 8;

    public function read(Buffer $buffer)
    {
        parent::read($buffer);
        $this->version          = [$buffer->readInt(), $buffer->readInt(), $buffer->readInt()];
        $this->machineCode      = $buffer->readInt();
        $this->floatingPointRep = $buffer->readInt();
        $this->compressionCode  = $buffer->readInt();
        $this->endianness       = $buffer->readInt();
        $this->characterCode    = $buffer->readInt();
    }

    public function write(Buffer $buffer)
    {
        parent::write($buffer);
        $buffer->writeInt($this->version[0]);
        $buffer->writeInt($this->version[1]);
        $buffer->writeInt($this->version[2]);
        $buffer->writeInt($this->machineCode);
        $buffer->writeInt($this->floatingPointRep);
        $buffer->writeInt($this->compressionCode);
        $buffer->writeInt($this->endianness);
        $buffer->writeInt($this->characterCode);
    }
}
