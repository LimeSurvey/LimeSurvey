<?php
/**
 * Description of LimeExpressionManager
 * This is a wrapper class around ExpressionManager that implements a Singleton and eases
 * passing of LimeSurvey variable values into ExpressionManager
 *
 * @author Thomas M. White (TMSWhite)
 */
include_once('ExpressionManager.php');

define('LEM_DEBUG_TIMING',1);
define('LEM_DEBUG_VALIDATION_SUMMARY',2);   // also includes  SQL error messages
define('LEM_DEBUG_VALIDATION_DETAIL',4);
define('LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB',8);
define('LEM_DEBUG_TRANSLATION_DETAIL',16);
define('LEM_PRETTY_PRINT_ALL_SYNTAX',32);

class LimeExpressionManager {
    private static $instance;
    private $em;    // Expression Manager
    private $groupRelevanceInfo;
    private $sid;
    private $groupNum;
    private $debugLevel=0;  // sum of LEM_DEBUG constants - use bitwise AND comparisons to identify which parts to use
    private $knownVars;
    private $pageRelevanceInfo;
    private $pageTailorInfo;
    private $allOnOnePage=false;    // internally set to true for survey.php so get group-specific logging but keep javascript variable namings consistent on the page.
    private $surveyMode='group';  // survey mode
    private $surveyOptions=array(); // a set of global survey options passed from LimeSurvey
    private $qid2code;  // array of mappings of Question # to list of SGQA codes used within it
    private $jsVar2qid; // reverse mapping of JavaScript Variable name to Question
    private $alias2varName; // JavaScript array of mappings of aliases to the JavaScript variable names
    private $varNameAttr;   // JavaScript array of mappings of canonical JavaScript variable name to key attributes.
    private $pageTailoringLog;  // Debug log of tailorings done on this page
    private $surveyLogicFile;   // Shows current configuration and data from most recent $fieldmap

    private $qans;  // array of answer lists indexed by qid
    private $groupId2groupSeq;  // map of gid to 0-based sequence number of groups
    private $questionId2questionSeq;    // map question # to an incremental count of question order across the whole survey
    private $questionId2groupSeq;   // map question  # to the group it is within, using an incremental count of group order
    private $groupSeqInfo;  // array of info about each Group, indexed by GroupSeq

    private $gid2relevanceStatus;   // tracks which groups have at least one relevant, non-hidden question
    private $qid2validationEqn;     // maps question # to the validation equation for that question.

    private $questionSeq2relevance; // keeps relevance in proper sequence so can minimize relevance processing to see what should be see on page and in indexes
    private $currentGroupSeq;   // current Group sequence (0-based index)
    private $currentQuestionSeq;    // for Question-by-Question mode, the 0-based index
    private $currentQID;        // used in Question-by-Question modecu
    private $currentQset=NULL;   // set of the current set of questions to be displayed, indexed by QID - at least one must be relevant
    private $lastMoveResult=NULL;   // last result of NavigateForwards, NavigateBackwards, or JumpTo
    private $indexQseq;         // array of information needed to generate navigation index in question-by-question mode
    private $indexGseq;         // array of information needed to generate navigation index in group-by-group mode
    private $gseq2info;         // array of group sequence number to static info

    private $maxGroupSeq;  // the maximum groupSeq reached -  this is needed for Index
    private $slang='en';
    private $q2subqInfo;    // mapping of questions to information about their subquestions.
    private $qattr; // array of attributes for each question
    private $syntaxErrors=array();
    private $subQrelInfo=array();   // list of needed sub-question relevance (e.g. array_filter)
    private $gRelInfo=array();  // array of Group-level relevance status

    private $runtimeTimings=array();
    private $initialized=false;
    private $processedRelevance=false;
    private $debugTimingMsg='';
    private $ParseResultCache;  // temporary variable to reduce need to parse same equation multiple times.  Used for relevance and validation
    private $multiflexiAnswers; // array of 2nd scale answer lists for types ':' and ';' -- needed for convenient print of logic file

    // A private constructor; prevents direct creation of object
    private function __construct()
    {
        self::$instance =& $this;
        $this->em = new ExpressionManager();
    }

    /**
     * Ensures there is only one instances of LEM.  Note, if switch between surveys, have to clear this cache
     * @return <type>
     */
    public static function &singleton()
    {
        $now = microtime(true);
        if (isset($_SESSION['LEMdirtyFlag'])) {
            $c = __CLASS__;
            self::$instance = new $c;
            unset($_SESSION['LEMdirtyFlag']);
        }
        else if (!isset(self::$instance)) {
            if (isset($_SESSION['LEMsingleton'])) {
                self::$instance = unserialize($_SESSION['LEMsingleton']);
            }
            else {
                $c = __CLASS__;
                self::$instance = new $c;
            }
        }
        else {
            // does exist, and OK to cache
            return self::$instance;
        }
        // only record duration if have to create new (or unserialize) an instance
        self::$instance->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
        return self::$instance;
    }

    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Tells Expression Manager that something has changed enough that needs to eliminate caching
     */
    public static function SetDirtyFlag()
    {
        $_SESSION['LEMdirtyFlag'] = true;
        $_SESSION['LEMforceRefresh'] = true;
    }

    /**
     * Set the SurveyId - really checks whether the survey you're about to work with is new, and if so, clears the LEM cache
     * @param <type> $sid
     */
    public static function SetSurveyId($sid=NULL)
    {
        if (!is_null($sid)) {
            if (isset($_SESSION['LEMsid']) && $sid != $_SESSION['LEMsid']) {
                // then trying to use a new survey - so clear the LEM cache
                $_SESSION['LEMdirtyFlag'] = true;
            }
            $_SESSION['LEMsid'] = $sid;
        }
    }

    /**
     * A legacy upgrader.  Relevance was initially a Question Attribute during development.  However, as of 2.0 alpha, it was already in the questions table.
     * @return <type>
     */
    public static function UpgradeRelevanceAttributeToQuestion()
    {
        $query = "SELECT qid, value from ".db_table_name('question_attributes')." where attribute='relevance'";
        $qresult = db_execute_assoc($query);
        $queries = array();
        foreach ($qresult->GetRows() as $row)
        {
            $query = "UPDATE ".db_table_name('questions')." SET relevance='".$row['value']."' WHERE qid=".$row['qid'];
            db_execute_assoc($query);
            $queries[] = $query;
        }
        return $queries;
    }

    /**
     * Do bulk-update/save of Conditions to Relevance
     * @param <type> $surveyId - if NULL, processes the entire database, otherwise just the specified survey
     * @param <type> $qid - if specified, just updates that one question
     * @return <type>
     */
    public static function UpgradeConditionsToRelevance($surveyId=NULL, $qid=NULL)
    {
        $releqns = self::ConvertConditionsToRelevance($surveyId,$qid);
        $num = count($releqns);
        if ($num == 0) {
            return NULL;
        }

        $queries = array();
        foreach ($releqns as $key=>$value) {
            $query = "UPDATE ".db_table_name('questions')." SET relevance='".addslashes($value)."' WHERE qid=".$key;
            db_execute_assoc($query);
            $queries[] = $query;
        }
        return $queries;
    }

    /**
     * This reverses UpgradeConditionsToRelevance().  It removes Relevance for questions that have Conditions
     * @param <type> $surveyId
     * @param <type> $qid
     */
    public static function RevertUpgradeConditionsToRelevance($surveyId=NULL, $qid=NULL)
    {
        $releqns = self::ConvertConditionsToRelevance($surveyId,$qid);
        $num = count($releqns);
        if ($num == 0) {
            return NULL;
        }

        foreach ($releqns as $key=>$value) {
            $query = "UPDATE ".db_table_name('questions')." SET relevance=1 WHERE qid=".$key;
            db_execute_assoc($query);
        }
        return count($releqns);
    }

    /**
     * If $qid is set, returns the relevance equation generated from conditions (or NULL if there are no conditions for that $qid)
     * If $qid is NULL, returns an array of relevance equations generated from Conditions, keyed on the question ID
     * @param <type> $surveyId
     * @param <type> $qid - if passed, only generates relevance equation for that question - otherwise genereates for all questions with conditions
     * @return <type>
     */
    public static function ConvertConditionsToRelevance($surveyId=NULL, $qid=NULL)
    {
        LimeExpressionManager::SetDirtyFlag();
        $query = LimeExpressionManager::getAllRecordsForSurvey($surveyId,$qid);

        $_qid = -1;
        $relevanceEqns = array();
        $scenarios = array();
        $relAndList = array();
        $relOrList = array();
        foreach($query->GetRows() as $row)
        {
            if ($row['qid'] != $_qid)
            {
                // output the values for prior question is there was one
                if ($_qid != -1)
                {
                    if (count($relOrList) > 0)
                    {
                        $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
                    }
                    if (count($relAndList) > 0)
                    {
                        $scenarios[] = '(' . implode(' and ', $relAndList) . ')';
                    }
                    $relevanceEqn = implode(' and ', $scenarios);
                    $relevanceEqns[$_qid] = $relevanceEqn;
                }

                // clear for next question
                $_qid = $row['qid'];
                $_scenario = $row['scenario'];
                $_cqid = $row['cqid'];
                $relAndList = array();
                $relOrList = array();
                $scenarios = array();
                $releqn = '';
            }
            if ($row['scenario'] != $_scenario)
            {
                if (count($relOrList) > 0)
                {
                    $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
                }
                $scenarios[] = '(' . implode(' and ', $relAndList) . ')';
                $relAndList = array();
                $relOrList = array();
                $_scenario = $row['scenario'];
                $_cqid = $row['cqid'];
            }
            if ($row['cqid'] != $_cqid)
            {
                $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
                $relOrList = array();
                $_cqid = $row['cqid'];
            }

            // fix fieldnames
            if ($row['type'] == '' && preg_match('/^{.+}$/',$row['cfieldname'])) {
                $fieldname = substr($row['cfieldname'],1,-1);    // {TOKEN:xxxx}
                $value = $row['value'];
            }
            else if ($row['type'] == 'M' || $row['type'] == 'P') {
                if (substr($row['cfieldname'],0,1) == '+') {
                    // if prefixed with +, then a fully resolved name
                    $fieldname = substr($row['cfieldname'],1);
                    $value = $row['value'];
                }
                else {
                    // else create name by concatenating two parts together
                    $fieldname = $row['cfieldname'] . $row['value'];
                    $value = 'Y';
                }
            }
            else {
                $fieldname = $row['cfieldname'];
                $value = $row['value'];
            }

            // fix values
            if (preg_match('/^@\d+X\d+X\d+.*@$/',$value)) {
                $value = substr($value,1,-1);
            }
            else if (preg_match('/^{.+}$/',$value)) {
                $value = substr($value,1,-1);
            }
            else {
                $value = '"' . $value . '"';
            }

            // add equation
            if  ($row['method'] == 'RX')
            {
                $relOrList[] = "regexMatch(" . $value . "," . $fieldname . ")";
            }
            else
            {
                $relOrList[] = $fieldname . " " . $row['method'] . " " . $value;
            }

            if ($row['cqid'] == 0 || substr($row['cfieldname'],0,1) == '+') {
                $_cqid = -1;    // forces this statement to be ANDed instead of being part of a cqid OR group
            }
        }
        // output last one
        if ($_qid != -1)
        {
            if (count($relOrList) > 0)
            {
                $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
            }
            if (count($relAndList) > 0)
            {
                $scenarios[] = '(' . implode(' and ', $relAndList) . ')';
            }
            $relevanceEqn = implode(' and ', $scenarios);
            $relevanceEqns[$_qid] = $relevanceEqn;
        }
        if (is_null($qid)) {
            return $relevanceEqns;
        }
        else {
            if (isset($relevanceEqns[$qid]))
            {
                $result = array();
                $result[$qid] = $relevanceEqns[$qid];
                return $result;
            }
            else
            {
                return NULL;
            }
        }
    }

    /**
     * Return list of relevance equations generated from conditions
     * @param <type> $surveyId
     * @param <type> $qid
     * @return <type>
     */
    public static function UnitTestConvertConditionsToRelevance($surveyId=NULL, $qid=NULL)
    {
        $LEM =& LimeExpressionManager::singleton();
        return $LEM->ConvertConditionsToRelevance($surveyId, $qid);
    }

    /**
     * Process all question attributes that apply to EM
     * (1) Sub-question-level  relevance:  e.g. array_filter, array_filter_exclude
     * (2) Validations: e.g. min/max number of answers; min/max/eq sum of answers
     */
    public function _CreateSubQLevelRelevanceAndValidationEqns($onlyThisQseq=NULL)
    {
//        $now = microtime(true);
        $this->subQrelInfo=array();  // reset it each time this is called
        $subQrels = array();    // array of sub-question-level relevance equations
        $validationEqn = array();

//        log_message('debug',print_r($this->q2subqInfo,true));
//        log_message('debug',print_r($this->qattr,true));

        // Associate these with $qid so that can be nested under appropriate question-level relevance?
        foreach ($this->q2subqInfo as $qinfo)
        {
            if (!is_null($onlyThisQseq) && $onlyThisQseq != $qinfo['qseq']) {
                continue;
            }
            else if (!$this->allOnOnePage && $this->groupNum != $qinfo['gid']) {
                continue; // only need subq relevance for current page.
            }
            $questionNum = $qinfo['qid'];
            $type = $qinfo['type'];
            $hasSubqs = (isset($qinfo['subqs']) && count($qinfo['subqs'] > 0));
            $qattr = isset($this->qattr[$questionNum]) ? $this->qattr[$questionNum] : array();

            // array_filter
            // If want to filter question Q2 on Q1, where each have subquestions SQ1-SQ3, this is equivalent to relevance equations of:
            // relevance for Q2_SQ1 is Q1_SQ1!=''
            if (isset($qattr['array_filter']) && trim($qattr['array_filter']) != '')
            {
                $array_filter = $qattr['array_filter'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case '1':   //Array (Flexible Labels) dual scale
                            case ':': //ARRAY (Multi Flexi) 1 to 10
                            case ';': //ARRAY (Multi Flexi) Text
                            case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                            case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                            case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                            case 'F': //ARRAY (Flexible) - Row Format
                            case 'L': //LIST drop-down/radio-button list
                            case 'M': //Multiple choice checkbox
                            case 'P': //Multiple choice with comments checkbox + text
                                $sq_name = $array_filter . $sq['sqsuffix'];
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $subQrels[] = array(
                                'qtype' => $type,
                                'type' => 'array_filter',
                                'rowdivid' => $sq['rowdivid'],
                                'eqn' => '(' . $sq_name . ' != "")',
                                'qid' => $questionNum,
                                'sgqa' => $qinfo['sgqa'],
                            );
                        }
                    }
                }
            }

            // array_filter_exclude
            // If want to filter question Q2 on Q1, where each have subquestions SQ1-SQ3, this is equivalent to relevance equations of:
            // relevance for Q2_SQ1 is Q1_SQ1==''
            if (isset($qattr['array_filter_exclude']) && trim($qattr['array_filter_exclude']) != '')
            {
                $array_filter_exclude = $qattr['array_filter_exclude'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case '1':   //Array (Flexible Labels) dual scale
                            case ':': //ARRAY (Multi Flexi) 1 to 10
                            case ';': //ARRAY (Multi Flexi) Text
                            case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                            case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                            case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                            case 'F': //ARRAY (Flexible) - Row Format
                            case 'L': //LIST drop-down/radio-button list
                            case 'M': //Multiple choice checkbox
                            case 'P': //Multiple choice with comments checkbox + text
                                $sq_name = $array_filter_exclude . $sq['sqsuffix'];
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $subQrels[] = array(
                                'qtype' => $type,
                                'type' => 'array_filter_exclude',
                                'rowdivid' => $sq['rowdivid'],
                                'eqn' => '(' . $sq_name . ' == "")',
                                'qid' => $questionNum,
                                'sgqa' => $qinfo['sgqa'],
                            );
                        }
                    }
                }
            }

            // code_filter:  WZ
            // This can be skipped, since question types 'W' (list-dropdown-flexible) and 'Z'(list-radio-flexible) are no longer supported

            // equals_num_value
            // Validation:= sum(sq1,...,sqN) == value (which could be an expression).
            if (isset($qattr['equals_num_value']) && trim($qattr['equals_num_value']) != '')
            {
                $equals_num_value = $qattr['equals_num_value'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                                $sq_name = $sq['varName'] . '.NAOK';
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'equals_num_value',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') == (' . $equals_num_value . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('Total of all entries must equal') . ' {' . $equals_num_value . '}',
                            );
                    }
                }
            }

            // exclude_all_others
            if (isset($qattr['exclude_all_others']) && trim($qattr['exclude_all_others']) != '')
            {
                $exclusive_option = $qattr['exclude_all_others'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        if ($sq['csuffix'] == $exclusive_option)
                        {
                            continue;   // so don't make the excluded option irrelevant
                        }
                        switch ($type)
                        {
                            case 'M': //Multiple choice checkbox
                                $sq_name = $qinfo['sgqa'] . $exclusive_option;
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $subQrels[] = array(
                                'qtype' => $type,
                                'type' => 'exclude_all_others',
                                'rowdivid' => $sq['rowdivid'],
                                'eqn' => '(' . $sq_name . ' == "")',
                                'qid' => $questionNum,
                                'sgqa' => $qinfo['sgqa'],
                            );
                        }
                    }
                }
            }

            // exclude_all_others_auto
            //  TODO

            // min_answers
            // Validation:= count(sq1,...,sqN) >= value (which could be an expression).
            if (isset($qattr['min_answers']) && trim($qattr['min_answers']) != '')
            {
                $min_answers = $qattr['min_answers'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'M': //Multiple choice checkbox
                                $sq_name = $sq['varName'] . '.NAOK';
                                break;
                            case 'P': //Multiple choice with comments checkbox + text
                                if (!preg_match('/comment$/',$sq['varName'])) {
                                    $sq_name = $sq['varName'] . '.NAOK';
                                }
                                break;
                            case 'R': //RANKING STYLE
                                // TODO - does not have sub-questions, so how should this be done?
                                // Current JavaScript works fine, but can't use expression value
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'min_answers',
                            'eqn' => '(count(' . implode(', ', $sq_names) . ') >= (' . $min_answers . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('The minimum number of answers for this question is') . ' {' . $min_answers . '}',
                        );
                    }
                }
            }

            // min_num_value
            // Validation:= sum(sq1,...,sqN) >= value (which could be an expression).
            if (isset($qattr['min_num_value']) && trim($qattr['min_num_value']) != '')
            {
                $min_num_value = $qattr['min_num_value'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                                $sq_name = $sq['varName'] . '.NAOK';
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'min_num_value',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') >= (' . $min_num_value . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('Total of all entries must be at least') . ' {' . $min_num_value . '}',
                        );
                    }
                }
            }

            // min_num_value_n
            // Validation:= N >= value (which could be an expression).
            if (isset($qattr['min_num_value_n']) && trim($qattr['min_num_value_n']) != '')
            {
                $min_num_value_n = $qattr['min_num_value_n'];
                if ($hasSubqs) {
                    $sq = $qinfo['subqs'][0];
                    switch ($type)
                    {
                        case 'N': //NUMERICAL QUESTION TYPE
                            $sq_name = $sq['varName'];
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'min_num_value_n',
                            'eqn' => '(' . $sq_name . ' >= (' . $min_num_value_n . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('The entry must be at least') . ' {' . $min_num_value_n . '}',
                        );
                    }
                }
            }

            // min_num_value_sgqa
            // Validation:= sum(sq1,...,sqN) >= value (which could be an expression).
            if (isset($qattr['min_num_value_sgqa']) && trim($qattr['min_num_value_sgqa']) != '')
            {
                $min_num_value_sgqa = $qattr['min_num_value_sgqa'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                                $sq_name = $sq['varName'] . '.NAOK';
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'min_num_value_sgqa',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') >= (' . $min_num_value_sgqa . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('Total of all entries must be at least') . ' {' . $min_num_value_sgqa . '}',
                        );
                    }
                }
            }

            // max_answers
            // Validation:= count(sq1,...,sqN) <= value (which could be an expression).
            if (isset($qattr['max_answers']) && trim($qattr['max_answers']) != '')
            {
                $max_answers = $qattr['max_answers'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'M': //Multiple choice checkbox
                                $sq_name = $sq['varName'] . '.NAOK';
                                break;
                            case 'P': //Multiple choice with comments checkbox + text
                                if (!preg_match('/comment$/',$sq['varName'])) {
                                    $sq_name = $sq['varName'] . '.NAOK';
                                }
                                break;
                            case 'R': //RANKING STYLE
                                // TODO - does not have sub-questions, so how should this be done?
                                // Current JavaScript works fine, but can't use expression value
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'max_answers',
                            'eqn' => '(count(' . implode(', ', $sq_names) . ') <= (' . $max_answers . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('The maximum number of answers for this question is') . ' {' . $max_answers . '}',
                        );
                    }
                }
            }

            // max_num_value
            // Validation:= sum(sq1,...,sqN) <= value (which could be an expression).
            if (isset($qattr['max_num_value']) && trim($qattr['max_num_value']) != '')
            {
                $max_num_value = $qattr['max_num_value'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                                $sq_name = $sq['varName'] . '.NAOK';
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'max_num_value',
                            'eqn' =>  '(sum(' . implode(', ', $sq_names) . ') <= (' . $max_num_value . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('Total of all entries must not exceed') . ' {' . $max_num_value . '}',
                        );
                    }
                }
            }

            // max_num_value_n
            // Validation:= N <= value (which could be an expression).
            if (isset($qattr['max_num_value_n']) && trim($qattr['max_num_value_n']) != '')
            {
                $max_num_value_n = $qattr['max_num_value_n'];
                if ($hasSubqs) {
                    $sq = $qinfo['subqs'][0];
                    switch ($type)
                    {
                        case 'N': //NUMERICAL QUESTION TYPE
                            $sq_name = $sq['varName'];
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'max_num_value_n',
                            'eqn' => '(' . $sq_name . ' <= (' . $max_num_value_n . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('The entry must not exceed') . ' {' . $max_num_value_n . '}',
                        );
                    }
                }
            }

            // max_num_value_sgqa
            // Validation:= sum(sq1,...,sqN) <= value (which could be an expression).
            if (isset($qattr['max_num_value_sgqa']) && trim($qattr['max_num_value_sgqa']) != '')
            {
                $max_num_value_sgqa = $qattr['max_num_value_sgqa'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                                $sq_name = $sq['varName'] . '.NAOK';
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'max_num_value_sgqa',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') <= (' . $max_num_value_sgqa . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('Total of all entries must not exceed') . ' {' . $max_num_value_sgqa . '}',
                        );
                    }
                }
            }

            // num_value_equals_sgqa
            // Validation:= sum(sq1,...,sqN) == value (which could be an expression).
            if (isset($qattr['num_value_equals_sgqa']) && trim($qattr['num_value_equals_sgqa']) != '')
            {
                $num_value_equals_sgqa = $qattr['num_value_equals_sgqa'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                                $sq_name = $sq['varName'] . '.NAOK';
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'num_value_equals_sgqa',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') == (' . $num_value_equals_sgqa . '))',
                            'qid' => $questionNum,
                            'tip' => $this->gT('Total of all entries must equal') . ' {' . $num_value_equals_sgqa . '}',
                        );
                    }
                }
            }

            // show_totals
            // TODO - create equations for these?

            // assessment_value
            // TODO?  How does it work?
            // The assesment value (referenced how?) = count(sq1,...,sqN) * assessment_value
            // Since there are easy work-arounds to this, skipping it for now

            // preg - a PHP Regular Expression to validate text input fields
            if (isset($qinfo['preg']) && !is_null($qinfo['preg']))
            {
                $preg = $qinfo['preg'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case 'N': //NUMERICAL QUESTION TYPE
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                            case 'Q': //MULTIPLE SHORT TEXT
                            case ';': //ARRAY (Multi Flexi) Text
                            case 'S': //SHORT FREE TEXT
                            case 'T': //LONG FREE TEXT
                            case 'U': //HUGE FREE TEXT
                                // TODO - should empty always be an option? or require that empty be an explicit option in the regex?
                                $sq_name = '(if((strlen('.$sq['varName'].'.NAOK)==0),0,!regexMatch("' . $preg . '", ' . $sq['varName'] . '.NAOK)))';
                                break;
                            default:
                                break;
                        }
                        // TODO - refactor this so validate each resposne separately:
                        // (1) store a flag in $_SESSION and JavaScript indicating valiations status
                        // (2) Use that flag to color-code individual responses that fail validation
                        // (3) Let overall validation equation assess those flags, not re-do full regex for all
                        if (!is_null($sq_name)) {
                            $sq_names[] = $sq_name;
                        }
                    }
                    if (count($sq_names) > 0) {
                        if (!isset($validationEqn[$questionNum]))
                        {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $type,
                            'type' => 'preg',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') == 0)',
                            'qid' => $questionNum,
                            'tip' => $this->gT('All entries must conform to this regular expression:') . " " . str_replace(array('{','}'),array('{ ',' }'), $preg),
                        );
                    }
                }
            }
        }
