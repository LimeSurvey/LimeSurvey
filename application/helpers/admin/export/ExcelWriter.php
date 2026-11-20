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
    private $forceDownload = true;
    /** @var SurveyObj|null */
    private $survey;
    /** @var FormattingOptions|null */
    private $options;

    /**
     * The presence of a filename will cause the writer to output to
     * a file rather than send.
     *
     * @return ExcelWriter
     */
    public function __construct()
    {
        $this->separator = '~|';
        $this->hasOutputHeader = false;
        $this->rowCounter = 0;
    }

    /**
     * Create the workbook and remember the export settings for close().
     *
     * @param SurveyObj $survey The survey to export
     * @param string $sLanguageCode Language of the export
     * @param FormattingOptions $oOptions Export options
     * @return void
     */
    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        $this->survey = $survey;
        $this->options = $oOptions;

        $this->workbook = new XLSXWriter();
        $this->workbook->setTempDir(Yii::app()->getConfig('tempdir'));
        $worksheetName = $survey->languageSettings['surveyls_title'];
        $worksheetName = mb_substr(str_replace(array('*', ':', '/', '\\', '?', '[', ']'), array(' '), (string) $worksheetName), 0, 31, 'utf-8'); // Remove invalid characters
        if ($worksheetName == '') {
            $worksheetName = 'survey_' . $survey->id;
        }
        $this->currentSheet = $worksheetName;
        $this->forceDownload = !($oOptions->output == 'file');
    }

    protected function outputRecord($headers, $values, FormattingOptions $oOptions, $fieldNames = [])
    {
        if (!$this->hasOutputHeader) {
            $this->workbook->writeSheetRow($this->currentSheet, $headers);
            $this->hasOutputHeader = true;
        }
        $this->workbook->writeSheetRow($this->currentSheet, $values);
    }

    /**
     * Write the workbook to its file and send it when it is a download.
     *
     * @return XLSXWriter The workbook
     */
    public function close()
    {
        if (!$this->hasOutputHeader) {
            $survey = clone $this->survey;
            $survey->responses = [];
            $this->write($survey, $this->languageCode, $this->options, true);
        }
        $this->workbook->writeToFile($this->filename);
        if ($this->forceDownload) {
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=\"{$this->webfilename}.xlsx\"");
            header('Content-Length: ' . filesize($this->filename));
            readfile($this->filename);
        }
        return $this->workbook;
    }
}
