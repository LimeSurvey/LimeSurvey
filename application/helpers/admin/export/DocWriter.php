<?php

class DocWriter extends Writer
{
    private $output;
    private $separator;
    private $isBeginning;
    /**
     * The open filehandle
     */
    private $file = null;

    public function __construct()
    {
        $this->separator = "\t";
        $this->output = '';
        $this->isBeginning = true;
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        App()->setLanguage($sLanguageCode);

        if ($oOptions->output == 'display') {
            header("Content-Disposition: attachment; filename=results-survey" . $survey->id . ".doc");
            header("Content-type: application/vnd.ms-word");
        }


        $sOutput = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style>
        table {
        border-collapse:collapse;
        }
        td, th {
        border:solid black 1.0pt;
        }
        th {
        background: #c0c0c0;
        }
        </style>';
        if ($oOptions->output == 'display') {
            echo  $sOutput;
        } elseif ($oOptions->output == 'file') {
            $this->file = fopen($this->filename, 'w');
            $this->output = $sOutput;
        }
    }

    /**
     * @param array $headers
     * @param array $values
     * @param FormattingOptions $oOptions
     */
    protected function outputRecord($headers, $values, FormattingOptions $oOptions, $fieldNames = [])
    {
        if ($oOptions->answerFormat == 'short') {
            //No headers at all, only output values.
            $this->output .= implode($this->separator, $values) . PHP_EOL;
        } elseif ($oOptions->answerFormat == 'long') {
            //Output each record, one per page, with a header preceding every value.
            if ($this->isBeginning) {
                $this->isBeginning = false;
            } else {
                $this->output .= "<br clear='all' style='page-break-before:always'>";
            }
            $this->output .= "<table><tr><th colspan='2'>" . gT("Survey response") . "</th></tr>" . PHP_EOL;

            $counter = 0;
            foreach ($headers as $header) {
                //if cell empty, output a space instead, otherwise the cell will be in 2pt font
                $value = "&nbsp;";
                if ($values[$counter] != "") {
                    $value = $values[$counter];
                }
                $this->output .= "<tr><td>" . $header . "</td><td>" . $value . "</td></tr>" . PHP_EOL;
                $counter++;
            }
            $this->output .= "</table>" . PHP_EOL;
        } else {
            safeDie('An invalid answer format was selected.  Only \'short\' and \'long\' are valid.');
        }
        if ($oOptions->output == 'display') {
            echo  $this->output;
            $this->output = '';
        } elseif ($oOptions->output == 'file') {
            fwrite($this->file, $this->output);
            $this->output = '';
        }
    }

    public function close()
    {
        if (!is_null($this->file)) {
            fclose($this->file);
        }
    }
}