//        log_message('debug','**SUBQUESTION RELEVANCE**' . print_r($subQrels,true));
//        log_message('debug','**VALIDATION EQUATIONS**' . print_r($validationEqn,true));

        // Consolidate logic across array filters
        $rowdivids = array();
        $order=0;
        foreach ($subQrels as $sq)
        {
            if (isset($rowdivids[$sq['rowdivid']]))
            {
                $backup = $rowdivids[$sq['rowdivid']];
                $rowdivids[$sq['rowdivid']] = array(
                    'order'=>$backup['order'],
                    'qid'=>$sq['qid'],
                    'rowdivid'=>$sq['rowdivid'],
                    'type'=>$backup['type'] . ';' .$sq['type'],
                    'qtype'=>$sq['qtype'],
                    'sgqa'=>$sq['sgqa'],
                    'eqns' => array_merge($backup['eqns'],array($sq['eqn'])),
                );
            }
            else
            {
                $rowdivids[$sq['rowdivid']] = array(
                    'order'=>$order++,
                    'qid'=>$sq['qid'],
                    'rowdivid'=>$sq['rowdivid'],
                    'type'=>$sq['type'],
                    'qtype'=>$sq['qtype'],
                    'sgqa'=>$sq['sgqa'],
                    'eqns'=>array($sq['eqn']),
                    );
            }
        }

        foreach ($rowdivids as $sq)
        {
            $sq['eqn'] = '(' . implode(' and ', array_unique($sq['eqns'])) . ')';   // without array_unique, get duplicate of filters for question types 1, :, and ;
            $result = $this->_ProcessSubQRelevance($sq['eqn'], $sq['qid'], $sq['rowdivid'], $sq['type'], $sq['qtype'],  $sq['sgqa']);
        }

        // TODO - refactor this so that done at subq level too?
        foreach ($validationEqn as $key=>$val)
        {
            $parts = array();
            $tips = array();
            foreach ($val as $v) {
                $parts[] = $v['eqn'];
                $tips[] = $v['tip'];
            }
            $this->qid2validationEqn[$key] = array(
                'eqn' => '(' . implode(' and ', $parts) . ')',
                'tips' => $tips,
            );
        }

