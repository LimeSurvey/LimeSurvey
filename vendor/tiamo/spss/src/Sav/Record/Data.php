<?php

namespace SPSS\Sav\Record;

use SPSS\Buffer;
use SPSS\Exception;
use SPSS\Sav\Record;
use SPSS\Utils;

class Data extends Record
{
    const TYPE = 999;

    /** No-operation. This is simply ignored. */
    const OPCODE_NOP = 0;

    /** End-of-file. */
    const OPCODE_EOF = 252;

    /** Verbatim raw data. Read an 8-byte segment of raw data. */
    const OPCODE_RAW_DATA = 253;

    /** Compressed whitespaces. Expand to an 8-byte segment of whitespaces. */
    const OPCODE_WHITESPACES = 254;

    /** Compressed sysmiss value. Expand to an 8-byte segment of SYSMISS value. */
    const OPCODE_SYSMISS = 255;

    /**
     * @var array [case_index][var_index]
     */
    public $matrix = [];

    /**
     * @var array [var_index]
     */
    public $row = [];

    /**
     * @var array Latest opcodes data
     */
    protected $opcodes = [];

    /**
     * @var int Current opcode index
     */
    protected $opcodeIndex = 0;

    /**
     * @var int Position where the data start
     */
    protected $startData = -1;

    /**
     * @var Buffer Temporary buffer
     */
    protected $dataBuffer;

    /**
     * @param  Buffer  $buffer
     * @param  int  $case
     *
     * @return void
     */
    public function readCase(Buffer $buffer, $case)
    {
        /* check if this is the first time */
        if ($this->startData === -1) {
            $this->opcodeIndex = 8;
            $this->opcodes     = [];

            $this->startData = $buffer->position();
            if (0 !== $buffer->readInt()) {
                throw new \InvalidArgumentException('Error reading data record. Non-zero value found.');
            }
            if (!isset($buffer->context->variables)) {
                throw new \InvalidArgumentException('Variables required');
            }
            if (!isset($buffer->context->header)) {
                throw new \InvalidArgumentException('Header required');
            }
            if (!isset($buffer->context->info)) {
                throw new \InvalidArgumentException('Info required');
            }
        }

        $compressed = $buffer->context->header->compression;
        $bias       = $buffer->context->header->bias;
        $casesCount = $buffer->context->header->casesCount;

        // @var Variable[] $variables
        $variables = $buffer->context->variables;

        // @var Record\Info[] $info
        $info = $buffer->context->info;

        $veryLongStrings = [];
        if (isset($info[Record\Info\VeryLongString::SUBTYPE])) {
            $veryLongStrings = $info[Record\Info\VeryLongString::SUBTYPE]->toArray();
        }

        if (isset($info[Record\Info\MachineFloatingPoint::SUBTYPE])) {
            $sysmis = $info[Record\Info\MachineFloatingPoint::SUBTYPE]->sysmis;
        } else {
            $sysmis = NAN;
        }

        if (($case >= 0) && ($case < $casesCount)) {
            $this->row = $this->readCaseData(
                $buffer,
                $compressed,
                $bias,
                $variables,
                $veryLongStrings,
                $sysmis
            );
        }
    }

    public function read(Buffer $buffer)
    {
        if ($this->startData === -1) {
            $this->startData = $buffer->position();
        }

        if ($buffer->readInt() !== 0) {
            throw new \InvalidArgumentException('Error reading data record. Non-zero value found.');
        }

        if (!isset($buffer->context->variables)) {
            throw new \InvalidArgumentException('Variables required');
        }

        if (!isset($buffer->context->header)) {
            throw new \InvalidArgumentException('Header required');
        }

        if (!isset($buffer->context->info)) {
            throw new \InvalidArgumentException('Info required');
        }

        $compressed = $buffer->context->header->compression;
        $bias       = $buffer->context->header->bias;
        $casesCount = $buffer->context->header->casesCount;

        /** @var Variable[] $variables */
        $variables = $buffer->context->variables;

        /** @var Record\Info[] $info */
        $info = $buffer->context->info;

        $veryLongStrings = [];
        if (isset($info[Record\Info\VeryLongString::SUBTYPE])) {
            $veryLongStrings = $info[Record\Info\VeryLongString::SUBTYPE]->toArray();
        }

        if (isset($info[Record\Info\MachineFloatingPoint::SUBTYPE])) {
            $sysmis = $info[Record\Info\MachineFloatingPoint::SUBTYPE]->sysmis;
        } else {
            $sysmis = NAN;
        }

        $this->opcodeIndex = 8;

        for ($case = 0; $case < $casesCount; $case++) {
            $this->matrix[$case] = $this->readCaseData(
                $buffer,
                $compressed,
                $bias,
                $variables,
                $veryLongStrings,
                $sysmis
            );
        }
    }

