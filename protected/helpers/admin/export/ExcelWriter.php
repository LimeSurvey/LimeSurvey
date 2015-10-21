<?php
use ls\models\Response;
use ls\models\Survey;

/**
* Exports results in Microsoft Excel format.  By default the Writer sends
* HTTP headers and the file contents via HTTP.  For testing purposes a
* file name can be  to the constructor which will cause the ExcelWriter to
* output to a file.
*/

class ExcelWriter implements IWriter {

    protected $options;
    /**
     * @var XLSXWriter
     */
    protected $writer;
    public function getMimeType() {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }
    public function getFileName(Survey $survey, $language) {
        // Remove invalid characters
        return substr(str_replace(array('*', ':', '/', '\\', '?', '[', ']'),array(' '), $survey->localizedTitle),0,31) . '.xlsx';
    }


    public function __construct(\ls\models\forms\FormattingOptions $options)
    {
        $this->options = $options;
        $this->writer = new XLSXWriter();
        $this->writer->writeSheetHeader('Sheet1', []);

    }

    /**
     * Writes the survey and all the responses it contains to the output
     * using the options specified in FormattingOptions.
     *
     * See ls\models\Survey for information on loading a survey
     * and results from the database.
     *
     * @param Survey $survey
     * @param string $language
     */
    public function write(Survey $survey, $language, \Psr\Http\Message\StreamInterface $stream = null)
    {
        //Output the survey.
        $headers = [];
        /**
         * @todo Introduce constants?
         */
        foreach ($this->options->selectedColumns as $sColumn)
        {
            //ls\models\Survey question field, $column value is a field name from the getFieldMap function.
            switch ($this->options->headingFormat)
            {
                case 'abbreviated':
                    $value = $this->getAbbreviatedHeading($survey, $sColumn);
                    break;
                case 'full':
                    $value = $this->getFullHeading($survey, $sColumn);
                    break;
                case 'codetext':
                    $value = $this->getHeadingCode($survey, $sColumn). $this->options->headCodeTextSeparator . $this->getHeadingText($survey, $sColumn);
                    break;
                case 'code':
                default:
                    $value = $this->getHeadingCode($survey, $sColumn);
                    break;
            }
            $headers[] = $value;
        }


        // If empty survey, prepare an empty responses array, and output just 1 empty record with header.
        if ($this->options->headingFormat != 'none') {
            $this->writeLine($headers);
        }
        if ($survey->responseCount == 0)
        {
            foreach ($this->options->selectedColumns as $column)
            {
                $elementArray[]="";
            }

            $this->writeLine($elementArray);
        }

        // If no empty survey, render/export responses array.
        /** @var Response $response */
        foreach($survey->responses as $response)
        {
            $elementArray = [];

            foreach ($this->options->selectedColumns as $column)
            {
                $value = $response[$column];
                if (isset($survey->fieldMap[$column]) && $survey->fieldMap[$column]['type']!='answer_time' && $survey->fieldMap[$column]['type']!='page_time' && $survey->fieldMap[$column]['type']!='interview_time')
                {
                    switch ($this->options->answerFormat) {
                        case 'long':
                            $elementArray[] = $response->getLongAnswer($column);
                            break;
                        default:
                        case 'short':
                            $elementArray[] = $this->transformResponseValue($value, $survey->fieldMap[$column]['type']);
                            break;
                    }
                } else {
                    // ls\models\Token table value
                    $elementArray[]=$value;
                }
            }

            $this->writeLine($elementArray);
        }
        $file = tempnam(sys_get_temp_dir(), __CLASS__);
        $this->writer->writeToFile($file);
        if (!isset($stream)) {
            $size = 1024 * 1024 * 10; // Allow 10 mb to be stored in memory, after that use a file.
            $stream = fopen("php://temp/maxmemory:$size", 'w+');
        }

        stream_copy_to_stream(fopen($file, 'r'), $stream);
        unlink($file);
        return $stream;

    }

    protected function writeLine(array $data) {
        $this->writer->writeSheetRow("Sheet1", $data);

    }


    /**
     * Return the question code according to options
     *
     * @param Survey $oSurvey
     * @param FormattingOptions $oOptions
     * @param string $fieldName
     * @return string
     */
    protected function getHeadingCode(Survey $survey, $fieldName)
    {
        $fieldMap = $survey->fieldMap;
        if (isset($fieldMap[$fieldName])) {
            $result = viewHelper::getFieldCode($fieldMap[$fieldName],array('separator'=>array('[',']'),'LEMcompat'=> $this->options->useEMCode));
        } else {
            $result = $fieldName;
        }
        return $result;
    }

    /**
     * Performs a transformation of the response value based on the value, the
     * type of field the value is a response for, and the FormattingOptions.
     * All transforms should be processed during the execution of this function!
     *
     * The final step in the transform is to apply a stripTagsFull on the $value.
     * This occurs for ALL values whether or not any other transform is applied.
     *
     * @param string $value
     * @param string $fieldType
     * @param FormattingOptions $oOptions
     * @param string $column The name of the column
     * @return string
     */
    protected function transformResponseValue($value, $fieldType)
    {
        //The following if block handles transforms of Ys and Ns.
        if (($this->options->nValue != 'N' || $this->options->yValue != 'Y')
            && isset($fieldType)
            && ($fieldType == 'M' || $fieldType == 'P' || $fieldType == 'Y')
        ) {
            if (($value == 'N' || ($value == '' && !is_null($value))))
            {
                return $this->options->nValue;
            }
            else if ($value == 'Y')
            {
                return $this->options->yValue;
            }
        }

        //This spot should only be reached if no transformation occurs.
        return $value;
    }
}