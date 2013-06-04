<?php
class SurveyDao
{
    /**
    * Loads a survey from the database that has the given ID.  If no matching
    * survey is found then null is returned.  Note that no results are loaded
    * from this function call, only survey structure/definition.
    *
    * In the future it would be nice to load all languages from the db at
    * once and have the infrastructure be able to return responses based
    * on language codes.
    *
    * @param int $id
    * @return Survey
    */
    public function loadSurveyById($id)
    {
        $survey = new SurveyObj();
        $clang = Yii::app()->lang;
        
        $intId = sanitize_int($id);
        $survey->id = $intId;
        $survey->info = getSurveyInfo($survey->id);
        $lang = Survey::model()->findByPk($intId)->language;
        $clang = new limesurvey_lang($lang);

        $survey->fieldMap = createFieldMap($intId,'full',false,false,getBaseLanguageFromSurveyID($intId));
        // Check to see if timings are present and add to fieldmap if needed
        if ($survey->info['savetimings']=="Y") {
            $survey->fieldMap = $survey->fieldMap + createTimingsFieldMap($intId,'full',false,false,getBaseLanguageFromSurveyID($intId));
        }

        if (empty($intId))
        {
            //The id given to us is not an integer, croak.
            safeDie("An invalid survey ID was encountered: $sid");
        }


        //Load groups
        $sQuery = 'SELECT g.* FROM {{groups}} AS g '.
        'WHERE g.sid = '.$intId.' '.
        'ORDER BY g.group_order;';
        $recordSet = Yii::app()->db->createCommand($sQuery)->query()->readAll();
        $survey->groups = $recordSet;

        //Load questions
        $sQuery = 'SELECT q.* FROM {{questions}} AS q '.
        'JOIN {{groups}} AS g ON q.gid = g.gid '.
        'WHERE q.sid = '.$intId.' AND q.language = \''.$lang.'\' '.
        'ORDER BY g.group_order, q.question_order;';
        $survey->questions = Yii::app()->db->createCommand($sQuery)->query()->readAll();

        //Load answers
        $sQuery = 'SELECT DISTINCT a.* FROM {{answers}} AS a '.
        'JOIN {{questions}} AS q ON a.qid = q.qid '.
        'WHERE q.sid = '.$intId.' AND a.language = \''.$lang.'\' '.
        'ORDER BY a.qid, a.sortorder;';
        //$survey->answers = Yii::app()->db->createCommand($sQuery)->queryAll();
        $aAnswers= Yii::app()->db->createCommand($sQuery)->queryAll();
        foreach($aAnswers as $aAnswer)
        {
             if(Yii::app()->controller->action->id !='remotecontrol')
				$aAnswer['answer']=stripTagsFull($aAnswer['answer']);
             $survey->answers[$aAnswer['qid']][$aAnswer['scale_id']][$aAnswer['code']]=$aAnswer;
        }
        //Load language settings
        $sQuery = 'SELECT * FROM {{surveys_languagesettings}} WHERE surveyls_survey_id = '.$intId.';';
        $recordSet = Yii::app()->db->createCommand($sQuery)->query()->readAll();
        $survey->languageSettings = $recordSet;

        return $survey;
    }

    /**
    * Loads results for the survey into the $survey->responses array.  The
    * results  begin from $minRecord and end with $maxRecord.  Either none,
    * or both,  the $minRecord and $maxRecord variables must be provided.
    * If none are then all responses are loaded.
    *
    * @param Survey $survey
    * @param int $iOffset 
    * @param int $iLimit 
    */
    public function loadSurveyResults(SurveyObj $survey, $iLimit, $iOffset, $iMaximum, $sFilter='' )
    {

        // Get info about the survey
        $aSelectFields=Yii::app()->db->schema->getTable('{{survey_' . $survey->id . '}}')->getColumnNames();
        
        $oRecordSet = Yii::app()->db->createCommand()->from('{{survey_' . $survey->id . '}}');
        if (tableExists('tokens_'.$survey->id) && array_key_exists ('token',SurveyDynamic::model($survey->id)->attributes) && Permission::model()->hasSurveyPermission($survey->id,'tokens','read'))
        {
            $oRecordSet->leftJoin('{{tokens_' . $survey->id . '}} tokentable','tokentable.token={{survey_' . $survey->id . '}}.token');
            $aTokenFields=Yii::app()->db->schema->getTable('{{tokens_' . $survey->id . '}}')->getColumnNames();
            $aSelectFields=array_merge($aSelectFields,array_diff($aTokenFields, array('token')));
            $aSelectFields=array_diff($aSelectFields, array('token'));
            $aSelectFields[]='{{survey_' . $survey->id . '}}.token';
        }
        if ($survey->info['savetimings']=="Y") {
            $oRecordSet->leftJoin("{{survey_" . $survey->id . "_timings}} survey_timings", "{{survey_" . $survey->id . "}}.id = survey_timings.id");
            $aTimingFields=Yii::app()->db->schema->getTable("{{survey_" . $survey->id . "_timings}}")->getColumnNames();
            $aSelectFields=array_merge($aSelectFields,array_diff($aTimingFields, array('id')));
            $aSelectFields=array_diff($aSelectFields, array('id'));
            $aSelectFields[]='{{survey_' . $survey->id . '}}.id';
        }

        if ($sFilter!='')
            $oRecordSet->where($sFilter);
            
        if ($iOffset+$iLimit>$iMaximum)
        {
            $iLimit=$iMaximum-$iOffset;
        }
            
        $survey->responses=$oRecordSet->select($aSelectFields)->order('{{survey_' . $survey->id . '}}.id')->limit($iLimit, $iOffset)->query()->readAll();

        return count($survey->responses);
    }
}