    public function writeCase(Buffer $buffer, $row)
    {
        if (!isset($buffer->context->variables)) {
            throw new \InvalidArgumentException('Variables required');
        }

        if (!isset($buffer->context->header)) {
            throw new \InvalidArgumentException('Header required');
        }

        if (!isset($buffer->context->info)) {
            throw new \InvalidArgumentException('Info required');
        }

        $compressed = $buffer->context->header->compression;
        $bias       = $buffer->context->header->bias;
        // $casesCount = $buffer->context->header->casesCount;

        /** @var Variable[] $variables */
        $variables = $buffer->context->variables;

        /** @var Record\Info[] $info */
        $info = $buffer->context->info;

        $veryLongStrings = [];
        if (isset($info[Record\Info\VeryLongString::SUBTYPE])) {
            $veryLongStrings = $info[Record\Info\VeryLongString::SUBTYPE]->toArray();
        }

        if (isset($info[Record\Info\MachineFloatingPoint::SUBTYPE])) {
            $sysmis = $info[Record\Info\MachineFloatingPoint::SUBTYPE]->sysmis;
        } else {
            $sysmis = NAN;
        }

        if (!isset($this->dataBuffer)) {
            $this->dataBuffer = Buffer::factory('', ['memory' => true]);
            $buffer->writeInt(self::TYPE);
            $this->startData = $buffer->position();
            $buffer->writeInt(0);
        }
        //for ($case = 0; $case < $casesCount; $case++) {
        $this->writeCaseData($buffer, $row, $compressed, $bias, $variables, $veryLongStrings, $sysmis);
        //}
        $this->writeOpcode($buffer, self::OPCODE_EOF);
    }

    public function write(Buffer $buffer)
    {
        if (!isset($buffer->context->variables)) {
            throw new \InvalidArgumentException('Variables required');
        }

        if (!isset($buffer->context->header)) {
            throw new \InvalidArgumentException('Header required');
        }

        if (!isset($buffer->context->info)) {
            throw new \InvalidArgumentException('Info required');
        }

        $compressed = $buffer->context->header->compression;
        $bias       = $buffer->context->header->bias;
        $casesCount = $buffer->context->header->casesCount;

        /** @var Variable[] $variables */
        $variables = $buffer->context->variables;

        /** @var Record\Info[] $info */
        $info = $buffer->context->info;

        $veryLongStrings = [];
        if (isset($info[Record\Info\VeryLongString::SUBTYPE])) {
            $veryLongStrings = $info[Record\Info\VeryLongString::SUBTYPE]->toArray();
        }

        if (isset($info[Record\Info\MachineFloatingPoint::SUBTYPE])) {
            $sysmis = $info[Record\Info\MachineFloatingPoint::SUBTYPE]->sysmis;
        } else {
            $sysmis = NAN;
        }

        $buffer->writeInt(self::TYPE);
        $this->startData = $buffer->position();
        $buffer->writeInt(0);
        $this->dataBuffer = Buffer::factory('', ['memory' => true]);

        if (\count($this->matrix) > 0) {
            for ($case = 0; $case < $casesCount; $case++) {
                $row = $this->matrix[$case];
                $this->writeCaseData(
                    $buffer,
                    $row,
                    $compressed,
                    $bias,
                    $variables,
                    $veryLongStrings,
                    $sysmis
                );
            }
        }

        $this->writeOpcode($buffer, self::OPCODE_EOF);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->matrix;
    }

    /**
     * @return array
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return true|false
     */
    public function close()
    {
        if (isset($this->dataBuffer)) {
            return $this->dataBuffer->close();
        }

        return false;
    }

    /**
     * @param  Buffer  $buffer
     * @return int
     */
    protected function readOpcode(Buffer $buffer)
    {
        if ($this->opcodeIndex >= 8) {
            $this->opcodes     = $buffer->readBytes(8);
            $this->opcodeIndex = 0;
        }

        return 0xFF & $this->opcodes[$this->opcodeIndex++];
    }

    /**
     * @param  Buffer  $buffer
     * @param  int  $opcode
     */
    protected function writeOpcode(Buffer $buffer, $opcode)
    {
        if ($this->opcodeIndex >= 8 || self::OPCODE_EOF === $opcode) {
            $pos = $buffer->position();
            foreach ($this->opcodes as $opc) {
                $buffer->write(\chr($opc));
            }
            $padding = max(8 - \count($this->opcodes), 0);
            for ($i = 0; $i < $padding; $i++) {
                $buffer->write(\chr(self::OPCODE_NOP));
            }
            /* @noinspection NotOptimalIfConditionsInspection */
            if (self::OPCODE_EOF === $opcode) {
                $dataPos = $this->dataBuffer->position();
                $this->dataBuffer->rewind();
                $buffer->writeStream($this->dataBuffer->getStream());
                $this->dataBuffer->seek($dataPos);
                $buffer->seek($pos);
            } else {
                $this->opcodes     = [];
                $this->opcodeIndex = 0;
                $this->dataBuffer->rewind();
                $buffer->writeStream($this->dataBuffer->getStream());
                $this->dataBuffer->truncate();
            }
        }

        if (self::OPCODE_EOF !== $opcode) {
            $this->opcodes[$this->opcodeIndex++] = 0xFF & $opcode;
        }
    }

