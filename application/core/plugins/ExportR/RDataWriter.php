<?php
Yii::import('application.helpers.admin.export.*');
class RDataWriter extends CsvWriter
{
    /**
     * The value to use when no data is present (for example unanswered because
     * of relevance)
     *
     * @var string
     */
    public $na = '';

    public $fieldmap = null;

    public function init(\SurveyObj $survey, $sLanguageCode, \FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);

        // Change filename
        $this->csvFilename = 'survey_'.$survey->id.'_R_data_file.csv';
        // Skip the first line with headers
        $this->doHeaders = true;

        $oOptions->answerFormat = "short"; // force answer codes

        // Save fieldmap so we can use it in transformResponseValue
        $this->fieldmap = $survey->fieldMap;
    }

    /**
     * Perform response transformation, for example F/M for female/male will be mapped to 1/2 values
     *
     * @param string $value
     * @param string $fieldType
     * @param FormattingOptions $oOptions
     * @param string $column
     * @return mixed
     */
    protected function transformResponseValue($value, $fieldType, FormattingOptions $oOptions, $column = null)
    {
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
                if (!empty($column) && isset($this->fieldmap[$column])) {
                    $aid = $this->fieldmap[$column]['aid'];
                    if (substr($aid, -7) == 'comment' || substr($aid, -5) == 'other') {
                        // Do not process comment or other fields
                        return $value;
                    }
                }

                if ($value == 'Y') {
                    // Yes
                    return 1;
                } elseif ($value === '') {
                    // No
                    return 0;
                }
                // Not shown
                return $this->na;

            case 'Y':       // Yes no question
                if ($value == 'Y') {
                    return 1;
                } elseif ($value == 'N') {
                    return 2;
                }
                // No data, probably a hidden question
                return $this->na;
            default:
                return $value;
        }
        return null;
    }
}