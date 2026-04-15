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

    /**
     * The filename to use for the resulting file when output = display
     *
     * @var string
     */
    protected $csvFilename = '';

    /**
     * Should headers be output? For example spss and r export use more or less
     * the same output but do not need headers at all.
     *
     * @var boolean
     */
    protected $doHeaders = true;

    function __construct()
    {
        $this->output = '';
        $this->separator = ';';
        $this->hasOutputHeader = false;
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        $this->csvFilename = "results-survey" . $survey->id . ".csv";
        if ($oOptions->output == 'file') {
            $this->file = fopen($this->filename, 'w');
        }
    }

    protected function outputRecord($headers, $values, FormattingOptions $oOptions, $fieldNames = [])
    {
        $sRecord = '';
        if (!empty($oOptions->csvFieldSeparator)) {
            $this->separator = $oOptions->csvFieldSeparator;
        }
        if (!$this->hasOutputHeader) {
            if ($oOptions->output == 'display') {
                header("Content-Disposition: attachment; filename=" . $this->csvFilename);
                header("Content-type: text/comma-separated-values; charset=UTF-8");
                echo chr(239) . chr(187) . chr(191);
            } else {
                fwrite($this->file, chr(239) . chr(187) . chr(191)); // Write UTF-8 Byte Order Mark (BOM)
            }

            // If we don't want headers in our csv, for example in exports like r/spss etc. we suppress the header by setting this switch in the init
            if ($this->doHeaders === true) {
                $index = 0;
                foreach ($headers as $header) {
                    $headers[$index] = $this->csvEscape($header);
                    $index++;
                }
                //Output the header...once and only once.
                $sRecord .= implode($this->separator, $headers) . "\r\n";
            }
            $this->hasOutputHeader = true;
        }
        //Output the values.
        $index = 0;
        foreach ($values as $value) {
            $values[$index] = $this->csvEscape($value);
            $index++;
        }
        $sRecord .= implode($this->separator, $values) . "\r\n";
        if ($oOptions->output == 'display') {
            echo $sRecord;
            $this->output = '';
        } elseif ($oOptions->output == 'file') {
            $this->output .= $sRecord;
            fwrite($this->file, $this->output);
            $this->output = '';
        }
    }

    public function close()
    {
        if (!is_null($this->file)) {
            fwrite($this->file, "\r\n");
            fclose($this->file);
        }
    }

    /**
     * Returns the value with all necessary escaping needed to place it into a CSV string.
     *
     * @param string|null $value
     * @return string
     */
    protected function csvEscape($value)
    {
        if (is_null($value)) {
            return '';
        }
        $sString = preg_replace(array('~\R~u'), array("\n"), $value);
        return '"' . str_replace('"', '""', $sString) . '"';
    }
}
