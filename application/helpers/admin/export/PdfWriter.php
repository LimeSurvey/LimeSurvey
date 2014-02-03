<?php
class PdfWriter extends Writer
{
    private $pdf;
    private $separator;
    private $rowCounter;
    private $pdfDestination;
    private $surveyName;
    private $clang;

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        $pdforientation=Yii::app()->getConfig('pdforientation');
        $this->clang = new limesurvey_lang($sLanguageCode);

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
        $this->pdf->SetFont($aPdfLanguageSettings['pdffont'], '', $aPdfLanguageSettings['pdffontsize']);
        $this->pdf->AddPage();
        $this->pdf->intopdf("PDF export ".date("Y.m.d-H:i", time()));
        $this->pdf->setLanguageArray($aPdfLanguageSettings['lg']);

        $this->separator="\t";

        $this->rowCounter = 0;        
        $this->surveyName = $survey->languageSettings['surveyls_title'];
        $this->pdf->titleintopdf($this->surveyName, $survey->languageSettings['surveyls_description']);
    }

    public function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        $this->rowCounter++;
        if ($oOptions->answerFormat == 'short')
        {
            $pdfstring = '';
            $this->pdf->titleintopdf($this->clang->gT("Survey response"));
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
            $this->pdf->Cell(0, 10, sprintf($this->clang->gT("Survey response %d"), $this->rowCounter), 1, 1);

            $columnCounter = 0;
            foreach($headers as $header)
            {
                $this->pdf->intopdf($header);
                $this->pdf->intopdf($this->stripTagsFull($values[$columnCounter]));
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