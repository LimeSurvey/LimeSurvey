<?php
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
* A Survey object may be loaded from the database via the SurveyDao
* (which follows the Data Access Object pattern).  Data access is broken
* into two separate functions: the first loads the survey structure from
* the database, and the second loads responses from the database.  The
* data loading is structured in this way to provide for speedy access in
* the event that a survey's response table contains a large number of records.
* The responses can be loaded a user-defined number at a time for output
* without having to load the entire set of responses from the database.
*
* The Survey object contains methods to conveniently access data that it
* contains in an attempt to encapsulate some of the complexity of its internal
* format.
*
* Data formatting operations that may be specific to the data export routines
* are relegated to the Writer class hierarcy and work with the Survey object
* and FormattingOptions objects to provide proper style/content when exporting
* survey information.
*
* Some guess work has been done when deciding what might be specific to exports
* and what is not.  In general, anything that requires altering of data fields
* (abbreviating, concatenating, etc...) has been moved into the writers and
* anything that is a direct access call with no formatting logic is a part of
* the Survey object.
*
* - elameno
*/


class ExportSurveyResultsService
{
    /**
    * Root function for any export results action
    *
    * @param mixed $iSurveyId
    * @param mixed $sLanguageCode
    * @param csv|doc|pdf|xls $sExportPlugin Type of export
    * @param FormattingOptions $oOptions
    * @param string $sFilter 
    */
    function exportSurvey($iSurveyId, $sLanguageCode, $sExportPlugin, FormattingOptions $oOptions, $sFilter = '')
    {
        //Do some input validation.
        if (empty($iSurveyId))
        {
            safeDie('A survey ID must be supplied.');
        }
        if (empty($sLanguageCode))
        {
            safeDie('A language code must be supplied.');
        }
        if (empty($oOptions))
        {
            safeDie('Formatting options must be supplied.');
        }
        if (empty($oOptions->selectedColumns))
        {
            safeDie('At least one column must be selected for export.');
        }
        //echo $oOptions->toString().PHP_EOL;
        $writer = null;

        $iSurveyId = sanitize_int($iSurveyId);
        if ($oOptions->output=='display')
        {
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
        }

        switch ( $sExportPlugin ) {
            case "doc":
                $writer = new DocWriter();
                break;
            case "xls":
                $writer = new ExcelWriter();
                break;
            case "pdf":
                $writer = new PdfWriter();
                break;
            case "csv":
            default:
                $writer = new CsvWriter();
                break;
        }

        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($iSurveyId);
        $writer->init($survey, $sLanguageCode, $oOptions);

        $iBatchSize=100; $iCurrentRecord=$oOptions->responseMinRecord-1;
        $bMoreRecords=true; $first=true;
        while ($bMoreRecords)
        {
            $iExported= $surveyDao->loadSurveyResults($survey, $iBatchSize, $iCurrentRecord, $oOptions->responseMaxRecord, $sFilter);
            $iCurrentRecord+=$iExported;
            $writer->write($survey, $sLanguageCode, $oOptions,$first);
            $first=false;
            $bMoreRecords= ($iExported == $iBatchSize);
        }
        $result = $writer->close();
        if ($oOptions->output=='file')
        {
            return $writer->filename;
        } else {
            return $result;
        }
    }
}

class FormattingOptions
{
    public $responseMinRecord;
    public $responseMaxRecord;

    /**
    * The columns that have been selected for output.  The values must be
    * in fieldMap format.
    *
    * @var array[]string
    */
    public $selectedColumns;

    /**
    * Acceptable values are:
    * "complete" = include only incomplete answers
    * "incomplete" = only include incomplete answers
    * "all" = include ALL answers
    *
    * @var mixed
    */
    public $responseCompletionState;

    /**
    * Acceptable values are:
    * "abbreviated" = Abbreviated headings
    * "full" = Full headings
    * "code" = Question codes
    *
    * @var string
    */
    public $headingFormat;

    /**
    * Indicates whether to convert spaces in question headers to underscores.
    *
    * @var boolean
    */
    public $headerSpacesToUnderscores;

    /**
    * Valid values are:
    * "short" = Answer codes
    * "long" = Full answers
    *
    * @var string
    */
    public $answerFormat;

    /**
    * If $answerFormat is set to "short" then this indicates that 'Y' responses
    * should be converted to another value that is specified by $yValue.
    *
    * @var boolean
    */
    public $convertY;

    public $yValue;

    /**
    * If $answerFormat is set to "short" then this indicates that 'N' responses
    * should be converted to another value that is specified by $nValue.
    *
    * @var boolean
    */
    public $convertN;

