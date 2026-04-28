<?php

class PdfWriter extends Writer
{
    private $pdf;
    private $separator;
    private $rowCounter;
    private $pdfDestination;
    private $surveyName;

    /**
     * Map of questions groups
     *
     * @var array
     * @access private
     */
    private $aGroupMap = array();

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);

        if ($oOptions->output == 'file') {
            $this->pdfDestination = 'F';
        } else {
            $this->pdfDestination = 'D';
        }
        Yii::import('application.libraries.admin.pdf', true);
        Yii::import('application.helpers.pdfHelper');
        $aPdfLanguageSettings = pdfHelper::getPdfLanguageSettings($sLanguageCode);

        // create PDF document
        $this->pdf = new pdf();
        $this->surveyName = $survey->info['surveyls_title'];
        $this->pdf->initAnswerPDF($survey->info, $aPdfLanguageSettings, Yii::app()->getConfig('sitename'), $this->surveyName);
        $this->separator = "\t";
        $this->rowCounter = 0;
        $this->aGroupMap = $this->setGroupMap($survey, $oOptions);
    }

    public function outputRecord(
        $headers,
        $values,
        FormattingOptions $oOptions,
        $fieldNames = []
    )
    {
        $this->rowCounter++;
        if ($oOptions->answerFormat == 'short') {
            $pdfstring = '';
            foreach ($values as $value) {
                $pdfstring .= $value . ' | ';
            }
            $this->pdf->intopdf($pdfstring);
        } elseif ($oOptions->answerFormat == 'long') {
            if ($this->rowCounter != 1) {
                $this->pdf->AddPage();
            }
            $this->pdf->addTitle(sprintf(gT("Survey response %d"), $this->rowCounter));
            foreach ($this->aGroupMap as $gid => $questions) {
                if ($gid != 0) {
                    $this->pdf->addGidAnswer($questions[0]['group_name'], '');
                }
                foreach ($questions as $question) {
                    if (isset($values[$question['index']]) && isset($headers[$question['index']])) {
                        $this->pdf->addAnswer($headers[$question['index']], $values[$question['index']], false);
                    }
                }
            }
        } else {
            safeDie('An invalid answer format was encountered: ' . $oOptions->answerFormat);
        }
    }

    public function close()
    {
        if ($this->pdfDestination == 'F') {
            //Save to file on filesystem.
            $filename = $this->filename;
        } else {
            //Presuming this else branch is a send to client via HTTP.
            $filename = $this->translate($this->surveyName, $this->languageCode) . '.pdf';
        }
        $this->pdf->Output($filename, $this->pdfDestination);
    }
}