//        $this->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
    }

    /**
     * Create the arrays needed by ExpressionManager to process LimeSurvey strings.
     * The long part of this function should only be called once per page display (e.g. only if $fieldMap changes)
     * TODO:  It should be possible to call this once per survey, and just update the values that change across page (e.g. jsVarName, relevanceStatus)
     *
     * @param <type> $surveyid
     * @param <type> $forceRefresh
     * @param <type> $anonymized
     * @param <type> $allOnOnePage - if true (like for survey_format), uses certain optimizations
     * @return boolean - true if $fieldmap had been re-created, so ExpressionManager variables need to be re-set
     */

    public function setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh=false,$anonymized=false,$allOnOnePage=false)
    {
        if (isset($_SESSION['LEMforceRefresh'])) {
            unset($_SESSION['LEMforceRefresh']);
            $forceRefresh=true;
        }
        else if (!$forceRefresh && isset($this->knownVars)) {
            return false;   // means that those variables have been cached and no changes needed
        }
        $now = microtime(true);
//        $LEM->slang = (isset($_SESSION['s_lang']) ? $_SESSION['s_lang'] : 'en');
//        log_message('debug','**Language=' . $LEM->slang);

        $fieldmap=createFieldMap($surveyid,$style='full',$forceRefresh);
        $this->sid= $surveyid;

        $this->runtimeTimings[] = array(__METHOD__ . '.createFieldMap',(microtime(true) - $now));
//      LimeExpressionManager::ShowStackTrace();

        $now = microtime(true);

        if (!isset($fieldmap)) {
            return false; // implies an error occurred
        }

        $this->knownVars = array();   // mapping of VarName to Value
        $this->debugLog = array();    // array of mappings among values to confirm their accuracy
        $this->qid2code = array();    // List of codes for each question - needed to know which to NULL if a question is irrelevant
        $this->jsVar2qid = array();
        $this->alias2varName = array();
        $this->varNameAttr = array();
        $this->questionId2questionSeq = array();
        $this->questionId2groupSeq = array();
        $this->questionSeq2relevance = array();
        $this->groupId2groupSeq = array();
        $this->qid2validationEqn = array();
        $this->groupSeqInfo = array();
        $this->gid2relevanceStatus = array();

        // Since building array of allowable answers, need to know preset values for certain question types
        $presets = array();
        $presets['G'] = array(  //GENDER drop-down list
            'M' => $this->gT("Male"),
            'F' => $this->gT("Female"),
        );
        $presets['Y'] = array(  //YES/NO radio-buttons
            'Y' => $this->gT("Yes"),
            'N' => $this->gT("No"),
        );
        $presets['C'] = array(   //ARRAY (YES/UNCERTAIN/NO) radio-buttons
            'Y' => $this->gT("Yes"),
            'N' => $this->gT("No"),
            'U' => $this->gT("Uncertain"),
        );
        $presets['E'] = array(  //ARRAY (Increase/Same/Decrease) radio-buttons
            'I' => $this->gT("Increase"),
            'S' => $this->gT("Same"),
            'D' => $this->gT("Decrease"),
        );

        $this->gseq2info = $this->getGroupInfoForEM($surveyid,$this->slang);
        for ($i=0;$i<count($this->gseq2info);++$i)
        {
            $gseq = $this->gseq2info[$i];
            $this->groupId2groupSeq[$gseq['gid']] = $i;
        }

        $qattr = $this->getEMRelatedRecordsForSurvey($surveyid);   // what happens if $surveyid is null?
        $this->qattr = $qattr;

        $this->runtimeTimings[] = array(__METHOD__ . ' - question_attributes_model->getEMRelatedRecordsForSurvey',(microtime(true) - $now));
        $now = microtime(true);

        $this->qans = $this->getAllAnswersForEM($surveyid,NULL);  // ,$this->slang);  // TODO - will this work for multi-lingual?

        $this->runtimeTimings[] = array(__METHOD__ . ' - answers_model->getAllAnswersForEM',(microtime(true) - $now));
        $now = microtime(true);

        $q2subqInfo = array();

        $this->multiflexiAnswers=array();

        foreach($fieldmap as $fielddata)
        {
            $sgqa = $fielddata['fieldname'];
            $type = $fielddata['type'];
            if (!preg_match('#^\d+X\d+X\d+#',$sgqa))
            {
                continue;   // not an SGQA value
            }
            $mandatory = $fielddata['mandatory'];
            $fieldNameParts = explode('X',$sgqa);
            $groupNum = $fieldNameParts[1];

            $questionId = $fieldNameParts[2];
            $questionNum = $fielddata['qid'];
            $relevance = (isset($fielddata['relevance'])) ? $fielddata['relevance'] : 1;
            $grelevance = (isset($fielddata['grelevance'])) ? $fielddata['grelevance'] : 1;
            $hidden = (isset($qattr[$questionNum]['hidden'])) ? $qattr[$questionNum]['hidden'] : 'N';
            $scale_id = (isset($fielddata['scale_id'])) ? $fielddata['scale_id'] : '0';
            $preg = (isset($fielddata['preg'])) ? $fielddata['preg'] : NULL; // a perl regular exrpession validation function
            $defaultValue = (isset($fielddata['defaultvalue']) ? $fielddata['defaultvalue'] : NULL);
            if (trim($preg) == '') {
                $preg = NULL;
            }
            $help = (isset($fielddata['help'])) ? $fielddata['help']: '';
            $other = (isset($fielddata['other'])) ? $fielddata['other'] : '';

            if (isset($this->questionId2groupSeq[$questionNum])) {
                $groupSeq = $this->questionId2groupSeq[$questionNum];
            }
            else {
                $groupSeq = (isset($fielddata['groupSeq'])) ? $fielddata['groupSeq'] : -1;
                $this->questionId2groupSeq[$questionNum] = $groupSeq;
            }

            if (isset($this->questionId2questionSeq[$questionNum])) {
                $questionSeq = $this->questionId2questionSeq[$questionNum];
            }
            else {
                $questionSeq = (isset($fielddata['questionSeq'])) ? $fielddata['questionSeq'] : -1;
                $this->questionId2questionSeq[$questionNum] = $questionSeq;
            }

            if (!isset($this->groupSeqInfo[$groupSeq])) {
                $this->groupSeqInfo[$groupSeq] = array(
                    'qstart' => $questionSeq,
                    'qend' => $questionSeq,
                );
            }
            else {
                $this->groupSeqInfo[$groupSeq]['qend'] = $questionSeq;  // with each question, update so know ending value
            }


            // Create list of codes associated with each question
            $codeList = (isset($this->qid2code[$questionNum]) ? $this->qid2code[$questionNum] : '');
            if ($codeList == '')
            {
                $codeList = $sgqa;
            }
            else
            {
                $codeList .= '|' . $sgqa;
            }
            $this->qid2code[$questionNum] = $codeList;

            $readWrite = 'N';

            // Set $ansArray
            switch($type)
            {
                case '!': //List - dropdown
                case 'L': //LIST drop-down/radio-button list
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                case '1': //Array (Flexible Labels) dual scale  // need scale
                case 'H': //ARRAY (Flexible) - Column Format
                case 'F': //ARRAY (Flexible) - Row Format
                case 'R': //RANKING STYLE
                    $ansArray = $this->qans[$questionNum];
                    if ($other == 'Y' && ($type == 'L' || $type == '!')) {
                        $_qattr = isset($qattr[$questionNum]) ? $qattr[$questionNum] : array();
                        if (isset($_qattr['other_replace_text']) && trim($_qattr['other_replace_text']) != '') {
                            $othertext = trim($_qattr['other_replace_text']);
                        }
                        else {
                            $othertext = $this->gT('Other:');
                        }
                        $ansArray['0~-oth-'] = '0|' . $othertext;
                    }
                    break;
                case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case '5': //5 POINT CHOICE radio-buttons
                    $ansArray=NULL;
                    break;
                case 'N': //NUMERICAL QUESTION TYPE
                case 'K': //MULTIPLE NUMERICAL QUESTION
                case 'Q': //MULTIPLE SHORT TEXT
                case ';': //ARRAY (Multi Flexi) Text
                case 'S': //SHORT FREE TEXT
                case 'T': //LONG FREE TEXT
                case 'U': //HUGE FREE TEXT
                case 'M': //Multiple choice checkbox
                case 'P': //Multiple choice with comments checkbox + text
                case 'D': //DATE
                case '*': //Equation
                case 'I': //Language Question
                case '|': //File Upload
                case 'X': //BOILERPLATE QUESTION
                    $ansArray = NULL;
                    break;
                case 'G': //GENDER drop-down list
                case 'Y': //YES/NO radio-buttons
                case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                    $ansArray = $presets[$type];
                    break;
            }

            // set $subqtext text - for display of primary sub-question
            $subqtext = '';
            switch ($type)
            {
                default:
                    $subqtext = (isset($fielddata['subquestion']) ? $fielddata['subquestion'] : '');
                    break;
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case ';': //ARRAY (Multi Flexi) Text
                    $subqtext = (isset($fielddata['subquestion1']) ? $fielddata['subquestion1'] : '');
                    $ansList = array();
                    if (isset($fielddata['answerList']))
                    {
                        foreach ($fielddata['answerList'] as $ans) {
                            $ansList['1~' . $ans['code']] = $ans['code'] . '|' . $ans['answer'];
                        }
                        $this->multiflexiAnswers[$questionNum] = $ansList;
                    }
                    break;
            }


            // Set $varName (question code / questions.title), $rowdivid, $csuffix, $sqsuffix, and $question
            $rowdivid=NULL;   // so that blank for types not needing it.
            $sqsuffix='';
            switch($type)
            {
                case '!': //List - dropdown
                case '5': //5 POINT CHOICE radio-buttons
                case 'D': //DATE
                case 'G': //GENDER drop-down list
                case 'I': //Language Question
                case 'L': //LIST drop-down/radio-button list
                case 'N': //NUMERICAL QUESTION TYPE
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                case 'S': //SHORT FREE TEXT
                case 'T': //LONG FREE TEXT
                case 'U': //HUGE FREE TEXT
                case 'X': //BOILERPLATE QUESTION
                case 'Y': //YES/NO radio-buttons
                case '|': //File Upload
                case '*': //Equation
                    $csuffix = '';
                    $sqsuffix = '';
                    $varName = $fielddata['title'];
                    $question = $fielddata['question'];
                    break;
                case '1': //Array (Flexible Labels) dual scale
                    $csuffix = $fielddata['aid'] . '#' . $fielddata['scale_id'];
                    $sqsuffix = '_' . $fielddata['aid'];
                    $varName = $fielddata['title'] . '_' . $fielddata['aid'] . '_' . $fielddata['scale_id'];;
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion'] . '[' . $fielddata['scale'] . ']';
                    $rowdivid = substr($sgqa,0,-2);
                    break;
                case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                case 'F': //ARRAY (Flexible) - Row Format
                case 'H': //ARRAY (Flexible) - Column Format    // note does not have javatbd equivalent - so array filters don't work on it
                case 'K': //MULTIPLE NUMERICAL QUESTION         // note does not have javatbd equivalent - so array filters don't work on it, but need rowdivid to process validations
                case 'M': //Multiple choice checkbox
                case 'P': //Multiple choice with comments checkbox + text
                case 'Q': //MULTIPLE SHORT TEXT                 // note does not have javatbd equivalent - so array filters don't work on it
                case 'R': //RANKING STYLE                       // note does not have javatbd equivalent - so array filters don't work on it
                    $csuffix = $fielddata['aid'];
                    $varName = $fielddata['title'] . '_' . $fielddata['aid'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion'];
                    if ($type != 'H' && $type != 'Q' && $type != 'R') {
                        if ($type == 'P' && preg_match("/comment$/", $sgqa)) {
//                            $rowdivid = substr($sgqa,0,-7);
                        }
                        else {
                            $sqsuffix = '_' . $fielddata['aid'];
                            $rowdivid = $sgqa;
                        }
                    }
                    break;
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case ';': //ARRAY (Multi Flexi) Text
                    $csuffix = $fielddata['aid'];
                    $sqsuffix = '_' . substr($fielddata['aid'],0,strpos($fielddata['aid'],'_'));
                    $varName = $fielddata['title'] . '_' . $fielddata['aid'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion1'] . '[' . $fielddata['subquestion2'] . ']';
                    $rowdivid = substr($sgqa,0,strpos($sgqa,'_'));
                    break;
            }

            // Set $jsVarName_on (for on-page variables - e.g. answerSGQA) and $jsVarName (for off-page  variables; the primary name - e.g. javaSGQA)
            switch($type)
            {
                case 'R': //RANKING STYLE
                    $jsVarName_on = 'fvalue_' . $fieldNameParts[2];
                    $jsVarName = 'java' . $sgqa;
                    break;
                case 'D': //DATE
                case 'N': //NUMERICAL QUESTION TYPE
                case 'S': //SHORT FREE TEXT
                case 'T': //LONG FREE TEXT
                case 'U': //HUGE FREE TEXT
                case 'Q': //MULTIPLE SHORT TEXT
                case 'K': //MULTIPLE NUMERICAL QUESTION
                case 'X': //BOILERPLATE QUESTION
                    $jsVarName_on = 'answer' . $sgqa;
                    $jsVarName = 'java' . $sgqa;
                    break;
                case '!': //List - dropdown
                case '5': //5 POINT CHOICE radio-buttons
                case 'G': //GENDER drop-down list
                case 'I': //Language Question
                case 'L': //LIST drop-down/radio-button list
                case 'Y': //YES/NO radio-buttons
                case '*': //Equation
                case '1': //Array (Flexible Labels) dual scale
                case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                case 'F': //ARRAY (Flexible) - Row Format
                case 'H': //ARRAY (Flexible) - Column Format
                case 'M': //Multiple choice checkbox
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case ';': //ARRAY (Multi Flexi) Text
                    $jsVarName = 'java' . $sgqa;
                    $jsVarName_on = $jsVarName;
                    break;
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                    if (preg_match("/comment$/", $sgqa)) {
                        $jsVarName = 'java' . $sgqa;
                        $varName = $varName . "_comment";
                    }
                    else {
                        $jsVarName = 'java' . $sgqa;
                    }
                    $jsVarName_on = $jsVarName;
                    break;
                case '|': //File Upload
                    // Only want the use the one that ends in '_filecount'
                    $goodcode = preg_replace("/^(.*?)(_filecount)?$/","$1",$sgqa);
                    $jsVarName = $goodcode . '_filecount';
                    $jsVarName_on = $jsVarName;
                    break;
                case 'P': //Multiple choice with comments checkbox + text
                    if (preg_match("/comment$/",$sgqa))
                    {
                        $jsVarName_on = 'answer' . $sgqa;  // is this true for survey.php and not for group.php?
                        $jsVarName = 'java' . $sgqa;
                    }
                    else
                    {
                        $jsVarName = 'java' . $sgqa;
                        $jsVarName_on = $jsVarName;
                    }
                    break;
            }
            if (!is_null($rowdivid) || $type == 'L' || $type == 'N' || !is_null($preg)) {
                if (!isset($q2subqInfo[$questionNum])) {
                    $q2subqInfo[$questionNum] = array(
                        'qid' => $questionNum,
                        'qseq' => $questionSeq,
                        'gid' => $groupNum,
                        'sgqa' => $surveyid . 'X' . $groupNum . 'X' . $questionNum,
                        'varName' => $varName,
                        'type' => $type,
                        'fieldname' => $sgqa,
                        'preg' => $preg,
                        );
                }
                if (!isset($q2subqInfo[$questionNum]['subqs'])) {
                    $q2subqInfo[$questionNum]['subqs'] = array();
                }
                if ($type == 'L')
                {
                    foreach (array_keys($ansArray) as $key)
                    {
                        $parts = explode('~',$key);
                        if ($parts[1] == '-oth-') {
                            $parts[1] = 'other';
                        }
                        $q2subqInfo[$questionNum]['subqs'][] = array(
                            'rowdivid' => $surveyid . 'X' . $groupNum . 'X' . $questionNum . $parts[1],
                            'varName' => $varName,
                            'sqsuffix' => '_' . $parts[1],
                            );
                    }
                }
                else if ($type == 'N'
                        || $type == 'S' || $type == 'T' || $type == 'U')    // for $preg
                {
                    $q2subqInfo[$questionNum]['subqs'][] = array(
                        'varName' => $varName,
                        );
                }
                else
                {
                    $q2subqInfo[$questionNum]['subqs'][] = array(
                        'rowdivid' => $rowdivid,
                        'varName' => $varName,
                        'jsVarName_on' => $jsVarName_on,
                        'jsVarName' => $jsVarName,
                        'csuffix' => $csuffix,
                        'sqsuffix' => $sqsuffix,
                        );
                }
            }

            $ansList = '';
            if (isset($ansArray) && !is_null($ansArray)) {
                $answers = array();
                foreach ($ansArray as $key => $value) {
                    $answers[] = "'" . $key . "':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$value),ENT_QUOTES) . "'";
                }
                $ansList = ",'answers':{ " . implode(",",$answers) . "}";
            }

            // Set mappings of variable names to needed attributes
            $varInfo_Code = array(
                'jsName_on'=>$jsVarName_on,
                'jsName'=>$jsVarName,
                'readWrite'=>$readWrite,
                'hidden'=>$hidden,
                'question'=>$question,
                'qid'=>$questionNum,
                'gid'=>$groupNum,
                'grelevance'=>$grelevance,
                'relevance'=>$relevance,
                'qcode'=>$varName,
                'qseq'=>$questionSeq,
                'gseq'=>$groupSeq,
                'type'=>$type,
                'sgqa'=>$sgqa,
                'rowdivid'=>$rowdivid,
                'ansList'=>$ansList,
                'ansArray'=>$ansArray,
                'scale_id'=>$scale_id,
                'default'=>$defaultValue,
                'rootVarName'=>$fielddata['title'],
                'subqtext'=>$subqtext,
                );

            $this->questionSeq2relevance[$questionSeq] = array(
                'relevance'=>$relevance,
                'grelevance'=>$grelevance,
                'qid'=>$questionNum,
                'qseq'=>$questionSeq,
                'gseq'=>$groupSeq,
                'jsResultVar_on'=>$jsVarName_on,
                'jsResultVar'=>$jsVarName,
                'type'=>$type,
                'hidden'=>$hidden,
                'gid'=>$groupNum,
                'mandatory'=>$mandatory,
                'eqn'=>(($type == '*') ? $question : ''),
                'help'=>$help,
                'qtext'=>$fielddata['question'],    // $question,
                'code'=>$varName,
                'other'=>$other,
                'default'=>$defaultValue,
                'rootVarName'=>$fielddata['title'],
                );

            $this->knownVars[$varName] = $varInfo_Code;
            $this->knownVars[$sgqa] = $varInfo_Code;

            $this->jsVar2qid[$jsVarName] = $questionNum;

            // Create JavaScript arrays
            $this->alias2varName[$varName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $varName . "':'" . $jsVarName . "'");
            $this->alias2varName[$sgqa] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $sgqa . "':'" . $jsVarName . "'");

            $this->varNameAttr[$jsVarName] = "'" . $jsVarName . "':{ "
                . "'jsName':'" . $jsVarName
                . "','jsName_on':'" . $jsVarName_on
                . "','sgqa':'" . $sgqa
                . "','qid':" . $questionNum
                . ",'gid':" . $groupNum
                . ",'mandatory':'" . $mandatory
                . "','question':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$question),ENT_QUOTES)
                . "','type':'" . $type
                . "','relevance':'" . (($relevance != '') ? htmlspecialchars(preg_replace('/[[:space:]]/',' ',$relevance),ENT_QUOTES) : 1)
                . "','readWrite':'" . $readWrite
                . "','grelevance':'" . (($grelevance != '') ? htmlspecialchars(preg_replace('/[[:space:]]/',' ',$grelevance),ENT_QUOTES) : 1)
                . "','default':'" . (is_null($defaultValue) ? '' : $defaultValue)
                . "','gseq':" . $groupSeq
                . ",'qseq':" . $questionSeq
                .$ansList."}";

            if (($this->debugLevel & LEM_DEBUG_TRANSLATION_DETAIL) == LEM_DEBUG_TRANSLATION_DETAIL)
            {
                $this->debugLog[] = array(
                    'sgqa' => $sgqa,
                    'type' => $type,
                    'varname' => $varName,
                    'jsName_on'=> $jsVarName_on,
                    'jsName' => $jsVarName,
                    'question' => $question,
                    'readWrite' => $readWrite,
                    'relevance' => $relevance,
                    'hidden' => $hidden,
                    );
            }
        }

        $this->q2subqInfo = $q2subqInfo;

        // Now set tokens
        if (isset($_SESSION['token']) && $_SESSION['token'] != '')
        {
            //Gather survey data for tokenised surveys, for use in presenting questions
            $_SESSION['thistoken']=getTokenData($surveyid, $_SESSION['token']);
        }
        if (isset($_SESSION['thistoken']))
        {
            foreach (array_keys($_SESSION['thistoken']) as $tokenkey)
            {
                if ($anonymized)
                {
                    $val = "";
                }
                else
                {
                    $val = $_SESSION['thistoken'][$tokenkey];
                }
                $key = "TOKEN:" . strtoupper($tokenkey);
                $this->knownVars[$key] = array(
                    'code'=>$val,
                    'jsName_on'=>'',
                    'jsName'=>'',
                    'readWrite'=>'N',
                    );

                if (($this->debugLevel & LEM_DEBUG_TRANSLATION_DETAIL) == LEM_DEBUG_TRANSLATION_DETAIL)
                {
                    $this->debugLog[] = array(
                        'sgqa' => $key,
                        'type' => '&nbsp;',
                        'varname' => '&nbsp;',
                        'jsName_on' => '&nbsp;',
                        'jsName' => '&nbsp;',
                        'question' => '&nbsp;',
                        'readWrite'=>'N',
                        'relevance'=>'',
                        'hidden'=>'',
                    );
                }
            }
        }
        else
        {
            // Explicitly set all tokens to blank
            $blankVal = array(
                    'code'=>'',
                    'jsName_on'=>'',
                    'jsName'=>'',
                    'readWrite'=>'N',
                    );
            $this->knownVars['TOKEN:FIRSTNAME'] = $blankVal;
            $this->knownVars['TOKEN:LASTNAME'] = $blankVal;
            $this->knownVars['TOKEN:EMAIL'] = $blankVal;
            $this->knownVars['TOKEN:USESLEFT'] = $blankVal;
            for ($i=1;$i<=100;++$i) // TODO - is there a way to know  how many attributes are set?  Looks like max is 100
            {
                $this->knownVars['TOKEN:ATTRIBUTE_' . $i] = $blankVal;
            }
        }

        $this->runtimeTimings[] = array(__METHOD__ . ' - process fieldMap',(microtime(true) - $now));
        if (($this->debugLevel & LEM_DEBUG_TRANSLATION_DETAIL) == LEM_DEBUG_TRANSLATION_DETAIL)
        {
            $debugLog_html = "<table border='1'>";
            $debugLog_html .= "<tr><th>Code</th><th>Type</th><th>VarName</th><th>CodeVal</th><th>DisplayVal</th><th>JSname</th><th>Writable?</th><th>Set On This Page?</th><th>Relevance</th><th>Hidden</th><th>Question</th></tr>";
            foreach ($this->debugLog as $t)
            {
                $debugLog_html .= "<tr><td>" . $t['sgqa']
                    . "</td><td>" . $t['type']
                    . "</td><td>" . $t['varname']
                    . "</td><td>" . $t['jsName']
                    . "</td><td>" . $t['readWrite']
                    . "</td><td>" . $t['relevance']
                    . "</td><td>" . $t['hidden']
                    . "</td><td>" . $t['question']
                    . "</td></tr>";
            }
            $debugLog_html .= "</table>";
            $this->surveyLogicFile = $debugLog_html;
        }
        usort($this->questionSeq2relevance,'self::cmpQuestionSeq');
        $this->numQuestions = count($this->questionSeq2relevance);
        $this->numGroups = count($this->groupId2groupSeq);
        
        return true;
    }

    /**
     * Return whether question $qid is relevanct
     * @param <type> $qid
     * @return boolean
     */
    static function QuestionIsRelevant($qid)
    {
        $LEM =& LimeExpressionManager::singleton();
        $qrel = (isset($_SESSION['relevanceStatus'][$qid]) ? $_SESSION['relevanceStatus'][$qid] : 1);
        $groupSeq = (isset($LEM->questionId2groupSeq[$qid]) ? $LEM->questionId2groupSeq[$qid] : -1);
        $gid = (isset($LEM->gseq2info[$groupSeq]['gid']) ? $LEM->gseq2info[$groupSeq]['gid'] : -1);
        $grel = (isset($_SESSION['relevanceStatus']['G' . $gid]) ? $_SESSION['relevanceStatus']['G' . $gid] : 1);   // group-level relevance based upon grelevance equation
        return ($grel && $qrel);
    }

    /**
     * Return whether group $gid is relevant
     * @param <type> $gid
     * @return boolean
     */
    static function GroupIsRelevant($gid)
    {
        $LEM =& LimeExpressionManager::singleton();
        $grel = (isset($_SESSION['relevanceStatus']['G' . $gid]) ? $_SESSION['relevanceStatus']['G' . $gid] : 1);   // group-level relevance based upon grelevance equation
        $qgrel = (isset($LEM->gid2relevanceStatus[$gid]) ? isset($LEM->gid2relevanceStatus[$gid]) : 1); // group-level relevance based upon ensuring at least one contained question is relevant
        return ($grel && $qgrel);
    }

    /**
 * Used by usort() to order $this->questionSeq2relevance in proper order
 * @param <type> $a
 * @param <type> $b
 * @return <type>
 */
    function cmpQuestionSeq($a, $b)
    {
        if (is_null($a['qseq'])) {
            if (is_null($b['qseq'])) {
                return 0;
            }
            return 1;
        }
        if (is_null($b['qseq'])) {
            return -1;
        }
        if ($a['qseq'] == $b['qseq']) {
            return 0;
        }
        return ($a['qseq'] < $b['qseq']) ? -1 : 1;
    }

    /**
     * Check the relevance status of all questions on or before the current group.
     * This generates needed JavaScript for dynamic relevance, and sets flags about which questions and groups are relevant
     */
    function ProcessAllNeededRelevance($onlyThisQseq=NULL)
    {
        // TODO - in a running survey, only need to process the current Group.  For Admin mode, do we need to process all prior questions or not?
//        $now = microtime(true);

        $grelComputed=array();  // so only process it once per group
        foreach($this->questionSeq2relevance as $rel)
        {
            if (!is_null($onlyThisQseq) && $onlyThisQseq!=$rel['qseq']) {
                continue;
            }
            $qid = $rel['qid'];
            $gid = $rel['gid'];
            $groupSeq = $rel['gseq'];
            if ($this->allOnOnePage) {
                ;   // process relevance for all questions
            }
            else if ($groupSeq != $this->currentGroupSeq) {
                continue;
            }
            $result = $this->_ProcessRelevance(htmlspecialchars_decode($rel['relevance'],ENT_QUOTES),
                    $qid,
                    $gid,
                    $rel['jsResultVar'],
                    $rel['type'],
                    $rel['hidden']
                    );
            $_SESSION['relevanceStatus'][$qid] = $result;

            if (!isset($grelComputed[$gid])) {
                $this->_ProcessGroupRelevance($gid);
                $grelComputed[$gid]=true;
            }
        }
//        $this->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
    }

    /**
     * Translate all Expressions, Macros, registered variables, etc. in $string
     * @param <type> $string - the string to be replaced
     * @param <type> $questionNum - the $qid of question being replaced - needed for properly alignment of question-level relevance and tailoring
     * @param <type> $replacementFields - optional replacement values
     * @param boolean $debug - if true,write translations for this page to html-formatted log file
     * @param <type> $numRecursionLevels - the number of times to recursively subtitute values in this string
     * @param <type> $whichPrettyPrintIteration - if want to pretty-print the source string, which recursion  level should be pretty-printed
     * @param <type> $noReplacements - true if we already know that no replacements are needed (e.g. there are no curly braces)
     * @return <type> - the original $string with all replacements done.
     */

    static function ProcessString($string, $questionNum=NULL, $replacementFields=array(), $debug=false, $numRecursionLevels=1, $whichPrettyPrintIteration=1, $noReplacements=false, $timeit=true)
    {
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();

        if ($noReplacements) {
            $LEM->em->SetPrettyPrintSource($string);
            return $string;
        }

        if (isset($replacementFields) && is_array($replacementFields) && count($replacementFields) > 0)
        {
            $replaceArray = array();
            foreach ($replacementFields as $key => $value) {
                $replaceArray[$key] = array(
                    'code'=>$value,
                    'jsName_on'=>'',
                    'jsName'=>'',
                    'readWrite'=>'N',
                );
            }
            $LEM->em->RegisterVarnamesUsingMerge($replaceArray);   // TODO - is it safe to just merge these in each time, or should a refresh be forced?
        }
        $questionSeq = -1;
        $groupSeq = -1;
        if (!is_null($questionNum)) {
            $questionSeq = isset($LEM->questionId2questionSeq[$questionNum]) ? $LEM->questionId2questionSeq[$questionNum] : -1;
            $groupSeq = isset($LEM->questionId2groupSeq[$questionNum]) ? $LEM->questionId2groupSeq[$questionNum] : -1;
        }
        $stringToParse = htmlspecialchars_decode($string,ENT_QUOTES);
        $qnum = is_null($questionNum) ? 0 : $questionNum;
        $result = $LEM->em->sProcessStringContainingExpressions($stringToParse,$qnum, $numRecursionLevels, $whichPrettyPrintIteration, $groupSeq, $questionSeq);
        $hasErrors = $LEM->em->HasErrors();
        if ($hasErrors && (($LEM->debugLevel & LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB) == LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB)) {
            $error = array(
                'errortime' => date('Y-m-d H:i:s'),
                'sid' => $LEM->sid,
                'type' => 'Text',
                'gid' => $LEM->groupNum,
                'gseq' => $groupSeq,
                'qid' => $qnum,
                'qseq' => $questionSeq,
                'eqn' => $stringToParse,
                'prettyPrint' => $LEM->em->GetLastPrettyPrintExpression(),
            );
            $LEM->syntaxErrors[] = $error;
        }

        if ($timeit) {
            $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
        }

        if (($LEM->debugLevel & LEM_DEBUG_TRANSLATION_DETAIL) == LEM_DEBUG_TRANSLATION_DETAIL)
        {
            $varsUsed = $LEM->em->GetJSVarsUsed();
            if (is_array($varsUsed) and count($varsUsed) > 0) {
                $LEM->pageTailoringLog .= '<tr><td>' . $LEM->groupNum . '</td><td>' . $string . '</td><td>' . $LEM->em->GetLastPrettyPrintExpression() . '</td><td>' . $result . "</td></tr>\n";
            }
        }

        return $result;
    }


    /**
     * Compute Relevance, processing $eqn to get a boolean value.  If there are syntax errors, return false.
     * @param <type> $eqn - the relevance equation
     * @param <type> $questionNum - needed to align question-level relevance and tailoring
     * @param <type> $jsResultVar - this variable determines whether irrelevant questions are hidden
     * @param <type> $type - question type
     * @param <type> $hidden - whether question should always be hidden
     * @return <type>
     */
    static function ProcessRelevance($eqn,$questionNum=NULL,$jsResultVar=NULL,$type=NULL,$hidden=0)
    {
        $LEM =& LimeExpressionManager::singleton();
        return $LEM->_ProcessRelevance($eqn,$questionNum,NULL,$jsResultVar,$type,$hidden);
    }

    /**
     * Compute Relevance, processing $eqn to get a boolean value.  If there are syntax errors, return false.
     * @param <type> $eqn - the relevance equation
     * @param <type> $questionNum - needed to align question-level relevance and tailoring
     * @param <type> $jsResultVar - this variable determines whether irrelevant questions are hidden
     * @param <type> $type - question type
     * @param <type> $hidden - whether question should always be hidden
     * @return <type>
     */
    private function _ProcessRelevance($eqn,$questionNum=NULL,$groupNum=NULL,$jsResultVar=NULL,$type=NULL,$hidden=0)
    {
        // These will be called in the order that questions are supposed to be asked
        // TODO - cache results and generated JavaScript equations?
        if (!isset($eqn) || trim($eqn=='') || trim($eqn)=='1')
        {
            $this->groupRelevanceInfo[] = array(
                'qid' => $questionNum,
                'gid' => $groupNum,
                'eqn' => $eqn,
                'result' => true,
                'numJsVars' => 0,
                'relevancejs' => '',
                'relevanceVars' => '',
                'jsResultVar'=> $jsResultVar,
                'type'=>$type,
                'hidden'=>$hidden,
                'hasErrors'=>false,
            );
            return true;
        }
        $questionSeq = -1;
        $groupSeq = -1;
        if (!is_null($questionNum)) {
            $questionSeq = isset($this->questionId2questionSeq[$questionNum]) ? $this->questionId2questionSeq[$questionNum] : -1;
            $groupSeq = isset($this->questionId2groupSeq[$questionNum]) ? $this->questionId2groupSeq[$questionNum] : -1;
        }

        $stringToParse = htmlspecialchars_decode($eqn,ENT_QUOTES);
        $result = $this->em->ProcessBooleanExpression($stringToParse,$groupSeq, $questionSeq);
        $hasErrors = $this->em->HasErrors();

        if ($hasErrors && (($this->debugLevel & LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB) == LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB)) {
            $prettyPrint = $this->em->GetPrettyPrintString();
            $error = array(
                'errortime' => date('Y-m-d H:i:s'),
                'sid' => $this->sid,
                'type' => 'Relevance',
                'gid' => $groupNum,
                'gseq' => $groupSeq,
                'qid' => $questionNum,
                'qseq' => $questionSeq,
                'eqn' => $stringToParse,
                'prettyPrint' => $prettyPrint,
                'hasErrors' => $hasErrors,
            );
            $this->syntaxErrors[] = $error;
        }

        if (!is_null($questionNum) && !is_null($jsResultVar)) { // so if missing either, don't generate JavaScript for this - means off-page relevance.
            $jsVars = $this->em->GetJSVarsUsed();
            $relevanceVars = implode('|',$this->em->GetJSVarsUsed());
            $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();
            $this->groupRelevanceInfo[] = array(
                'qid' => $questionNum,
                'gid' => $groupNum,
                'eqn' => $eqn,
                'result' => $result,
                'numJsVars' => count($jsVars),
                'relevancejs' => $relevanceJS,
                'relevanceVars' => $relevanceVars,
                'jsResultVar' => $jsResultVar,
                'type'=>$type,
                'hidden'=>$hidden,
                'hasErrors'=>$hasErrors,
            );
        }
        return $result;
    }

    /**
     * Create JavaScript needed to process sub-question-level relevance (e.g. for array_filter and  _exclude)
     * @param <type> $eqn - the equation to parse
     * @param <type> $questionNum - the question number - needed to align relavance and tailoring blocks
     * @param <type> $rowdivid - the javascript ID that needs to be shown/hidden in order to control array_filter visibility
     * @param <type> $type - the type of sub-question relevance (e.g. 'array_filter', 'array_filter_exclude')
     * @return <type>
     */
   private function _ProcessSubQRelevance($eqn,$questionNum=NULL,$rowdivid=NULL, $type=NULL, $qtype=NULL, $sgqa=NULL)
    {
        // These will be called in the order that questions are supposed to be asked
        if (!isset($eqn) || trim($eqn=='') || trim($eqn)=='1')
        {
            return true;
        }
        $questionSeq = -1;
        $groupSeq = -1;
        if (!is_null($questionNum)) {
            $questionSeq = isset($this->questionId2questionSeq[$questionNum]) ? $this->questionId2questionSeq[$questionNum] : -1;
            $groupSeq = isset($this->questionId2groupSeq[$questionNum]) ? $this->questionId2groupSeq[$questionNum] : -1;
        }

        $stringToParse = htmlspecialchars_decode($eqn,ENT_QUOTES);
        $result = $this->em->ProcessBooleanExpression($stringToParse,$groupSeq, $questionSeq);
        $hasErrors = $this->em->HasErrors();
        $prettyPrint = '';
        if (($this->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
            $prettyPrint= $this->em->GetPrettyPrintString();
        }

        if ($hasErrors && (($this->debugLevel & LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB) == LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB)) {
            $error = array(
                'errortime' => date('Y-m-d H:i:s'),
                'sid' => $this->sid,
                'type' => $type,
                'gid' => $this->groupNum,
                'gseq' => $groupSeq,
                'qid' => $questionNum,
                'qseq' => $questionSeq,
                'eqn' => $stringToParse,
                'prettyPrint' => $prettyPrint,
            );
            $this->syntaxErrors[] = $error;
        }
        else if (!is_null($questionNum)) {
            $jsVars = $this->em->GetJSVarsUsed();
            $relevanceVars = implode('|',$this->em->GetJSVarsUsed());
            $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();

            if (!isset($this->subQrelInfo[$questionNum])) {
                $this->subQrelInfo[$questionNum] = array();
            }
            $this->subQrelInfo[$questionNum][$rowdivid] = array(
                'qid' => $questionNum,
                'eqn' => $eqn,
                'prettyPrintEqn' => $prettyPrint,
                'result' => $result,
                'numJsVars' => count($jsVars),
                'relevancejs' => $relevanceJS,
                'relevanceVars' => $relevanceVars,
                'rowdivid' => $rowdivid,
                'type'=>$type,
                'qtype'=>$qtype,
                'sgqa'=>$sgqa,
                'hasErrors'=>$hasErrors,
            );
        }
        return $result;
    }

    private function _ProcessGroupRelevance($gid)
    {
        // These will be called in the order that questions are supposed to be asked
        $groupSeq = (isset($this->groupId2groupSeq[$gid]) ? $this->groupId2groupSeq[$gid] : -1);
        if ($groupSeq == -1) {
            return; // invalid group, so ignore
        }

        $eqn = (isset($this->gseq2info[$groupSeq]['grelevance']) ? $this->gseq2info[$groupSeq]['grelevance'] : 1);
        if (is_null($eqn) || trim($eqn=='') || trim($eqn)=='1')
        {
            $this->gRelInfo[$groupSeq] = array(
                'gid' => $gid,
                'gseq' => $groupSeq,
                'eqn' => '',
                'result' => 1,
                'numJsVars' => 0,
                'relevancejs' => '',
                'relevanceVars' => '',
                'prettyPrint'=> '',
            );
            return;
        }
        $stringToParse = htmlspecialchars_decode($eqn,ENT_QUOTES);
        $result = $this->em->ProcessBooleanExpression($stringToParse,$groupSeq);
        $hasErrors = $this->em->HasErrors();

        if ($hasErrors && (($this->debugLevel & LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB) == LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB)) {
            $prettyPrint = $this->em->GetPrettyPrintString();
            $error = array(
                'errortime' => date('Y-m-d H:i:s'),
                'sid' => $this->sid,
                'type' => '',
                'gid' => $this->groupNum,
                'gseq' => $groupSeq,
                'qid' => -1,
                'qseq' => -1,
                'eqn' => $stringToParse,
                'prettyPrint' => $prettyPrint,
            );
            $this->syntaxErrors[] = $error;
        }
        else {
            $jsVars = $this->em->GetJSVarsUsed();
            $relevanceVars = implode('|',$this->em->GetJSVarsUsed());
            $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();
            $prettyPrint = $this->em->GetPrettyPrintString();

            $this->gRelInfo[$groupSeq] = array(
                'gid' => $gid,
                'gseq' => $groupSeq,
                'eqn' => $stringToParse,
                'result' => $result,
                'numJsVars' => count($jsVars),
                'relevancejs' => $relevanceJS,
                'relevanceVars' => $relevanceVars,
                'prettyPrint'=> $prettyPrint,
                'hasErrors' => $hasErrors,
            );
            $_SESSION['relevanceStatus']['G' . $gid] = $result;
        }
    }

    /**
     * Used to show potential syntax errors of processing Relevance or Equations.
     * @return <type>
     */
    static function GetLastPrettyPrintExpression()
    {
        $LEM =& LimeExpressionManager::singleton();
        return $LEM->em->GetLastPrettyPrintExpression();
    }

    /**
     * Should be first function called on each page - sets/clears internally needed variables
     * @param <type> $allOnOnePage - true if StartProcessingGroup will be called multiple times on this page - does some optimizatinos
     * @param <type> $rooturl - if set, this tells LEM to enable hyperlinking of syntax highlighting to ease editing of questions
     */
    static function StartProcessingPage($allOnOnePage=false,$rooturl=NULL)
    {
//        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();
        $LEM->pageRelevanceInfo=array();
        $LEM->pageTailorInfo=array();
        $LEM->allOnOnePage=$allOnOnePage;
        $LEM->pageTailoringLog='';
        $LEM->surveyLogicFile='';
        $LEM->slang = (isset($_SESSION['s_lang']) ? $_SESSION['s_lang'] : 'en');
        $LEM->processedRelevance=false;
        if (!is_null($rooturl)) {
            $LEM->surveyOptions['rooturl'] = $rooturl;
            $LEM->surveyOptions['hyperlinkSyntaxHighlighting']=true;    // this will be temporary - should be reset in running survey
        }

//        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));

        if (($LEM->debugLevel & LEM_DEBUG_TRANSLATION_DETAIL) == LEM_DEBUG_TRANSLATION_DETAIL)
        {
            $LEM->pageTailoringLog .= '<tr><th>Source</th><th>Pretty Print</th><th>Result</th></tr>';
        }
        $LEM->initialized=true;
    }

    /**
     * Initialize a survey so can use EM to manage navigation
     * @param <type> $surveyid
     * @param <type> $surveyMode
     * @param <type> $anonymized
     * @param <type> $forceRefresh
     */

    static function StartSurvey($surveyid,$surveyMode='group',$options=NULL,$forceRefresh=false,$debugLevel=0)
    {
        $LEM =& LimeExpressionManager::singleton();
        $LEM->sid=sanitize_int($surveyid);

        if (is_null($options)) {
            $options = array();
        }
        $LEM->surveyOptions['active'] = (isset($options['active']) ? $options['active'] : false);
        $LEM->surveyOptions['allowsave'] = (isset($options['allowsave']) ? $options['allowsave'] : false);
        $LEM->surveyOptions['anonymized'] = (isset($options['anonymized']) ? $options['anonymized'] : false);
        $LEM->surveyOptions['assessments'] = (isset($options['assessments']) ? $options['assessments'] : false);
        $LEM->surveyOptions['datestamp'] = (isset($options['datestamp']) ? $options['datestamp'] : false);
        $LEM->surveyOptions['hyperlinkSyntaxHighlighting'] = (isset($options['hyperlinkSyntaxHighlighting']) ? $options['hyperlinkSyntaxHighlighting'] : false);
        $LEM->surveyOptions['ipaddr'] = (isset($options['ipaddr']) ? $options['ipaddr'] : false);
        $LEM->surveyOptions['refurl'] = (isset($options['refurl']) ? $options['refurl'] : NULL);
        $LEM->surveyOptions['rooturl'] = (isset($options['rooturl']) ? $options['rooturl'] : '');
        $LEM->surveyOptions['savetimings'] = (isset($options['savetimings']) ? $options['savetimings'] : '');
        $LEM->surveyOptions['startlanguage'] = (isset($options['startlanguage']) ? $options['startlanguage'] : 'en');
        $LEM->surveyOptions['surveyls_dateformat'] = (isset($options['surveyls_dateformat']) ? $options['surveyls_dateformat'] : 1);
        $LEM->surveyOptions['tablename'] = (isset($options['tablename']) ? $options['tablename'] : db_table_name('survey_' . $LEM->sid));
        $LEM->surveyOptions['target'] = (isset($options['target']) ? $options['target'] : '/temp/files/');
        $LEM->surveyOptions['timeadjust'] = (isset($options['timeadjust']) ? $options['timeadjust'] : 0);
        $LEM->surveyOptions['tempdir'] = (isset($options['tempdir']) ? $options['tempdir'] : '/temp/');
        $LEM->surveyOptions['token'] = (isset($options['token']) ? $options['token'] : NULL);

        $LEM->debugLevel=$debugLevel;
        $_SESSION['LEMdebugLevel']=$debugLevel; // need acces to SESSSION to decide whether to cache serialized instance of $LEM
        switch ($surveyMode) {
            case 'survey':
                $LEM->allOnOnePage=true;
                $LEM->surveyMode = 'survey';
                break;
            case 'question':
                $LEM->allOnOnePage=false;
                $LEM->surveyMode = 'question';
                break;
            default:
            case 'group':
                $LEM->allOnOnePage=false;
                $LEM->surveyMode = 'group';
                break;
        }
        
        if ($LEM->setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh,$LEM->surveyOptions['anonymized'],$LEM->allOnOnePage))
        {
            // means that some values changed, so need to update what was registered to ExpressionManager
            $LEM->em->RegisterVarnamesUsingMerge($LEM->knownVars);
        }
        $LEM->currentGroupSeq=-1;
        $LEM->currentQuestionSeq=-1;    // for question-by-question mode
        $LEM->indexGseq=array();
        $LEM->indexQseq=array();

        return array(
            'hasNext'=>true,
            'hasPrevious'=>false,
        );
    }

     static function NavigateBackwards()
    {
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();

        $LEM->ParseResultCache=array();    // to avoid running same test more than once for a given group

        switch ($LEM->surveyMode)
        {
            case 'survey':
                // should never be called?
                break;
            case 'group':
                // First validate the current group
                $LEM->StartProcessingPage();
                $updatedValues=$LEM->ProcessCurrentResponses();
                $message = '';
                while (true)
                {
                    $LEM->currentQset = array();    // reset active list of questions
                    if (--$LEM->currentGroupSeq < 0)
                    {
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        return array(
                            'at_start'=>true,
                            'finished'=>false,
                            'message'=>$message,
                            'unansweredSQs'=>(isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                            'invalidSQs'=>(isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                        );
                    }

                    $result = $LEM->_ValidateGroup($LEM->currentGroupSeq);
                    if (is_null($result)) {
                        continue;   // this is an invalid group - skip it
                    }
                    $message .= $result['message'];
                    if (!$result['relevant'] || $result['hidden'])
                    {
                        // then skip this group - assume already saved?
                        continue;
                    }
                    else
                    {
                        // display new group
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        return array(
                            'at_start'=>false,
                            'finished'=>false,
                            'message'=>$message,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentGroupSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                    }
                }
                break;
            case 'question':
                $LEM->StartProcessingPage();
                $updatedValues=$LEM->ProcessCurrentResponses();
                $message = '';
                while (true)
                {
                    $LEM->currentQset = array();    // reset active list of questions
                    if (--$LEM->currentQuestionSeq < 0)
                    {
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        return array(
                            'at_start'=>true,
                            'finished'=>false,
                            'message'=>$message,
                            'unansweredSQs'=>(isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                            'invalidSQs'=>(isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                        );
                    }

                    // Set certain variables normally set by StartProcessingGroup()
                    $LEM->groupRelevanceInfo=array();   // TODO only important thing from StartProcessingGroup?
                    $qInfo = $LEM->questionSeq2relevance[$LEM->currentQuestionSeq];
                    $LEM->currentQID=$qInfo['qid'];
                    $LEM->currentGroupSeq=$qInfo['gseq'];
                    $LEM->groupNum=$qInfo['gid'];
                    if ($LEM->currentGroupSeq > $LEM->maxGroupSeq) {
                        $LEM->maxGroupSeq = $LEM->currentGroupSeq;
                    }

                    $LEM->ProcessAllNeededRelevance($LEM->currentQuestionSeq);
                    $LEM->_CreateSubQLevelRelevanceAndValidationEqns($LEM->currentQuestionSeq);
                    $result = $LEM->_ValidateQuestion($LEM->currentQuestionSeq);
                    $message .= $result['message'];

                    if (!$result['relevant'] || $result['hidden'])
                    {
                        // then skip this question - assume already saved?
                        continue;
                    }
                    else
                    {
                        // display new question
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        return array(
                            'at_start'=>false,
                            'finished'=>false,
                            'message'=>$message,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentQuestionSeq,
                            'qseq'=>$LEM->currentQuestionSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                    }
                }
                break;
        }
    }

    /**
     *
     * @param <type> $force - if true, continue to go forward even if there are violations to the mandatory and/or validity rules
     */
    static function NavigateForwards($force=false) {
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();

        $LEM->ParseResultCache=array();    // to avoid running same test more than once for a given group

        switch ($LEM->surveyMode)
        {
            case 'survey':
                $startingGroup = $LEM->currentGroupSeq;
                $LEM->StartProcessingPage(true);
                $updatedValues=$LEM->ProcessCurrentResponses();
                $message = '';

                $LEM->currentQset = array();    // reset active list of questions
                $result = $LEM->_ValidateSurvey();
                $message .= $result['message'];
                $updatedValues = array_merge($updatedValues,$result['updatedValues']);
                if (!$force && !is_null($result) && ($result['mandViolation'] || !$result['valid'] || $startingGroup == -1))
                {
                    $finished=false;
                }
                else
                {
                    $finished = true;
                }
                $message .= $LEM->_UpdateValuesInDatabase($updatedValues,$finished);
                $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                $LEM->lastMoveResult = array(
                    'finished'=>$finished,
                    'message'=>$message,
                    'gseq'=>1,
                    'seq'=>1,
                    'mandViolation'=>$result['mandViolation'],
                    'valid'=>$result['valid'],
                    'unansweredSQs'=>$result['unansweredSQs'],
                    'invalidSQs'=>$result['invalidSQs'],
                );
                return $LEM->lastMoveResult;
                break;
            case 'group':
                // First validate the current group
                $LEM->StartProcessingPage();
                $updatedValues=$LEM->ProcessCurrentResponses();
                $message = '';
                if (!$force && $LEM->currentGroupSeq != -1)
                {
                    $result = $LEM->_ValidateGroup($LEM->currentGroupSeq);
                    $message .= $result['message'];
                    $updatedValues = array_merge($updatedValues,$result['updatedValues']);
                    if (!is_null($result) && ($result['mandViolation'] || !$result['valid']))
                    {
                        // redisplay the current group
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>false,
                            'message'=>$message,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentGroupSeq,
                            'mandViolation'=>$result['mandViolation'],
                            'valid'=>$result['valid'],
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                        return $LEM->lastMoveResult;
                    }
                }
                while (true)
                {
                    $LEM->currentQset = array();    // reset active list of questions
                    if (++$LEM->currentGroupSeq >= $LEM->numGroups)
                    {
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,true);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>true,
                            'message'=>$message,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentGroupSeq,
                            'mandViolation'=>(isset($result['mandViolation']) ? $result['mandViolation'] : false),
                            'valid'=>(isset($result['valid']) ? $result['valid'] : false),
                            'unansweredSQs'=>(isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                            'invalidSQs'=>(isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                        );
                        return $LEM->lastMoveResult;
                    }

                    $result = $LEM->_ValidateGroup($LEM->currentGroupSeq);
                    if (is_null($result)) {
                        continue;   // this is an invalid group - skip it
                    }
                    $message .= $result['message'];
                    $updatedValues = array_merge($updatedValues,$result['updatedValues']);
                    if (!$result['relevant'] || $result['hidden'])
                    {
                        // then skip this group
                        continue;
                    }
                    else
                    {
                        // display new group
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>false,
                            'message'=>$message,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentGroupSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                        return $LEM->lastMoveResult;
                    }
                }
                break;
            case 'question':
                $LEM->StartProcessingPage();
                $updatedValues=$LEM->ProcessCurrentResponses();
                $message = '';
                if (!$force && $LEM->currentQuestionSeq != -1)
                {
                    $result = $LEM->_ValidateQuestion($LEM->currentQuestionSeq);
                    $message .= $result['message'];
                    $updatedValues = array_merge($updatedValues,$result['updatedValues']);
                    if (!is_null($result) && ($result['mandViolation'] || !$result['valid']))
                    {
                        // redisplay the current question
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>false,
                            'message'=>$message,
                            'qseq'=>$LEM->currentQuestionSeq,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentQuestionSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                        return $LEM->lastMoveResult;
                    }
                }
                while (true)
                {
                    $LEM->currentQset = array();    // reset active list of questions
                    if (++$LEM->currentQuestionSeq >= $LEM->numQuestions)
                    {
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,true);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>true,
                            'message'=>$message,
                            'qseq'=>$LEM->currentQuestionSeq,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentQuestionSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>(isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                            'invalidSQs'=>(isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                        );
                        return $LEM->lastMoveResult;
                    }

                    // Set certain variables normally set by StartProcessingGroup()
                    $LEM->groupRelevanceInfo=array();   // TODO only important thing from StartProcessingGroup?
                    $qInfo = $LEM->questionSeq2relevance[$LEM->currentQuestionSeq];
                    $LEM->currentQID=$qInfo['qid'];
                    $LEM->currentGroupSeq=$qInfo['gseq'];
                    $LEM->groupNum=$qInfo['gid'];
                    if ($LEM->currentGroupSeq > $LEM->maxGroupSeq) {
                        $LEM->maxGroupSeq = $LEM->currentGroupSeq;
                    }

                    $LEM->ProcessAllNeededRelevance($LEM->currentQuestionSeq);
                    $LEM->_CreateSubQLevelRelevanceAndValidationEqns($LEM->currentQuestionSeq);
                    $result = $LEM->_ValidateQuestion($LEM->currentQuestionSeq);
                    $message .= $result['message'];
                    $updatedValues = array_merge($updatedValues,$result['updatedValues']);

                    if (!$result['relevant'] || $result['hidden'])
                    {
                        // then skip this question - assume already saved?
                        continue;
                    }
                    else
                    {
                        // display new question
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>false,
                            'message'=>$message,
                            'qseq'=>$LEM->currentQuestionSeq,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentQuestionSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                        return $LEM->lastMoveResult;
                    }
                }
                break;
        }
    }

    /**
     * Write values to database.
     * @param <type> $updatedValues
     * @param <boolean> $finished - true if the survey needs to be finalized
     */
    private function _UpdateValuesInDatabase($updatedValues, $finished=false)
    {
        // Update these values in the database
        global $connect;

        $message = '';
        $_SESSION['datestamp']=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $this->surveyOptions['timeadjust']);
        if ($this->surveyOptions['active'] && !isset($_SESSION['srid']))
        {
            // Create initial insert row for this record
            $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $this->surveyOptions['timeadjust']);
            $sdata = array(
                "datestamp"=>$today,
                "ipaddr"=>(($this->surveyOptions['ipaddr'] && isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ''),
                "startlanguage"=>$this->surveyOptions['startlanguage'],
                "token"=>($this->surveyOptions['token']),
                "datestamp"=>($this->surveyOptions['datestamp'] ? $_SESSION['datestamp'] : NULL),
                "startdate"=>($this->surveyOptions['datestamp'] ? $_SESSION['datestamp'] : date("Y-m-d H:i:s",0)),
                );
            //One of the strengths of ADOdb's AutoExecute() is that only valid field names for $table are updated
            if ($connect->AutoExecute($this->surveyOptions['tablename'], $sdata,'INSERT'))    // Checked
            {
                $srid = $connect->Insert_ID($this->surveyOptions['tablename'],"sid");
                $_SESSION['srid'] = $srid;
            }
            else if (($this->debugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY)
            {
                $message .= "Unable to insert record into survey table.<br />".$connect->ErrorMsg() . "<br/>";
            }
        }

        if (count($updatedValues) > 0 || $finished)
        {
            $query = 'UPDATE '.$this->surveyOptions['tablename'] . " SET ";
            $setter = array();
            switch ($this->surveyMode)
            {
                case 'question':
                    $thisstep = $this->currentQuestionSeq;
                    break;
                case 'group':
                    $thisstep = $this->currentGroupSeq;
                    break;
                case 'survey':
                    $thisstep = 1;
                    break;
            }
            $setter[] = db_quote_id('lastpage') . "=" . db_quoteall($thisstep);

            if ($this->surveyOptions['datestamp'] && isset($_SESSION['datestamp'])) {
                $setter[] = db_quote_id('datestamp') . "=" . db_quoteall($_SESSION['datestamp']);
            }
            if ($this->surveyOptions['ipaddr'] && isset($_SERVER['REMOTE_ADDR'])) {
                $setter[] = db_quote_id('ipaddr') . "=" . db_quoteall($_SERVER['REMOTE_ADDR']);
            }
            if ($finished) {
                $setter[] = db_quote_id('submitdate') . "=" . db_quoteall($_SESSION['datestamp']);
            }

            foreach ($updatedValues as $key=>$value)
            {
                $val = (is_null($value) ? NULL : $value['value']);
                $type = (is_null($value) ? NULL : $value['type']);

                // Clean up the values to cope with database storage requirements
                switch($type)
                {
                    case 'D': //DATE
                        if (trim($val)=='') {
                            $val=NULL;  // since some databases can't store blanks in date fields
                        }
                        // otherwise will already be in yyyy-mm-dd format after ProcessCurrentResponses()
                        break;
                    case 'N': //NUMERICAL QUESTION TYPE
                    case 'K': //MULTIPLE NUMERICAL QUESTION
                        if (trim($val)=='') {
                            $val=NULL;  // since some databases can't store blanks in numerical inputs
                        }
                        break;
                    default:
                        break;
                }

                if (is_null($val))
                {
                    $setter[] = db_quote_id($key) . "=NULL";
                }
                else
                {
                    $setter[] = db_quote_id($key) . "=" . db_quoteall($val);
                }
            }
            $query .= implode(', ', $setter);
            $query .= " WHERE ID=";
            
            if (isset($_SESSION['srid']) && $this->surveyOptions['active'])
            {
                $query .= $_SESSION['srid'];

                if (!db_execute_assoc($query))
                {
                    echo submitfailed($connect->ErrorMsg());

                    if (($this->debugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY) {
                        $message .= 'Error in SQL update: '. $connect->ErrorMsg() . '<br/>';
                    }
                }
                if ($finished)
                {
                    // Delete the save control record if successfully finalize the submission
                    $query = "DELETE FROM ".db_table_name("saved_control")." where srid=".$_SESSION['srid'].' and sid='.$this->sid;
                    $connect->Execute($query);   // Checked

                    if (($this->debugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY) {
                        $message .= ';<br/>'.$query;
                    }
                    
                    // Check Quotas
                    $bQuotaMatched = false;
                    $aQuotas = check_quota('return', $this->sid);
                    if ($aQuotas !== false)
                    {
                        if ($aQuotas != false)
                        {
                            foreach ($aQuotas as $aQuota)
                            {
                                if (isset($aQuota['status']) && $aQuota['status'] == 'matched') {
                                    $bQuotaMatched = true;
                                }
                            }
                        }
                    }
                    if ($bQuotaMatched)
                    {
                        check_quota('enforce',$this->sid);  // will create a page and quit.
                    }

                    // Save Timings if needed
                    if ($this->surveyOptions['savetimings']) {
                        set_answer_time();
                    }
                }
                else if ($this->surveyOptions['allowsave'] && isset($_SESSION['scid']))
                {
                    $connect->Execute("UPDATE " . db_table_name("saved_control") . " SET saved_thisstep=" . db_quoteall($thisstep) . " where scid=" . $_SESSION['scid']);  // Checked
                }
            }
            if (($this->debugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY) {
                $message .= $query;
            }
        }
        return $message;
    }

    static function GetLastMoveResult()
    {
        $LEM =& LimeExpressionManager::singleton();
        return (isset($LEM->lastMoveResult) ? $LEM->lastMoveResult : NULL);
    }

    /**
     * Jump to a specific question or group sequence.  If jumping forward, it re-validates everything in between
     * @param <type> $seq
     * @param <type> $force - if true, then skip validation of current group (e.g. will jump even if there are errors)
     * @param <type> $preview - if true, then treat this group/question as relevant, even if it is not, so that it can be displayed
     * @return <type>
     */
    static function JumpTo($seq,$preview=false,$processPOST=true,$force=false) {
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();

        $LEM->ParseResultCache=array();    // to avoid running same test more than once for a given group
        --$seq; // convert to 0-based numbering

        switch ($LEM->surveyMode)
        {
            case 'survey':
                // This only happens if saving data so far, so don't want to submit it, just validate and return
                $startingGroup = $LEM->currentGroupSeq;
                $LEM->StartProcessingPage(true);
                if ($processPOST) {
                    $updatedValues=$LEM->ProcessCurrentResponses();
                }
                else  {
                    $updatedValues = array();
                }
                $message = '';

                $LEM->currentQset = array();    // reset active list of questions
                $result = $LEM->_ValidateSurvey();
                $message .= $result['message'];
                $updatedValues = array_merge($updatedValues,$result['updatedValues']);
                $finished=false;
                $message .= $LEM->_UpdateValuesInDatabase($updatedValues,$finished);
                $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                $LEM->lastMoveResult = array(
                    'finished'=>$finished,
                    'message'=>$message,
                    'gseq'=>1,
                    'seq'=>1,
                    'mandViolation'=>$result['mandViolation'],
                    'valid'=>$result['valid'],
                    'unansweredSQs'=>$result['unansweredSQs'],
                    'invalidSQs'=>$result['invalidSQs'],
                );
                return $LEM->lastMoveResult;
                break;
            case 'group':
                // First validate the current group
                $LEM->StartProcessingPage();
                if ($processPOST) {
                    $updatedValues=$LEM->ProcessCurrentResponses();
                }
                else  {
                    $updatedValues = array();
                }
                $message = '';
                if (!$force && $LEM->currentGroupSeq != -1 && $seq > $LEM->currentGroupSeq) // only re-validate if jumping forward
                {
                    $result = $LEM->_ValidateGroup($LEM->currentGroupSeq);
                    $message .= $result['message'];
                    $updatedValues = array_merge($updatedValues,$result['updatedValues']);
                    if (!is_null($result) && ($result['mandViolation'] || !$result['valid']))
                    {
                        // redisplay the current group
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>false,
                            'message'=>$message,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentGroupSeq,
                            'mandViolation'=>$result['mandViolation'],
                            'valid'=>$result['valid'],
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                        return $LEM->lastMoveResult;
                    }
                }
                if ($seq <= $LEM->currentGroupSeq || $preview) {
                    $LEM->currentGroupSeq = $seq-1; // Try to jump to the requested group, but navigate to next if needed
                }
                while (true)
                {
                    $LEM->currentQset = array();    // reset active list of questions
                    if (++$LEM->currentGroupSeq >= $LEM->numGroups)
                    {
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,true);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>true,
                            'message'=>$message,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentGroupSeq,
                            'mandViolation'=>(isset($result['mandViolation']) ? $result['mandViolation'] : false),
                            'valid'=>(isset($result['valid']) ? $result['valid'] : false),
                            'unansweredSQs'=>(isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                            'invalidSQs'=>(isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                        );
                        return $LEM->lastMoveResult;
                    }

                    $result = $LEM->_ValidateGroup($LEM->currentGroupSeq);
                    if (is_null($result)) {
                        return NULL;    // invalid group - either bad number, or no questions within it
                    }
                    $message .= $result['message'];
                    $updatedValues = array_merge($updatedValues,$result['updatedValues']);
                    if (!$preview && (!$result['relevant'] || $result['hidden']))
                    {
                        // then skip this group - assume already saved?
                        continue;
                    }
                    else if (!($result['mandViolation'] || !$result['valid']) && $LEM->currentGroupSeq < $seq) {
                        // if there is a violation while moving forward, need to stop and ask that set of questions
                        // if there are no violations, can skip this group as long as changed values are saved.
                        continue;
                    }
                    else
                    {
                        // display new group
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>false,
                            'message'=>$message,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentGroupSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                        return $LEM->lastMoveResult;
                    }
                }
                break;
            case 'question':
                $LEM->StartProcessingPage();
                if ($processPOST) {
                    $updatedValues=$LEM->ProcessCurrentResponses();
                }
                else  {
                    $updatedValues = array();
                }
                $message = '';
                if (!$force && $LEM->currentQuestionSeq != -1 && $seq > $LEM->currentQuestionSeq)
                {
                    $result = $LEM->_ValidateQuestion($LEM->currentQuestionSeq);
                    $message .= $result['message'];
                    $updatedValues = array_merge($updatedValues,$result['updatedValues']);
                    if ($result['mandViolation'] || !$result['valid'])
                    {
                        // redisplay the current question
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>false,
                            'message'=>$message,
                            'qseq'=>$LEM->currentQuestionSeq,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentQuestionSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                        return $LEM->lastMoveResult;
                    }
                }
                if ($seq <= $LEM->currentQuestionSeq || $preview) {
                    $LEM->currentQuestionSeq = $seq-1; // Try to jump to the requested group, but navigate to next if needed
                }
                while (true)
                {
                    $LEM->currentQset = array();    // reset active list of questions
                    if (++$LEM->currentQuestionSeq >= $LEM->numQuestions)
                    {
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,true);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>true,
                            'message'=>$message,
                            'qseq'=>$LEM->currentQuestionSeq,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentQuestionSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>(isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                            'invalidSQs'=>(isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                        );
                        return $LEM->lastMoveResult;
                    }

                    // Set certain variables normally set by StartProcessingGroup()
                    $LEM->groupRelevanceInfo=array();   // TODO only important thing from StartProcessingGroup?
                    $qInfo = $LEM->questionSeq2relevance[$LEM->currentQuestionSeq];
                    $LEM->currentQID=$qInfo['qid'];
                    $LEM->currentGroupSeq=$qInfo['gseq'];
                    $LEM->groupNum=$qInfo['gid'];
                    if ($LEM->currentGroupSeq > $LEM->maxGroupSeq) {
                        $LEM->maxGroupSeq = $LEM->currentGroupSeq;
                    }

                    $LEM->ProcessAllNeededRelevance($LEM->currentQuestionSeq);
                    $LEM->_CreateSubQLevelRelevanceAndValidationEqns($LEM->currentQuestionSeq);
                    $result = $LEM->_ValidateQuestion($LEM->currentQuestionSeq);
                    $message .= $result['message'];
                    $updatedValues = array_merge($updatedValues,$result['updatedValues']);

                    if (!$preview && (!$result['relevant'] || $result['hidden']))
                    {
                        // then skip this question
                        continue;
                    }
                    else if (!($result['mandViolation'] || !$result['valid']) && $LEM->currentQuestionSeq < $seq) {
                        // if there is a violation while moving forward, need to stop and ask that set of questions
                        // if there are no violations, can skip this group as long as changed values are saved.
                        continue;
                    }
                    else
                    {
                        // display new question
                        $message .= $LEM->_UpdateValuesInDatabase($updatedValues,false);
                        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
                        $LEM->lastMoveResult = array(
                            'finished'=>false,
                            'message'=>$message,
                            'qseq'=>$LEM->currentQuestionSeq,
                            'gseq'=>$LEM->currentGroupSeq,
                            'seq'=>$LEM->currentQuestionSeq,
                            'mandViolation'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['mandViolation'] : false),
                            'valid'=> (($LEM->maxGroupSeq > $LEM->currentGroupSeq) ? $result['valid'] : false),
                            'unansweredSQs'=>$result['unansweredSQs'],
                            'invalidSQs'=>$result['invalidSQs'],
                        );
                        return $LEM->lastMoveResult;
                    }
                }
                break;
        }
    }

    /**
     * Check the entire survey
     * @return <type>
     */
    private function _ValidateSurvey()
    {
        $LEM =& $this;

        $message = '';
        $srel=false;
        $shidden=true;
        $smandViolation=false;
        $svalid=true;
        $unansweredSQs = array();
        $invalidSQs = array();
        $updatedValues = array();

        ///////////////////////////////////////////////////////
        // CHECK EACH GROUP, AND SET SURVEY-LEVEL PROPERTIES //
        ///////////////////////////////////////////////////////
        for ($i=0;$i<$LEM->numGroups;++$i) {
            $LEM->currentGroupSeq=$i;
            $gStatus = $LEM->_ValidateGroup($i);
            if (is_null($gStatus)) {
                continue;   // invalid group, so skip it
            }
            $message .= $gStatus['message'];

            if ($gStatus['relevant']) {
                $srel = true;
            }
            if (!$gStatus['hidden']) {
                $shidden=false;
            }
            if ($gStatus['mandViolation']) {
                $smandViolation = true;
            }
            if (!$gStatus['valid']) {
                $svalid=false;
            }

            if (strlen($gStatus['unansweredSQs']) > 0) {
                $unansweredSQs = array_merge($unansweredSQs, explode('|',$gStatus['unansweredSQs']));
            }
            if (strlen($gStatus['invalidSQs']) > 0) {
                $invalidSQs = array_merge($invalidSQs, explode('|',$gStatus['invalidSQs']));
            }
            $updatedValues = array_merge($updatedValues, $gStatus['updatedValues']);
            // array_merge destroys the key, so do it manually
            foreach ($gStatus['qset'] as $key=>$value) {
                $LEM->currentQset[$key] = $value;
            }

            $LEM->FinishProcessingGroup();
        }
        return array(
            'relevant' => $srel,
            'hidden' => $shidden,
            'mandViolation' => $smandViolation,
            'valid' => $svalid,
            'message' => $message,
            'unansweredSQs' => implode('|',$unansweredSQs),
            'invalidSQs' => implode('|',$invalidSQs),
            'updatedValues' => $updatedValues,
            'seq'=>1,
        );
    }

    /**
     * Check a group and all of the questions it contains
     * @param <type> $groupSeq - the index-0 sequence number for this group
     * @return <array> - detailed information about this group
     */
    function _ValidateGroup($groupSeq)
    {
        $LEM =& $this;

        if ($groupSeq < 0 || $groupSeq >= $LEM->numGroups) {
            return NULL;    // TODO - what is desired behavior?
        }
        $groupSeqInfo = (isset($LEM->groupSeqInfo[$groupSeq]) ? $LEM->groupSeqInfo[$groupSeq] : NULL);
        if (is_null($groupSeqInfo)) {
            // then there are no questions in this group
            return NULL;
        }
        $qInfo = $LEM->questionSeq2relevance[$groupSeqInfo['qstart']];
        $gid = $qInfo['gid'];
        $LEM->StartProcessingGroup($gid, $LEM->surveyOptions['anonymized'], $LEM->sid); // analyze the data we have about this group

        $grel=false;  // assume irrelevant until find a relevant question
        $ghidden=true;   // assume hidden until find a non-hidden question.  If there are no relevant questions on this page, $ghidden will stay true
        $gmandViolation=false;  // assume that the group contains no manditory questions that have not been fully answered
        $gvalid=true;   // assume valid until discover otherwise
        $debug_message = '';
        $messages = array();
        $currentQset = array();
        $unansweredSQs = array();
        $invalidSQs = array();
        $updatedValues = array();

        $gRelInfo = $LEM->gRelInfo[$groupSeq];

        /////////////////////////////////////////////////////////
        // CHECK EACH QUESTION, AND SET GROUP-LEVEL PROPERTIES //
        /////////////////////////////////////////////////////////
        for ($i=$groupSeqInfo['qstart'];$i<=$groupSeqInfo['qend']; ++$i)
        {
            $qStatus = $LEM->_ValidateQuestion($i);

            $updatedValues = array_merge($updatedValues,$qStatus['updatedValues']);

            if ($qStatus['relevant']==true) {
                $grel = $gRelInfo['result'];    // true;   // at least one question relevant
            }
            if ($qStatus['hidden']==false) {
                $ghidden=false; // at least one question is visible
            }
            if ($qStatus['mandViolation']==true) {
                $gmandViolation=true;   // at least one relevant question fails mandatory test
            }
            if ($qStatus['valid']==false) {
                $gvalid=false;  // at least one question fails validity constraints
            }
            $currentQset[$qStatus['info']['qid']] = $qStatus;
            $messages[] = $qStatus['message'];
            if (strlen($qStatus['unansweredSQs']) > 0) {
                $unansweredSQs[] = $qStatus['unansweredSQs'];
            }
            if (strlen($qStatus['invalidSQs']) > 0) {
                $invalidSQs[] = $qStatus['invalidSQs'];
            }
        }
        $unansweredSQList = implode('|',$unansweredSQs);
        $invalidSQList = implode('|',$invalidSQs);

        /////////////////////////////////////////////////////////
        // OPTIONALLY DISPLAY (DETAILED) DEBUGGING INFORMATION //
        /////////////////////////////////////////////////////////
        if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY)
        {
            $debug_message .= '<br/>[G#' . $LEM->currentGroupSeq . ']'
                    . '[' . $groupSeqInfo['qstart'] . '-' . $groupSeqInfo['qend'] . ']'
                    . "[<a href='../../../admin/admin.php?action=orderquestions&sid={$LEM->sid}&gid=$gid'>"
                    .  'GID:' . $gid . "</a>]:  "
                    . ($grel ? 'relevant ' : " <span style='color:red'>irrelevant</span> ")
                    . (($gRelInfo['eqn'] != '') ? $gRelInfo['prettyPrint'] : '')
                    . (($ghidden && $grel) ? " <span style='color:red'>always-hidden</span> " : ' ')
                    . ($gmandViolation ? " <span style='color:red'>(missing a relevant mandatory)</span> " : ' ')
                    . ($gvalid ? '' : " <span style='color:red'>(fails at least one validation rule)</span> ")
                    . "<br/>\n"
                    . implode('', $messages);

            if ($grel == true)
            {
                if (!$gvalid)
                {
                    if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_DETAIL) == LEM_DEBUG_VALIDATION_DETAIL)
                    {
                        $debug_message .= "**At least one relevant question was invalid, so re-show this group<br/>\n";
                        $debug_message .= "**Validity Violators: " . implode(', ', explode('|',$invalidSQList)) . "<br/>\n";
                    }
                }
                if ($gmandViolation)
                {
                    if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_DETAIL) == LEM_DEBUG_VALIDATION_DETAIL)
                    {
                        $debug_message .= "**At least one relevant question was mandatory but unanswered, so re-show this group<br/>\n";
                        $debug_message .= '**Mandatory Violators: ' . implode(', ', explode('|',$unansweredSQList)). "<br/>\n";
                    }
                }

                if ($ghidden == true)
                {
                    if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_DETAIL) == LEM_DEBUG_VALIDATION_DETAIL)
                    {
                        $debug_message .= '** Page is relevant but hidden, so NULL irrelevant values and save relevant Equation results:</br>';
                    }
                }
            }
            else
            {
                if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_DETAIL) == LEM_DEBUG_VALIDATION_DETAIL)
                {
                    $debug_message .= '** Page is irrelevant, so NULL all questions in this group<br/>';
                }
            }
        }

        //////////////////////////////////////////////////////////////////////////
        // STORE METADATA NEEDED FOR SUBSEQUENT PROCESSING AND DISPLAY PURPOSES //
        //////////////////////////////////////////////////////////////////////////
        $currentGroupInfo = array(
            'gid' => $gid,
            'gseq' => $groupSeq,
            'message' => $debug_message,
            'relevant' => $grel,
            'hidden' => $ghidden,
            'mandViolation' => $gmandViolation,
            'valid' => $gvalid,
            'qset' => $currentQset,
            'unansweredSQs' => $unansweredSQList,
            'invalidSQs' => $invalidSQList,
            'updatedValues' => $updatedValues,
        );

        ////////////////////////////////////////////////////////
        // STORE METADATA NEEDED TO GENERATE NAVIGATION INDEX //
        ////////////////////////////////////////////////////////
        $LEM->indexGseq[$groupSeq] = array(
            'gtext' => $LEM->gseq2info[$groupSeq]['description'],
            'gname' => $LEM->gseq2info[$groupSeq]['group_name'],
            'gid' => $LEM->gseq2info[$groupSeq]['gid'],
            'anyUnanswered' => ((strlen($unansweredSQList) > 0) ? true : false),
            'anyErrors' => (($gmandViolation || !$gvalid) ? true : false),
            'valid' => $gvalid,
            'mandViolation' => $gmandViolation,
            'show' => (($grel && !$ghidden) ? true : false),
        );

        $LEM->gid2relevanceStatus[$gid] = $grel;

        return $currentGroupInfo;
    }



    /**
     * For the current set of questions (whether in survey, gtoup, or question-by-question mode), assesses the following:
     * (a) mandatory - if so, then all relevant sub-questions must be answered (e.g. pay attention to array_filter and array_filter_exclude)
     * (b) always-hidden
     * (c) relevance status - including sub-question-level relevance
     * (d) answered - if $_SESSION[sgqa]=='' or NULL, then it is not answered
     * (e) validity - whether relevant questions pass their validity tests
     * @param <type> $questionSeq - the 0-index sequence number for this question
     * @return <array> of information about this question and its sub-questions
     */

    function _ValidateQuestion($questionSeq)
    {
        $LEM =& $this;
        $qInfo = $LEM->questionSeq2relevance[$questionSeq];   // this array is by group and question sequence
        $qrel=true;   // assume relevant unless discover otherwise
        $prettyPrintRelEqn='';    //  assume no relevance eqn by default
        $qid=$qInfo['qid'];
        $gid=$qInfo['gid'];
        $qhidden = $qInfo['hidden'];
        $debug_qmessage='';

        ///////////////////////////
        // IS QUESTION RELEVANT? //
        ///////////////////////////
        if (!isset($qInfo['relevance']) || $qInfo['relevance'] == '')
        {
            $relevanceEqn = 1;
        }
        else
        {
            $relevanceEqn = $qInfo['relevance'];
        }

        // cache results
        $relevanceEqn = htmlspecialchars_decode($relevanceEqn,ENT_QUOTES);  // TODO is this needed?
        if (isset($LEM->ParseResultCache[$relevanceEqn]))
        {
            $qrel = $LEM->ParseResultCache[$relevanceEqn]['result'];
            if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
            {
                $prettyPrintRelEqn = $LEM->ParseResultCache[$relevanceEqn]['prettyPrint'];
            }
        }
        else
        {
            $qrel = $LEM->em->ProcessBooleanExpression($relevanceEqn,$qInfo['gseq'], $qInfo['qseq']);    // assumes safer to re-process relevance and not trust POST values
            $hasErrors = $LEM->em->HasErrors();
            if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
            {
                $prettyPrintRelEqn = $LEM->em->GetPrettyPrintString();
            }
            $LEM->ParseResultCache[$relevanceEqn] = array(
                'result'=>$qrel,
                'prettyPrint'=>$prettyPrintRelEqn,
                'hasErrors'=>$hasErrors,
                );            
        }

        //////////////////////////////////////
        // ARE ANY SUB-QUESTION IRRELEVANT? //
        //////////////////////////////////////
        // identify the relevant subquestions (array_filter and array_filter_exclude may make some irrelevant)
        $relevantSQs=array();
        $irrelevantSQs=array();
        $prettyPrintSQRelEqns=array();
        $prettyPrintSQRelEqn='';
        $prettyPrintValidTip='';
        if (!$qrel)
        {
            // All sub-questions are irrelevant
            $irrelevantSQs = explode('|', $LEM->qid2code[$qid]);
        }
        else
        {
            // Check filter status to determine which subquestions are relevant
            if ($qInfo['type'] == 'X') {
                $sgqas = array();   // Boilerplate questions can be ignored
            }
            else {
                $sgqas = explode('|',$LEM->qid2code[$qid]);
            }
            foreach ($sgqas as $sgqa)
            {
                // for each subq, see if it is part of an array_filter or array_filter_exclude
                if (!isset($LEM->subQrelInfo[$qid]))
                {
                    $relevantSQs[] = $sgqa;
                    continue;
                }
                $foundSQrelevance=false;
                foreach ($LEM->subQrelInfo[$qid] as $sq)
                {
                    switch ($sq['qtype'])
                    {
                        case '1':   //Array (Flexible Labels) dual scale
                            if ($sgqa == ($sq['rowdivid'] . '#0') || $sgqa == ($sq['rowdivid'] . '#1')) {
                                $foundSQrelevance=true;
                                if (isset($LEM->ParseResultCache[$sq['eqn']]))
                                {
                                    $sqrel = $LEM->ParseResultCache[$sq['eqn']]['result'];
                                    if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
                                    {
                                        $prettyPrintSQRelEqns[$sq['rowdivid']] = $LEM->ParseResultCache[$sq['eqn']]['prettyPrint'];
                                    }
                                }
                                else
                                {
                                    $stringToParse = htmlspecialchars_decode($sq['eqn'],ENT_QUOTES);  // TODO is this needed?
                                    $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['gseq'], $qInfo['qseq']);
                                    $hasErrors = $LEM->em->HasErrors();
                                    if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
                                    {
                                        $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                        $prettyPrintSQRelEqns[$sq['rowdivid']] = $prettyPrintSQRelEqn;
                                    }
                                    $LEM->ParseResultCache[$sq['eqn']] = array(
                                        'result'=>$sqrel,
                                        'prettyPrint'=>$prettyPrintSQRelEqn,
                                        'hasErrors'=>$hasErrors,
                                        );
                                }
                                if ($sqrel)
                                {
                                    $relevantSQs[] = $sgqa;
                                }
                                else
                                {
                                    $irrelevantSQs[] = $sgqa;
                                }
                            }
                            break;
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case ';': //ARRAY (Multi Flexi) Text
                            if (preg_match('/^' . $sq['rowdivid'] . '/', $sgqa))
                            {
                                $foundSQrelevance=true;
                                if (isset($LEM->ParseResultCache[$sq['eqn']]))
                                {
                                    $sqrel = $LEM->ParseResultCache[$sq['eqn']]['result'];
                                    if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
                                    {
                                        $prettyPrintSQRelEqns[$sq['rowdivid']] = $LEM->ParseResultCache[$sq['eqn']]['prettyPrint'];
                                    }
                                }
                                else
                                {
                                    $stringToParse = htmlspecialchars_decode($sq['eqn'],ENT_QUOTES);  // TODO is this needed?
                                    $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['gseq'], $qInfo['qseq']);
                                    $hasErrors = $LEM->em->HasErrors();
                                    if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
                                    {
                                        $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                        $prettyPrintSQRelEqns[$sq['rowdivid']] = $prettyPrintSQRelEqn;
                                    }
                                    $LEM->ParseResultCache[$sq['eqn']] = array(
                                        'result'=>$sqrel,
                                        'prettyPrint'=>$prettyPrintSQRelEqn,
                                        'hasErrors'=>$hasErrors,
                                        );
                                }
                                if ($sqrel)
                                {
                                    $relevantSQs[] = $sgqa;
                                }
                                else
                                {
                                    $irrelevantSQs[] = $sgqa;
                                }
                            }
                        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                        case 'F': //ARRAY (Flexible) - Row Format
                        case 'M': //Multiple choice checkbox
                        case 'P': //Multiple choice with comments checkbox + text
                            // Note, for M and P, Mandatory should mean that at least one answer was picked - not that all were checked
                            if ($sgqa == $sq['rowdivid'] || $sgqa == ($sq['rowdivid'] . 'comment'))     // to catch case 'P'
                            {
                                $foundSQrelevance=true;
                                if (isset($LEM->ParseResultCache[$sq['eqn']]))
                                {
                                    $sqrel = $LEM->ParseResultCache[$sq['eqn']]['result'];
                                    if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
                                    {
                                        $prettyPrintSQRelEqns[$sq['rowdivid']] = $LEM->ParseResultCache[$sq['eqn']]['prettyPrint'];
                                    }
                                }
                                else
                                {
                                    $stringToParse = htmlspecialchars_decode($sq['eqn'],ENT_QUOTES);  // TODO is this needed?
                                    $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['gseq'], $qInfo['qseq']);
                                    $hasErrors = $LEM->em->HasErrors();
                                    if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
                                    {
                                        $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                        $prettyPrintSQRelEqns[$sq['rowdivid']] = $prettyPrintSQRelEqn;
                                    }
                                    $LEM->ParseResultCache[$sq['eqn']] = array(
                                        'result'=>$sqrel,
                                        'prettyPrint'=>$prettyPrintSQRelEqn,
                                        'hasErrors'=>$hasErrors,
                                        );
                                }
                                if ($sqrel)
                                {
                                    $relevantSQs[] = $sgqa;
                                }
                                else
                                {
                                    $irrelevantSQs[] = $sgqa;
                                }
                            }
                            break;
                        case 'L': //LIST drop-down/radio-button list
                            if ($sgqa == ($sq['sgqa'] . 'other') && $sgqa == $sq['rowdivid'])   // don't do sub-q level validition to main question, just to other option
                            {
                                $foundSQrelevance=true;
                                if (isset($LEM->ParseResultCache[$sq['eqn']]))
                                {
                                    $sqrel = $LEM->ParseResultCache[$sq['eqn']]['result'];
                                    if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
                                    {
                                        $prettyPrintSQRelEqns[$sq['rowdivid']] = $LEM->ParseResultCache[$sq['eqn']]['prettyPrint'];
                                    }
                                }
                                else
                                {
                                    $stringToParse = htmlspecialchars_decode($sq['eqn'],ENT_QUOTES);  // TODO is this needed?
                                    $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['gseq'], $qInfo['qseq']);
                                    $hasErrors = $LEM->em->HasErrors();
                                    if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
                                    {
                                        $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                        $prettyPrintSQRelEqns[$sq['rowdivid']] = $prettyPrintSQRelEqn;
                                    }
                                    $LEM->ParseResultCache[$sq['eqn']] = array(
                                        'result'=>$sqrel,
                                        'prettyPrint'=>$prettyPrintSQRelEqn,
                                        'hasErrors'=>$hasErrors,
                                        );
                                }
                                if ($sqrel)
                                {
                                    $relevantSQs[] = $sgqa;
                                }
                                else
                                {
                                    $irrelevantSQs[] = $sgqa;
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }   // end foreach($LEM->subQrelInfo) [checking array-filters]
                if (!$foundSQrelevance)
                {
                    // then this question is relevant
                    $relevantSQs[] = $sgqa; // TODO - check this
                }
            }
        } // end of processing relevant question for sub-questions

        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)
        {
            // TODO - why is array_unique needed here?
//            $prettyPrintSQRelEqns = array_unique($prettyPrintSQRelEqns);
        }
        // These array_unique only apply to array_filter of type L (list)
        $relevantSQs = array_unique($relevantSQs);
        $irrelevantSQs = array_unique($irrelevantSQs);

        ////////////////////////////////////////////////////////////////////
        // WHICH RELEVANT, VISIBLE (SUB)-QUESTIONS HAVEN'T BEEN ANSWERED? //
        ////////////////////////////////////////////////////////////////////
        // check that all mandatories have been fully answered (but don't require answers for sub-questions that are irrelevant
        $unansweredSQs = array();   // list of sub-questions that weren't answered
        foreach ($relevantSQs as $sgqa)
        {
            if (($qInfo['type'] != '*') && (!isset($_SESSION[$sgqa]) || ($_SESSION[$sgqa] == '' || is_null($_SESSION[$sgqa]))))
            {
                // then a relevant, visible, mandatory question hasn't been answered
                // Equations are ignored, since set automatically
                $unansweredSQs[] = $sgqa;
            }
        }

        //////////////////////////////////////////////
        // DETECT ANY VIOLATIONS OF MANDATORY RULES //
        //////////////////////////////////////////////
        $qmandViolation = false;    // assume there is no mandatory violation until discover otherwise
        $mandatoryTip = '';
        if ($qrel && !$qhidden && ($qInfo['mandatory'] == 'Y'))
        {
            $mandatoryTip = "<strong><br /><span class='errormandatory'>".$LEM->gT('This question is mandatory').'.  ';
            switch ($qInfo['type'])
            {
                case 'M':
                case 'P':
                case '!': //List - dropdown
                case 'L': //LIST drop-down/radio-button list
                    // If at least one checkbox is checked, we're OK
                    if (count($relevantSQs) > 0 && (count($relevantSQs) == count($unansweredSQs)))
                    {
                        $qmandViolation = true;
                    }
                    $mandatoryTip .= $LEM->gT('Please check at least one item.');
                    if ($qInfo['other']=='Y')
                    {
                        $qattr = isset($LEM->qattr[$qid]) ? $LEM->qattr[$qid] : array();
                        if (isset($qattr['other_replace_text']) && trim($qattr['other_replace_text']) != '') {
                            $othertext = trim($qattr['other_replace_text']);
                        }
                        else {
                            $othertext = $LEM->gT('Other:');
                        }
                        $mandatoryTip .= "<br />\n".sprintf($LEM->gT("If you choose '%s' you must provide a description."), $othertext);
                    }
                    break;
                case 'X':   // Boilerplate can never be mandatory
                case '*':   // Equation is auto-computed, so can't violate mandatory rules
                    break;
                case 'A':
                case 'B':
                case 'C':
                case 'Q':
                case 'K':
                case 'E':
                case 'F':
                case 'J':
                case 'H':
                case ':':
                case ';':
                    // In general, if any relevant questions aren't answered, then it violates the mandatory rule
                    if (count($unansweredSQs) > 0)
                    {
                        $qmandViolation = true; // TODO - what about 'other'?
                    }
                    $mandatoryTip .= $LEM->gT('Please complete all parts').'.';
                    break;
                case '1':
                    if (count($unansweredSQs) > 0)
                    {
                        $qmandViolation = true; // TODO - what about 'other'?
                    }
                    $mandatoryTip .= $LEM->gT('Please check the items').'.';
                    break;
                case 'R':
                    if (count($unansweredSQs) > 0)
                    {
                        $qmandViolation = true; // TODO - what about 'other'?
                    }
                    $mandatoryTip .= $LEM->gT('Please rank all items').'.';
                    break;
                default:
                    if (count($unansweredSQs) > 0)
                    {
                        $qmandViolation = true; 
                    }
                    $mandatoryTip .= $LEM->gT('Please answer this question').'.';
                    break;
            }
            $mandatoryTip .= "</span></strong>\n";
        }

        ///////////////////////////////////////////////
        // DETECT ANY VIOLATIONS OF VALIDATION RULES //
        ///////////////////////////////////////////////
        $qvalid=true;   // assume valid unless discover otherwise
        $hasValidationEqn=false;
        $prettyPrintValidEqn='';    //  assume no validation eqn by default
        $validationEqn='';
        $validationJS='';       // assume can't generate JavaScript to validate equation
        $validTip='';           // default is none
        // TODO - when there are multiple questions which each use the same validation, need to know which sub-questions are invalid
        if (isset($LEM->qid2validationEqn[$qid]))
        {
            $hasValidationEqn=true;
            if ($qrel && !$qhidden)
            {
                $validationEqn = $LEM->qid2validationEqn[$qid]['eqn'];
                $qvalid = $LEM->em->ProcessBooleanExpression($validationEqn,$qInfo['gseq'], $qInfo['qseq']);
                $hasErrors = $LEM->em->HasErrors();
                if (!$hasErrors)
                {
                    $validationJS = $LEM->em->GetJavaScriptEquivalentOfExpression();
                }
                $prettyPrintValidEqn = $validationEqn;
                if ((($this->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX))
                {
                    $prettyPrintValidEqn = $LEM->em->GetPrettyPrintString();
                }

                $stringToParse = implode('<br/>',$LEM->qid2validationEqn[$qid]['tips']);
                $prettyPrintValidTip = $stringToParse;
                $validTip = $LEM->ProcessString($stringToParse, $qid,NULL,false,1,1,false,false);
                // TODO check for errors?
                if ((($this->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX))
                {
                    $prettyPrintValidTip = $LEM->GetLastPrettyPrintExpression();
                }
            }
            else
            {
                if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_DETAIL) == LEM_DEBUG_VALIDATION_DETAIL)
                {
                    $prettyPrintValidEqn = 'Question is Irrelevant, so no need to further validate it';
                }
            }
        }
        if (!$qvalid)
        {
            $invalidSQs = $LEM->qid2code[$qid]; // TODO - currently invalidates all - should only invalidate those that truly fail validation rules.
        }

        /////////////////////////////////////////////////////////
        // OPTIONALLY DISPLAY (DETAILED) DEBUGGING INFORMATION //
        /////////////////////////////////////////////////////////
        if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY)
        {
            $debug_qmessage .= '--[Q#' . $qInfo['qseq'] . ']'
                . "[<a href='../../../admin/admin.php?sid={$LEM->sid}&gid=$gid&qid=$qid'>"
                . 'QID:'. $qid . '</a>][' . $qInfo['type'] . ']: '
                . ($qrel ? 'relevant' : " <span style='color:red'>irrelevant</span> ")
                . ($qhidden ? " <span style='color:red'>always-hidden</span> " : ' ')
                . (($qInfo['mandatory'] == 'Y')? ' mandatory' : ' ')
                . (($hasValidationEqn) ? (!$qvalid ? " <span style='color:red'>(fails validation rule)</span> " : ' valid') : '')
                . ($qmandViolation ? " <span style='color:red'>(missing a relevant mandatory)</span> " : ' ')
                . $prettyPrintRelEqn
                . "<br/>\n";

            if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_DETAIL) == LEM_DEBUG_VALIDATION_DETAIL)
            {
                if ($mandatoryTip != '')
                {
                    $debug_qmessage .= '----Mandatory Tip: ' . FlattenText($mandatoryTip) . "<br/>\n";
                }

                if ($prettyPrintValidTip != '')
                {
                    $debug_qmessage .= '----Pretty Validation Tip: <br/>' . $prettyPrintValidTip . "<br/>\n";
                }
                if ($validTip != '')
                {
                    $debug_qmessage .= '----Validation Tip: <br/>' . $validTip . "<br/>\n";
                }

                if ($prettyPrintValidEqn != '')
                {
                    $debug_qmessage .= '----Validation Eqn: ' . $prettyPrintValidEqn . "<br/>\n";
                }
                if ($validationJS != '')
                {
                    $debug_qmessage .= '----Validation JavaScript: ' . $validationJS . "<br/>\n";
                }

                // what are the database question codes for this question?
                $subQList = '{' . implode('}, {', explode('|',$LEM->qid2code[$qid])) . '}';
                // pretty-print them
                $LEM->ProcessString($subQList, $qid,NULL,false,1,1,false,false);
                $prettyPrintSubQList = $LEM->GetLastPrettyPrintExpression();
                $debug_qmessage .= '----SubQs=> ' . $prettyPrintSubQList . "<br/>\n";

                if (count($prettyPrintSQRelEqns) > 0)
                {
                    $debug_qmessage .= "----Array Filters Applied:<br/>\n";
                    foreach ($prettyPrintSQRelEqns as $key => $value)
                    {
                        $debug_qmessage .= '------' . $key . ': ' . $value . "<br/>\n";
                    }
                    $debug_qmessage .= "<br/>\n";
                }

                if (count($relevantSQs) > 0)
                {
                    $subQList = '{' . implode('}, {', $relevantSQs) . '}';
                    // pretty-print them
                    $LEM->ProcessString($subQList, $qid,NULL,false,1,1,false,false);
                    $prettyPrintSubQList = $LEM->GetLastPrettyPrintExpression();
                    $debug_qmessage .= '----Relevant SubQs: ' . $prettyPrintSubQList . "<br/>\n";
                }

                if (count($irrelevantSQs) > 0)
                {
                    $subQList = '{' . implode('}, {', $irrelevantSQs) . '}';
                    // pretty-print them
                    $LEM->ProcessString($subQList, $qid,NULL,false,1,1,false,false);
                    $prettyPrintSubQList = $LEM->GetLastPrettyPrintExpression();
                    $debug_qmessage .= '----Irrelevant SubQs: ' . $prettyPrintSubQList . "<br/>\n";
                }

                // show which relevant subQs were not answered
                if (count($unansweredSQs) > 0)
                {
                    $subQList = '{' . implode('}, {', $unansweredSQs) . '}';
                    // pretty-print them
                    $LEM->ProcessString($subQList, $qid,NULL,false,1,1,false,false);
                    $prettyPrintSubQList = $LEM->GetLastPrettyPrintExpression();
                    $debug_qmessage .= '----Unanswered Relevant SubQs: ' . $prettyPrintSubQList . "<br/>\n";
                }
            }
        }

        /////////////////////////////////////////////////////////////
        // CREATE ARRAY OF VALUES THAT NEED TO BE SILENTLY UPDATED //
        /////////////////////////////////////////////////////////////
        $updatedValues=array();
        if (!$qrel)
        {
            // If not relevant, then always NULL it in the database
            $sgqas = explode('|',$LEM->qid2code[$qid]);
            foreach ($sgqas as $sgqa)
            {
                $_SESSION[$sgqa] = NULL;
                $updatedValues[$sgqa] = NULL;
            }
        }
        else if ($qInfo['hidden'] && $qInfo['type'] == '*')
        {
            // Process relevant equations, even if hidden, and write the result to the database
            $result = FlattenText($LEM->ProcessString($qInfo['eqn'], $qInfo['qid'],NULL,false,1,1,false,false));
            $sgqa = $LEM->qid2code[$qid];   // there will be only one, since Equation
            // Store the result of the Equation in the SESSION
            $_SESSION[$sgqa] = $result;
            $updatedValues[$sgqa] = array(
                'type'=>'*',
                'value'=>$result,
            );
            if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_DETAIL) == LEM_DEBUG_VALIDATION_DETAIL)
            {
                $prettyPrintEqn = $LEM->em->GetPrettyPrintString();
                $debug_qmessage .= '** Process Hidden but Relevant Equation [' . $sgqa . '](' . $prettyPrintEqn . ') => ' . $result . "<br/>\n";
            }
        }
        foreach ($irrelevantSQs as $sq)
        {
            // NULL irrelevant sub-questions
            $_SESSION[$sq] = NULL;
            $updatedValues[$sq] = NULL;
        }

        // Regardless of whether relevant or hidden, if there is a default value and $_SESSION[$sgqa] is NULL, then use the default value in $_SESSION, but don't write to database
        // Also, set this AFTER testing relevance
        $sgqas = explode('|',$LEM->qid2code[$qid]);
        foreach ($sgqas as $sgqa)
        {
            if (!is_null($LEM->knownVars[$sgqa]['default']) && !isset($_SESSION[$sgqa])) {
                $_SESSION[$sgqa] = $LEM->knownVars[$sgqa]['default'];
            }
        }

        //////////////////////////////////////////////////////////////////////////
        // STORE METADATA NEEDED FOR SUBSEQUENT PROCESSING AND DISPLAY PURPOSES //
        //////////////////////////////////////////////////////////////////////////

        $qStatus = array(
            'info' => $qInfo,   // collect all questions within the group - includes mandatory and always-hiddden status
            'relevant' => $qrel,
            'hidden' => $qInfo['hidden'],
            'relEqn' => $prettyPrintRelEqn,
            'sgqa' => $LEM->qid2code[$qid],
            'unansweredSQs' => implode('|',$unansweredSQs),
            'valid' => $qvalid,
            'validEqn' => $validationEqn,
            'prettyValidEqn' => $prettyPrintValidEqn,
            'validTip' => $validTip,
            'prettyValidTip' => $prettyPrintValidTip,
            'validJS' => $validationJS,
            'invalidSQs' => (isset($invalidSQs) ? $invalidSQs : ''),
            'relevantSQs' => implode('|',$relevantSQs),
            'irrelevantSQs' => implode('|',$irrelevantSQs),
            'subQrelEqn' => implode('<br/>',$prettyPrintSQRelEqns),
            'mandViolation' => $qmandViolation,
            'mandTip' => $mandatoryTip,
            'message' => $debug_qmessage,
            'updatedValues' => $updatedValues,
            );

        $LEM->currentQset[$qid] = $qStatus;

        ////////////////////////////////////////////////////////
        // STORE METADATA NEEDED TO GENERATE NAVIGATION INDEX //
        ////////////////////////////////////////////////////////

        $groupSeq = $qInfo['gseq'];
        $LEM->indexQseq[$questionSeq] = array(
            'qid' => $qInfo['qid'],
            'qtext' => $qInfo['qtext'],
            'qcode' => $qInfo['code'],
            'qhelp' => $qInfo['help'],
            'anyUnanswered' => ((count($unansweredSQs) > 0) ? true : false),
            'anyErrors' => (($qmandViolation || !$qvalid) ? true : false),
            'show' => (($qrel && !$qInfo['hidden']) ? true : false),
            'gseq' => $groupSeq,
            'gtext' => $LEM->gseq2info[$groupSeq]['description'],
            'gname' => $LEM->gseq2info[$groupSeq]['group_name'],
            'gid' => $LEM->gseq2info[$groupSeq]['gid'],
            'mandViolation' => $qmandViolation,
            'valid' => $qvalid,
        );

        $_SESSION['relevanceStatus'][$qid] = $qrel;

        return $qStatus;
    }

    static function GetQuestionStatus($qid)
    {
        $LEM =& LimeExpressionManager::singleton();
        if (isset($LEM->currentQset[$qid]))
        {
            return $LEM->currentQset[$qid];
        }
        return NULL;
    }

    /**
     * Get array of info needed to display the Group Index
     * @return <type>
     */
    static function GetGroupIndexInfo($gseq=NULL)
    {
        $LEM =& LimeExpressionManager::singleton();
        if (is_null($gseq)) {
            return $LEM->indexGseq;
        }
        else {
            return $LEM->indexGseq[$gseq];
        }
    }

    /**
     * Translate GID to 0-index Group Sequence number
     * @param <type> $gid
     * @return <type>
     */
    static function GetGroupSeq($gid)
    {
        $LEM =& LimeExpressionManager::singleton();
        return (isset($LEM->groupId2groupSeq[$gid]) ? $LEM->groupId2groupSeq[$gid] : -1);
    }

    /**
     * Get question sequence number from QID
     * @param <type> $qid
     * @return <type>
     */
    static function GetQuestionSeq($qid)
    {
        $LEM =& LimeExpressionManager::singleton();
        return (isset($LEM->questionId2questionSeq[$qid]) ? $LEM->questionId2questionSeq[$qid] : -1);
    }

    /**
     * Get array of info needed to display the Question Index
     * @return <type>
     */
    static function GetQuestionIndexInfo()
    {
        $LEM =& LimeExpressionManager::singleton();
        return $LEM->indexQseq;
    }

    /**
     * Return entries needed to build the navigation index
     * @param <type> $step - if specified, return a single value, otherwise return entire array
     * @return <type> - will be either question or group-level, depending upon $surveyMode
     */
    static function GetStepIndexInfo($step=NULL)
    {
        $LEM =& LimeExpressionManager::singleton();
        switch ($LEM->surveyMode)
        {
            case 'survey':
                return $LEM->lastMoveResult;
                break;
            case 'group':
                if (is_null($step)) {
                    return $LEM->indexGseq;
                }
                return $LEM->indexGseq[$step];
                break;
            case 'question':
                if (is_null($step)) {
                    return $LEM->indexQseq;
                }
                return $LEM->indexQseq[$step];
                break;
        }
    }

    /**
     * This should be called each time a new group is started, whether on same or different pages. Sets/Clears needed internal parameters.
     * @param <type> $groupNum - the group number
     * @param <type> $anonymized - whether anonymized
     * @param <type> $surveyid - the surveyId
     * @param <type> $forceRefresh - whether to force refresh of setting variable and token mappings (should be done rarely)
     */
    static function StartProcessingGroup($groupNum=NULL,$anonymized=false,$surveyid=NULL,$forceRefresh=false)
    {
        $LEM =& LimeExpressionManager::singleton();
        $LEM->em->StartProcessingGroup(
                isset($surveyid) ? $surveyid : NULL,
                isset($LEM->surveyOptions['rooturl']) ? $LEM->surveyOptions['rooturl'] : '',
                isset($LEM->surveyOptions['hyperlinkSyntaxHighlighting']) ? $LEM->surveyOptions['hyperlinkSyntaxHighlighting'] : false
                );
        $LEM->groupRelevanceInfo = array();
        if (!is_null($groupNum))
        {
            $LEM->groupNum = $groupNum;

            if (!is_null($surveyid))
            {
                if ($LEM->setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh,$anonymized,$LEM->allOnOnePage))
                {
                    // means that some values changed, so need to update what was registered to ExpressionManager
                    $LEM->em->RegisterVarnamesUsingMerge($LEM->knownVars);
                }
                if (isset ($LEM->groupId2groupSeq[$groupNum]))
                {
                    $groupSeq = $LEM->groupId2groupSeq[$groupNum];
                    $LEM->currentGroupSeq = $groupSeq;
                    if ($groupSeq > $LEM->maxGroupSeq) {
                        $LEM->maxGroupSeq = $groupSeq;
                    }                      
                }

                if (!$LEM->allOnOnePage || ($LEM->allOnOnePage && !$LEM->processedRelevance)) {
                    $LEM->ProcessAllNeededRelevance();  // TODO - what if this is called using Survey or Data Entry format?
                    $LEM->_CreateSubQLevelRelevanceAndValidationEqns();
                    $LEM->processedRelevance=true;
                }
            }
        }
    }

    /**
     * Should be called after each group finishes
     */
    static function FinishProcessingGroup()
    {
//        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();
        $LEM->pageTailorInfo[] = $LEM->em->GetCurrentSubstitutionInfo();
        $LEM->pageRelevanceInfo[] = $LEM->groupRelevanceInfo;
//        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));

    }

    /**
     * Return a formatted table showing how much time each part of EM consumed
     * @return <type>
     */
    static function GetDebugTimingMessage()
    {
        $LEM =& LimeExpressionManager::singleton();
        return $LEM->debugTimingMsg;
    }

    /**
     * Should be called at end of each page
     */
    static function FinishProcessingPage()
    {
        $LEM =& LimeExpressionManager::singleton();
        $_SESSION['EM_pageTailoringLog'] = $LEM->pageTailoringLog;
        $_SESSION['EM_surveyLogicFile'] = $LEM->surveyLogicFile;

        $totalTime = 0.;
        if ((($LEM->debugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING) && count($LEM->runtimeTimings)>0) {
            $LEM->debugTimingMsg='';
            foreach($LEM->runtimeTimings as $unit) {
                $totalTime += $unit[1];
            }
            $LEM->debugTimingMsg .= "<table border='1'><tr><td colspan=2><b>Total time attributable to EM = " . $totalTime . "</b></td></tr>\n";
            foreach ($LEM->runtimeTimings as $t)
            {
                $LEM->debugTimingMsg .= "<tr><td>" . $t[0] . "</td><td>" . $t[1] . "</td></tr>\n";
            }
            $LEM->debugTimingMsg .= "</table>\n";
        }
//        log_message('debug','Total time attributable to EM = ' . $totalTime);
//        log_message('debug',print_r($LEM->runtimeTimings,true));

        $LEM->runtimeTimings = array(); // reset them

        if (count($LEM->syntaxErrors) > 0 && (($LEM->debugLevel & LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB) == LEM_DEBUG_LOG_SYNTAX_ERRORS_TO_DB))
        {
            global $connect;
            foreach ($LEM->syntaxErrors as $err)
            {
                $query = "INSERT INTO ".db_table_name('expression_errors')." (errortime,sid,gid,qid,gseq,qseq,type,eqn,prettyprint) VALUES("
                    .db_quoteall($err['errortime'])
                    .",".$err['sid']
                    .",".$err['gid']
                    .",".$err['qid']
                    .",".$err['gseq']
                    .",".$err['qseq']
                    .",".db_quoteall($err['type'])
                    .",".db_quoteall($err['eqn'])
                    .",".db_quoteall($err['prettyPrint'])
                    .")";
                if (!$connect->Execute($query))
                {
                    print $connect->ErrorMsg();
                }
            }
        }
        $LEM->initialized=false;    // so detect calls after done
        $LEM->ParseResultCache=array(); // don't need to persist it in session
        $_SESSION['LEMsingleton']=serialize($LEM);
    }

    /**
     * Show the HTML for the logic file if $debugLevel has LEM_DEBUG_TRANSLATION_DETAIL bit set
     * @return <type>
     */
    static function ShowLogicFile()
    {
        if (isset($_SESSION['EM_surveyLogicFile'])) {
            return $_SESSION['EM_surveyLogicFile'];
        }
        return '';
    }

    /**
     * Show the HTML of the tailorings on this page if $debugLevel has LEM_DEBUG_TRANSLATION_DETAIL bit set
     * @return <type>
     */
    static function ShowPageTailorings()
    {
        if (isset($_SESSION['EM_pageTailoringLog'])) {
            return $_SESSION['EM_pageTailoringLog'];
        }
        return '';
    }

    /*
     * Generate JavaScript needed to do dynamic relevance and tailoring
     * Also create list of variables that need to be declared
     */
    static function GetRelevanceAndTailoringJavaScript()
    {
        global $rooturl;
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();

        $knownVars =& $LEM->knownVars;

        $jsParts=array();
        $allJsVarsUsed=array();
        $jsParts[] = '<script type="text/javascript" src="'.$rooturl.'/classes/eval/em_javascript.js"></script>';
        $jsParts[] = "\n<script type='text/javascript'>\n<!--\n";
        $jsParts[] = "var LEMgid=" . $LEM->groupNum . ";\n";    // current group num so can compute isOnCurrentPage
        $jsParts[] = "var LEMallOnOnePage=" . (($LEM->allOnOnePage) ? 'true' : 'false') . ";\n";
        $jsParts[] = "function ExprMgr_process_relevance_and_tailoring(evt_type){\n";
        $jsParts[] = "if (typeof LEM_initialized == 'undefined') {\nLEM_initialized=true;\nLEMsetTabIndexes();\n}\n";
        $jsParts[] = "if (evt_type == 'onchange' && (typeof last_evt_type != 'undefined' && last_evt_type == 'keydown') && (typeof target_tabIndex != 'undefined' && target_tabIndex == document.activeElement.tabIndex)) {\nreturn;\n}\n";
        $jsParts[] = "last_evt_type = evt_type;\n\n";

        // flatten relevance array, keeping proper order

        $pageRelevanceInfo=array();
        $qidList = array(); // list of questions used in relevance and tailoring
        $gidList = array(); // list of groups on this page

        if (is_array($LEM->pageRelevanceInfo))
        {
            foreach($LEM->pageRelevanceInfo as $prel)
            {
                foreach($prel as $rel)
                {
                    $pageRelevanceInfo[] = $rel;
                }
            }
        }

        if (is_array($pageRelevanceInfo))
        {
            foreach ($pageRelevanceInfo as $arg)
            {
                if (!$LEM->allOnOnePage && $LEM->groupNum != $arg['gid']) {
                    continue;
                }
                $gidList[$arg['gid']] = $arg['gid'];    // so keep them in order
                // First check if there is any tailoring  and construct the tailoring JavaScript if needed
                $tailorParts = array();
                foreach ($LEM->pageTailorInfo as $tailor)
                {
                    if (is_array($tailor))
                    {
                        foreach ($tailor as $sub)
                        {
                            if ($sub['questionNum'] == $arg['qid'])
                            {
                                $tailorParts[] = $sub['js'];
                                $vars = explode('|',$sub['vars']);
                                if (is_array($vars))
                                {
                                    $allJsVarsUsed = array_merge($allJsVarsUsed,$vars);
                                }
                            }
                        }
                    }
                }

                // Now check whether there is sub-question relevance to perform for this question
                $subqParts = array();
                if (isset($LEM->subQrelInfo[$arg['qid']]))
                {
                    foreach ($LEM->subQrelInfo[$arg['qid']] as $subq)
                    {
                        $subqParts[$subq['rowdivid']] = $subq;
                    }
                }

                $qidList[$arg['qid']] = $arg['qid'];

                $relevance = $arg['relevancejs'];

                if (($relevance == '' || $relevance == '1') && count($tailorParts) == 0 && count($subqParts) == 0)
                {
                    // Only show constitutively true relevances if there is tailoring that should be done.
//                    $jsParts[] = "document.getElementById('relevance" . $arg['qid'] . "').value='1'; // always true\n";
                    $jsParts[] = "$('#relevance" . $arg['qid'] . "').val('1');  // always true\n";
                    continue;
                }
                $relevance = ($relevance == '') ? '1' : $relevance;
                $jsResultVar = $LEM->em->GetJsVarFor($arg['jsResultVar']);
                $jsParts[] = "\n// Process Relevance for Question " . $arg['qid'];
                if ($relevance != 1)
                {
                    $jsParts[] = ": { " . $arg['eqn'] . " }";
                }
                $jsParts[] = "\nif (\n  ";
                $jsParts[] = $relevance;
                $jsParts[] = "\n  )\n{\n";
                // Do all tailoring
                $jsParts[] = implode("\n",$tailorParts);

                // Do all sub-question filtering (e..g array_filter)
                foreach ($subqParts as $sq)
                {
                    $jsParts[] = "  // Apply " . $sq['type'] . ": " . $sq['eqn'] ."\n";
                    $jsParts[] = "  if ( " . $sq['relevancejs'] . " ) {\n";
                    $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . "').show();\n";
                    switch ($sq['qtype'])
                    {
                        case '1': //Array (Flexible Labels) dual scale
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "#0').value = 'on';\n";
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "#1').value = 'on';\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "#0').val('on');\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "#1').val('on');\n";
                            break;
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case ';': //ARRAY (Multi Flexi) Text
                        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                        case 'F': //ARRAY (Flexible) - Row Format
                        case 'L': //LIST drop-down/radio-button list
                        case 'M': //Multiple choice checkbox
                        case 'P': //Multiple choice with comments checkbox + text
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'on';\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "').val('on');\n";
                            break;
                        default:
                            break;
                    }
                    $jsParts[] = "  }\n  else {\n";
                    $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . "').hide();\n";
                    switch ($sq['qtype'])
                    {
                        case '1': //Array (Flexible Labels) dual scale
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "#0').value = 'off';\n";
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "#1').value = 'off';\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "#0').val('off');\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "#1').val('off');\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "#0').val('');\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "#1').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=radio]').attr('checked',false);\n";
                            $jsParts[] = "    $('#answer" . $sq['rowdivid'] . "#0-').attr('checked',true);\n";
                            break;
                        case ';': //ARRAY (Multi Flexi) Text
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "').val('off');\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=text]').val('');\n";
                            break;
                        case ':': //ARRAY (Multi Flexi) 1 to 10
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "').val('off');\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " select').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=checkbox]').attr('checked',false);\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=text]').val('');\n";
                            break;
                        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                        case 'F': //ARRAY (Flexible) - Row Format
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "').val('off');\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=radio]').attr('checked',false);\n";
                            $jsParts[] = "    $('#answer" . $sq['rowdivid'] . "-').attr('checked',true);\n";
                            break;
                        case 'M': //Multiple choice checkbox
                        case 'P': //Multiple choice with comments checkbox + text
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "').val('off');\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=checkbox]').attr('checked',false);\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=text]').val('');\n";
                            break;
                        case 'L': //LIST drop-down/radio-button list
//                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
                            $jsParts[] = "    $('#tbdisp" . $sq['rowdivid'] . "').val('off');\n";
                            $listItem = substr($sq['rowdivid'],strlen($sq['sgqa']));    // gets the part of the rowdiv id past the end of the sgqa code.
                            $jsParts[] = "    if ($('#java" . $sq['sgqa'] ."').val() == '" . $listItem . "'){\n";
                            $jsParts[] = "      $('#java" . $sq['sgqa'] . "').val('');\n";
                            $jsParts[] = "      $('#answer" . $sq['sgqa'] . "NANS').attr('checked',true);\n";
                            $jsParts[] = "    }\n";
                            break;
                        default:
                            break;
                    }
                    $jsParts[] = "  }\n";

                    $sqvars = explode('|',$sq['relevanceVars']);
                    if (is_array($sqvars))
                    {
                        $allJsVarsUsed = array_merge($allJsVarsUsed,$sqvars);
                    }
                }

                if ($arg['hidden'] == 1) {
                    $jsParts[] = "  // This question should always be hidden\n";
                    $jsParts[] = "  $('#question" . $arg['qid'] . "').hide();\n";
//                    $jsParts[] = "  document.getElementById('display" . $arg['qid'] . "').value='';\n";
                    $jsParts[] = "  $('#display" . $arg['qid'] . "').val('');\n";
                }
                else {
                    $jsParts[] = "  $('#question" . $arg['qid'] . "').show();\n";
//                    $jsParts[] = "  document.getElementById('display" . $arg['qid'] . "').value='on';\n";
                    $jsParts[] = "  $('#display" . $arg['qid'] . "').val('on');\n";
                }
                // If it is an equation, and relevance is true, then write the value from the question to the answer field storing the result
                if ($arg['type'] == '*')
                {
                    $jsParts[] = "  // Write value from the question into the answer field\n";
//                    $jsParts[] = "  document.getElementById('" . $jsResultVar . "').value=escape(jQuery.trim(LEMstrip_tags($('#question" . $arg['qid'] . " .questiontext').find('span').next().next().html()))).replace(/%20/g,' ');\n";
                    $jsParts[] = "  $('#" . substr($jsResultVar,1,-1) . "').val(escape(jQuery.trim(LEMstrip_tags($('#question" . $arg['qid'] . " .questiontext').find('span').next().next().html()))).replace(/%20/g,' '));\n";
                }
//                $jsParts[] = "  document.getElementById('relevance" . $arg['qid'] . "').value='1';\n";
                $jsParts[] = "  $('#relevance" . $arg['qid'] . "').val('1');\n";
                $jsParts[] = "}\nelse {\n";
                $jsParts[] = "  $('#question" . $arg['qid'] . "').hide();\n";
//                $jsParts[] = "  document.getElementById('display" . $arg['qid'] . "').value='';\n";
                $jsParts[] = "  $('#display" . $arg['qid'] . "').val('');\n";
//                $jsParts[] = "  document.getElementById('relevance" . $arg['qid'] . "').value='0';\n";
                $jsParts[] = "  $('#relevance" . $arg['qid'] . "').val('0');\n";
                $jsParts[] = "}\n";

                $vars = explode('|',$arg['relevanceVars']);
                if (is_array($vars))
                {
                    $allJsVarsUsed = array_merge($allJsVarsUsed,$vars);
                }
            }
        }

        // Finally do Group-level Relevance.  Might consider surrounding questions with group-level relevance, but might message up Q.NAOK - not sure.
        foreach ($LEM->gRelInfo as $gr)
        {
            if (!array_key_exists($gr['gid'],$gidList)) {
                continue;
            }
            $jsParts[] = "\n// Process Relevance for Group " . $gr['gid'];
            if ($gr['relevancejs'] != '')
            {
                $jsParts[] = ": { " . $gr['eqn'] . " }";
                $jsParts[] = "\nif (" . $gr['relevancejs'] . ") {\n";
                $jsParts[] = "  $('#group-" . $gr['gid'] . "').show();\n";
                $jsParts[] = "  $('#relevanceG" . $gr['gid'] . "').val(1);\n";
                $jsParts[] = "}\nelse {\n";
                $jsParts[] = "  $('#group-" . $gr['gid'] . "').hide();\n";
                $jsParts[] = "  $('#relevanceG" . $gr['gid'] . "').val(0);\n";
                $jsParts[] = "}\n";
            }
            // now make sure any needed variables are accessible
            $vars = explode('|',$arg['relevanceVars']);
            if (is_array($vars))
            {
                $allJsVarsUsed = array_merge($allJsVarsUsed,$vars);
            }
        }

        $jsParts[] = "\n}\n";

        $allJsVarsUsed = array_unique($allJsVarsUsed);

        // Add JavaScript Mapping Arrays
        if (isset($LEM->alias2varName) && count($LEM->alias2varName) > 0)
        {
            $neededAliases=array();
            $neededCanonical=array();
            $neededCanonicalAttr=array();
            foreach ($allJsVarsUsed as $jsVar)
            {
                if ($jsVar == '') {
                    continue;
                }
                if (preg_match("/^.*\.NAOK$/", $jsVar)) {
                    $jsVar = preg_replace("/\.NAOK$/","",$jsVar);
                }
                $neededCanonical[] = $jsVar;
                foreach ($LEM->alias2varName as $key=>$value)
                {
                    if ($jsVar == $value['jsName'])
                    {
                        $neededAliases[] = $value['jsPart'];
                    }
                }
            }
            $neededCanonical = array_unique($neededCanonical);
            foreach ($neededCanonical as $nc)
            {
                $neededCanonicalAttr[] = $LEM->varNameAttr[$nc];
            }
            $neededAliases = array_unique($neededAliases);
            if (count($neededAliases) > 0)
            {
                $jsParts[] = "var LEMalias2varName = {\n";
                $jsParts[] = implode(",\n",$neededAliases);
                $jsParts[] = "};\n";
            }
            if (count($neededCanonicalAttr) > 0)
            {
                $jsParts[] = "var LEMvarNameAttr = {\n";
                $jsParts[] = implode(",\n",$neededCanonicalAttr);
                $jsParts[] = "};\n";
            }
        }

        $jsParts[] = "//-->\n</script>\n";

        // Now figure out which variables have not been declared (those not on the current page)
        $undeclaredJsVars = array();
        $undeclaredVal = array();
        if (!$LEM->allOnOnePage && isset($knownVars) && is_array($knownVars))
        {
            foreach ($knownVars as $key=>$knownVar)
            {
                if (!is_numeric($key[0])) {
                    continue;
                }
                if ($knownVar['jsName'] == '') {
                    continue;
                }
                foreach ($allJsVarsUsed as $jsVar)
                {
                    if ($jsVar == $knownVar['jsName'])
                    {
                        if ($LEM->surveyMode=='group' && $knownVar['gid'] == $LEM->groupNum) {
                            continue;
                        }
                        if ($LEM->surveyMode=='question' && $knownVar['qid'] == $LEM->currentQID) {
                            continue;
                        }
                        $undeclaredJsVars[] = $jsVar;
                        $sgqa = $knownVar['sgqa'];
                        $codeValue = (isset($_SESSION[$sgqa])) ? $_SESSION[$sgqa] : '';
                        $undeclaredVal[$jsVar] = $codeValue;

                        if (isset($LEM->jsVar2qid[$jsVar])) {
                            $qidList[$LEM->jsVar2qid[$jsVar]] = $LEM->jsVar2qid[$jsVar];
                        }
//                        break;    // why was this here?
                    }
                }
            }
            $undeclaredJsVars = array_unique($undeclaredJsVars);
            foreach ($undeclaredJsVars as $jsVar)
            {
                // TODO - is different type needed for text?  Or process value to striphtml?
                if ($jsVar == '') continue;
                $jsParts[] = "<input type='hidden' id='" . $jsVar . "' name='" . $jsVar .  "' value='" . htmlspecialchars($undeclaredVal[$jsVar],ENT_QUOTES) . "'/>\n";
            }
        }
        sort($qidList,SORT_NUMERIC);
        foreach ($qidList as $qid)
        {
            if (isset($_SESSION['relevanceStatus'])) {
                $relStatus = (isset($_SESSION['relevanceStatus'][$qid]) ? $_SESSION['relevanceStatus'][$qid] : 1);
            }
            else {
                $relStatus = 1;
            }
            $jsParts[] = "<input type='hidden' id='relevance" . $qid . "' name='relevance" . $qid .  "' value='" . $relStatus . "'/>\n";
        }

        foreach ($gidList as $gid)
        {
            if (isset($_SESSION['relevanceStatus'])) {
                $relStatus = (isset($_SESSION['relevanceStatus']['G' . $gid]) ? $_SESSION['relevanceStatus']['G' . $gid] : 1);
            }
            else {
                $relStatus = 1;
            }
            $jsParts[] = "<input type='hidden' id='relevanceG" . $gid . "' name='relevanceG" . $gid .  "' value='" . $relStatus . "'/>\n";
        }
        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));

        return implode('',$jsParts);
    }

    /**
     * Return array of most recent syntax errors for whatever scope was most recently processed
     * @return <type>
     */
    static function GetSyntaxErrors()
    {
        $query = "SELECT * FROM ".db_table_name('expression_errors');
        $data = db_execute_assoc($query);
        return $data->GetRows();
    }

    /**
     * Truncate the expression_errors table to clear the history of syntax errors.
     */
    static function ResetSyntaxErrorLog()
    {
        // truncate the table
        $query = "TRUNCATE TABLE ".db_table_name('expression_errors');
        db_execute_assoc($query);
    }

    /**
     * Unit test strings containing expressions
     */
    static function UnitTestProcessStringContainingExpressions()
    {
        $vars = array(
'name' => array('sgqa'=>'name', 'code'=>'Peter', 'jsName'=>'java61764X1X1', 'readWrite'=>'N', 'type'=>'X', 'question'=>'What is your first/given name?', 'qseq'=>10, 'gseq'=>1),
'surname' => array('sgqa'=>'surname', 'code'=>'Smith', 'jsName'=>'java61764X1X1', 'readWrite'=>'Y', 'type'=>'X', 'question'=>'What is your last/surname?', 'qseq'=>20, 'gseq'=>1),
'age' => array('sgqa'=>'age', 'code'=>45, 'jsName'=>'java61764X1X2', 'readWrite'=>'Y', 'type'=>'X', 'question'=>'How old are you?', 'qseq'=>30, 'gseq'=>2),
'numKids' => array('sgqa'=>'numKids', 'code'=>2, 'jsName'=>'java61764X1X3', 'readWrite'=>'Y', 'type'=>'X', 'question'=>'How many kids do you have?', 'relevance'=>'1', 'qid'=>'40','qseq'=>40, 'gseq'=>2),
'numPets' => array('sgqa'=>'numPets', 'code'=>1, 'jsName'=>'java61764X1X4', 'readWrite'=>'Y', 'type'=>'X','question'=>'How many pets do you have?', 'qseq'=>50, 'gseq'=>2),
'gender' => array('sgqa'=>'gender', 'code'=>'M', 'jsName'=>'java61764X1X5', 'readWrite'=>'Y', 'type'=>'X', 'shown'=>'Male','question'=>'What is your gender (male/female)?', 'qseq'=>110, 'gseq'=>2),
'notSetYet' => array('sgqa'=>'notSetYet', 'code'=>'?', 'jsName'=>'java61764X3X6', 'readWrite'=>'Y', 'type'=>'X', 'shown'=>'Unknown','question'=>'Who will win the next election?', 'qseq'=>200, 'gseq'=>3),
// Constants
'61764X1X1' => array('sgqa'=>'61764X1X1', 'code'=> '<Sergei>', 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X', 'qseq'=>70, 'gseq'=>2),
'61764X1X2' => array('sgqa'=>'61764X1X2', 'code'=> 45, 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X', 'qseq'=>80, 'gseq'=>2),
'61764X1X3' => array('sgqa'=>'61764X1X3', 'code'=> 2, 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X', 'qseq'=>15, 'gseq'=>1),
'61764X1X4' => array('sgqa'=>'61764X1X4', 'code'=> 1, 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X', 'qseq'=>100, 'gseq'=>2),
'TOKEN:ATTRIBUTE_1' => array('code'=> 'worker', 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X'),
        );

        $tests = <<<EOD
<b>Here is an example of OK syntax with tooltips</b><br/>Hello {if(gender=='M','Mr.','Mrs.')} {surname}, it is now {date('g:i a',time())}.  Do you know where your {sum(numPets,numKids)} chidren and pets are?
<b>Here are common errors so you can see the tooltips</b><br/>Variables used before they are declared:  {notSetYet}<br/>Unknown Function:  {iff(numPets>numKids,1,2)}<br/>Unknown Variable: {sum(age,num_pets,numKids)}<br/>Wrong # parameters: {sprintf()},{if(1,2)},{date()}<br/>Assign read-only-vars:{TOKEN:ATTRIBUTE_1+=10},{name='Sally'}<br/>Unbalanced parentheses: {pow(3,4},{(pow(3,4)},{pow(3,4))}
<b>Here is some of the unsupported syntax</b><br/>No support for '++', '--', '%',';': {min(++age, --age,age % 2);}<br/>Nor '|', '&', '^': {(sum(2 | 3,3 & 4,5 ^ 6)}}<br/>Nor arrays: {name[2], name['mine']}
<b>Inline JavaScipt that forgot to add spaces after curly brace</b><br/>[script type="text/javascript" language="Javascript"] var job='{TOKEN:ATTRIBUTE_1}'; if (job=='worker') {document.write('BOSSES');}[/script]
<b>Unknown/Misspelled Variables, Functions, and Operators</b><br/>{if(sex=='M','Mr.','Mrs.')} {surname}, next year you will be {age++} years old.
<b>Warns if use = instead of == or perform value assignments</b><br>Hello, {if(gender='M','Mr.','Mrs.')} {surname}, next year you will be {age+=1} years old.
<b>Wrong number of arguments for functions:</b><br/>{if(gender=='M','Mr.','Mrs.','Other')} {surname}, sum(age,numKids,numPets)={sum(age,numKids,numPets,)}
<b>Mismatched parentheses</b><br/>pow(3,4)={pow(3,4)}<br/>but these are wrong: {pow(3,4}, {(pow(3,4)}, {pow(3,4))}
<b>Unsupported syntax</b><br/>No support for '++', '--', '%',';': {min(++age, --age, age % 2);}<br/>Nor '|', '&', '^':  {(sum(2 | 3, 3 & 4, 5 ^ 6)}}<br/>Nor arrays:  {name[2], name['mine']}
<b>Invalid assignments</b><br/>Assign values to equations or strings:  {(3 + 4)=5}, {'hi'='there'}<br/>Assign read-only vars:  {TOKEN:ATTRIBUTE_1='boss'}, {name='Sally'}
<b>Values:</b><br/>name={name}; surname={surname}<br/>gender={gender}; age={age}; numPets={numPets}<br/>numKids=INSERTANS:61764X1X3={numKids}={INSERTANS:61764X1X3}<br/>TOKEN:ATTRIBUTE_1={TOKEN:ATTRIBUTE_1}
<b>Question Attributes:</b><br/>numKids.question={numKids.question}; Question#={numKids.qid}; .relevance={numKids.relevance}
<b>Math:</b><br/>5+7={5+7}; 2*pi={2*pi()}; sin(pi/2)={sin(pi()/2)}; max(age,numKids,numPets)={max(age,numKids,numPets)}
<b>Text Processing:</b><br/>{str_replace('like','love','I like LimeSurvey')}<br/>{ucwords('hi there')}, {name}<br/>{implode('--',name,'this is','a convenient way','way to','concatenate strings')}
<b>Dates:</b><br/>{name}, the current date/time is: {date('F j, Y, g:i a',time())}
<b>Conditional:</b><br/>Hello, {if(gender=='M','Mr.','Mrs.')} {surname}, may I call you {name}?
<b>Tailored Paragraph:</b><br/>{name}, you said that you are {age} years old, and that you have {numKids} {if((numKids==1),'child','children')} and {numPets} {if((numPets==1),'pet','pets')} running around the house. So, you have {numKids + numPets} wild {if((numKids + numPets ==1),'beast','beasts')} to chase around every day.<p>Since you have more {if((numKids > numPets),'children','pets')} than you do {if((numKids > numPets),'pets','children')}, do you feel that the {if((numKids > numPets),'pets','children')} are at a disadvantage?</p>
<b>EM processes within strings:</b><br/>Here is your picture [img src='images/users_{name}_{surname}.jpg' alt='{if(gender=='M','Mr.','Mrs.')} {name} {surname}'/];
<b>EM doesn't process curly braces like these:</b><br/>{name}, { this is not an expression}<br/>{nor is this }, { nor  this }<br/>\{nor this\},{this\},\{or this }
{INSERTANS:61764X1X1}, you said that you are {INSERTANS:61764X1X2} years old, and that you have {INSERTANS:61764X1X3} {if((INSERTANS:61764X1X3==1),'child','children')} and {INSERTANS:61764X1X4} {if((INSERTANS:61764X1X4==1),'pet','pets')} running around the house.  So, you have {INSERTANS:61764X1X3 + INSERTANS:61764X1X4} wild {if((INSERTANS:61764X1X3 + INSERTANS:61764X1X4 ==1),'beast','beasts')} to chase around every day.
Since you have more {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'children','pets')} than you do {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'pets','children')}, do you feel that the {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'pets','children')} are at a disadvantage?
{INSERTANS:61764X1X1}, you said that you are {INSERTANS:61764X1X2} years old, and that you have {INSERTANS:61764X1X3} {if((INSERTANS:61764X1X3==1),'child','children','kiddies')} and {INSERTANS:61764X1X4} {if((INSERTANS:61764X1X4==1),'pet','pets')} running around the house.  So, you have {INSERTANS:61764X1X3 + INSERTANS:61764X1X4} wild {if((INSERTANS:61764X1X3 + INSERTANS:61764X1X4 ==1),'beast','beasts')} to chase around every day.
This line should throw errors since the curly-brace enclosed functions do not have linefeeds after them (and before the closing curly brace): var job='{TOKEN:ATTRIBUTE_1}'; if (job=='worker') { document.write('BOSSES') } else { document.write('WORKERS') }
This line has a script section, but if you look at the source, you will see that it has errors: <script type="text/javascript" language="Javascript">var job='{TOKEN:ATTRIBUTE_1}'; if (job=='worker') {document.write('BOSSES')} else {document.write('WORKERS')} </script>.
Substitions that begin or end with a space should be ignored: { name} {age }
EOD;
        $alltests = explode("\n",$tests);

        $javascript1 = <<<EOST
                    var job='{TOKEN:ATTRIBUTE_1}';
                    if (job=='worker') {
                    document.write('BOSSES')
                    } else {
                    document.write('WORKERS')
                    }
EOST;
        $javascript2 = <<<EOST
var job='{TOKEN:ATTRIBUTE_1}';
    if (job=='worker') {
       document.write('BOSSES')
    } else { document.write('WORKERS')  }
EOST;
        $alltests[] = 'This line should have no errors - the Javascript has curly braces followed by line feeds:' . $javascript1;
        $alltests[] = 'This line should also be OK: ' . $javascript2;
        $alltests[] = 'This line has a hidden script: <script type="text/javascript" language="Javascript">' . $javascript1 . '</script>';
        $alltests[] = 'This line has a hidden script: <script type="text/javascript" language="Javascript">' . $javascript2 . '</script>';

        LimeExpressionManager::StartProcessingPage();
        LimeExpressionManager::StartProcessingGroup(1);

        $LEM =& LimeExpressionManager::singleton();
        $LEM->em->RegisterVarnamesUsingMerge($vars);

        $LEM->questionId2questionSeq = array();
        $LEM->questionId2groupSeq = array();
        $_SESSION['relevanceStatus'] = array();
        foreach ($vars as $var) {
            if (isset($var['qseq'])) {
                $LEM->questionId2questionSeq[$var['qseq']] = $var['qseq'];
                $LEM->questionId2groupSeq[$var['qseq']] = $var['gseq'];
                $_SESSION['relevanceStatus'][$var['qseq']] = 1;
            }
        }

        print "<h3>Note, if the <i>Vars Used</i> column is red, then at least one error was found in the <b>Source</b>. In such cases, the <i>Vars Used</i> list may be missing names of variables from sub-expressions containing errors</h3>";
        print '<table border="1"><tr><th>Source</th><th>Pretty Print</th><th>Result</th><th>Vars Used</th></tr>';
        for ($i=0;$i<count($alltests);++$i)
        {
            $test = $alltests[$i];
            $result = LimeExpressionManager::ProcessString($test, 40, NULL, false, 1, 1);
            $prettyPrint = LimeExpressionManager::GetLastPrettyPrintExpression();
            $varsUsed = $LEM->em->GetAllVarsUsed();
            if (count($varsUsed) > 0) {
                sort($varsUsed);
                $varList = implode(',<br />', $varsUsed);
            }
            else {
                $varList  = '&nbsp;';
            }

            print "<tr><td>" . htmlspecialchars($test,ENT_QUOTES) . "</td>\n";
            print "<td>" . $prettyPrint . "</td>\n";
            print "<td>" . $result . "</td>\n";
            if ($LEM->em->HasErrors()) {
                print "<td style='background-color:  red'>";
            }
            else {
                print "<td>";
            }
            print $varList . "</td>\n";
            print "</tr>\n";
        }
        print '</table>';
        LimeExpressionManager::FinishProcessingGroup();
        $LEM->syntaxErrors=array(); // so doesn't try to write test errors to database
        LimeExpressionManager::FinishProcessingPage();
    }

    /**
     * Unit test Relevance using a simplified syntax to represent questions.
     */
    static function UnitTestRelevance()
    {
        // Tests:  varName~relevance~inputType~message
$tests = <<<EOT
name~1~text~What is your name?
age~1~text~How old are you (must be 16-80)?
badage~1~expr~{badage=((age<16) || (age>80))}
agestop~!is_empty(age) && ((age<16) || (age>80))~message~Sorry, {name}, you are too {if((age<16),'young',if((age>80),'old','middle-aged'))} for this test.
kids~!((age<16) || (age>80))~yesno~Do you have children (Y/N)?
kidsO~!is_empty(kids) && !(kids=='Y' or kids=='N')~message~Please answer the question about whether you have children with 'Y' or 'N'.
wantsKids~kids=='N'~yesno~Do you hope to have kids some day (Y/N)?
wantsKidsY~wantsKids=='Y'~message~{name}, I hope you are able to have children some day!
wantsKidsN~wantsKids=='N'~message~{name}, I hope you have a wonderfully fulfilling life!
wantsKidsO~!is_empty(wantsKids) && !(wantsKids=='Y' or wantsKids=='N')~message~Please answer the question about whether you want children with 'Y' or 'N'.
parents~1~expr~{parents = (!badage && kids=='Y')}
numKids~kids=='Y'~text~How many children do you have?
numKidsValidation~parents and strlen(numKids) > 0 and numKids <= 0~message~{name}, please check your entries.  You said you do have children, {numKids} of them, which makes no sense.
kid1~numKids >= 1~text~How old is your first child?
kid2~numKids >= 2~text~How old is your second child?
kid3~numKids >= 3~text~How old is your third child?
kid4~numKids >= 4~text~How old is your fourth child?
kid5~numKids >= 5~text~How old is your fifth child?
sumage~1~expr~{sumage=sum(kid1.NAOK,kid2.NAOK,kid3.NAOK,kid4.NAOK,kid5.NAOK)}
report~numKids > 0~message~{name}, you said you are {age} and that you have {numKids} kids.  The sum of ages of your first {min(numKids,5)} kids is {sumage}.
EOT;

        $vars = array();
        $varsNAOK = array();
        $varSeq = array();
        $testArgs = array();
        $argInfo = array();

        $LEM =& LimeExpressionManager::singleton();


        LimeExpressionManager::StartProcessingPage(true);
        LimeExpressionManager::StartProcessingGroup(1); // pretending this is group 1

        // collect variables
        $i=0;
        foreach(explode("\n",$tests) as $test)
        {
            $args = explode("~",$test);
            $vars[$args[0]] = array('sgqa'=>$args[0], 'code'=>'', 'jsName'=>'java_' . $args[0], 'jsName_on'=>'java_' . $args[0], 'readWrite'=>'Y', 'type'=>'X', 'relevanceStatus'=>'1','gseq'=>1, 'qseq'=>$i);
            $varSeq[] = $args[0];
            $testArgs[] = $args;
            $LEM->questionId2questionSeq[$i] = $i;
            $LEM->questionId2groupSeq[$i] = 1;
            $LEM->questionSeq2relevance[$i] = array(
                'relevance'=>htmlspecialchars(preg_replace('/[[:space:]]/',' ',$args[1]),ENT_QUOTES),
                'qid'=>$i,
                'qseq'=>$i,
                'gseq'=>1,
                'jsResultVar'=>'java_' . $args[0],
                'type'=>(($args[1]=='expr') ? '*' : ($args[1]=='message') ? 'X' : 'S'),
                'hidden'=>0,
                'gid'=>1,   // ($i % 3),
                );
            ++$i;
        }

        $LEM->em->RegisterVarnamesUsingMerge($vars);
        $LEM->ProcessAllNeededRelevance();

        // collect relevance
        $alias2varName = array();
        $varNameAttr = array();
        for ($i=0;$i<count($testArgs);++$i)
        {
            $testArg = $testArgs[$i];
            $var = $testArg[0];
            $rel = LimeExpressionManager::QuestionIsRelevant($i);
            $question = LimeExpressionManager::ProcessString($testArg[3], $i, NULL, true, 1, 1);

            $jsVarName='java_' . $testArg[0];

            $argInfo[] = array(
                'num' => $i,
                'name' => $jsVarName,
                'type' => $testArg[2],
                'question' => $question,
                'relevance' => $testArg[1],
                'relevanceStatus' => $rel
            );
            $alias2varName[$var] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $var . "':'" . $jsVarName . "'");
            $alias2varName[$jsVarName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $jsVarName . "':'" . $jsVarName . "'");
            $varNameAttr[$jsVarName] = "'" . $jsVarName . "':{"
                . "'jsName':'" . $jsVarName
                . "','jsName_on':'" . $jsVarName
                . "','sgqa':'" . $jsVarName
                . "','qid':" . $i
                . ",'gid':". 1  // ($i % 3)   // so have 3 possible group numbers
            . "}";
        }
        $LEM->alias2varName = $alias2varName;
        $LEM->varNameAttr = $varNameAttr;
        LimeExpressionManager::FinishProcessingGroup();
        LimeExpressionManager::FinishProcessingPage();

        print LimeExpressionManager::GetRelevanceAndTailoringJavaScript();

        // Print Table of questions
        print "<h3>This is a test of dynamic relevance.</h3>";
        print "Enter your name and age, and try all the permutations of answers to whether you have or want children.<br/>\n";
        print "Note how the text and sum of ages changes dynamically; that prior answers are remembered; and that irrelevant values are not included in the sum of ages.<br/>";
        print "<table border='1'><tr><td>";
        foreach ($argInfo as $arg)
        {
            $rel = LimeExpressionManager::QuestionIsRelevant($arg['num']);
            print "<div id='question" . $arg['num'] . (($rel) ? "'" : "' style='display: none'") . ">\n";
            print "<input type='hidden' id='display" . $arg['num'] . "' name='" . $arg['num'] .  "' value='" . (($rel) ? 'on' : '') . "'/>\n";
            if ($arg['type'] == 'expr')
            {
                // Hack for testing purposes - rather than using LimeSurvey internals to store the results of equations, process them via a hidden <div>
                print "<div style='display: none' id='hack_" . $arg['name'] . "'>" . $arg['question'];
                print "<input type='hidden' id='" . $arg['name'] . "' name='" . $arg['name'] . "' value=''/></div>\n";
            }
            else {
                print "<table border='1' width='100%'>\n<tr>\n<td>[Q" . $arg['num'] . "] " . $arg['question'] . "</td>\n";
                switch($arg['type'])
                {
                    case 'yesno':
                    case 'text':
                        print "<td><input type='text' id='" . $arg['name'] . "' value='' onchange='ExprMgr_process_relevance_and_tailoring(\"onchange\")'/></td>\n";
                        break;
                    case 'message':
                        print "<input type='hidden' id='" . $arg['name'] . "' name='" . $arg['name'] . "' value=''/>\n";
                        break;
                }
                print "</tr>\n</table>\n";
            }
            print "</div>\n";
        }
        print "</table>";
    }

    public static function ShowStackTrace($msg=NULL,&$args=NULL)
    {
        $LEM =& LimeExpressionManager::singleton();

        $msg = array("**Stack Trace**" . (is_null($msg) ? '' : ' - ' . $msg));

        $count = 0;
        foreach (debug_backtrace(false) as $log)
        {
            if ($count++ == 0){
                continue;   // skip this call
            }
            $LEM->debugStack = array();

            $subargs=array();
            if (!is_null($args) && $log['function'] == 'templatereplace') {
                foreach ($args as $arg)
                {
                    if (isset($log['args'][2][$arg])) {
                        $subargs[$arg] = $log['args'][2][$arg];
                    }
                }
                if (count($subargs) > 0) {
                    $arglist = print_r($subargs,true);
                }
                else {
                    $arglist = '';
                }
            }
            else {
                $arglist = '';
            }
            $msg[] = '  '
            .   (isset($log['file']) ? '[' . basename($log['file']) . ']': '')
            .   (isset($log['class']) ? $log['class'] : '')
            .   (isset($log['type']) ? $log['type'] : '')
            .   (isset($log['function']) ? $log['function'] : '')
            .   (isset($log['line']) ? '[' . $log['line'] . ']' : '')
            .   $arglist;
        }
    }

    private function gT($string)
    {
        // eventually replace this with i8n
        global $clang;
        if (isset($clang))  {
            return $clang->gT($string);
        }
        else {
            return $string;
        }
    }

    private static function getAllRecordsForSurvey($surveyid=NULL, $qid=NULL)
    {
        if (!is_null($qid)) {
            $where = " c.qid = ".$qid." and ";
        }
        else if (!is_null($surveyid)) {
            $where = " c.qid in (select qid from ".db_table_name('questions')." where sid = ".$surveyid.") and ";
        }
        else {
            $where = "";
        }

        $query = "select distinct c.*"
                .", q.sid, q.type"
                ." from ".db_table_name('conditions')." as c"
                .", ".db_table_name('questions')." as q"
                ." where " . $where
                ." c.cqid=q.qid"
                ." union "
                ." select c.*, q.sid, '' as type"
                ." from ".db_table_name('conditions')." as c"
                .", ".db_table_name('questions')." as q"
                ." where ". $where
                ." c.cqid = 0 and c.qid = q.qid"
                ." order by sid, qid, scenario, cqid, cfieldname, value";

		$data = db_execute_assoc($query);

		return $data;
    }

    private function getEMRelatedRecordsForSurvey($surveyid=NULL,$qid=NULL)
    {
        if (!is_null($qid)) {
            $where = " a.qid = ".$qid." and ";
        }
        else if (!is_null($surveyid)) {
            $where = " a.qid=b.qid and b.sid=".$surveyid." and ";
        }
        else {
            $where = " and ";
        }

        // TODO - does this need to be filtered by language?
        $query = "select distinct a.qid, a.attribute, a.value"
                ." from ".db_table_name('question_attributes')." as a, ".db_table_name('questions')." as b"
                ." where " . $where . '1'
//                ." a.attribute in ('hidden', 'array_filter', 'array_filter_exclude', 'code_filter', 'equals_num_value', 'exclude_all_others', 'exclude_all_others_auto', 'max_answers', 'max_num_value', 'max_num_value_n', 'max_num_value_sgqa', 'min_answers', 'min_num_value', 'min_num_value_n', 'min_num_value_sgqa', 'multiflexible_max', 'multiflexible_min', 'num_value_equals_sgqa', 'other_replace_text', 'show_totals')"
                ." order by a.qid, a.attribute";

        $data = db_execute_assoc($query);
        $qattr = array();

        foreach($data->GetRows() as $row) {
            $qattr[$row['qid']][$row['attribute']] = $row['value'];
        }

		return $qattr;
    }

    /**
     * Return array of language-specific answer codes
     * @param <type> $surveyid
     * @param <type> $qid
     * @return <type>
     */

    function getAllAnswersForEM($surveyid=NULL,$qid=NULL,$lang=NULL)
    {
        if (!is_null($qid)) {
            $where = "a.qid = ".$qid;
        }
        else if (!is_null($surveyid)) {
            $where = "a.qid = q.qid and q.sid = ".$surveyid;
        }
        else {
            $where = "1";
        }
        if (!is_null($lang)) {
            $lang = " and a.language='".$lang."' and q.language='".$lang."'";
        }

        $query = "SELECT a.qid, a.code, a.answer, a.scale_id, a.assessment_value"
            ." FROM ".db_table_name('answers')." AS a, ".db_table_name('questions')." as q"
            ." WHERE ".$where
            .$lang
            ." ORDER BY qid, scale_id, sortorder";

        $data = db_execute_assoc($query);

        $qans = array();

        $useAssessments = ((isset($this->surveyOptions['assessments'])) ? $this->surveyOptions['assessments'] : false);

        foreach($data->GetRows() as $row) {
            if (!isset($qans[$row['qid']])) {
                $qans[$row['qid']] = array();
            }
            $qans[$row['qid']][$row['scale_id'].'~'.$row['code']] = ($useAssessments ? $row['assessment_value'] : $row['code']) . '|' . $row['answer'];
        }

        return $qans;
    }

    /**
     * Returns group info needed for indexes
     * @param <type> $surveyid
     * @param string $lang
     * @return <type>
     */

    function getGroupInfoForEM($surveyid,$lang=NULL)
    {
        if (!is_null($lang)) {
            $lang = " and a.language='".$lang."'";
        }

        $query = "SELECT a.group_name, a.description, a.gid, a.group_order, a.grelevance"
            ." FROM ".db_table_name('groups')." AS a"
            ." WHERE a.sid=".$surveyid
            .$lang
            ." ORDER BY group_order";

        $data = db_execute_assoc($query);

        $qinfo = array();
        foreach ($data as $d)
        {
            $qinfo[$d['group_order']] = array(
                'group_order' => $d['group_order'],
                'gid' => $d['gid'],
                'group_name' => $d['group_name'],
                'description' =>  $d['description'],
                'grelevance' => $d['grelevance'],
            );
        }

        return $qinfo;
    }

    /**
     * Cleanse the $_POSTed data and update $_SESSION variables accordingly
     */
    static function ProcessCurrentResponses()
    {
        $LEM =& LimeExpressionManager::singleton();
        if (!isset($LEM->currentQset)) {
            return array();
        }
        $updatedValues=array();
        foreach ($LEM->currentQset as $qinfo)
        {
            $relevant=false;
            $qid = $qinfo['info']['qid'];
            $relevant = (isset($_POST['relevance' . $qid]) ? ($_POST['relevance' . $qid] == 1) : false);
            $_SESSION['relevanceStatus'][$qid] = $relevant;
            foreach (explode('|',$qinfo['sgqa']) as $sq)
            {
                if ($relevant)
                {
                    $value = (isset($_POST[$sq]) ? $_POST[$sq] : '');
                    $type = $qinfo['info']['type'];
                    switch($type)
                    {
                        case 'D': //DATE
                            $dateformatdatat=getDateFormatData($LEM->surveyOptions['surveyls_dateformat']);
                            $datetimeobj = new Date_Time_Converter($value, $dateformatdatat['phpdate']);
                            $value=$datetimeobj->convert("Y-m-d");
                            break;
                        case 'N': //NUMERICAL QUESTION TYPE
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                            if (trim($value)=="") {
                                $value = "";
                            }
                            else {
                                $value = sanitize_float($value);
                            }
                            break;
                        case '|': //File Upload
                            if (!preg_match('/_filecount$/', $sq))
                            {
                                $json = $value;
                                $phparray = json_decode(stripslashes($json));

                                // if the files have not been saved already,
                                // move the files from tmp to the files folder

                                $tmp = $LEM->surveyOptions['tempdir'] . '/upload/';
                                if (!is_null($phparray) && count($phparray) > 0)
                                {
                                    // Move the (unmoved, temp) files from temp to files directory.
                                    // Check all possible file uploads
                                    for ($i = 0; $i < count($phparray); $i++)
                                    {
                                        if (file_exists($tmp . $phparray[$i]->filename))
                                        {
                                            $sDestinationFileName = 'fu_' . sRandomChars(15);
                                            if (!rename($tmp . $phparray[$i]->filename, $LEM->surveyOptions['target'] . $sDestinationFileName))
                                            {
                                                echo "Error moving file to target destination";
                                            }
                                            $phparray[$i]->filename = $sDestinationFileName;
                                        }
                                    }
                                    $value = str_replace('{','{ ',json_encode($phparray));  // so that EM doesn't try to parse it.
                                }
                            }
                            break;
                    }
                    $_SESSION[$sq] = $value;
                    $updatedValues[$sq] = array (
                        'type'=>$type,
                        'value'=>$value,
                        );
                }
                else {  // irrelevant, so database will be NULLed separately
                    // Must unset the value, rather than setting to '', so that EM can re-use the default value as needed.
                    unset($_SESSION[$sq]);
                }
            }
        }
        if (isset($_POST['timerquestion']))
        {
            $_SESSION[$_POST['timerquestion']]=sanitize_float($_POST[$_POST['timerquestion']]);
        }
        return $updatedValues;
    }
    
    /**
     * Create HTML view of the survey, showing everything that uses EM
     * @param <type> $sid
     * @param <type> $language
     * @param <type> $gid
     * @param <type> $qid 
     */
    static public function ShowSurveyLogicFile($sid, $language=NULL, $gid=NULL, $qid=NULL,$LEMdebugLevel=0,$assessments=false)
    {
        // Title
        // Welcome
        // G1, name, relevance, text
        // *Q1, name [type], relevance [validation], text, help, default, help_msg
        // SQ1, name [scale], relevance [validation], text
        // A1, code, assessment_value, text
        // End Message
        global $rooturl;

        $LEM =& LimeExpressionManager::singleton();

        $allErrors = array();

        $surveyOptions = array(
            'assessments'=>$assessments,
            'hyperlinkSyntaxHighlighting'=>true,
            'rooturl'=>$rooturl,
        );

        $varNamesUsed = array(); // keeps track of whether variables have been declared
        
        if (!is_null($qid))
        {
            $surveyMode='question';
            LimeExpressionManager::StartSurvey($sid, 'question', $surveyOptions, false,$LEMdebugLevel);
            $qseq = LimeExpressionManager::GetQuestionSeq($qid);
            $moveResult = LimeExpressionManager::JumpTo($qseq+1,true,false,true);
        }
        else if (!is_null($gid)) 
        {
            $surveyMode='group';
            LimeExpressionManager::StartSurvey($sid, 'group', $surveyOptions, false,$LEMdebugLevel);
            $gseq = LimeExpressionManager::GetGroupSeq($gid);
            $moveResult = LimeExpressionManager::JumpTo($gseq+1,true,false,true);
        }
        else
        {
            $surveyMode='survey';
            LimeExpressionManager::StartSurvey($sid, 'survey', $surveyOptions, false,$LEMdebugLevel);
            $moveResult = LimeExpressionManager::NavigateForwards();
        }

        $qtypes=getqtypelist('','array');

        templatereplace('{SITENAME}');  // to ensure that lime replacement fields loaded
        
        $out = "<table border='1'>"
        . "<tr><th>#</th><th>Name [ID]</th><th>Relevance [Validation] (Default)</th><th>Text [Help] (Tip)</th></tr>\n";

        $_gseq=-1;
        foreach ($LEM->currentQset as $q) {
            $gseq = $q['info']['gseq'];
            $gid = $q['info']['gid'];
            $qid = $q['info']['qid'];
            $qseq = $q['info']['qseq'];

            $errorCount=0;

            //////
            // SHOW GROUP-LEVEL INFO
            //////
            if ($gseq != $_gseq) {
                $LEM->ParseResultCache=array(); // reset for each group so get proper color coding?
                $_gseq = $gseq;
                $ginfo = $LEM->gseq2info[$gseq];

                $grelevance = '{' . (($ginfo['grelevance']=='') ? 1 : $ginfo['grelevance']) . '}';
                $gtext = ((trim($ginfo['description']) == '') ? '&nbsp;' : $ginfo['description']);

                $groupRow = "<tr class='LEMgroup'>"
                . "<td>G-$gseq</td>"
                . "<td><b>".$ginfo['group_name']."</b><br/>[<a target='_blank' href='$rooturl/admin/admin.php?action=orderquestions&sid=$sid&gid=$gid'>GID ".$gid."</a>]</td>"
                . "<td>".$grelevance."</td>"
                . "<td>".$gtext."</td>"
                . "</tr>\n";

                $LEM->ProcessString($groupRow, $qid,NULL,false,1,1,false,false);
                $out .= $LEM->GetLastPrettyPrintExpression();
                if ($LEM->em->HasErrors()) {
                    ++$errorCount;
                }
            }

            //////
            // SHOW QUESTION-LEVEL INFO
            //////
            $mandatory = (($q['info']['mandatory']=='Y') ? "<span style='color:red'>*</span>" : '');
            $type = $q['info']['type'];
            $typedesc = $qtypes[$type]['description'];

            $default = (is_null($q['info']['default']) ? '' : '<p>(DEFAULT: ' . $q['info']['default'] . ')</p>');

            $qtext = (($q['info']['qtext'] != '') ? $q['info']['qtext'] : '&nbsp');
            $help = (($q['info']['help'] != '') ? '<hr/>[HELP: ' . $q['info']['help'] . ']': '');
            $prettyValidTip = (($q['prettyValidTip'] == '') ? '' : '<hr/>(TIP: ' . $q['prettyValidTip'] . ')');

            //////
            // SHOW QUESTION ATTRIBUTES THAT ARE PROCESSED BY EM
            //////
            $attrTable = '';
            if (isset($LEM->qattr[$qid]) && count($LEM->qattr[$qid]) > 0) {
                $attrTable = "<hr/><table border='1'><tr><th>Question Attribute</th><th>Value</th></tr>\n";
                $count=0;
                foreach ($LEM->qattr[$qid] as $key=>$value) {
                    if (is_null($value) || trim($value) == '') {
                        continue;
                    }
                    switch ($key)
                    {
                        default:
                        case 'exclude_all_others':
                        case 'exclude_all_others_auto':
                        case 'hidden':
                            if ($value == '0') {
                                $value = NULL; // so can skip this one - just using continue here doesn't work.
                            }
                            break;
                        case 'relevance':
                            $value = NULL;  // means an outdate database structure
                            break;
                        case 'array_filter':
                        case 'array_filter_exclude':
                        case 'code_filter':
                            break;
                        case 'equals_num_value':
                        case 'max_answers':
                        case 'max_num_value':
                        case 'max_num_value_n':
                        case 'max_num_value_sgqa':
                        case 'min_answers':
                        case 'min_num_value':
                        case 'min_num_value_n':
                        case 'min_num_value_sgqa':
                        case 'multiflexible_max':
                        case 'multiflexible_min':
                        case 'num_value_equals_sgqa':
                            $value = '{' . $value . '}';
                            break;
                        case 'other_replace_text':
                        case 'show_totals':
                            break;
                    }
                    if (is_null($value)) {
                        continue;   // since continuing from within a switch statement doesn't work
                    }
                    ++$count;
                    $attrTable .= "<tr><td>$key</td><td>$value</td></tr>\n";
                }
                $attrTable .= "</table>\n";
                if ($count == 0) {
                    $attrTable = '';
                }
            }

            $LEM->ProcessString($qtext . $help . $prettyValidTip . $attrTable, $qid,NULL,false,1,1,false,false);
            $qdetails = $LEM->GetLastPrettyPrintExpression();
            if ($LEM->em->HasErrors()) {
                ++$errorCount;
            }

            //////
            // SHOW RELEVANCE
            //////
            // Must parse Relevance this way, otherwise if try to first split expressions, regex equations won't work
            $relevanceEqn = (($q['info']['relevance'] == '') ? 1 : $q['info']['relevance']);
            if (!isset($LEM->ParseResultCache[$relevanceEqn]))
            {
                $result = $LEM->em->ProcessBooleanExpression($relevanceEqn, $gseq, $qseq);
                $prettyPrint = $LEM->em->GetPrettyPrintString();
                $hasErrors =  $LEM->em->HasErrors();
                $LEM->ParseResultCache[$relevanceEqn] = array(
                    'result' => $result,
                    'prettyPrint' => $prettyPrint,
                    'hasErrors' => $hasErrors,
                );
            }
            $relevance = $LEM->ParseResultCache[$relevanceEqn]['prettyPrint'];
            if ($LEM->ParseResultCache[$relevanceEqn]['hasErrors']) {
                ++$errorCount;
            }

            //////
            // SHOW VALIDATION EQUATION
            //////
            // Must parse Validation this way so that regex (preg) works
            $prettyValidEqn = '';
            if ($q['prettyValidEqn'] != '') {
                $validationEqn = $q['validEqn'];
                if (!isset($LEM->ParseResultCache[$validationEqn]))
                {
                    $result = $LEM->em->ProcessBooleanExpression($validationEqn, $gseq, $qseq);
                    $prettyPrint = $LEM->em->GetPrettyPrintString();
                    $hasErrors =  $LEM->em->HasErrors();
                    $LEM->ParseResultCache[$validationEqn] = array(
                        'result' => $result,
                        'prettyPrint' => $prettyPrint,
                        'hasErrors' => $hasErrors,
                    );
                }
                $prettyValidEqn = '<hr/>(VALIDATION: ' . $LEM->ParseResultCache[$validationEqn]['prettyPrint'] . ')';
                if ($LEM->ParseResultCache[$validationEqn]['hasErrors']) {
                    ++$errorCount;
                }
            }

            //////
            // TEST VALIDITY OF ROOT VARIABLE NAME AND WHETHER HAS BEEN USED
            //////
            $rootVarName = $q['info']['rootVarName'];
            $varNameErrorMsg = '';
            $varNameError = NULL;
            if (isset($varNamesUsed[$rootVarName]))
            {
                $varNameErrorMsg .= $LEM->gT('This variable name has already been used.');
            }
            else
            {
                $varNamesUsed[$rootVarName] = array(
                    'gid'=>$gid,
                    'qid'=>$qid
                    );
            }

            if (!preg_match('/^[_a-zA-Z][_0-9a-zA-Z]*$/', $rootVarName))
            {
                $varNameErrorMsg .= $LEM->gT('This variable name contains invalid characters.');
            }
            if ($varNameErrorMsg != '')
            {
                $varNameError = array (
                    'message' => $varNameErrorMsg,
                    'gid' => $varNamesUsed[$rootVarName]['gid'],
                    'qid' => $varNamesUsed[$rootVarName]['qid']
                    );
                ++$errorCount;
            }

            //////
            // SHOW ALL SUB-QUESTIONS
            //////
            $sgqas = explode('|',$q['sgqa']);
            $sqRows='';
            $i=0;
            $sawthis = array(); // array of rowdivids already seen so only show them once
            foreach ($sgqas as $sgqa)
            {
                if ($LEM->knownVars[$sgqa]['qcode'] == $rootVarName) {
                    continue;   // so don't show the main question as a sub-question too
                }
                $rowdivid=$sgqa;
                $varName=$LEM->knownVars[$sgqa]['qcode'];
                switch  ($q['info']['type'])
                {
                    case '1':
                        if (preg_match('/#1$/',$sgqa)) {
                            $rowdivid = NULL;   // so that doesn't show same message for second scale
                        }
                        else {
                            $rowdivid = substr($sgqa,0,-2); // strip suffix
                            $varName = substr($LEM->knownVars[$sgqa]['qcode'],0,-2);
                        }
                        break;
                    case 'P':
                        if (preg_match('/comment$/',$sgqa)) {
                            $rowdivid = NULL;
                        }
                        break;
                    case ':':
                    case ';':
                        $_rowdivid = $LEM->knownVars[$sgqa]['rowdivid'];
                        if (isset($sawThis[$qid . '~' . $_rowdivid])) {
                            $rowdivid = NULL;   // so don't show again
                        }
                        else {
                            $sawThis[$qid . '~' . $_rowdivid] = true;
                            $rowdivid = $_rowdivid;
                            $sgqa_len = strlen($sid . 'X'. $gid . 'X' . $qid);
                            $varName = $rootVarName . '_' . substr($_rowdivid,$sgqa_len);
                        }
                    case 'L':
                        // TODO - need to show array filters applied to lists
                        break;
                }
                if (is_null($rowdivid)) {
                    continue;
                }
                ++$i;
                $subQeqn = '&nbsp;';
                if (isset($LEM->subQrelInfo[$qid][$rowdivid]))
                {
                    $sq = $LEM->subQrelInfo[$qid][$rowdivid];
                    $subQeqn = $sq['prettyPrintEqn'];   // {' . $sq['eqn'] . '}';  // $sq['prettyPrintEqn'];
                    if ($sq['hasErrors']) {
                        ++$errorCount;
                    }
                }

                $sgqaInfo = $LEM->knownVars[$sgqa];
                $subqText = $sgqaInfo['subqtext'];

                $sqRows .= "<tr class='LEMsubq'>"
                . "<td>SQ-$i</td>"
                . "<td><b>" . $varName . "</b></td>"
                . "<td>$subQeqn</td>"
                . "<td>" .$subqText . "</td>"
                . "</tr>";
            }
            $LEM->ProcessString($sqRows, $qid,NULL,false,1,1,false,false);
            $sqRows = $LEM->GetLastPrettyPrintExpression();
            if ($LEM->em->HasErrors()) {
                ++$errorCount;
            }

            //////
            // SHOW ANSWER OPTIONS FOR ENUMERATED LISTS, AND FOR MULTIFLEXI
            //////
            $answerRows='';
            if (isset($LEM->qans[$qid]) || isset($LEM->multiflexiAnswers[$qid]))
            {
                $_scale=-1;
                if (isset($LEM->multiflexiAnswers[$qid])) {
                    $ansList = $LEM->multiflexiAnswers[$qid];
                }
                else {
                    $ansList = $LEM->qans[$qid];
                }
                foreach ($ansList as $ans=>$value)
                {
                    $ansInfo = explode('~',$ans);
                    $valParts = explode('|',$value);
                    $valInfo[0] = array_shift($valParts);
                    $valInfo[1] = implode('|',$valParts);
                    if ($_scale != $ansInfo[0]) {
                        $i=1;
                        $_scale = $ansInfo[0];
                    }
                    $answerRows .= "<tr class='LEManswer'>"
                    . "<td>A[" . $ansInfo[0] . "]-" . $i++ . "</td>"
                    . "<td><b>" . $ansInfo[1]. "</b></td>"
                    . "<td>[VALUE: " . $valInfo[0] . "]</td>"
                    . "<td>" . $valInfo[1] . "</td>"
                    . "</tr>\n";
                }
                $LEM->ProcessString($answerRows, $qid,NULL,false,1,1,false,false);
                $answerRows = $LEM->GetLastPrettyPrintExpression();
                if ($LEM->em->HasErrors()) {
                    ++$errorCount;
                }
            }

            //////
            // FINALLY, SHOW THE QUESTION ROW(S), COLOR-CODING QUESTIONS THAT CONTAIN ERRORS
            //////
            $errclass = ($errorCount > 0) ? "class='LEMerror' title='This question has at least $errorCount error(s)'" : '';

            $questionRow = "<tr class='LEMquestion'>"
            . "<td $errclass>Q-" . $q['info']['qseq'] . "</td>"
            . "<td><b>" . $mandatory;

            if ($varNameErrorMsg == '')
            {
                $questionRow .= $rootVarName;
            }
            else
            {
                $editlink = $LEM->surveyOptions['rooturl'] . '/admin/admin.php?sid=' . $LEM->sid . '&gid=' . $varNameError['gid'] . '&qid=' . $varNameError['qid'];
                $questionRow .= "<span style='border-style: solid; border-width: 2px; border-color: red;' title='" . $varNameError['message'] . "' "
                    . "onclick='window.open(\"$editlink\",\"_blank\")'>"
                    . $rootVarName . "</span>";
            }
            
            $questionRow .= "</b><br/>[<a target='_blank' href='$rooturl/admin/admin.php?sid=$sid&gid=$gid&qid=$qid'>QID $qid</a>]<br/>$typedesc [$type]</td>"
            . "<td>" . $relevance . $prettyValidEqn . $default . "</td>"
            . "<td>" . $qdetails . "</td>"
            . "</tr>\n";

            $out .= $questionRow;
            $out .= $sqRows;
            $out .= $answerRows;

            if ($errorCount > 0) {
                $allErrors[$gid . '~' . $qid] = $errorCount;
            }
        }
        $out .= "</table>";

        LimeExpressionManager::FinishProcessingPage();
        if (($LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING) {
            $out .= LimeExpressionManager::GetDebugTimingMessage();
        }

        if (count($allErrors) > 0) {
            $out = "<p class='LEMerror'>". count($allErrors) . $LEM->gT(" Question(s) contain errors that need to be corrected") . "</p>\n" . $out;
        }
        else {
            switch ($surveyMode)
            {
                case 'survey':
                    $message = $LEM->gT('No syntax errors detected in this survey');
                    break;
                case 'group':
                    $message = $LEM->gT('This group, by itself, does not contain any syntax errors');
                    break;
                case 'question':
                    $message = $LEM->gT('This question, by itself, does not contain any syntax errors');
                    break;
            }
            $out = "<p class='LEMerror'>$message</p>\n" . $out;
        }

        return array(
            'errors'=>$allErrors,
            'html'=>$out
            );
    }
}
?>