    public $nValue;
    
    /**
    * Destination format - either 'display' (send to browser) or 'file' (send to file)
    * 
    * @var string
    */
    public $output;

    public function toString()
    {
        return $this->format.','.$this->headingFormat.','
        .$this->headerSpacesToUnderscores.','.$this->responseCompletionState
        .','.$this->responseMinRecord.','.$this->responseMaxRecord.','
        .$this->answerFormat.','.$this->convertY.','.$this->yValue.','
        .$this->convertN.','.$this->nValue.','
        .implode(',',$this->selectedColumns);
    }
}

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
        //Load tokens
        if (tableExists('{{tokens_' . $intId . '}}') && hasSurveyPermission($intId,'tokens','read'))
        {
            $sQuery = 'SELECT t.* FROM {{tokens_' . $intId . '}} AS t;';
            $recordSet = Yii::app()->db->createCommand($sQuery)->query()->readAll();
            $survey->tokens = $recordSet;
        }
        else
        {
            $survey->tokens=array();
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
        if (tableExists('tokens_'.$survey->id) && array_key_exists ('token',Survey_dynamic::model($survey->id)->attributes) && hasSurveyPermission($survey->id,'tokens','read'))
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
    * Answers, codes, and full text to the questions.
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

class Translator
{
    //An associative array:  key = language code, value = translation library
    private $translations = array();

    //The following array stores field names that require pulling a value from the
    //internationalization layer. <fieldname> => <internationalization key>
    private $headerTranslationKeys = array(
    'id' => 'id',
    'lastname' => 'Last name',
    'firstname' => 'First name',
    'email' => 'Email address',
    'token' => 'Token',
    'datestamp' => 'Date last action',
    'startdate' => 'Date started',
    'submitdate' => 'Completed',
    'ipaddr' => 'IP address',
    'refurl' => 'Referring URL',
    'lastpage' => 'Last page',
    'startlanguage' => 'Start language'
    );

    public function translate($key, $sLanguageCode)
    {
        return $this->getTranslationLibrary($sLanguageCode)->gT($key);
    }

    /**
    * Accepts a fieldName from a survey fieldMap and returns the translated value
    * for the fieldName in the survey's base language (if one exists).
    * If no translation exists for the provided column/fieldName then
    * false is returned.
    *
    * To add any columns/fieldNames to be processed by this function, simply add the
    * column/fieldName to the $headerTranslationKeys associative array.
    *
    * This provides a mechanism for determining of a column in a survey's data table
    * needs to be translated through the translation mechanism, or if its an actual
    * survey data column.
    *
    * @param string $column
    * @param string $sLanguageCode
    * @return string
    */
    public function translateHeading($column, $sLanguageCode)
    {
        $key = $this->getHeaderTranslationKey($column);
        //echo "Column: $column, Key: $key".PHP_EOL;
        if ($key)
        {
            return $this->translate($key, $sLanguageCode);
        }
        else
        {
            return false;
        }
    }

    protected function getTranslationLibrary($sLanguageCode)
    {
        $library = null;
        if (!array_key_exists($sLanguageCode, $this->translations))
        {
            $library = new limesurvey_lang($sLanguageCode);
            $this->translations[$sLanguageCode] = $library;
        }
        else
        {
            $library = $this->translations[$sLanguageCode];
        }
        return $library;
    }

    /**
    * Finds the header translation key for the column passed in.  If no key is
    * found then false is returned.
    *
    * @param string $key
    * @return string (or false if no match is found)
    */
    public function getHeaderTranslationKey($column)
    {
        if (isset($this->headerTranslationKeys[$column]))
        {
            return $this->headerTranslationKeys[$column];
        }
        else
        {
            return false;
        }
    }
}

interface IWriter
{
    /**
    * Writes the survey and all the responses it contains to the output
    * using the options specified in FormattingOptions.
    *
    * See Survey and SurveyDao objects for information on loading a survey
    * and results from the database.
    *
    * @param Survey $survey
    * @param string $sLanguageCode
    * @param FormattingOptions $oOptions
    */
    public function write(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions);
    public function close();
}

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

    protected function translateHeading($column, $sLanguageCode)
    {
        if (substr($column,0,10)=='attribute_') return $column;
        return $this->translator->translateHeading($column, $sLanguageCode);
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
    * Returns true if, given the $oOptions, the response should be included in the
    * output, and false if otherwise.
    *
    * @param mixed $response
    * @param FormattingOptions $oOptions
    * @return boolean
    */
    protected function shouldOutputResponse(array $response, FormattingOptions $oOptions)
    {
        switch ($oOptions->responseCompletionState)
        {
            default:
            case 'all':
                return true;
                break;
            case 'incomplete':
                return !isset($response['submitdate']);
                break;
            case 'complete':
                return isset($response['submitdate']);
                break;

        }
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
        $question = $survey->fieldMap[$fieldName];
        if ($question)
        {
            $heading = $question['question'];
            $heading = $this->stripTagsFull($heading);
            $heading = mb_substr($heading, 0, 15).'.. ';
            $aid = $survey->fieldMap[$fieldName]['aid'];
            if (!empty($aid))
            {
                $heading .= '['.$this->stripTagsFull($aid).']';
            }
            return $heading;
        }
        return false;
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
        $question = $survey->fieldMap[$fieldName];
        
        $heading = $question['question'];
        $heading = $this->stripTagsFull($heading);
        $heading.=$this->getFullFieldSubHeading($survey, $oOptions, $fieldName);
        return $heading;
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
                //The headers created by this section of code are significantly different from
                //the old code.  I believe that they are more accurate. - elameno
                list($scaleZeroTitle, $scaleOneTitle) = explode('_', $answerCode);
                $sqs = $survey->getSubQuestionArrays($questionId);

                $scaleZeroText = '';
                $scaleOneText = '';
                foreach ($sqs as $sq)
                {
                    if ($sq['title'] == $scaleZeroTitle && $sq['scale_id'] == 0)
                    {
                        $scaleZeroText = $sq['question'];
                    }
                    elseif ($sq['title'] == $scaleOneTitle && $sq['scale_id'] == 1)
                    {
                        $scaleOneText = $sq['question'];
                    }
                }

                $subHeading .= ' ['.$this->stripTagsFull($scaleZeroText).']['.$this->stripTagsFull($scaleOneText).']';
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
    * @return string
    */
    private function transformResponseValue($value, $fieldType, FormattingOptions $oOptions)
    {
        //The following if block handles transforms of Ys and Ns.
        if (($oOptions->convertN || $oOptions->convertY) &&
        isset($fieldType) &&
        ($fieldType == 'M' || $fieldType == 'P' || $fieldType == 'Y'))
        {
            if ($value == 'N' && $oOptions->convertN)
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
                //Output the header.
                $value = $this->translateHeading($column, $sLanguageCode);
                if($value===false)
                {
                    //This branch may be reached erroneously if columns are added to the LimeSurvey product
                    //but are not updated in the Writer->headerTranslationKeys array.  We should trap for this
                    //condition and do a safeDie.
                    //FIXME fix the above condition

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
                            $value = $this->getCodeHeading($survey, $oOptions, $column);
                            break;
                    }
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
        foreach($survey->responses as $response)
        {
            $elementArray = array();

            //If we shouldn't be outputting this response then we should skip the rest
            //of the loop and continue onto the next value.
            if (!$this->shouldOutputResponse($response, $oOptions))
            {
                continue;
            }
            foreach ($oOptions->selectedColumns as $column)
            {
                $value = $response[$column];
                if (isset($survey->fieldMap[$column]) && $survey->fieldMap[$column]['type']!='answer_time' && $survey->fieldMap[$column]['type']!='page_time' && $survey->fieldMap[$column]['type']!='interview_time')
                {
                    switch ($oOptions->answerFormat) {
                        case 'long':
                            $elementArray[] = $this->transformResponseValue($survey->getFullAnswer($column, $value, $this->translator, $this->languageCode), $survey->fieldMap[$column]['type'], $oOptions);
                            break;
                        default:
                        case 'short':
                            $elementArray[] = $this->transformResponseValue($value,
                            $survey->fieldMap[$column]['type'], $oOptions);
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

class CsvWriter extends Writer
{
    private $output;
    private $separator;
    private $hasOutputHeader;
    /**
     * The open filehandle
     */
    private $file = null;

    function __construct()
    {
        $this->output = '';
        $this->separator = ',';
        $this->hasOutputHeader = false;
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        if ($oOptions->output=='display') {
            header("Content-Disposition: attachment; filename=results-survey".$survey->id.".csv");
            header("Content-type: text/comma-separated-values; charset=UTF-8");
        } elseif ($oOptions->output == 'file') {
            $this->file = fopen($this->filename, 'w');
        }
        
    }
    
    protected function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        $sRecord='';
        if(!$this->hasOutputHeader)
        {
            $index = 0;
            foreach ($headers as $header)
            {
                $headers[$index] = $this->csvEscape($header);
                $index++;
            }
            //Output the header...once and only once.
            $sRecord.=implode($this->separator, $headers);
            $this->hasOutputHeader = true;
        }
        //Output the values.
        $index = 0;
        foreach ($values as $value)
        {
            $values[$index] = $this->csvEscape($value);
            $index++;
        }
        $sRecord.=PHP_EOL.implode($this->separator, $values);
        if ($oOptions->output=='display')
        {
            echo $sRecord; 
            $this->output = '';
        } elseif ($oOptions->output == 'file') {
            $this->output .= $sRecord;
            fwrite($this->file, $this->output);
            $this->output='';
        } 
    }

    public function close()
    {
        if (!is_null($this->file)) {
            fclose($this->file);
        }
    }

    /**
    * Returns the value with all necessary escaping needed to place it into a CSV string.
    *
    * @param string $value
    * @return string
    */
    protected function csvEscape($value)
    {
        return CSVEscape($value);
    }
}

class DocWriter extends Writer
{
    private $output;
    private $separator;
    private $isBeginning;
    /**
     * The open filehandle
     */
    private $file = null;

    public function __construct()
    {
        $this->separator = "\t";
        $this->output = '';
        $this->isBeginning = true;
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);

        if ($oOptions->output=='display')
        {
            header("Content-Disposition: attachment; filename=results-survey".$survey->id.".doc");
            header("Content-type: application/vnd.ms-word");
        }
        
        
        $sOutput = '<style>
        table {
        border-collapse:collapse;
        }
        td, th {
        border:solid black 1.0pt;
        }
        th {
        background: #c0c0c0;
        }
        </style>';
        if ($oOptions->output=='display'){
            echo  $sOutput;
        } elseif ($oOptions->output == 'file') {
            $this->file = fopen($this->filename, 'w');
            $this->output = $sOutput;
        }
    }

    /**
    * @param array $headers
    * @param array $values
    * @param FormattingOptions $oOptions
    */
    protected function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        if ($oOptions->answerFormat == 'short')
        {
            //No headers at all, only output values.
            $this->output .= implode($this->separator, $values).PHP_EOL;          
        }
        elseif ($oOptions->answerFormat == 'long')
        {
            //Output each record, one per page, with a header preceding every value.
            if ($this->isBeginning)
            {
                $this->isBeginning = false;
            }
            else
            {
                $this->output .= "<br clear='all' style='page-break-before:always'>";
            }
            $this->output .= "<table><tr><th colspan='2'>".$this->translate('New Record', $this->languageCode)."</td></tr>".PHP_EOL;

            $counter = 0;
            foreach ($headers as $header)
            {
                $this->output .= "<tr><td>".$header."</td><td>".$values[$counter]."</td></tr>".PHP_EOL;
                $counter++;
            }
            $this->output .= "</table>".PHP_EOL;           
        }
        else
        {
            safeDie('An invalid answer format was selected.  Only \'short\' and \'long\' are valid.');
        }
        if ($oOptions->output=='display'){
            echo  $this->output;
            $this->output='';
        } elseif ($oOptions->output == 'file') {
            fwrite($this->file, $this->output);
            $this->output='';
        }
    }

    public function close()
    {
        if (!is_null($this->file)) {
            fclose($this->file);
        }
    }
}

/**
* Exports results in Microsoft Excel format.  By default the Writer sends
* HTTP headers and the file contents via HTTP.  For testing purposes a
* file name can be  to the constructor which will cause the ExcelWriter to
* output to a file.
*/
class ExcelWriter extends Writer
{
    private $workbook;
    private $currentSheet;
    private $separator;
    private $hasOutputHeader;
    private $rowCounter;

    //Indicates if the Writer is outputting to a file rather than sending via HTTP.
    private $outputToFile = false;

    /**
    * The presence of a filename will cause the writer to output to
    * a file rather than send.
    *
    * @param string $filename
    * @return ExcelWriter
    */
    public function __construct($filename = null)
    {
        Yii::import('application.libraries.admin.pear.Spreadsheet.Excel.Xlswriter', true);
        $this->separator = '~|';
        $this->hasOutputHeader = false;
        $this->rowCounter = 0;
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);

        if ($oOptions->output=='file')
        {
            $this->workbook = new xlswriter($this->filename);
            $this->outputToFile = true;
        }
        else
        {
            $this->workbook = new xlswriter;
        }
        $this->workbook->setTempDir(Yii::app()->getConfig("tempdir"));

        if ($oOptions->output=='display') {
            $this->workbook->send('results-survey'.$survey->id.'.xls');
  
        }
        $worksheetName = $survey->languageSettings[0]['surveyls_title'];
        $worksheetName=substr(str_replace(array('*', ':', '/', '\\', '?', '[', ']'),array(' '),$worksheetName),0,31); // Remove invalid characters

        $this->workbook->setVersion(8);
        $sheet =$this->workbook->addWorksheet($worksheetName); // do not translate/change this - the library does not support any special chars in sheet name
        $sheet->setInputEncoding('utf-8');
        $this->currentSheet = $sheet;
    }

    protected function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        if (!$this->hasOutputHeader)
        {
            $columnCounter = 0;
            foreach ($headers as $header)
            {
                $this->currentSheet->write($this->rowCounter,$columnCounter,str_replace('?', '-', $this->excelEscape($header)));
                $columnCounter++;
            }
            $this->hasOutputHeader = true;
            $this->rowCounter++;
        }
        $columnCounter = 0;
        foreach ($values as $value)
        {
            $this->currentSheet->write($this->rowCounter, $columnCounter, $this->excelEscape($value));
            $columnCounter++;
        }
        $this->rowCounter++;
    }

    private function excelEscape($value)
    {
        if (substr($value, 0, 1) == '=')
        {
            $value = '"'.$value.'"';
        }
        return $value;
    }

    public function close()
    {
        $this->workbook->close();
        return $this->workbook;
    }
}

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
        $pdfdefaultfont=Yii::app()->getConfig('pdfdefaultfont');
        $pdffontsize=Yii::app()->getConfig('pdffontsize');
        $pdforientation=Yii::app()->getConfig('pdforientation');
        $clang = new limesurvey_lang($sLanguageCode);

        if ($oOptions->output=='file') 
        {
            $this->pdfDestination = 'F';
        } else {
            $this->pdfDestination = 'D';
        }
        Yii::import('application.libraries.admin.pdf', true);
        if($pdfdefaultfont=='auto')
        {
            $pdfdefaultfont=PDF_FONT_NAME_DATA;
        }
        // Array of PDF core fonts: are replaced by according fonts according to the alternatepdffontfile array.Maybe just courier,helvetica and times but if a user want symbol: why not ....
        $pdfcorefont=array("courier","helvetica","symbol","times","zapfdingbats");
        $pdffontsize=Yii::app()->getConfig('pdffontsize');

        // create new PDF document
        $this->pdf = new pdf();
        if (in_array($pdfdefaultfont,$pdfcorefont))
        {
            $alternatepdffontfile=Yii::app()->getConfig('alternatepdffontfile');
            if(array_key_exists($sLanguageCode,$alternatepdffontfile))
            {
                $pdfdefaultfont = $alternatepdffontfile[$sLanguageCode];// Actually use only core font
            }
        }
        if ($pdffontsize=='auto')
        {
            $pdffontsize=PDF_FONT_SIZE_MAIN;
        }

        $this->pdf = new pdf();
        $this->pdf->SetFont($pdfdefaultfont, '', $pdffontsize);
        $this->pdf->AddPage();
        $this->pdf->intopdf("PDF export ".date("Y.m.d-H:i", time()));
        //Set some pdf metadata
        Yii::app()->loadHelper('surveytranslator');
        $lg=array();
        $lg['a_meta_charset'] = 'UTF-8';
        if (getLanguageRTL($sLanguageCode))
        {
            $lg['a_meta_dir'] = 'rtl';
        }
        else
        {
            $lg['a_meta_dir'] = 'ltr';
        }
        $lg['a_meta_language'] = $sLanguageCode;
        $lg['w_page']=$clang->gT("page");
        $this->pdf->setLanguageArray($lg);

        $this->separator="\t";

        $this->rowCounter = 0;        
        $this->surveyName = $survey->languageSettings[0]['surveyls_title'];
        $this->pdf->titleintopdf($this->surveyName, $survey->languageSettings[0]['surveyls_description']);
    }

    public function outputRecord($headers, $values, FormattingOptions $oOptions)
    {
        $this->rowCounter++;
        if ($oOptions->answerFormat == 'short')
        {
            $pdfstring = '';
            $this->pdf->titleintopdf($this->translate('New Record', $this->languageCode));
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
            $this->pdf->Cell(0, 10, $this->translate('NEW RECORD', $this->languageCode).' '.$this->rowCounter, 1, 1);

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