class SurveyObj
{
    /**
    * @var int
    */
    public $id;

    /**
    * Whether the survey is anonymous or not.
    * @var boolean
    */
    public $anonymous;

    /**
    * Answer, codes, and full text to the questions.
    * This is used in conjunction with the fieldMap to produce
    * some of the more verbose output in a survey export.
    * array[recordNo][columnName]
    *
    * @var array[int][string]mixed
    */
    public $answers;

    /**
    * The fieldMap as generated by createFieldMap(...).
    * @var array[]mixed
    */
    public $fieldMap;

    /**
    * The groups in the survey.
    *
    * @var array[int][string]mixed
    */
    public $groups;
    
    /**
     * info about the survey
     * 
     * @var array
     */
    public $info;

    /**
    * The questions in the survey.
    *
    * @var array[int][string]mixed
    */
    public $questions;

    /**
    * The tokens in the survey.
    *
    * @var array[int][string]mixed
    */
    public $tokens;

    /**
    * Stores the responses to the survey in a two dimensional array form.
    * array[recordNo][fieldMapName]
    *
    * @var array[int][string]mixed
    */
    public $responses;

    /**
    *
    * @var array[int][string]mixed
    */
    public $languageSettings;
    
    /**
    * Returns question arrays ONLY for questions that are part of the
    * indicated group and are top level (i.e. no subquestions will be
    * returned).   If there are no then an empty array will be returned.
    * If $groupId is not set then all top level questions will be
    * returned regardless of the group they are a part of.
    */
    public function getQuestions($groupId = null)
    {
        $qs = array();
        foreach($this->questions as $q)
        {
            if ($q['parent_qid'] == 0)
            {
                if(empty($groupId) || $q['gid'] == $groupId)
                {
                    $qs[] = $q;
                }
            }
        }
        return $qs;
    }

    /**
    * Returns the question code/title for the question that matches the $fieldName.
    * False is returned if no matching question is found.
    * @param string $fieldName
    * @return string (or false)
    */
    public function getQuestionCode($fieldName)
    {
        if (isset($this->fieldMap[$fieldName]['title']))
        {
            return $this->fieldMap[$fieldName]['title'];
        }
        else
        {
            return false;
        }
    }

    public function getQuestionText($fieldName)
    {
        $question = $this->fieldMap[$fieldName];
        if ($question)
        {
            return $question['question'];
        }
        else
        {
            return false;
        }
    }


    /**
    * Returns all token records that have a token value that matches
    * the one given.  An empty array is returned if there are no
    * matching token records.
    *
    * @param mixed $token
    */
    public function getTokens($token)
    {
        $matchingTokens = array();

        foreach($this->tokens as $t)
        {
            if ($t['token'] == $token)
            {
                $matchingTokens[] = $t;
            }
        }

        return $matchingTokens;
    }

    /**
    * Returns an array containing all child question rows for the given parent
    * question ID.  If no children are found then an empty array is
    * returned.
    *
    * @param int $parentQuestionId
    * @return array[int]array[string]mixed
    */
    public function getSubQuestionArrays($parentQuestionId)
    {
        $results = array();
        foreach ($this->questions as $question)
        {
            if ($question['parent_qid'] == $parentQuestionId)
            {
                $results[$question['qid']] = $question;
            }
        }
        return $results;
    }

