<?php
class CsvWriter extends Writer
{
    private $output;
    private $separator;
    private $hasOutputHeader;
    /**
     * The open filehandle
     */
    private $file = null;

    function __construct()
    {
        $this->output = '';
        $this->separator = ',';
        $this->hasOutputHeader = false;
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        if ($oOptions->output=='display') {
            header("Content-Disposition: attachment; filename=results-survey".$survey->id.".csv");
            header("Content-type: text/comma-separated-values; charset=UTF-8");
        } elseif ($oOptions->output == 'file') {
            $this->file = fopen($this->filename, 'w');
        }
        
    }
    
    protected function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        $sRecord='';
        if(!$this->hasOutputHeader)
        {
            $index = 0;
            foreach ($headers as $header)
            {
                $headers[$index] = $this->csvEscape($header);
                $index++;
            }
            //Output the header...once and only once.
            $sRecord.=implode($this->separator, $headers);
            $this->hasOutputHeader = true;
        }
        //Output the values.
        $index = 0;
        foreach ($values as $value)
        {
            $values[$index] = $this->csvEscape($value);
            $index++;
        }
        $sRecord.=PHP_EOL.implode($this->separator, $values);
        if ($oOptions->output=='display')
        {
            echo $sRecord; 
            $this->output = '';
        } elseif ($oOptions->output == 'file') {
            $this->output .= $sRecord;
            fwrite($this->file, $this->output);
            $this->output='';
        } 
    }

    public function close()
    {
        if (!is_null($this->file)) {
            fclose($this->file);
        }
    }

    /**
    * Returns the value with all necessary escaping needed to place it into a CSV string.
    *
    * @param string $value
    * @return string
    */
    protected function csvEscape($value)
    {
        return CSVEscape($value);
    }
}