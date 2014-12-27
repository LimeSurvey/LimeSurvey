<?php
/**
* Contains functions and properties that are common to all writers.
* All extending classes must implement the internalWrite(...) method and
* have access to functionality as described below:
*
* TODO Write more docs here
*/
abstract class Writer implements IWriter
{
    protected $sLanguageCode;
    protected $translator;
    public $filename;
    
    protected function translate($key, $sLanguageCode)
    {
        return $this->translator->translate($key, $sLanguageCode);
    }

    /**
    * An initialization method that implementing classes can override to gain access
    * to any information about the survey, language, or formatting options they
    * may need for setup.
    *
    * @param Survey $survey
    * @param mixed $sLanguageCode
    * @param FormattingOptions $oOptions
    */
    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        $this->languageCode = $sLanguageCode;
        $this->translator = new Translator();
        if ($oOptions->output == 'file') {
            $sRandomFileName=Yii::app()->getConfig("tempdir") . DIRECTORY_SEPARATOR . randomChars(40);
            $this->filename = $sRandomFileName;
        }
    }

    /**
    * Return map of questions groups
    *
    * @param Survey $survey
    * @param FormattingOptions $oOptions
    * @return array
    */
    public function setGroupMap(SurveyObj $survey, FormattingOptions $oOptions)
    {
        $aGroupMap = array();
        $index = 0;
        foreach ($oOptions->selectedColumns as $column) {
            if (isset($survey->fieldMap[$column])) {
                $question = $survey->fieldMap[$column];
            } else {
                // Token field
                $question = array('gid'=>0, 'qid'=>'');
            }
            $question['index'] = $index;
            $aGroupMap[intval($question['gid'])][] = $question;
            $index++;
        }
        return $aGroupMap;
    }

    /**
    * Returns an abbreviated heading for the survey's question that matches
    * the $fieldName parameter (or false if a match is not found).
    *
    * @param Survey $survey
    * @param string $fieldName
    * @return string
    */
    public function getAbbreviatedHeading(SurveyObj $survey, $fieldName)
    {
        if (isset($survey->fieldMap[$fieldName])) {
            $question = $survey->fieldMap[$fieldName];

            $heading = $question['question'];
            $heading = $this->stripTagsFull($heading);
            $heading = mb_substr($heading, 0, 15).'.. ';
            $aid = $survey->fieldMap[$fieldName]['aid'];
            if (!empty($aid))
            {
                $heading .= '['.$this->stripTagsFull($aid).']';
            }
            return $heading;
        } else {
            // Token field
            if (isset($survey->tokenFields[$fieldName])) {
                return $survey->tokenFields[$fieldName]['description'];
            }
            return $fieldName;
        }
    }

    /**
    * Returns a full heading for the question that matches the $fieldName.
    * False is returned if no matching question is found.
    *
    * @param Survey $survey
    * @param FormattingOptions $oOptions
    * @param string $fieldName
    * @return string (or false)
    */
    public function getFullHeading(SurveyObj $survey, FormattingOptions $oOptions, $fieldName)
    {                                
        if (isset($survey->fieldMap[$fieldName])) {
            $question = $survey->fieldMap[$fieldName];

            $heading = $question['question'];
            $heading = $this->stripTagsFull($heading);
            $heading.=$this->getFullFieldSubHeading($survey, $oOptions, $fieldName);
            return $heading;
        } else {
            // Token field
            if (isset($survey->tokenFields[$fieldName])) {
                return $survey->tokenFields[$fieldName]['description'];
            }
            return $fieldName;
        }        
    }

    public function getCodeHeading(SurveyObj $survey, FormattingOptions $oOptions, $fieldName)
    {
        $question = $survey->fieldMap[$fieldName];
        
        $heading = $question['title'];
        $heading = $this->stripTagsFull($heading);
        $heading.=$this->getCodeFieldSubHeading($survey, $oOptions, $fieldName);
        return $heading;
    }

    public function getCodeFieldSubHeading(SurveyObj $survey, FormattingOptions $oOptions, $fieldName)
    {
        $field = $survey->fieldMap[$fieldName];
        $answerCode = $field['aid'];
        $questionId = $field['qid'];
        $fieldType = $field['type'];

        $subHeading = '';
        switch ($fieldType)
        {
            case 'R':
                $subHeading .= ' ['.$this->translate('Ranking', $this->languageCode).' '.
                $answerCode.']';
                break;

            case 'L':
            case '!':
                if ($answerCode == 'other')
                {
                    $subHeading .= ' '.$this->getOtherSubHeading();
                }
                break;

            case 'O':
                if ($answerCode == 'comment')
                {
                    $subHeading .= ' '.$this->getCommentSubHeading();
                }
                break;

            case 'M':
            case 'P':
                //This section creates differing output from the old code base, but I do think
                //that it is more correct than the old code.
                $isOther = ($answerCode == 'other');
                $isComment = (mb_substr($answerCode, -7, 7) == 'comment');

                if ($isComment)
                {
                    $isOther = (mb_substr($answerCode, 0, -7) == 'other');
                }

                if ($isOther)
                {
                    $subHeading .= ' '.$this->getOtherSubHeading();
                }
                else
                {
                    $subHeading .= ' ['.$answerCode.']';
                }
                break;

            case ':':
            case ';':
                list($scaleZeroTitle, $scaleOneTitle) = explode('_', $answerCode);
                $subHeading .= ' ['.$scaleZeroTitle.']['.$scaleOneTitle.']';
                break;

            case '1':
                $answerScale = substr($fieldName, -1) + 1;
                $subQuestions = $survey->getSubQuestionArrays($questionId);
                foreach ($subQuestions as $question)
                {
                    if ($question['title'] == $answerCode && $question['scale_id'] == 0)
                    {
                        $subHeading = ' ['.flattenText($question['title'], true,true).'][Scale '.$answerScale.']';
                    }
                }
                break;

            default:
                if (!empty($answerCode))
                {
                    $subHeading .= ' ['.$answerCode.']';
                }
        }

        //rtrim the result since it could be an empty string ' ' which
        //should be removed.                          
        return rtrim($subHeading);
    }

    public function getFullFieldSubHeading(SurveyObj $survey, FormattingOptions $oOptions, $fieldName)
    {
        $field = $survey->fieldMap[$fieldName];
        $answerCode = $field['aid'];
        $questionId = $field['qid'];
        $fieldType = $field['type'];

        $subHeading = '';
        switch ($fieldType)
        {
            case 'R':
                $subHeading .= ' ['.$this->translate('Ranking', $this->languageCode).' '.
                $answerCode.']';
                break;

            case 'L':
            case '!':
                if ($answerCode == 'other')
                {
                    $subHeading .= ' '.$this->getOtherSubHeading();
                }
                break;

            case 'O':
                if ($answerCode == 'comment')
                {
                    $subHeading .= ' '.$this->getCommentSubHeading();
                }
                break;

            case 'M':
            case 'P':
                //This section creates differing output from the old code base, but I do think
                //that it is more correct than the old code.
                $isOther = ($answerCode == 'other');
                $isComment = (mb_substr($answerCode, -7, 7) == 'comment');

                if ($isComment)
                {
                    $isOther = (mb_substr($answerCode, 0, -7) == 'other');
                }

                if ($isOther)
                {
                    $subHeading .= ' '.$this->getOtherSubHeading();
                }
                else
                {
                    $sqs = $survey->getSubQuestionArrays($questionId);
                    foreach ($sqs as $sq)
                    {
                        if (!$isComment && $sq['title'] == $answerCode)
                        {
                            $value = $sq['question'];
                        }
                    }
                    if (!empty($value))
                    {
                        $subHeading .= ' ['.$this->stripTagsFull($value).']';
                    }
                }
                if (isset($isComment) && $isComment == true)
                {
                    $subHeading .= ' '.$this->getCommentSubHeading();
                    $comment = false;
                }

                break;
            case ':':
            case ';':
                $subHeading .= ' ['.$this->stripTagsFull($field['subquestion1']).']['.$this->stripTagsFull($field['subquestion2']).']';
                break;
            case '1':
                $answerScale = substr($fieldName, -1) + 1;
                $subQuestions = $survey->getSubQuestionArrays($questionId);
                foreach ($subQuestions as $question)
                {
                    if ($question['title'] == $answerCode && $question['scale_id'] == 0)
                    {
                        $subHeading = ' ['.flattenText($question['question'], true,true).'][Scale '.$answerScale.']';
                    }
                }
                break;

            default:
                $subQuestion = null;
                $subQuestions = $survey->getSubQuestionArrays($questionId);
                foreach ($subQuestions as $question)
                {
                    if ($question['title'] == $answerCode)
                    {
                        $subQuestion = $question;
                    }
                }
                if (!empty($subQuestion) && !empty($subQuestion['question']))
                {
                    $subHeading .= ' ['.$this->stripTagsFull($subQuestion['question']).']';
                }
        }

        //rtrim the result since it could be an empty string ' ' which
        //should be removed.
        return rtrim($subHeading);
    }

    private function getOtherSubHeading()
    {
        return '['.$this->translate('Other', $this->languageCode).']';
    }

    private function getCommentSubHeading()
    {
        return '- comment';
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
    protected function transformResponseValue($value, $fieldType, FormattingOptions $oOptions, $column = null)
    {
        //The following if block handles transforms of Ys and Ns.
        if (($oOptions->convertN || $oOptions->convertY) &&
        isset($fieldType) &&
        ($fieldType == 'M' || $fieldType == 'P' || $fieldType == 'Y'))
        {
            if (($value == 'N' || ($value == '' && !is_null($value)))  && $oOptions->convertN)
            {
                //echo "Transforming 'N' to ".$oOptions->nValue.PHP_EOL;
                return $oOptions->nValue;
            }
            else if ($value == 'Y' && $oOptions->convertY)
                {
                    //echo "Transforming 'Y' to ".$oOptions->yValue.PHP_EOL;
                    return $oOptions->yValue;
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
    * @param string $sLanguageCode
    * @param FormattingOptions $oOptions
    * @param boolean $bOutputHeaders Set if header should be given back
    */
    final public function write(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions, $bOutputHeaders=true)
    {

        //Output the survey.
        $headers = array();
        if ($bOutputHeaders)
        {
            
            foreach ($oOptions->selectedColumns as $column)
            {
                //Survey question field, $column value is a field name from the getFieldMap function.
                switch ($oOptions->headingFormat)
                {
                    case 'abbreviated':
                        $value = $this->getAbbreviatedHeading($survey, $column);
                        break;
                    case 'full':
                        $value = $this->getFullHeading($survey, $oOptions, $column);
                        break;
                    default:
                    case 'code':
                        if (isset($survey->fieldMap[$column])) {
                            $value = viewHelper::getFieldCode($survey->fieldMap[$column]);
                        } else {
                            // Token field
                            $value = $column;
                        }
                        break;
                }
                if ($oOptions->headerSpacesToUnderscores)
                {
                    $value = str_replace(' ', '_', $value);
                }

                //$this->output.=$this->csvEscape($value).$this->separator;
                $headers[] = $value;
            }
        }
        //Output the results.
        $sFile='';
        
        // If empty survey, prepare an empty responses array, and output just 1 empty record with header.
        if ($survey->responses->rowCount == 0)
        {
             foreach ($oOptions->selectedColumns as $column)
             {
             	$elementArray[]="";
             }
        	$this->outputRecord($headers, $elementArray, $oOptions);
        }
        		
        // If no empty survey, render/export responses array.
        foreach($survey->responses as $response)
        {
            $elementArray = array();

            foreach ($oOptions->selectedColumns as $column)
            {
                $value = $response[$column];
                if (isset($survey->fieldMap[$column]) && $survey->fieldMap[$column]['type']!='answer_time' && $survey->fieldMap[$column]['type']!='page_time' && $survey->fieldMap[$column]['type']!='interview_time')
                {
                    switch ($oOptions->answerFormat) {
                        case 'long':
                            $elementArray[] = $this->transformResponseValue(
                                $survey->getFullAnswer($column, $value, $this->translator, $this->languageCode), 
                                $survey->fieldMap[$column]['type'], 
                                $oOptions,
                                $column);
                            break;
                        default:
                        case 'short':
                            $elementArray[] = $this->transformResponseValue(
                                $value,
                                $survey->fieldMap[$column]['type'], 
                                $oOptions,
                                $column);
                            break;
                    }
                }
                else //Token table value
                {
                    $elementArray[]=$value;
                }
            }
            if ($oOptions->output=='display')
            {
                $this->outputRecord($headers, $elementArray, $oOptions);
            } else {
                $sFile.=$this->outputRecord($headers, $elementArray, $oOptions);
            }
        }
        return $sFile;
    }

    protected function stripTagsFull($string)
    {
        $string=str_replace('-oth-','',$string);
        return flattenText($string,false,true,'UTF-8',false);
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
    */
    abstract protected function outputRecord($headers, $values, FormattingOptions $oOptions);
}
