<?php
/**
* Exports results in Microsoft Excel format.  By default the Writer sends
* HTTP headers and the file contents via HTTP.  For testing purposes a
* file name can be  to the constructor which will cause the ExcelWriter to
* output to a file.
*/
class ExcelWriter extends Writer
{
    private $workbook;
    private $currentSheet;
    private $separator;
    private $hasOutputHeader;
    private $rowCounter;

    //Indicates if the Writer is outputting to a file rather than sending via HTTP.
    private $outputToFile = false;

    /**
    * The presence of a filename will cause the writer to output to
    * a file rather than send.
    *
    * @param string $filename
    * @return ExcelWriter
    */
    public function __construct($filename = null)
    {
        Yii::import('application.libraries.admin.pear.Spreadsheet.Excel.Xlswriter', true);
        $this->separator = '~|';
        $this->hasOutputHeader = false;
        $this->rowCounter = 0;
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);

        if ($oOptions->output=='file')
        {
            $this->workbook = new xlswriter($this->filename);
            $this->outputToFile = true;
        }
        else
        {
            $this->workbook = new xlswriter;
        }
        $this->workbook->setTempDir(Yii::app()->getConfig("tempdir"));

        if ($oOptions->output=='display') {
            $this->workbook->send('results-survey'.$survey->id.'.xls');
  
        }
        $worksheetName = $survey->languageSettings['surveyls_title'];
        $worksheetName=substr(str_replace(array('*', ':', '/', '\\', '?', '[', ']'),array(' '),$worksheetName),0,31); // Remove invalid characters

        $this->workbook->setVersion(8);
        $sheet =$this->workbook->addWorksheet($worksheetName); // do not translate/change this - the library does not support any special chars in sheet name
        $sheet->setInputEncoding('utf-8');
        $this->currentSheet = $sheet;
    }

    protected function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        if (!$this->hasOutputHeader)
        {
            $columnCounter = 0;
            foreach ($headers as $header)
            {
                $this->currentSheet->write($this->rowCounter,$columnCounter,str_replace('?', '-', $this->excelEscape($header)));
                $columnCounter++;
            }
            $this->hasOutputHeader = true;
            $this->rowCounter++;
        }
        $columnCounter = 0;
        foreach ($values as $value)
        {
            $this->currentSheet->write($this->rowCounter, $columnCounter, $this->excelEscape($value));
            $columnCounter++;
        }
        $this->rowCounter++;
    }

    private function excelEscape($value)
    {
        if ((substr($value, 0, 1) == '=') || (substr($value, 0, 1) == '@'))
        {
            $value = '"'.$value.'"';
        }
        return $value;
    }

    public function close()
    {
        $this->workbook->close();
        return $this->workbook;
    }
}