    /**
    * Returns the full answer for the question that matches $fieldName
    * and the answer that matches the $answerCode.  If a match cannot
    * be made then false is returned.
    *
    * The name of the variable $answerCode is not strictly an answerCode
    * but could also be a comment entered by a participant.
    *
    * @param string $fieldName
    * @param string $answerCode
    * @param Translator $translator
    * @param string $sLanguageCode
    * @return string (or false)
    */
    public function getFullAnswer($fieldName, $answerCode, Translator $translator, $sLanguageCode)
    {
        $fullAnswer = null;
        $fieldType = $this->fieldMap[$fieldName]['type'];
        $question = $this->fieldMap[$fieldName];
        $questionId = $question['qid'];
        $answer = null;
        if ($questionId)
        {
            $answers = $this->getAnswers($questionId);
            if (isset($answers[$answerCode]))
            {
                $answer = $answers[$answerCode]['answer'];
            }
        }

        //echo "\n$fieldName: $fieldType = $answerCode";
        switch ($fieldType)
        {
            case 'R':   //RANKING TYPE
                $fullAnswer = $answer;
                break;

            case '1':   //Array dual scale
                if (mb_substr($fieldName, -1) == 0)
                {
                    $answers = $this->getAnswers($questionId, 0);
                }
                else
                {
                    $answers = $this->getAnswers($questionId, 1);
                }
                if (array_key_exists($answerCode, $answers))
                {
                    $fullAnswer = $answers[$answerCode]['answer'];
                }
                else
                {
                    $fullAnswer = null;
                }
                break;

            case 'L':   //DROPDOWN LIST
            case '!':
                if (mb_substr($fieldName, -5, 5) == 'other')
                {
                    $fullAnswer = $answerCode;
                }
                else
                {
                    if ($answerCode == '-oth-')
                    {
                        $fullAnswer = $translator->translate('Other', $sLanguageCode);
                    }
                    else
                    {
                        $fullAnswer = $answer;
                    }
                }
                break;

            case 'O':   //DROPDOWN LIST WITH COMMENT
                if (isset($answer))
                {
                    //This is one of the dropdown list options.
                    $fullAnswer = $answer;
                }
                else
                {
                    //This is a comment.
                    $fullAnswer = $answerCode;
                }
                break;

            case 'Y':   //YES/NO
            switch ($answerCode)
            {
                case 'Y':
                    $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                    break;

                case 'N':
                    $fullAnswer = $translator->translate('No', $sLanguageCode);
                    break;

                default:
                    $fullAnswer = $translator->translate('N/A', $sLanguageCode);
            }
            break;

            case 'G':
            switch ($answerCode)
            {
                case 'M':
                    $fullAnswer = $translator->translate('Male', $sLanguageCode);
                    break;

                case 'F':
                    $fullAnswer = $translator->translate('Female', $sLanguageCode);
                    break;

                default:
                    $fullAnswer = $translator->translate('N/A', $sLanguageCode);
            }
            break;

            case 'M':   //MULTIOPTION
            case 'P':
                if (mb_substr($fieldName, -5, 5) == 'other' || mb_substr($fieldName, -7, 7) == 'comment')
                {
                    //echo "\n -- Branch 1 --";
                    $fullAnswer = $answerCode;
                }
                else
                {
                    switch ($answerCode)
                    {
                        case 'Y':
                            $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                            break;

                        case 'N':
                        case '':
                            $fullAnswer = $translator->translate('No', $sLanguageCode);
                            break;

                        default:
                            //echo "\n -- Branch 2 --";
                            $fullAnswer = $answerCode;
                    }
                }
                break;

            case 'C':
            switch ($answerCode)
            {
                case 'Y':
                    $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                    break;

                case 'N':
                    $fullAnswer = $translator->translate('No', $sLanguageCode);
                    break;

                case 'U':
                    $fullAnswer = $translator->translate('Uncertain', $sLanguageCode);
                    break;
            }
            break;

            case 'E':
            switch ($answerCode)
            {
                case 'I':
                    $fullAnswer = $translator->translate('Increase', $sLanguageCode);
                    break;

                case 'S':
                    $fullAnswer = $translator->translate('Same', $sLanguageCode);
                    break;

                case 'D':
                    $fullAnswer = $translator->translate('Decrease', $sLanguageCode);
                    break;
            }
            break;

            case 'F':
            case 'H':
                $answers = $this->getAnswers($questionId, 0);
                $fullAnswer = (isset($answers[$answerCode])) ? $answers[$answerCode]['answer'] : "";
                break;

            default:

                $fullAnswer .= $answerCode;
        }

        return $fullAnswer;
    }

    /**
    * Returns an array of possible answers to the question.  If $scaleId is
    * specified then only answers that match the $scaleId value will be
    * returned. An empty array may be returned by this function if answers 
    * are found that match the questionId.
    *
    * @param int $questionId
    * @param int $scaleId
    * @return array[string]array[string]mixed (or false)
    */
    public function getAnswers($questionId, $scaleId = '0')
    {
        if(isset($this->answers[$questionId]) && isset($this->answers[$questionId][$scaleId]))
        {
            return $this->answers[$questionId][$scaleId];
        }
        return array();
    }
}