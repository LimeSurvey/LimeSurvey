<?php
Yii::import('application.helpers.admin.export.*');
class RDataWriter extends CsvWriter {
    /**
     * The value to use when no data is present (for example unanswered because
     * of relevance)
     *
     * @var string
     */
    public $na = '';

    public function init(\SurveyObj $survey, $sLanguageCode, \FormattingOptions $oOptions) {
        parent::init($survey, $sLanguageCode, $oOptions);

        // Change filename
        $this->csvFilename = 'survey_' . $survey->id .'_R_data_file.csv';
        // Skip the first line with headers
        $this->doHeaders = false;

        $oOptions->answerFormat = "short";      // force answer codes
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
        switch ($fieldType) {
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
                
            case 'G':       // Gender question
                if ($value == 'F') {
                    return 1;
                } elseif ($value == 'M') {
                    return 2;
                }
                break;
                
            case 'M':       // Multiple choice
            case 'P':
                if ($value == 'Y') {
                    return 1;
                } elseif ($value == 'N') {
                    return 0;
                } else {
                    // We can not use the $this->na value yet since we can also have a comment field
                    return $value;
                }
                break;

            case 'Y':       // Yes no question
                if ($value == 'Y') {
                    return 1;
                } elseif ($value == 'N') {
                    return 0;
                } else {
                    // No data, probably a hidden question
                    return $this->na;
                }
                break;

            default:
                return $value;
                break;
        }
    }
}