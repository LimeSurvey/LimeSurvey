<?php
class FormattingOptions
{
    public $responseMinRecord;
    public $responseMaxRecord;

    /**
    * The columns that have been selected for output.  The values must be
    * in fieldMap format.
    *
    * @var array[]string
    */
    public $selectedColumns;

    /**
    * Acceptable values are:
    * "complete" = include only incomplete answers
    * "incomplete" = only include incomplete answers
    * "all" = include ALL answers
    *
    * @var mixed
    */
    public $responseCompletionState;

    /**
    * Acceptable values are:
    * "abbreviated" = Abbreviated headings
    * "full" = Full headings
    * "code" = Question codes
    *
    * @var string
    */
    public $headingFormat;

    /**
    * Indicates whether to convert spaces in question headers to underscores.
    *
    * @var boolean
    */
    public $headerSpacesToUnderscores;

    /**
    * Valid values are:
    * "short" = Answer codes
    * "long" = Full answers
    *
    * @var string
    */
    public $answerFormat;

    /**
    * If $answerFormat is set to "short" then this indicates that 'Y' responses
    * should be converted to another value that is specified by $yValue.
    *
    * @var boolean
    */
    public $convertY;

    public $yValue;

    /**
    * If $answerFormat is set to "short" then this indicates that 'N' responses
    * should be converted to another value that is specified by $nValue.
    *
    * @var boolean
    */
    public $convertN;

    public $nValue;
    
    /**
    * Destination format - either 'display' (send to browser) or 'file' (send to file)
    * 
    * @var string
    */
    public $output;

    public function toString()
    {
        return $this->format.','.$this->headingFormat.','
        .$this->headerSpacesToUnderscores.','.$this->responseCompletionState
        .','.$this->responseMinRecord.','.$this->responseMaxRecord.','
        .$this->answerFormat.','.$this->convertY.','.$this->yValue.','
        .$this->convertN.','.$this->nValue.','
        .implode(',',$this->selectedColumns);
    }
}