    /**
     * @param  Buffer  $buffer
     * @param  bool  $compressed
     * @param  int  $bias
     * @param  array  $variables
     * @param  array  $veryLongStrings
     * @param  int  $sysmis
     *
     * @return array
     */
    protected function readCaseData(Buffer $buffer, $compressed, $bias, $variables, $veryLongStrings, $sysmis)
    {
        $result   = [];
        $varCount = \count($variables);
        $varNum   = 0;

        for ($index = 0; $index < $varCount; $index++) {
            $var = $variables[$index];
            $isNumeric = 0 === $var->width && \SPSS\Sav\Variable::isNumberFormat($var->write[1]);
            $width = isset($var->write[2]) ? $var->write[2] : $var->width;

            // var_dump($var);
            // exit;

            if ($isNumeric) {
                if (!$compressed) {
                    $result[$varNum] = $buffer->readDouble();
                } else {
                    $opcode = $this->readOpcode($buffer);
                    switch ($opcode) {
                        case self::OPCODE_NOP:
                            break;
                        case self::OPCODE_EOF:
                            throw new Exception('Error reading data: unexpected end of compressed data file (cluster code 252)');
                            break;
                        case self::OPCODE_RAW_DATA:
                            $result[$varNum] = $buffer->readDouble();
                            break;
                        case self::OPCODE_SYSMISS:
                            $result[$varNum] = $sysmis;
                            break;
                        default:
                            $result[$varNum] = $opcode - $bias;
                            break;
                    }
                }
            } else {
                $width = isset($veryLongStrings[$var->name]) ? $veryLongStrings[$var->name] : $width;
                $result[$varNum] = '';
                $segmentsCount = Utils::widthToSegments($width);
                $opcode = self::OPCODE_RAW_DATA;
                for ($s = 0; $s < $segmentsCount; $s++) {
                    $segWidth = Utils::segmentAllocWidth($width, $s);
                    if (self::OPCODE_NOP === $opcode || self::OPCODE_EOF === $opcode) {
                        // If next segments are empty too, skip
                        continue;
                    }
                    for ($i = $segWidth; $i > 0; $i -= 8) {
                        $val = '';
                        if (!$compressed) {
                            $val = $buffer->readString(8);
                        } else {
                            $opcode = $this->readOpcode($buffer);
                            switch ($opcode) {
                                case self::OPCODE_NOP:
                                    break 2;
                                case self::OPCODE_EOF:
                                    throw new Exception('Error reading data: unexpected end of compressed data file (cluster code 252)');
                                    break 2;
                                case self::OPCODE_RAW_DATA:
                                    $val = $buffer->readString(8);
                                    break;
                                case self::OPCODE_WHITESPACES:
                                    $val = '        ';
                                    break;
                            }
                        }
                        $result[$varNum] .= $val;
                    }
                    $result[$varNum] = rtrim($result[$varNum]);
                }
            }
            $varNum++;
        }

        return $result;
    }

    /**
     * @param  Buffer  $buffer
     * @param  array  $row
     * @param  bool  $compressed
     * @param  int  $bias
     * @param  array  $variables
     * @param  array  $veryLongStrings
     * @param  int  $sysmis
     *
     * @return void
     */
    protected function writeCaseData(Buffer $buffer, $row, $compressed, $bias, $variables, $veryLongStrings, $sysmis)
    {
        foreach ($variables as $index => $var) {
            $value = $row[$index];

            // $isNumeric = $var->width == 0;
            $isNumeric = 0 === $var->width && \SPSS\Sav\Variable::isNumberFormat($var->write[1]);
            $width = isset($var->write[2]) ? $var->write[2] : $var->width;

            if ($isNumeric) {
                if (!$compressed) {
                    $buffer->writeDouble($value);
                } elseif ($value === $sysmis || '' === $value) {
                    $this->writeOpcode($buffer, self::OPCODE_SYSMISS);
                } elseif ($value >= 1 - $bias && $value <= 251 - $bias && $value === (int) $value) {
                    $this->writeOpcode($buffer, $value + $bias);
                } else {
                    $this->writeOpcode($buffer, self::OPCODE_RAW_DATA);
                    $this->dataBuffer->writeDouble($value);
                }
            } else {
                $offset = 0;
                $width = isset($veryLongStrings[$var->name]) ? $veryLongStrings[$var->name] : $width;
                $segmentsCount = Utils::widthToSegments($width);
                for ($s = 0; $s < $segmentsCount; $s++) {
                    $segWidth = Utils::segmentAllocWidth($width, $s);
                    for ($i = $segWidth; $i > 0; $i -= 8) {
                        $chunkSize = $segWidth === 255 ? min($i, 8) : 8;
                        $val = substr($value, $offset, $chunkSize); // Read 8 byte segments, don't use mbsubstr here
                        if ($compressed) {
                            if ('' === $val) {
                                $this->writeOpcode($buffer, self::OPCODE_WHITESPACES);
                            } else {
                                $this->writeOpcode($buffer, self::OPCODE_RAW_DATA);
                                $this->dataBuffer->writeString($val, 8);
                            }
                        } else {
                            $this->dataBuffer->writeString($val, 8);
                        }
                        $offset += $chunkSize;
                    }
                }
            }
        }
    }
}
