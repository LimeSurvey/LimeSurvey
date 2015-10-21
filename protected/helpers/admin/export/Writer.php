<?php
/**
* Contains functions and properties that are common to all writers.
* All extending classes must implement the internalWrite(...) method and
* have access to functionality as described below:
*
* TODO Write more docs here
*/

use \ls\models\forms\FormattingOptions;
use ls\models\Survey;

abstract class Writer implements IWriter
{

    protected $groupMap = [];
    protected $language;
    /**
     * @var FormattingOptions
     */
    protected $options;

    
    protected function translate($key, $language)
    {
        return gT($key, 'html', $language);
    }

    /**
    * @param FormattingOptions $options
    */
    public function __construct(\ls\models\forms\FormattingOptions $options)
    {
        $this->options = $options;
        $this->init();
    }

    public function init() {

    }
    /**
    * Return map of questions groups
    *
    * @param Survey $survey
    * @param FormattingOptions $oOptions
    * @return array
    */
    public function createGroupMap(Survey $survey)
    {
        $aGroupMap = [];
        $index = 0;
        foreach ($this->options->selectedColumns as $column) {
            if (isset($survey->getFieldMap('full')[$column])) {
                $question = $survey->getFieldMap('full')[$column];
            } else {
                // ls\models\Token field
                $question = ['gid'=>0, 'qid'=>''];
            }
            $question['index'] = $index;
            $aGroupMap[intval($question['gid'])][] = $question;
            $index++;
        }
        return $aGroupMap;
    }

    /**
    * Returns an abbreviated heading for the survey's question that matches
    * Force headingTextLength to be set, set to 15 if is not set (old behaviour)
    *
    * @param Survey $oSurvey
    * @param string $fieldName
    * @return string
    */
    public function getAbbreviatedHeading(Survey $oSurvey, $fieldName)
    {
        $this->options->headingTextLength=((int)$this->options->headingTextLength)?(int)$this->options->headingTextLength:15;
        return $this->getHeadingText($oSurvey, $fieldName);
    }

    /**
    * Returns a full heading for the question that matches the $fieldName.
    * Force headingTextLength to null (old behaviour)
    *
    * @deprecated
    * @param Survey $oSurvey
    * @param FormattingOptions $oOptions
    * @param string $fieldName
    * @return string
    */
    public function getFullHeading(Survey $oSurvey, $fieldName)
    {
        $this->options->headingTextLength=null;
        return $this->getHeadingText($oSurvey, $fieldName);
    }

    /**
    * Return the subquestion part, if not empty : add a space before it.
    * 
    * @param Survey $oSurvey
    * @param FormattingOptions $oOptions
    * @param string $fieldName
    * @return string
    */
    public function getFullFieldSubHeading(Survey $oSurvey, FormattingOptions $oOptions, $fieldName)
    {
        if (isset($oSurvey->fieldMap[$fieldName]))
        {
            $aField=$oSurvey->fieldMap[$fieldName];
            $aField['question']='';
            $subHeading = trim(viewHelper::getFieldText($aField,array('separator'=>array('[',']'),'abbreviated'=>$oOptions->headingTextLength,'ellipsis'=>".. ")));
            if($subHeading)
                return " {$subHeading}";
        }
        return false;
    }

    /**
    * Return the question text part without any subquestion
    * 
    * @param Survey $oSurvey
    * @param FormattingOptions $oOptions
    * @param string $fieldName
    * @return string
    */
    public function getFullQuestionHeading(Survey $oSurvey, $fieldName)
    {
        if (isset($oSurvey->fieldMap[$fieldName]))
        {
            $aField=$oSurvey->fieldMap[$fieldName];
            $aField['question']=stripTagsFull($aField['question']);
            if ($this->options->headingTextLength)
            {
              $aField['question']=ellipsize($aField['question'],$this->options->headingTextLength,1,".. ");
            } 
            return $aField['question'];
        }
        return false;
    }

    /**
    * Return the question code according to options
    *
    * @param Survey $oSurvey
    * @param FormattingOptions $oOptions
    * @param string $fieldName
    * @return string
    */
    public function getHeadingCode(Survey $survey, $fieldName)
    {
        if (isset($survey->fieldMap[$fieldName]))
        {
            return viewHelper::getFieldCode($survey->fieldMap[$fieldName],array('separator'=>array('[',']'),'LEMcompat'=> $this->options->useEMCode));
        }
        else
        {
            return $fieldName;
        }
    }

    /**
    * Return the question text according to options
    *
    * @param Survey $oSurvey
    * @param FormattingOptions $oOptions
    * @param string $fieldName
    * @return string
    */
    public function getHeadingText(Survey $oSurvey, $fieldName)
    {
        if (isset($oSurvey->fieldMap[$fieldName]))
        {
            $textHead = $this->getFullQuestionHeading($oSurvey, $fieldName).$this->getFullFieldSubHeading($oSurvey, $fieldName);
        }
        elseif(isset($oSurvey->tokenFields[$fieldName]))
        {
            $textHead = $oSurvey->tokenFields[$fieldName]['description'];
        }
        else
        {
            $textHead = $fieldName;
        }
        if ($oOptions->headerSpacesToUnderscores)
        {
            $textHead = str_replace(' ', '_', $textHead);
        }
        return $textHead;
    }

