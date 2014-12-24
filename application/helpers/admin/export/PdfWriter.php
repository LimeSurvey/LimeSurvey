<?php
class PdfWriter extends Writer
{
    private $pdf;
    private $separator;
    private $rowCounter;
    private $pdfDestination;
    private $surveyName;

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        $pdforientation=Yii::app()->getConfig('pdforientation');

        if ($oOptions->output=='file')
        {
            $this->pdfDestination = 'F';
        } else {
            $this->pdfDestination = 'D';
        }
        Yii::import('application.libraries.admin.pdf', true);
        Yii::import('application.helpers.pdfHelper');
        $aPdfLanguageSettings=pdfHelper::getPdfLanguageSettings($sLanguageCode);

        // create new PDF document
        $this->pdf = new pdf();
        $this->surveyName = $survey->info['surveyls_title'];
        $this->pdf->initAnswerPDF($survey->info, $aPdfLanguageSettings, Yii::app()->getConfig('sitename'), $this->surveyName);
        $this->separator="\t";
        $this->rowCounter = 0;
    }

    public function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        $this->rowCounter++;
        if ($oOptions->answerFormat == 'short')
        {
            $pdfstring = '';
            foreach ($values as $value)
            {
                $pdfstring .= $value.' | ';
            }
            $this->pdf->intopdf($pdfstring);
        }
        elseif ($oOptions->answerFormat == 'long')
        {
            if ($this->rowCounter != 1)
            {
                $this->pdf->AddPage();
            }
            $this->pdf->addTitle(sprintf(gT("Survey response %d"), $this->rowCounter));

            $columnCounter = 0;
            foreach($headers as $header)
            {
                $this->pdf->addValue($header, $values[$columnCounter]);
                $columnCounter++;
            }
        }
        else
        {
            safeDie('An invalid answer format was encountered: '.$oOptions->answerFormat);
        }

    }

    public function close()
    {
        if ($this->pdfDestination == 'F')
        {
            //Save to file on filesystem.
            $filename = $this->filename;
        }
        else
        {
            //Presuming this else branch is a send to client via HTTP.
            $filename = $this->translate($this->surveyName, $this->languageCode).'.pdf';
        }
        $this->pdf->Output($filename, $this->pdfDestination);
    }
}
