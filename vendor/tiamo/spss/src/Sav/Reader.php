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
     * @var Header
     */
    public $header;

    /**
     * @var Record\Variable[]
     */
    public $variables = [];

    /**
     * @var ValueLabel[]
     */
    public $valueLabels = [];

    /**
     * @var array
     */
    public $documents = [];

    /**
     * @var Info[]
     */
    public $info = [];

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var int
     */
    public $lastCase = -1;

    /**
     * @var int
     */
    public $dataPosition = -1;

    /**
     * @var record
     */
    public $record;

    /**
     * @var Buffer
     */
    protected $_buffer;

    /**
     * Reader constructor.
     *
     * @param  Buffer  $buffer
     */
    private function __construct(Buffer $buffer)
    {
        $this->_buffer          = $buffer;
        $this->_buffer->context = $this;
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
        $this->header = Record\Header::fill($this->_buffer);

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

        // TODO: refactory
        $infoCollection = new Record\InfoCollection();
        $tempVars       = [];
        $posVar         = 0;

        do {
            $recType = $this->_buffer->readInt();
            switch ($recType) {
                case Record\Variable::TYPE:
                    $variable               = Record\Variable::fill($this->_buffer);
                    $variable->realPosition = $posVar;
                    $tempVars[]             = $variable;
                    $posVar++;
                    break;
                case Record\ValueLabel::TYPE:
                    $this->valueLabels[] = Record\ValueLabel::fill($this->_buffer, [
                        // TODO: refactory
                        'variables' => $tempVars,
                    ]);
                    break;
                case Record\Info::TYPE:
                    $this->info = $infoCollection->fill($this->_buffer);
                    break;
                case Record\Document::TYPE:
                    $this->documents = Record\Document::fill($this->_buffer)->toArray();
                    break;
            }
        } while (Record\Data::TYPE !== $recType);

        // Excluding the records that are creating only as a consequence of very long string records
        // from the variables computation.
        $veryLongStrings = [];
        if (isset($this->info[Record\Info\VeryLongString::SUBTYPE])) {
            $veryLongStrings = $this->info[Record\Info\VeryLongString::SUBTYPE]->toArray();
        }

        $segmentsCount = 0;
        foreach ($tempVars as $index => $var) {
            // Skip blank records from the variables computation
            if (-1 !== $var->width) {
                if ($segmentsCount <= 0) {
                    $segmentsCount = Utils::widthToSegments(
                        isset($veryLongStrings[$var->name]) ?
                            $veryLongStrings[$var->name] : $var->width
                    );
                    $this->variables[] = $var;
                }
                $segmentsCount--;
            }
        }
        $this->dataPosition = $this->_buffer->position();

        return $this;
    }

    /**
     * @return self
     */
    public function readData()
    {
        $this->data = Record\Data::fill($this->_buffer)->toArray();

        return $this;
    }
    
    /**
     * @return booleam
     */
    public function rewindCaseIterator()
    {
        if ($this->dataPosition !== -1) {
            $this->lastCase = -1;
            $this->record = null;
            if ($this->_buffer->seek($this->dataPosition) === 0) {
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
        if (!isset($this->record)) {
            $this->record = Record\Data::create();
        }

        $this->lastCase++;

        if (($this->lastCase >= 0) && ($this->lastCase < $this->_buffer->context->header->casesCount)) {
            $this->record->readCase($this->_buffer, $this->lastCase);

            return true;
        }

        return false;
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
        return $this->record->getRow();
    }
}