    /**
    * Return the answer text according to options
    *
    * @param Survey $oSurvey
    * @param FormattingOptions $oOptions
    * @param string $fieldName
    * @param string $sValue
    * @return string
    */
    public function getLongAnswer(Survey $oSurvey, $fieldName,$sValue)
    {
        return $this->transformResponseValue(
                $oSurvey->getFullAnswer($fieldName, $sValue, $this->options->lang),
                $oSurvey->fieldMap[$fieldName]['type'],
                $fieldName
               );
    }

    /**
    * Return the answer text according to options
    *
    * @param Survey $oSurvey
    * @param FormattingOptions $oOptions
    * @param string $fieldName
    * @param string $sValue
    * @return string
    */
    public function getShortAnswer(Survey $survey, $fieldName, $value)
    {
        return $this->transformResponseValue(
            $value,
            $survey->fieldMap[$fieldName]['type'],
            $fieldName
       );
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
    protected function transformResponseValue($value, $fieldType, $column = null)
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

    /**
    * This method is made final to prevent extending code from circumventing the
    * initialization process that must take place prior to any of the translation
    * infrastructure to work.
    *
    * The inialization process is dependent upon the survey being passed into the
    * write function and so must be performed when the method is called and not
    * prior to (such as in a constructor).
    *
    * All extending classes must implement the internalWrite function which is
    * the code that is called after all initialization is completed.
    *
    * @param Survey $survey
    * @param string $language
    * @param FormattingOptions $options
    * @param Psr\Http\Message\StreamInterface $stream
    */
    final public function write(Survey $survey, $language, \Psr\Http\Message\StreamInterface $stream = null)
    {
        if (!isset($stream)) {
            $size = 1024 * 1024 * 10; // Allow 10 mb to be stored in memory, after that use a file.
            $stream = fopen("php://temp/maxmemory:$size", 'w+');
        }
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

        $this->groupMap = $this->createGroupMap($survey);
        // If empty survey, prepare an empty responses array, and output just 1 empty record with header.
        if (null !== $fileHeader = $this->beforeRenderRecords($headers, $survey)) {
            fwrite($stream, $fileHeader);
        }
        if ($survey->responseCount == 0)
        {
             foreach ($this->options->selectedColumns as $column)
             {
             	$elementArray[]="";
             }
        	fwrite($stream, $this->renderRecord($headers, $elementArray));
        }

        // If no empty survey, render/export responses array.
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
                            $elementArray[] = $this->getLongAnswer($survey, $column, $value);
                            break;
                        default:
                        case 'short':
                            $elementArray[] = $this->getShortAnswer($survey, $column, $value);
                            break;
                    }
                }
                else //ls\models\Token table value
                {
                    $elementArray[]=$value;
                }
            }

            fwrite($stream, $this->renderRecord($headers, $elementArray));
        }

        if (null !== $fileFooter = $this->afterRenderRecords($headers, $survey)) {
            fwrite($stream, $fileFooter);
        }
        return $stream;
    }

    protected function stripTagsFull($string)
    {
        $string=str_replace('-oth-','',$string);
        return flattenText($string,false,true,'UTF-8',false);
    }

    /**
    * Mimic old functionnality, leave it if some plugin use it
    * No core plugin seems to use it, and function name seem broken (?)
    * @deprecated
    */
    public function getCodeHeading(Survey $oSurvey, FormattingOptions $oOptions, $fieldName)
    {
        return $this->getFullQuestionHeading($oSurvey,$oOptions,$fieldName).$this->getCodeFieldSubHeading($oSurvey,$oOptions,$fieldName);
    }
    /**
    * Mimic old functionnality, leave it if some plugin use it
    * No core plugin seems to use it, and function name seem broken (?)
    * @deprecated
    */
    public function getCodeFieldSubHeading(Survey $oSurvey, FormattingOptions $oOptions, $fieldName)
    {
        $fieldName['question']="";
        return $this->getFullFieldSubHeading($oSurvey,$oOptions,$fieldName);
    }

    /**
    * This method will be called once for every row of data that needs to be
    * output.
    *
    * Implementations must use the data from these method calls to construct
    * proper output for their output type and the given FormattingOptions.
    *
    * @param array $headers
    * @param array $values
    * @param FormattingOptions $oOptions
    * @return string The rendered record.
    */
    abstract protected function renderRecord($headers, $values);

    /**
     * This function is called before any records are rendered.
     * Use it to add document headers.
     */
    public function beforeRenderRecords($headers, Survey $survey) {

    }
    public function getFileName(Survey $survey, $language) {
        return "export_responses.dat";
    }
}
