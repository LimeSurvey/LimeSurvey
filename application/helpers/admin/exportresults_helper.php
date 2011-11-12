<?php
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

//include_once 'login_check.php';
//include_once $rootdir.'/config-defaults.php';
//require_once $rootdir."/common_functions.php";
//require_once $rootdir."/classes/core/language.php";
//require_once $rootdir.'/classes/core/sanitize.php';
//require_once 'classes/pear/Spreadsheet/Excel/Writer.php';
//require_once 'classes/phpexcel/PHPExcel.php';
//require_once 'classes/tcpdf/extensiontcpdf.php';

class ExportSurveyResultsService
{
    function exportSurvey($surveyId, $languageCode, FormattingOptions $options)
    {
        //Do some input validation.
        if (empty($surveyId))
        {
            safe_die('A survey ID must be supplied.');
        }
        if (empty($languageCode))
        {
            safe_die('A language code must be supplied.');
        }
        if (empty($options))
        {
            safe_die('Formatting options must be supplied.');
        }
        if (empty($options->selectedColumns))
        {
            safe_die('At least one column must be selected for export.');
        }
        //echo $options->toString().PHP_EOL;
        $writer = null;
        $intSurveyId = sanitize_int($surveyId);

        switch ( $options->format ) {
            case "doc":
                header("Content-Disposition: attachment; filename=results-survey".$intSurveyId.".doc");
                header("Content-type: application/vnd.ms-word");
                $writer = new DocWriter();
                break;
            case "xls":
                $writer = new ExcelWriter();
                break;
            case "csv":
                header("Content-Disposition: attachment; filename=results-survey".$intSurveyId.".csv");
                header("Content-type: text/comma-separated-values; charset=UTF-8");
                $writer = new CsvWriter();
                break;
            case "pdf":
                $writer = new PdfWriter();
                break;
            default:
                header("Content-Disposition: attachment; filename=results-survey".$intSurveyId.".csv");
                header("Content-type: text/comma-separated-values; charset=UTF-8");
                $writer = new CsvWriter();
                break;
        }
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");

        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($intSurveyId);
        $surveyDao->loadSurveyResults($survey, $options->responseMinRecord, $options->responseMaxRecord);

        $writer->write($survey, $languageCode, $options);

        $output = $writer->close();

        if ($options->format == 'csv' || $options->format == 'doc')
        {
            echo $output;
        }
        return $output;
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
    * "filter" = do not include incomplete answers
    * "incomplete" = only include incomplete answers
    * "show" = include ALL answers
    *
    * @var mixed
    */
    public $responseCompletionState;

    /**
    * Acceptable values are:
    * "abrev" = Abbreviated headings
    * "full" = Full headings
    * "headcodes" = Question codes
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
    * "doc", "xls", "csv", "pdf"
    * @var string
    */
    public $format;

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
        $CI=& get_instance();
        $survey = new Survey();
        $clang = $CI->limesurvey_lang;

        $intId = sanitize_int($id);
        $survey->id = $intId;
        //$clang must be set up prior to calling the createFieldMap function.
        $lang = GetBaseLanguageFromSurveyID($intId);
        $clang = new limesurvey_lang(array($lang));
        $survey->fieldMap = createFieldMap($id, 'full');

        if (empty($intId))
        {
            //The id given to us is not an integer, croak.
            safe_die("An invalid survey ID was encountered: $sid");
        }


        //Load groups
        $sql = 'SELECT g.* FROM '.$CI->db->dbprefix('groups').' AS g '.
        'WHERE g.sid = '.$intId.' '.
        'ORDER BY g.group_order;';
        $recordSet = db_execute_assoc($sql);
        $survey->groups = $recordSet->result_array();

        //Load questions
        $sql = 'SELECT q.* FROM '.$CI->db->dbprefix('questions').' AS q '.
        'JOIN '.$CI->db->dbprefix('groups').' AS g ON q.gid = g.gid '.
        'WHERE q.sid = '.$intId.' AND q.language = \''.$lang.'\' '.
        'ORDER BY g.group_order, q.question_order;';
        $recordSet = db_execute_assoc($sql);
        $survey->questions = $recordSet->result_array();

        //Load answers
        $sql = 'SELECT DISTINCT a.* FROM '.$CI->db->dbprefix('answers').' AS a '.
        'JOIN '.$CI->db->dbprefix('questions').' AS q ON a.qid = q.qid '.
        'WHERE q.sid = '.$intId.' AND a.language = \''.$lang.'\' '.
        'ORDER BY a.qid, a.sortorder;';
        $recordSet = db_execute_assoc($sql);
        $survey->answers = $recordSet->result_array();

        //Load tokens
        if (tableExists('tokens_'.$survey->id))
        {
            $sql = 'SELECT t.* FROM '.$CI->db->dbprefix('tokens_'.$intId).' AS t;';
            $recordSet = db_execute_assoc($sql);
            $survey->tokens = $recordSet->result_array();
        }
        else
        {
            $survey->tokens=array();
        }

        //Load language settings
        $sql = 'SELECT * FROM '.$CI->db->dbprefix('surveys_languagesettings').' WHERE surveyls_survey_id = '.$intId.';';
        $recordSet = db_execute_assoc($sql);
        $survey->languageSettings = $recordSet->result_array();

        return $survey;
    }

