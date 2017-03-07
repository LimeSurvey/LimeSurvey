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
    private $forceDownload=true;

    //Indicates if the Writer is outputting to a file rather than sending via HTTP.
    private $outputToFile = false;

    /**
     * The filename to use for the resulting file when export
     * @var string
     */
    protected $xlsFilename = 'Excel.xlsx';

    /**
    * The presence of a filename will cause the writer to output to
    * a file rather than send.
    *
    * @param string $filename
    * @return ExcelWriter
    */
    public function __construct($filename = null)
    {
        require_once(APPPATH.'/third_party/xlsx_writer/xlsxwriter.class.php');
        $this->separator = '~|';
        $this->hasOutputHeader = false;
        $this->rowCounter = 0;
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        $this->xlsFilename = "results-survey".$survey->id.".xlsx";

        $this->workbook = new XLSXWriter();
        $this->workbook->setTempDir(Yii::app()->getRuntimePath());
        $worksheetName = $survey->languageSettings['surveyls_title'];
        $worksheetName=substr(str_replace(array('*', ':', '/', '\\', '?', '[', ']'),array(' '),$worksheetName),0,31); // Remove invalid characters

        $this->currentSheet = $worksheetName;
        $this->forceDownload=!($oOptions->output=='file');
    }

    protected function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        if (!$this->hasOutputHeader)
        {
            $columnCounter = 0;
            $this->workbook->writeSheetRow($this->currentSheet, $headers );
            $this->hasOutputHeader = true;
        }
        $this->workbook->writeSheetRow($this->currentSheet, $values );
    }

    public function close()
    {
        $this->workbook->writeToFile($this->filename);
        if ($this->forceDownload){
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$this->xlsFilename}\"");
            header('Content-Length: ' . filesize($this->filename));
            readfile($this->filename);
        }
        return $this->workbook;
    }
}
