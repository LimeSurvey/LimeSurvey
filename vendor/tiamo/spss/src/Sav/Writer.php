<?php

namespace SPSS\Sav;

use SPSS\Buffer;
use SPSS\Exception;
use SPSS\Utils;

class Writer
{
    /**
     * @var Record\Header
     */
    public $header;

    /**
     * @var Record\Variable[]
     */
    public $variables = [];

    /**
     * @var Record\ValueLabel[]
     */
    public $valueLabels = [];

    /**
     * @var Record\Document
     */
    public $document;

    /**
     * @var Record\Info[]
     */
    public $info = [];

    /**
     * @var Record\Data
     */
    public $data;

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * Writer constructor.
     *
     * @param array $data
     *
     */
    public function __construct($data = [])
    {
        $this->buffer          = Buffer::factory();
        $this->buffer->context = $this;

        if (!empty($data)) {
            $this->write($data);
        }
    }

    public function write($data)
    {
        $this->header                  = new Record\Header($data['header']);
        $this->header->nominalCaseSize = 0;
        $this->header->casesCount      = 0;

        $this->info[Record\Info\MachineInteger::SUBTYPE] = $this->prepareInfoRecord(
            Record\Info\MachineInteger::class,
            $data
        );

        $this->info[Record\Info\MachineFloatingPoint::SUBTYPE] = $this->prepareInfoRecord(
            Record\Info\MachineFloatingPoint::class,
            $data
        );

        $this->info[Record\Info\VariableDisplayParam::SUBTYPE]  = new Record\Info\VariableDisplayParam();
        $this->info[Record\Info\LongVariableNames::SUBTYPE]     = new Record\Info\LongVariableNames();
        $this->info[Record\Info\VeryLongString::SUBTYPE]        = new Record\Info\VeryLongString();
        $this->info[Record\Info\ExtendedNumberOfCases::SUBTYPE] = $this->prepareInfoRecord(
            Record\Info\ExtendedNumberOfCases::class,
            $data
        );
        $this->info[Record\Info\VariableAttributes::SUBTYPE]      = new Record\Info\VariableAttributes();
        $this->info[Record\Info\LongStringValueLabels::SUBTYPE]   = new Record\Info\LongStringValueLabels();
        $this->info[Record\Info\LongStringMissingValues::SUBTYPE] = new Record\Info\LongStringMissingValues();
        $this->info[Record\Info\CharacterEncoding::SUBTYPE]       = new Record\Info\CharacterEncoding('UTF-8');

        $this->data = new Record\Data();

        $nominalIdx = 0;

        /** @var Variable $var */
        // for ($idx = 0; $idx <= $variablesCount; $idx++) {
        foreach (array_values($data['variables']) as $idx => $var) {
            if (\is_array($var)) {
                $var = new Variable($var);
            }

            //if (! preg_match('/^[A-Za-z0-9_]+$/', $var->name)) {
            // UTF-8 and '.' characters could pass here
            if (!preg_match('/^[A-Za-z0-9_\.\x{4e00}-\x{9fa5}]+$/u', $var->name)) {
                throw new \InvalidArgumentException(sprintf('Variable name `%s` contains an illegal character.', $var->name));
            }

            if (empty($var->width)) {
                throw new \InvalidArgumentException(sprintf('Invalid field width. Should be an integer number greater than zero.'));
            }

            $variable = new Record\Variable();

            // TODO: refactory - keep 7 positions so we can add after that for 100 very long string segments
            $variable->name  = 'V' . str_pad($idx + 1, 5, 0, STR_PAD_LEFT);
            $variable->width = Variable::FORMAT_TYPE_A === $var->format ? $var->width : 0;

            $variable->label = $var->label;
            $variable->print = [
                0,
                $var->format,
                $var->width !== [] ? min($var->width, 255) : 8,
                $var->decimals,
            ];
            $variable->write = [
                0,
                $var->format,
                $var->width !== [] ? min($var->width, 255) : 8,
                $var->decimals,
            ];

            // TODO: refactory
            $shortName = $variable->name;
            $longName  = $var->name;

            if ($var->attributes !== []) {
                $this->info[Record\Info\VariableAttributes::SUBTYPE][$longName] = $var->attributes;
            }

            if ($var->missing !== []) {
                if ($var->width <= 8) {
                    if (\count($var->missing) >= 3) {
                        $variable->missingValuesFormat = 3;
                    } elseif (2 === \count($var->missing)) {
                        $variable->missingValuesFormat = -2;
                    } else {
                        $variable->missingValuesFormat = 1;
                    }
                    $variable->missingValues = $var->missing;
                } else {
                    $this->info[Record\Info\LongStringMissingValues::SUBTYPE][$shortName] = $var->missing;
                }
            }

            $this->variables[$idx] = $variable;

            if ($var->values !== []) {
                if ($variable->width > 8) {
                    $this->info[Record\Info\LongStringValueLabels::SUBTYPE][$longName] = [
                        'width'  => $var->width,
                        'values' => $var->values,
                    ];
                } else {
                    $valueLabel = new Record\ValueLabel([
                        'variables' => $this->variables,
                    ]);
                    foreach ($var->values as $key => $value) {
                        $valueLabel->labels[] = [
                            'value' => $key,
                            'label' => $value,
                        ];
                        $valueLabel->indexes = [$nominalIdx + 1];
                    }
                    $this->valueLabels[] = $valueLabel;
                }
            }

            $this->info[Record\Info\LongVariableNames::SUBTYPE][$shortName] = $var->name;

            if (Record\Variable::isVeryLong($var->width) !== 0) {
                $this->info[Record\Info\VeryLongString::SUBTYPE][$shortName] = $var->width;
            }

            $segmentCount = Utils::widthToSegments($var->width);

            for ($i = 0; $i < $segmentCount; $i++) {
                $this->info[Record\Info\VariableDisplayParam::SUBTYPE][] = [
                    $var->getMeasure(),
                    $var->getColumns(),
                    $var->getAlignment(),
                ];
            }

            // TODO: refactory
            $dataCount = \count($var->data);

            if ($dataCount > $this->header->casesCount) {
                $this->header->casesCount = $dataCount;
            }

            foreach ($var->data as $case => $value) {
                $this->data->matrix[$case][$idx] = $value;
            }

            if (Variable::isNumberFormat($var->format)) {
                $nominalIdx += 1;
            } else {
                $nominalIdx += Utils::widthToOcts($var->width);
            }
        }

        $this->header->nominalCaseSize = $nominalIdx;

        // write header
        $this->header->write($this->buffer);

        // write variables
        foreach ($this->variables as $variable) {
            $variable->write($this->buffer);
        }

        // write valueLabels
        foreach ($this->valueLabels as $valueLabel) {
            $valueLabel->write($this->buffer);
        }

        // write documents
        if (!empty($data['documents'])) {
            $this->document = new Record\Document([
                    'lines' => $data['documents'],
                ]
            );
            $this->document->write($this->buffer);
        }

        foreach ($this->info as $info) {
            $info->write($this->buffer);
        }

        $this->data->write($this->buffer);
    }

    /**
     * @param $row
     *
     * @return void
     */
    public function writeCase($row)
    {
        if (!isset($this->data)) {
            $this->data = new Record\Data();
        }

        // update the header info about number of cases
        $this->header->increaseCasesCount($this->buffer);

        // write data
        $this->data->writeCase($this->buffer, $row);
    }

    /**
     * @param $file
     *
     * @return false|int
     */
    public function save($file)
    {
        return $this->buffer->saveToFile($file);
    }

    /**
     * @return bool
     */
    public function close()
    {
        if (isset($this->data)) {
            $this->data->close();
        }

        return $this->buffer->close();
    }

    /**
     * @return Buffer
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @param string $className
     * @param array  $data
     * @param string $group
     *
     * @throws Exception
     *
     * @return array
     */
    private function prepareInfoRecord($className, $data, $group = 'info')
    {
        if (!class_exists($className)) {
            throw new Exception('Unknown class');
        }
        $key = lcfirst(substr($className, strrpos($className, '\\') + 1));

        return new $className(
            isset($data[$group]) && isset($data[$group][$key]) ?
                $data[$group][$key] :
                []
        );
    }
}