    /**
    * Loads results for the survey into the $survey->responses array.  The
    * results  begin from $minRecord and end with $maxRecord.  Either none,
    * or both,  the $minRecord and $maxRecord variables must be provided.
    * If none are then all responses are loaded.
    *
    * @param Survey $survey
    * @param int $minRecord
    * @param int $maxRecord
    */
    public function loadSurveyResults(Survey $survey, $minRecord = null, $maxRecord = null)
    {
        $CI=& get_instance();
        /* @var $recordSet ADORecordSet */
        $sql = 'SELECT * FROM '.$CI->db->dbprefix('survey_'.$survey->id);
        if (!isset($minRecord) && !isset($maxRecord))
        {
            //Neither min or max is set, load it all.
            $recordSet = db_execute_assoc($sql);
        }
        elseif (!isset($minRecord) xor !isset($maxRecord))
        {
            //One is set, but not the other...invalid input.
            safe_die('Either none of, or both of, the variables $minRecord and $maxRecord must be set.');
        }
        else
        {
            //Both min and max are set.
            $recordSet = db_select_limit_assoc($sql, $maxRecord - $minRecord + 1, $minRecord);
        }
        //Convert the data in the recordSet to a 2D array and stuff it in $responses.
        $survey->responses = $recordSet->result_array(-1);
    }
}

