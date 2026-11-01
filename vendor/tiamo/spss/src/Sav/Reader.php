<?php

namespace SPSS\Sav;

use SPSS\Buffer;
use SPSS\Sav\Record\Header;
use SPSS\Sav\Record\Info;
use SPSS\Sav\Record\ValueLabel;
use SPSS\Utils;

class Reader
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
     * @var int
     */
    public $lastCase = -1;

    /**
     * @var int
     */
    public $dataPosition = -1;

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * Reader constructor.
     *
     * @param  Buffer  $buffer
     */
    private function __construct(Buffer $buffer)
    {
        $this->buffer          = $buffer;
        $this->buffer->context = $this;
    }

    private function readBodyInternal()
    {
        $infoCollection = new Record\InfoCollection();
        $posVar         = 0;
        do {
            $recType = $this->buffer->readInt();
            switch ($recType) {
                case Record\Variable::TYPE:
                    $variable               = Record\Variable::fill($this->buffer);
                    $variable->realPosition = $posVar;
                    $this->variables[]      = $variable;
                    $posVar++;
                    break;
                case Record\ValueLabel::TYPE:
                    $this->valueLabels[] = Record\ValueLabel::fill($this->buffer, [
                        'variables' => $this->variables,
                    ]);
                    break;
                case Record\Info::TYPE:
                    $this->info = $infoCollection->fill($this->buffer);
                    break;
                case Record\Document::TYPE:
                    $this->document = Record\Document::fill($this->buffer);
                    break;
            }
        } while (Record\Data::TYPE !== $recType);
    }
    
    private function haveVar($valueLabel, $variable) {
        foreach ($valueLabel->indexes as $index) {
            if ($index == $variable->realPosition) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $file
     *
     * @return Reader
     */
    public static function fromFile($file)
    {
        return new self(Buffer::factory(fopen($file, 'rb')));
    }

    /**
     * @param string $str
     *
     * @return Reader
     */
    public static function fromString($str)
    {
        return new self(Buffer::factory($str));
    }

    /**
     * @param int $index
     *
     * @return string|null
     */
    public function getVariableName($index)
    {
        $subType = Record\Info\LongVariableNames::SUBTYPE;
        if (isset($this->info) && isset($this->info[$subType])) {
            $names = $this->info[$subType]->data;
            $shortName = (isset($this->variables[$index])) ? $this->variables[$index]->name : "";
            return (isset($names) && \is_array($names) && isset($names[$shortName])) ? $names[$shortName] : $shortName;
        }
        return null;
    }

    /**
     * @return self
     */
    public function readMetaData()
    {
        return $this->readHeader()->readBody();
    }

    /**
     * @return self
     */
    public function read()
    {
        return $this->readHeader()->readBody()->readData();
    }

    /**
     * @return self
     */
    public function readHeader()
    {
        $this->header = Record\Header::fill($this->buffer);

        return $this;
    }

    /**
     * @return self
     */
    public function readBody()
    {
        if (!$this->header) {
            $this->readHeader();
        }

        // TODO: We need to find a better way to decode the body, because the CharacterEncoding
        // data is not necessary set at the beginning of the body and any string that is set
        // before it is then not decode. So, we need to read twice the body, once to find the
        // encode and another to decode it.
        $headerPosition = $this->buffer->position();
        $this->readBodyInternal();
        if (isset($this->info) && isset($this->info[Record\Info\CharacterEncoding::SUBTYPE])) {
            $encode = $this->info[Record\Info\CharacterEncoding::SUBTYPE]->value;
            // If is not set assume the UTF-8 encode.
            $encode = (isset($encode) && !empty($encode)) ? $encode : "UTF-8";
            $this->buffer->charset = $encode;

            if ($this->buffer->seek($headerPosition) === 0) {
                $this->valueLabels = [];
                $this->info        = [];
                $this->document    = null;
                $this->data        = null;
                $this->variables   = [];
                $this->readBodyInternal();
            }
        }

        // Excluding the records that are creating only as a consequence of very long string records
        // from the variables computation.
        $veryLongStrings = [];
        if (isset($this->info[Record\Info\VeryLongString::SUBTYPE])) {
            $veryLongStrings = $this->info[Record\Info\VeryLongString::SUBTYPE]->toArray();
        }

        $segmentsCount = 0;
        $tempVars = $this->variables;
        $this->variables = [];
        foreach ($tempVars as $index => $var) {
            // Skip blank records from the variables computation
            if ($var->width !== -1) {
                if ($segmentsCount <= 0) {
                    $segmentsCount = Utils::widthToSegments(
                        isset($veryLongStrings[$var->name]) ?
                            $veryLongStrings[$var->name] : $var->width
                    );
                    //Read the ValueLabels and set it to $var->values.
                    if (Record\Variable::isVeryLong($var->width) !== false) {
                        $longName = $this->getVariableName($newIndex);
                        $subType = Record\Info\LongStringValueLabels::SUBTYPE;
                        if (isset($this->info[$subType]) && isset($this->info[$subType][$longName]) && 
                            isset($this->info[$subType][$longName]["values"])) {
                            $var->values = $this->info[Record\Info\LongStringValueLabels::SUBTYPE][$longName]["values"];
                        }
                    } else {
                        foreach ($this->valueLabels as $pos => $valueLabel) {
                            if ($this->haveVar($valueLabel, $var) && isset($valueLabel->labels)) {
                                foreach($valueLabel->labels as $posV => $valueLabelData) {
                                    $label = $valueLabelData["label"];
                                    $var->values[$valueLabelData["value"]] = $label;
                                }
                                break;
                            }
                        }
                    }

                    $this->variables[] = $var;
                }
                $segmentsCount--;
            }
        }
        $this->dataPosition = $this->buffer->position();

        return $this;
    }

    /**
     * @return self
     */
    public function readData()
    {
        $this->rewindCaseIterator();
        $this->dataPosition = $this->buffer->position();
        $this->data = Record\Data::fill($this->buffer);

        return $this;
    }

    /**
     * @return []
     */
    public function getDataArray()
    {
        return (isset($this->data)) ? $this->data->toArray() : [];
    }

    /**
     * @return []
     */
    public function getDocumentArray()
    {
        return (isset($this->document)) ? $this->document->toArray() : [];
    }

    /**
     * @return booleam
     */
    public function rewindCaseIterator()
    {
        if ($this->dataPosition !== -1) {
            $this->lastCase = -1;
            $this->data = null;
            if ($this->buffer->seek($this->dataPosition) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function readCase()
    {
        if (($this->lastCase + 1 >= 0) && ($this->lastCase + 1 < $this->buffer->context->header->casesCount)) {
            if (!isset($this->data)) {
                $this->data = Record\Data::create();
            }
            $this->data->readCase($this->buffer, $this->lastCase + 1);
            $this->lastCase++;
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getNumberOfCases()
    {
        return $this->buffer->context->header->casesCount;
    }

    /**
     * @return int
     */
    public function getCaseNumber()
    {
        return $this->lastCase;
    }

    /**
     * @return int
     */
    public function getCase()
    {
        return (isset($this->data)) ? $this->data->getRow() : [];
    }
}
