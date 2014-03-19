<?php
Yii::import('application.helpers.admin.export.*');
class RDataWriter extends CsvWriter {
    public function init(\SurveyObj $survey, $sLanguageCode, \FormattingOptions $oOptions) {       
        parent::init($survey, $sLanguageCode, $oOptions);
        
        // Change filename
        $this->csvFilename = 'survey_' . $survey->id .'_R_data_file.csv';
        // Skip the first line with headers
        $this->doHeaders = false;
        
        $oOptions->answerFormat = "short";      // force answer codes
        $oOptions->convertN = true;
        $oOptions->nValue = 1;
        $oOptions->convertY = true;
        $oOptions->yValue = 2;
    }

    /**
     * Perform response transformation, for example F/M for female/male will be mapped to 1/2 values
     *
     * @param type $value
     * @param type $fieldType
     * @param FormattingOptions $oOptions
     * @return mixed
     */
    protected function transformResponseValue($value, $fieldType, FormattingOptions $oOptions) {
        $value = parent::transformResponseValue($value, $fieldType, $oOptions);

        switch ($fieldType) {
            case 'G':       // Gender question
                if ($value == 'F') {
                    return 1;
                } elseif ($value == 'M') {
                    return 2;
                }
                break;
                
            case 'C':       // Yes/no/uncertain
                if ($value == 'Y') {
                    return 1;
                } elseif ($value == 'N') {
                    return 2;
                } elseif ($value == 'U') {
                    return 3;
                }
                break;
                
            case 'E':       // Increase/same/decrease
                if ($value == 'I') {
                    return 1;
                } elseif ($value == 'S') {
                    return 2;
                } elseif ($value == 'D') {
                    return 3;
                }                
                break;
  
            default:
                return $value;
                break;
        }
    }
}