class Survey
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
        $question = $this->getQuestionArray($fieldName);
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
    * Returns an associative array containing keys that are equivalent to the
    * field names in the question table. The values are for the question that matches
    * the given $fieldName.  If no match is found then false is returned.
    *
    * @param string $fieldName
    * @return array[string]mixed  (or false)
    */
    public function getQuestionArray($fieldName)
    {
        foreach ($this->questions as $question)
        {
            if ($question['qid'] == $this->fieldMap[$fieldName]['qid'])
            {
                return $question;
            }
        }
        return false;
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
    * @param string $languageCode
    * @return string (or false)
    */
    public function getFullAnswer($fieldName, $answerCode, Translator $translator, $languageCode)
    {
        $fullAnswer = null;
        $fieldType = $this->fieldMap[$fieldName]['type'];
        $question = $this->getQuestionArray($fieldName);
        $questionId = $question['qid'];
        $answers = $this->getAnswers($questionId);
        if (array_key_exists($answerCode, $answers))
        {
            $answer = $answers[$answerCode]['answer'];
        }
        else
        {
            $answer = null;
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
                        $fullAnswer = $translator->translate('Other', $languageCode);
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
                    $fullAnswer = $translator->translate('Yes', $languageCode);
                    break;

                case 'N':
                    $fullAnswer = $translator->translate('No', $languageCode);
                    break;

                default:
                    $fullAnswer = $translator->translate('N/A', $languageCode);
            }
            break;

            case 'G':
            switch ($answerCode)
            {
                case 'M':
                    $fullAnswer = $translator->translate('Male', $languageCode);
                    break;

                case 'F':
                    $fullAnswer = $translator->translate('Female', $languageCode);
                    break;

                default:
                    $fullAnswer = $translator->translate('N/A', $languageCode);
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
                            $fullAnswer = $translator->translate('Yes', $languageCode);
                            break;

                        case 'N':
                        case '':
                            $fullAnswer = $translator->translate('No', $languageCode);
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
                    $fullAnswer = $translator->translate('Yes', $languageCode);
                    break;

                case 'N':
                    $fullAnswer = $translator->translate('No', $languageCode);
                    break;

                case 'U':
                    $fullAnswer = $translator->translate('Uncertain', $languageCode);
                    break;
            }
            break;

            case 'E':
            switch ($answerCode)
            {
                case 'I':
                    $fullAnswer = $translator->translate('Increase', $languageCode);
                    break;

                case 'S':
                    $fullAnswer = $translator->translate('Same', $languageCode);
                    break;

                case 'D':
                    $fullAnswer = $translator->translate('Decrease', $languageCode);
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
    * returned. An empty array
    * may be returned by this function if answers are found that match the
    * questionId.
    *
    * @param int $questionId
    * @param int $scaleId
    * @return array[string]array[string]mixed (or false)
    */
    public function getAnswers($questionId, $scaleId = null)
    {
        $answers = array();
        foreach ($this->answers as $answer)
        {
            if (null == $scaleId && $answer['qid'] == $questionId)
            {
                $answers[$answer['code']] = $answer;
            }
            else if ($answer['qid'] == $questionId && $answer['scale_id'] == $scaleId)
                {
                    $answers[$answer['code']] = $answer;
                }
        }
        return $answers;
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
    'lastname' => 'Last Name',
    'firstname' => 'First Name',
    'email' => 'Email Address',
    'token' => 'Token',
    'datestamp' => 'Date Last Action',
    'startdate' => 'Date Started',
    'submitdate' => 'Completed',
    //'completed' => 'Completed',
    'ipaddr' => 'IP-Address',
    'refurl' => 'Referring URL',
    'lastpage' => 'Last page seen',
    'startlanguage' => 'Start language'//,
    //'tid' => 'Token ID'
    );

    public function translate($key, $languageCode)
    {
        return $this->getTranslationLibrary($languageCode)->gT($key);
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
    * @param string $languageCode
    * @return string
    */
    public function translateHeading($column, $languageCode)
    {
        $key = $this->getHeaderTranslationKey($column);
        //echo "Column: $column, Key: $key".PHP_EOL;
        if ($key)
        {
            return $this->translate($key, $languageCode);
        }
        else
        {
            return false;
        }
    }

    protected function getTranslationLibrary($languageCode)
    {
        $library = null;
        if (!array_key_exists($languageCode, $this->translations))
        {
            $library = new limesurvey_lang(array($languageCode));
            $this->translations[$languageCode] = $library;
        }
        else
        {
            $library = $this->translations[$languageCode];
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
    * @param string $languagecode
    * @param FormattingOptions $options
    */
    public function write(Survey $survey, $languageCode, FormattingOptions $options);
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
    protected $languageCode;
    protected $translator;

    protected function translate($key, $languageCode)
    {
        return $this->translator->translate($key, $languageCode);
    }

    protected function translateHeading($column, $languageCode)
    {
        return $this->translator->translateHeading($column, $languageCode);
    }

    private final function initialize(Survey $survey, $languageCode, FormattingOptions $options)
    {
        $this->languageCode = $languageCode;
        $this->translator = new Translator();
        $this->init($survey, $languageCode, $options);
    }

    /**
    * An initialization method that implementing classes can override to gain access
    * to any information about the survey, language, or formatting options they
    * may need for setup.
    *
    * @param Survey $survey
    * @param mixed $languageCode
    * @param FormattingOptions $options
    */
    protected function init(Survey $survey, $languageCode, FormattingOptions $options)
    {
        //This implementation does nothing.
    }

    /**
    * Returns true if, given the $options, the response should be included in the
    * output, and false if otherwise.
    *
    * @param mixed $response
    * @param FormattingOptions $options
    * @return boolean
    */
    protected function shouldOutputResponse(array $response, FormattingOptions $options)
    {
        switch ($options->responseCompletionState)
        {
            case 'show':
                return true;
                break;

            case 'incomplete':
                return !isset($response['submitdate']);
                break;

            case 'filter':
                return isset($response['submitdate']);
                break;

            default:
                //Ut oh
                safe_die('An invalid incomplete answer filter state was encountered: '.$options->responseCompletionState);
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
    public function getAbbreviatedHeading(Survey $survey, $fieldName)
    {
        $question = $survey->getQuestionArray($fieldName);
        if ($question)
        {
            $heading = $question['question'];
            $heading = $this->strip_tags_full($heading);
            $heading = mb_substr($heading, 0, 15).'.. ';
            $aid = $survey->fieldMap[$fieldName]['aid'];
            if (!empty($aid))
            {
                $heading .= '['.$aid.']';
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
    * @param FormattingOptions $options
    * @param string $fieldName
    * @return string (or false)
    */
    public function getFullHeading(Survey $survey, FormattingOptions $options, $fieldName)
    {
        $question = $survey->getQuestionArray($fieldName);
        $heading = $question['question'];
        $heading = $this->strip_tags_full($heading);
        $heading.=$this->getFullFieldSubHeading($survey, $options, $fieldName);
        return $heading;
    }

    public function getCodeHeading(Survey $survey, FormattingOptions $options, $fieldName)
    {
        $question = $survey->getQuestionArray($fieldName);
        $heading = $question['title'];
        $heading = $this->strip_tags_full($heading);
        $heading.=$this->getCodeFieldSubHeading($survey, $options, $fieldName);
        return $heading;
    }

    public function getCodeFieldSubHeading(Survey $survey, FormattingOptions $options, $fieldName)
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
                elseif (!$isComment)
                {
                    $subHeading .= ' ['.$answerCode.']';
                }
                if (isset($isComment) && $isComment == true)
                {
                    $subHeading .= ' '.$this->getCommentSubHeading();
                    $comment = false;
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
                        $subHeading = ' ['.FlattenText($question['title'], true,true).'][Scale '.$answerScale.']';
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

    public function getFullFieldSubHeading(Survey $survey, FormattingOptions $options, $fieldName)
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
                        $subHeading .= ' ['.$value.']';
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

                $subHeading .= ' ['.$this->strip_tags_full($scaleZeroText).']['.$this->strip_tags_full($scaleOneText).']';
                break;

            case '1':
                $answerScale = substr($fieldName, -1) + 1;
                $subQuestions = $survey->getSubQuestionArrays($questionId);
                foreach ($subQuestions as $question)
                {
                    if ($question['title'] == $answerCode && $question['scale_id'] == 0)
                    {
                        $subHeading = ' ['.FlattenText($question['question'], true,true).'][Scale '.$answerScale.']';
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
                    $subHeading .= ' ['.$this->strip_tags_full($subQuestion['question']).']';
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
    * The final step in the transform is to apply a strip_tags_full on the $value.
    * This occurs for ALL values whether or not any other transform is applied.
    *
    * @param string $value
    * @param string $fieldType
    * @param FormattingOptions $options
    * @return string
    */
    private function transformResponseValue($value, $fieldType, FormattingOptions $options)
    {
        //The following if block handles transforms of Ys and Ns.
        if (($options->convertN || $options->convertY) &&
        isset($fieldType) &&
        ($fieldType == 'M' || $fieldType == 'P' || $fieldType == 'Y'))
        {
            if ($value == 'N' && $options->convertN)
            {
                //echo "Transforming 'N' to ".$options->nValue.PHP_EOL;
                return $options->nValue;
            }
            else if ($value == 'Y' && $options->convertY)
                {
                    //echo "Transforming 'Y' to ".$options->yValue.PHP_EOL;
                    return $options->yValue;
                }
        }

        //This spot should only be reached if no transformation occurs.
        return $this->strip_tags_full($value);
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
    * @param string $languageCode
    * @param FormattingOptions $options
    */
    final public function write(Survey $survey, $languageCode, FormattingOptions $options)
    {
        $this->initialize($survey, $languageCode, $options);

        //Output the survey.
        $headers = array();
        foreach ($options->selectedColumns as $column)
        {
            //Output the header.
            $value = $this->translateHeading($column, $languageCode);
            if(!$value)
            {
                //This branch may be reached erroneously if columns are added to the LimeSurvey product
                //but are not updated in the Writer->headerTranslationKeys array.  We should trap for this
                //condition and do a safe_die.
                //FIXME fix the above condition

                //Survey question field, $column value is a field name from the getFieldMap function.
                switch ($options->headingFormat)
                {
                    case 'abrev':
                        $value = $this->getAbbreviatedHeading($survey, $column);
                        break;

                    case 'headcodes':
                        $value = $this->getCodeHeading($survey, $options, $column);
                        break;

                    case 'full':
                        $value = $this->getFullHeading($survey, $options, $column);
                        break;

                    default:
                        //Ut oh.
                        safe_die('An invalid header format option was specified: '.$options->headingFormat);
                }
            }
            if ($options->headerSpacesToUnderscores)
            {
                $value = str_replace(' ', '_', $value);
            }

            //$this->output.=$this->csvEscape($value).$this->separator;
            $headers[] = $value;
        }

        //Output the results.
        foreach($survey->responses as $response)
        {
            $elementArray = array();

            //If we shouldn't be outputting this response then we should skip the rest
            //of the loop and continue onto the next value.
            if (!$this->shouldOutputResponse($response, $options))
            {
                continue;
            }

            foreach ($options->selectedColumns as $column)
            {
                $value = $response[$column];

                switch ($options->answerFormat) {
                    case 'short':
                        $elementArray[] = $this->transformResponseValue($value,
                        $survey->fieldMap[$column]['type'], $options);
                        break;

                    case 'long':
                        $elementArray[] = $this->transformResponseValue($survey->getFullAnswer(
                        $column, $value, $this->translator, $this->languageCode),
                        $survey->fieldMap[$column]['type'], $options);
                        break;

                    default:
                        //Ut oh
                        safe_die('An invalid answer format was encountered: '.$options->answerFormat);
                }
            }

            $this->outputRecord($headers, $elementArray, $options);
        }
    }

    protected function strip_tags_full($string)
    {
        $string=str_replace('-oth-','',$string);
        return FlattenText($string,false,true,'UTF-8',false);
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
    * @param FormattingOptions $options
    */
    abstract protected function outputRecord($headers, $values, FormattingOptions $options);
}

class CsvWriter extends Writer
{
    private $output;
    private $separator;
    private $hasOutputHeader;

    function __construct()
    {
        $this->output = '';
        $this->separator = ',';
        $this->hasOutputHeader = false;
    }

    protected function outputRecord($headers, $values, FormattingOptions $options)
    {
        if(!$this->hasOutputHeader)
        {
            $index = 0;
            foreach ($headers as $header)
            {
                $headers[$index] = $this->csvEscape($header);
                $index++;
            }

            //Output the header...once and only once.
            $this->output .= implode($this->separator, $headers);
            $this->hasOutputHeader = true;
        }
        //Output the values.
        $index = 0;
        foreach ($values as $value)
        {
            $values[$index] = $this->csvEscape($value);
            $index++;
        }
        $this->output .= PHP_EOL.implode($this->separator, $values);
    }

    public function close()
    {
        return $this->output;
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

    public function __construct()
    {
        $this->separator = "\t";
        $this->output = '';
        $this->isBeginning = true;
    }

    public function init(Survey $survey, $languageCode, FormattingOptions $options)
    {
        //header("Content-Disposition: attachment; filename=results-survey".$survey->id.".doc");
        //header("Content-type: application/vnd.ms-word");
        $this->output .= '<style>
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
    }

    /**
    * @param array $headers
    * @param array $values
    * @param FormattingOptions $options
    */
    protected function outputRecord($headers, $values, FormattingOptions $options)
    {
        if ($options->answerFormat == 'short')
        {
            //No headers at all, only output values.
            $this->output .= implode($this->separator, $values).PHP_EOL;
        }
        elseif ($options->answerFormat == 'long')
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
            safe_die('An invalid answer format was selected.  Only \'short\' and \'long\' are valid.');
        }
    }

    public function close()
    {
        $this->output = rtrim($this->output, PHP_EOL);
        return $this->output;
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
    private $fileName;
    private $outputToFile;

    /**
    * The presence of a filename will cause the writer to output to
    * a file rather than send.
    *
    * @param string $filename
    * @return ExcelWriter
    */
    public function __construct($filename = null)
    {
        $CI=& get_instance();
        $CI->load->library('admin/pear/Spreadsheet/Excel/Xlswriter');
        if (!empty($filename))
        {
            $this->workbook = $CI->xlswriter;
            $this->outputToFile = true;
            $this->fileName = $filename;
        }
        else
        {
            $this->workbook = $CI->xlswriter;
            $this->outputToFile = false;
        }

        $this->separator = '~|';
        $this->hasOutputHeader = false;
        $this->rowCounter = 1;
    }

    protected function init(Survey $survey, $languageCode, FormattingOptions $options)
    {
        $this->workbook->send('results-survey'.$survey->id.'.xls');
        $worksheetName = $survey->languageSettings[0]['surveyls_title'];
        $worksheetName=substr(str_replace(array('*', ':', '/', '\\', '?', '[', ']'),array(' '),$worksheetName),0,31); // Remove invalid characters

        $this->workbook->setVersion(8);
        if (!empty($tempdir)) {
            $this->$workbook->setTempDir($tempdir);
        }
        $sheet =$this->workbook->addWorksheet($worksheetName); // do not translate/change this - the library does not support any special chars in sheet name
        $sheet->setInputEncoding('utf-8');
        $this->currentSheet = $sheet;
    }

    protected function outputRecord($headers, $values, FormattingOptions $options)
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
    private $fileName;
    private $surveyName;

    public function __construct($filename = null)
    {
        if (!empty($filename))
        {
            $this->pdfDestination = 'F';
            $this->fileName = $filename;
        }
        else
        {
            $this->pdfDestination = 'D';
        }

        //The $pdforientation, $pdfDefaultFont, and $pdfFontSize values
        //come from the Lime Survey config files.

        global $pdforientation, $pdfdefaultfont, $pdffontsize;

        $this->pdf = new PDF($pdforientation,'mm','A4');
        $this->pdf->SetFont($pdfdefaultfont, '', $pdffontsize);
        $this->pdf->AddPage();
        $this->pdf->intopdf("PDF Export ".date("Y.m.d-H:i", time()));


        $this->separator="\t";

        $this->rowCounter = 0;
    }

    protected function init(Survey $survey, $languageCode, FormattingOptions $options)
    {
        $this->surveyName = $survey->languageSettings[0]['surveyls_title'];
        $this->pdf->titleintopdf($this->surveyName, $survey->languageSettings[0]['surveyls_description']);
    }

    public function outputRecord($headers, $values, FormattingOptions $options)
    {
        $this->rowCounter++;
        if ($options->answerFormat == 'short')
        {
            $pdfstring = '';
            $this->pdf->titleintopdf($this->translate('New Record', $this->languageCode));
            foreach ($values as $value)
            {
                $pdfstring .= $value.' | ';
            }
            $this->pdf->intopdf($pdfstring);
        }
        elseif ($options->answerFormat == 'long')
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
                $this->pdf->intopdf($this->strip_tags_full($values[$columnCounter]));
                $columnCounter++;
            }
        }
        else
        {
            safe_die('An invalid answer format was encountered: '.$options->answerFormat);
        }

    }

    public function close()
    {
        if ($this->pdfDestination == 'F')
        {
            //Save to file on filesystem.
            $filename = $this->fileName;
        }
        else
        {
            //Presuming this else branch is a send to client via HTTP.
            $filename = $this->translate($this->surveyName, $this->languageCode).'pdf';
        }
        $this->pdf->Output($filename, $this->pdfDestination);
    }
}