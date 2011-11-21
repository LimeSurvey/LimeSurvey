<?php
/**
 * Description of LimeExpressionManager
 * This is a wrapper class around ExpressionManager that implements a Singleton and eases
 * passing of LimeSurvey variable values into ExpressionManager
 *
 * @author Thomas M. White (TMSWhite)
 */
include_once('ExpressionManager.php');

class LimeExpressionManager {
    private static $instance;
    private $em;    // Expression Manager
    private $groupRelevanceInfo;
    private $sid;
    private $groupNum;
    private $debugLEM = false;   // set this to false to turn off debugging
    private $knownVars;
    private $pageRelevanceInfo;
    private $pageTailorInfo;
    private $allOnOnePage=false;    // internally set to true for survey.php so get group-specific logging but keep javascript variable namings consistent on the page.
    private $surveyMode='group';  // survey mode
    private $anonymized;
    private $qid2code;  // array of mappings of Question # to list of SGQA codes used within it
    private $jsVar2qid; // reverse mapping of JavaScript Variable name to Question
    private $alias2varName; // JavaScript array of mappings of aliases to the JavaScript variable names
    private $varNameAttr;   // JavaScript array of mappings of canonical JavaScript variable name to key attributes.
    private $pageTailoringLog;  // Debug log of tailorings done on this page
    private $surveyLogicFile;   // Shows current configuration and data from most recent $fieldmap

    private $groupId2groupSeq;  // map of gid to 0-based sequence number of groups
    private $questionId2questionSeq;    // map question # to an incremental count of question order across the whole survey
    private $questionId2groupSeq;   // map question  # to the group it is within, using an incremental count of group order
    private $groupSeqInfo;  // array of info about each Group, indexed by GroupSeq

    private $gid2relevanceStatus;   // tracks which groups have at least one relevant, non-hidden question
    private $qid2validationEqn;     // maps question # to the validation equation for that question.

    private $questionSeq2relevance; // keeps relevance in proper sequence so can minimize relevance processing to see what should be see on page and in indexes
    private $currentGroupSeq;   // current Group sequence (0-based index)
    private $currentQuestionSeq;    // for Question-by-Question mode, the 0-based index
    private $currentQset=NULL;   // set of the current set of questions to be displayed - at least one must be relevant

    private $maxGroupSeq;  // the maximum groupSeq reached -  this is needed for Index
    private $navigationIndex=false; // whether to build an index showing groups that have relevant questions // TODO - color code whether any visible questions are unanswered?
    private $slang='en';
    private $q2subqInfo;    // mapping of questions to information about their subquestions.
    private $qattr; // array of attributes for each question
    private $syntaxErrors=array();
    private $subQrelInfo;   // list of needed sub-question relevance (e.g. array_filter)

    private $runtimeTimings;
    private $initialized=false;
    private $processedRelevance=false;

    // A private constructor; prevents direct creation of object
    private function __construct()
    {
        self::$instance =& $this;
        $this->em = new ExpressionManager();
        
        // some common debug messages
        define('IRRELEVANT'," <span style='color:red'>irrelevant</span> ");
        define('ALWAYS_HIDDEN'," <span style='color:red'>always-hidden</span> ");
        define('INVALID'," <span style='color:red'>(a relevant Q fails validation tests)</span> ");
        define('MANDVIOLATION'," <span style='color:red'>(missing a relevant mandatory)</span> ");
    }

    // The singleton method
    public static function &singleton($ser=NULL)
    {
        if (!is_null($ser))
        {
            self::$instance = unserialize($ser);
        }
        if (!isset(self::$instance)) {
            if (isset($_SESSION['LEMsingleton'])) {
                self::$instance = unserialize($_SESSION['LEMsingleton']);
            }
            else {
                $c = __CLASS__;
                self::$instance = new $c;
            }
        }
        return self::$instance;
    }

    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
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
    public function _CreateSubQLevelRelevanceAndValidationEqns()
    {
        $now = microtime(true);

        $subQrels = array();    // array of sub-question-level relevance equations
        $validationEqn = array();

//        log_message('debug',print_r($this->q2subqInfo,true));
//        log_message('debug',print_r($this->qattr,true));

        // Associate these with $qid so that can be nested under appropriate question-level relevance?
        foreach ($this->q2subqInfo as $qinfo)
        {
            if (!$this->allOnOnePage && $this->groupNum != $qinfo['gid']) {
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
                                $sq_name = '(!regexMatch("' . $preg . '", ' . $sq['varName'] . '.NAOK))';
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
                            'type' => 'preg',
                            'eqn' => '(count(' . implode(', ', $sq_names) . ') > 0)',
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

        $this->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
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
        if (!$forceRefresh && isset($this->knownVars)) {
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

        $qattr = $this->getEMRelatedRecordsForSurvey($surveyid);   // what happens if $surveyid is null?
        $this->qattr = $qattr;

        $this->runtimeTimings[] = array(__METHOD__ . ' - question_attributes_model->getEMRelatedRecordsForSurvey',(microtime(true) - $now));
        $now = microtime(true);

        $qans = $this->getAllAnswersForEM($surveyid,NULL);  // ,$this->slang);  // TODO - will this work for multi-lingual?

        $this->runtimeTimings[] = array(__METHOD__ . ' - answers_model->getAllAnswersForEM',(microtime(true) - $now));
        $now = microtime(true);

        $q2subqInfo = array();

        foreach($fieldmap as $fielddata)
        {
            $code = $fielddata['fieldname'];
            $type = $fielddata['type'];
            if (!preg_match('#^\d+X\d+X\d+#',$code))
            {
                continue;   // not an SGQA value
            }
            $mandatory = $fielddata['mandatory'];
            $fieldNameParts = explode('X',$code);
            $groupNum = $fieldNameParts[1];

            $questionId = $fieldNameParts[2];
            $questionNum = $fielddata['qid'];
            $relevance = (isset($fielddata['relevance'])) ? $fielddata['relevance'] : 1;
            $hidden = (isset($qattr[$questionNum]['hidden'])) ? $qattr[$questionNum]['hidden'] : 'N';
            $scale_id = (isset($fielddata['scale_id'])) ? $fielddata['scale_id'] : '0';
            $preg = (isset($fielddata['preg'])) ? $fielddata['preg'] : NULL; // a perl regular exrpession validation function
            if (trim($preg) == '') {
                $preg = NULL;
            }

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
            if (!isset($this->groupId2groupSeq[$groupNum])) {
                $this->groupId2groupSeq[$groupNum] = $groupSeq;
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
                $codeList = $code;
            }
            else
            {
                $codeList .= '|' . $code;
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
                    $ansArray = $qans[$questionNum];
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
                    $rowdivid = substr($code,0,-2);
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
                        if ($type == 'P' && preg_match("/comment$/", $code)) {
//                            $rowdivid = substr($code,0,-7);
                        }
                        else {
                            $sqsuffix = '_' . $fielddata['aid'];
                            $rowdivid = $code;
                        }
                    }
                    break;
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case ';': //ARRAY (Multi Flexi) Text
                    $csuffix = $fielddata['aid'];
                    $sqsuffix = '_' . substr($fielddata['aid'],0,strpos($fielddata['aid'],'_'));
                    $varName = $fielddata['title'] . '_' . $fielddata['aid'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion1'] . '[' . $fielddata['subquestion2'] . ']';
                    $rowdivid = substr($code,0,strpos($code,'_'));
                    break;
            }

            // Set $jsVarName_on (for on-page variables - e.g. answerSGQA) and $jsVarName (for off-page  variables; the primary name - e.g. javaSGQA)
            switch($type)
            {
                case 'R': //RANKING STYLE
                    $jsVarName_on = 'fvalue_' . $fieldNameParts[2];
                    $jsVarName = 'java' . $code;
                    break;
                case 'D': //DATE
                case 'N': //NUMERICAL QUESTION TYPE
                case 'S': //SHORT FREE TEXT
                case 'T': //LONG FREE TEXT
                case 'U': //HUGE FREE TEXT
                case 'Q': //MULTIPLE SHORT TEXT
                case 'K': //MULTIPLE NUMERICAL QUESTION
                case 'X': //BOILERPLATE QUESTION
                    $jsVarName_on = 'answer' . $code;
                    $jsVarName = 'java' . $code;
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
                    $jsVarName = 'java' . $code;
                    $jsVarName_on = $jsVarName;
                    break;
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                    if (preg_match("/comment$/", $code)) {
                        $jsVarName = 'java' . $code;
                        $varName = $varName . "_comment";
                    }
                    else {
                        $jsVarName = 'java' . $code;
                    }
                    $jsVarName_on = $jsVarName;
                    break;
                case '|': //File Upload
                    // Only want the use the one that ends in '_filecount'
                    $goodcode = preg_replace("/^(.*?)(_filecount)?$/","$1",$code);
                    $jsVarName = $goodcode . '_filecount';
                    $jsVarName_on = $jsVarName;
                    break;
                case 'P': //Multiple choice with comments checkbox + text
                    if (preg_match("/comment$/",$code))
                    {
                        $jsVarName_on = 'answer' . $code;  // is this true for survey.php and not for group.php?
                        $jsVarName = 'java' . $code;
                    }
                    else
                    {
                        $jsVarName = 'java' . $code;
                        $jsVarName_on = $jsVarName;
                    }
                    break;
            }
            if (!is_null($rowdivid) || $type == 'L' || $type == 'N' || !is_null($preg)) {
                if (!isset($q2subqInfo[$questionNum])) {
                    $q2subqInfo[$questionNum] = array(
                        'qid' => $questionNum,
                        'gid' => $groupNum,
                        'sgqa' => $surveyid . 'X' . $groupNum . 'X' . $questionNum,
                        'varName' => $varName,
                        'type' => $type,
                        'fieldname' => $code,
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

            // TODO - should these arrays only be built for questions that require substitution at run-time?
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
                'relevance'=>$relevance,
                'relevanceNum'=>'relevance' . $questionNum,
                'qcode'=>$varName,
                'questionSeq'=>$questionSeq,
                'groupSeq'=>$groupSeq,
                'type'=>$type,
                'sgqa'=>$code,
                'rowdivid'=>$rowdivid,
                'ansList'=>$ansList,
                'ansArray'=>$ansArray,
                'scale_id'=>$scale_id,
                );

            $this->questionSeq2relevance[$questionSeq] = array(
                'relevance'=>$relevance,
                'qid'=>$questionNum,
                'questionSeq'=>$questionSeq,
                'groupSeq'=>$groupSeq,
                'jsResultVar_on'=>$jsVarName_on,
                'jsResultVar'=>$jsVarName,
                'type'=>$type,
                'hidden'=>$hidden,
                'gid'=>$groupNum,
                'mandatory'=>$mandatory,
                'eqn'=>(($type == '*') ? $question : ''),
                );

            $this->knownVars[$varName] = $varInfo_Code;
            $this->knownVars['INSERTANS:' . $code] = $varInfo_Code; // $varInfo_DisplayVal;
            $this->knownVars[$code] = $varInfo_Code;

            $this->jsVar2qid[$jsVarName] = $questionNum;

            // Create JavaScript arrays
            $this->alias2varName[$varName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $varName . "':'" . $jsVarName . "'");
            $this->alias2varName[$jsVarName_on] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $jsVarName_on . "':'" . $jsVarName . "'");
            $this->alias2varName[$jsVarName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $jsVarName . "':'" . $jsVarName . "'");
            $this->alias2varName[$code] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $code . "':'" . $jsVarName . "'");
            $this->alias2varName['INSERTANS:' . $code] = array('jsName'=>$jsVarName, 'jsPart' => "'INSERTANS:" . $code . "':'" . $jsVarName . "'");

            $this->varNameAttr[$jsVarName] = "'" . $jsVarName . "':{ "
                . "'jsName':'" . $jsVarName
                . "','jsName_on':'" . $jsVarName_on
                . "','sgqa':'" . $code
                . "','qid':" . $questionNum
                . ",'gid':" . $groupNum
                . ",'mandatory':'" . $mandatory
                . "','question':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$question),ENT_QUOTES)
                . "','type':'" . $type
                . "','relevance':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$relevance),ENT_QUOTES)
                . "'".$ansList."}";

            if ($this->debugLEM)
            {
                $this->debugLog[] = array(
                    'code' => $code,
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
                    'codeValue'=>$val,
                    'jsName_on'=>'',
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'relevanceNum'=>'',
                    );

                if ($this->debugLEM)
                {
                    $this->debugLog[] = array(
                        'code' => $key,
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
                    'codeValue'=>'',
                    'jsName_on'=>'',
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'relevanceNum'=>'',
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
        if ($this->debugLEM)
        {
            $debugLog_html = "<table border='1'>";
            $debugLog_html .= "<tr><th>Code</th><th>Type</th><th>VarName</th><th>CodeVal</th><th>DisplayVal</th><th>JSname</th><th>Writable?</th><th>Set On This Page?</th><th>Relevance</th><th>Hidden</th><th>Question</th></tr>";
            foreach ($this->debugLog as $t)
            {
                $debugLog_html .= "<tr><td>" . $t['code']
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
        if (isset($_SESSION['relevanceStatus'][$qid])) {
            return $_SESSION['relevanceStatus'][$qid];
        }
        return true;    // TODO - is this the right default?
    }

    /**
     * Return whether group $gid is relevant
     * @param <type> $gid
     * @return boolean
     */
    static function GroupIsRelevant($gid)
    {
        $LEM =& LimeExpressionManager::singleton();
        if (isset($LEM->gid2relevanceStatus[$gid])) {
            return $LEM->gid2relevanceStatus[$gid];
        }
        else {
            return true;    // TODO correct default?
        }
    }

    /**
 * Used by usort() to order $this->questionSeq2relevance in proper order
 * @param <type> $a
 * @param <type> $b
 * @return <type>
 */
    function cmpQuestionSeq($a, $b)
    {
        if (is_null($a['questionSeq'])) {
            if (is_null($b['questionSeq'])) {
                return 0;
            }
            return 1;
        }
        if (is_null($b['questionSeq'])) {
            return -1;
        }
        if ($a['questionSeq'] == $b['questionSeq']) {
            return 0;
        }
        return ($a['questionSeq'] < $b['questionSeq']) ? -1 : 1;
    }

    /**
     * Check the relevance status of all questions on or before the current group.
     * This generates needed JavaScript for dynamic relevance, and sets flags about which questions and groups are relevant
     */
    function ProcessAllNeededRelevance()
    {
        $now = microtime(true);

        // TODO - refactor this to not call a static function
        $this->gid2relevanceStatus = array();
        $_groupSeq = -1;
        foreach($this->questionSeq2relevance as $rel)
        {
            $qid = $rel['qid'];
            $gid = $rel['gid'];
            if ($this->allOnOnePage) {
                ;   // process relevance for all questions
            }
            else {
                $groupSeq = $rel['groupSeq'];

                if ($groupSeq > $this->maxGroupSeq) {
                    break;   // break out of loop
                }
                if (!$this->navigationIndex) {
                    if ($groupSeq > $this->currentGroupSeq) {
                        break;
                    }
                    if ($groupSeq < $this->currentGroupSeq) {
                        continue;
                    }
                }
                else {
                    if  ($groupSeq != $_groupSeq) {
                        $_groupSeq = $groupSeq;   // if new group, then reset status flags
                        $_groupSeqVisibility=false;
                        $this->gid2relevanceStatus[$gid]=false;    // default until found to be true
                    }

                    // TODO - augment this to show color coding for whether there are unanswered questions?
                    if ($groupSeq < $this->currentGroupSeq || $groupSeq > $this->currentGroupSeq) {
                        // Must know relevance of all prior questions so know what to display in reports.
                        // TODO - if sure relevance won't retroactively change, can reduce calls to this (e.g. if can't have equations depend on future variables and can't assign values)
                        if ($_groupSeqVisibility == true) {
                            continue;   // if at least one in the group is visible, then skip relevance check
                        }
                        else {
                            $result = $this->_ProcessRelevance(htmlspecialchars_decode($rel['relevance'],ENT_QUOTES), $qid, $gid);
                            $_SESSION['relevanceStatus'][$qid] = $result;   // is this needed?  YES, if trying to tailor using a question that was irrelevant on prior page
                            $this->gid2relevanceStatus[$gid]=true;
                            continue;
                        }
                    }
                    else {
                        ;   // current group, so process this one
                    }
                }
            }

            $result = $this->_ProcessRelevance(htmlspecialchars_decode($rel['relevance'],ENT_QUOTES),
                    $qid,
                    $gid,
                    $rel['jsResultVar'],
                    $rel['type'],
                    $rel['hidden']
                    );
            $_SESSION['relevanceStatus'][$qid] = $result;
        }
        $this->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
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

    static function ProcessString($string, $questionNum=NULL, $replacementFields=array(), $debug=false, $numRecursionLevels=1, $whichPrettyPrintIteration=1, $noReplacements=false)
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
                    'codeValue'=>$value,
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
        if ($LEM->em->HasErrors()) {
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

        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));

        if ($LEM->debugLEM)
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

        if ($this->em->HasErrors()) {
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

        if ($this->em->HasErrors()) {
            $prettyPrint = $this->em->GetPrettyPrintString();
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
//            $hasErrors = $this->em->HasErrors();
            $this->subQrelInfo[] = array(
                'qid' => $questionNum,
                'eqn' => $eqn,
                'result' => $result,
                'numJsVars' => count($jsVars),
                'relevancejs' => $relevanceJS,
                'relevanceVars' => $relevanceVars,
                'rowdivid' => $rowdivid,
                'type'=>$type,
                'qtype'=>$qtype,
                'sgqa'=>$sgqa,
            );
        }
        return $result;
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
     * @param <type> $navigationIndex - true if should compute the navigation index (list of questions or groups to display)
     * @param <type> $allOnOnePage - true if StartProcessingGroup will be called multiple times on this page - does some optimizatinos
     * @param <type> $debug - whether to generate debug logs
     */
    static function StartProcessingPage($navigationIndex=false,$allOnOnePage=false,$debug=true)
    {
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();
        $LEM->pageRelevanceInfo=array();
        $LEM->pageTailorInfo=array();
        $LEM->allOnOnePage=$allOnOnePage;
        $LEM->pageTailoringLog='';
        $LEM->surveyLogicFile='';
        $LEM->navigationIndex=$navigationIndex;
        $LEM->slang = (isset($_SESSION['s_lang']) ? $_SESSION['s_lang'] : 'en');
        $LEM->subQrelInfo=array();
        $LEM->processedRelevance=false;

        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));

        if ($debug && $LEM->debugLEM)
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

    static function StartSurvey($surveyid,$surveyMode='group',$anonymized=false,$forceRefresh=false)
    {
        $LEM =& LimeExpressionManager::singleton();
        $LEM->sid=$surveyid;   // TMSW - santize this?
        $LEM->anonymized=$anonymized;
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
        
        if ($LEM->setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh,$anonymized,$LEM->allOnOnePage))
        {
            // means that some values changed, so need to update what was registered to ExpressionManager
            $LEM->em->RegisterVarnamesUsingMerge($LEM->knownVars);
            // are the following two lines needed?
            $LEM->ProcessAllNeededRelevance();  // TODO - what if this is called using Survey or Data Entry format?
            $LEM->_CreateSubQLevelRelevanceAndValidationEqns();
        }
        $LEM->currentGroupSeq=-1;

        return array(
            'hasNext'=>true,
            'hasPrevious'=>false,
        );
    }

     static function NavigateBackwards($debug=false)
    {
        return self::NextRelevantSet(false,$debug);
    }

    /**
     *
     * @param <type> $force - if true, continue to go forward even if there are violations to the mandatory and/or validity rules
     * @param <type> $debug - if true, show more detailed debug messages
     */
    static function NavigateForwards($force=false,$debug=false) {
        $LEM =& LimeExpressionManager::singleton();
        $previousGroupSeq = $LEM->currentGroupSeq;   // so know where started

        $currentGInfo = array();
        $LEM->RelevanceResultCache=array();    // to avoid running same test more than once for a given group

        // $LEM->ProcessCurrentResponses();
        switch ($LEM->surveyMode)
        {
            case 'survey':
                break;
            case 'group':
                // First validate the current group
                if (!$force)
                {
                    $result = $LEM->_ValidateGroup($LEM->currentGroupSeq,$debug);
                    if ($result['mandViolation'] || !$result['valid'])
                    {
                        // redisplay the current group

                    }
                }
                $message = '';
                while (true)
                {
                    $result = $LEM->_ValidateGroup(++$LEM->currentGroupSeq,  $debug);
                    $message .= $result['message'];
                    if ($LEM->currentGroupSeq > $LEM->numGroups)
                    {
                        return array(
                            'finished'=>true,
                            'message'=>$message,
                        );
                    }
                    else if (!$result['relevant'] || $result['hidden'])
                    {
                        // then skip this group - assume already saved?
                        continue;
                    }
                    else
                    {
                        // display new group
                        return array(
                            'finished'=>false,
                            'message'=>$message,
                        );
                    }
                }
                break;
            case 'question':
                break;
        }
    }

    /**
     *
     * @param <type> $groupSeq - the index-0 sequence number for this group
     * @param <type> $debug - if true, generate detailed debug messages.
     * @return <array> - detailed information about this group
     */
    function _ValidateGroup($groupSeq, $debug=false)
    {
        $LEM =& $this;

        if ($groupSeq < 0 || $groupSeq >= $LEM->numGroups) {
            return NULL;    // TODO - what is desired behavior?
        }
        $groupSeqInfo = $LEM->groupSeqInfo[$groupSeq];
        $qInfo = $LEM->questionSeq2relevance[$groupSeqInfo['qstart']];
        $gid = $qInfo['gid'];
        $LEM->StartProcessingGroup($gid, $LEM->anonymized, $LEM->sid); // analyze the data we have about this group

        $grel=false;  // assume irrelevant until find a relevant question
        $ghidden=true;   // assume hidden until find a non-hidden question.  If there are no relevant questions on this page, $ghidden will stay true
        $gmandViolation=false;  // assume that the group contains no manditory questions that have not been fully answered
        $gvalid=true;   // assume valid until discover otherwise
        $debug_message = '';
        $messages = array();
        $currentQset = array();

        for ($i=$groupSeqInfo['qstart'];$i<=$groupSeqInfo['qend']; ++$i)
        {
            $qStatus = $LEM->_ValidateQuestion($i, $debug);

            if ($qStatus['relStatus']==true) {
                $grel = true;   // at least one question relevant
            }
            if ($qStatus['info']['hidden']==false) {
                $ghidden=false; // at least one question is visible
            }
            if ($qStatus['qmandViolation']==true) {
                $gmandViolation=true;   // at least one relevant question fails mandatory test
            }
            if ($qStatus['valid']==false) {
                $gvalid=false;  // at least one question fails validity constraints
            }
            $currentQset[] = $qStatus;
            $messages[] = $qStatus['message'];
        }

        // Done processing all questions/sub-questions in the potentially relevant group
        // Now check whether the group as a whole is relevant, valid, and/or hidden
        if ($debug)    // always want to see this
        {
            $debug_message .= '<br/>[G#' . $LEM->currentGroupSeq . ']'
                    . '[' . $groupSeqInfo['qstart'] . '-' . $groupSeqInfo['qend'] . ']'
                    . "[<a href='../../../admin/admin.php?action=orderquestions&sid={$LEM->sid}&gid=$gid'>"
                    .  'GID:' . $gid . "</a>]:  "
                    . ($grel ? 'relevant ' : IRRELEVANT)
                    . (($ghidden && $grel) ? ALWAYS_HIDDEN : ' ')
                    . ($gmandViolation ? MANDVIOLATION : ' ')
                    . ($gvalid ? '' : INVALID)
                    . "<br/>\n"
                    . implode('', $messages);
        }

        if ($grel == true)
        {
            if (!$gvalid)
            {
                // At least one question is invalid, so re-show this group
                // Validation logic needs to happen before trying to move to the next group
                if ($debug)
                {
                    $debug_message .= "----At least one relevant question was invalid, so re-show this group<br/>\n";
                }
            }
            if ($gmandViolation)
            {
                // At least one releavant question was mandatory but not answered
                // Mandatory logic needs to happen before trying to move to the next group
                if ($debug)
                {
                    $debug_message .= "----At least one relevant question was mandatory but unanswered, so re-show this group<br/>\n";
                }
            }

            if ($ghidden == true)
            {
                // This group has at least one relevant question, but they are all hidden.
                // NULL irrelevant values and process + save the result of any relevant equations
                $updatedValues=array();
                foreach ($currentQset as $qs)
                {
                    // Process any relevant equations
                    if (($qs['info']['type'] == '*') && $qs['relStatus'] == true)
                    {
                        // Process this equation
                        $result = $LEM->ProcessString($qs['info']['eqn'], $qs['info']['qid']);
                        $sgqa = $qs['sgqa'];   // there will be only one, since Equation
                        // Store the result of the Equation in the SESSION
                        $_SESSION[$sgqa] = $result;
                        $updatedValues[$sgqa] = $result;
                        if ($debug)
                        {
                            $prettyPrintEqn = $LEM->em->GetPrettyPrintString();
                            $debug_message .= '----** Process Hidden but Relevant Equation [' . $sgqa . '](' . $prettyPrintEqn . ') => ' . $result . "<br/>\n";
                        }
                    }
                    // NULL all irrelevant questions
                    if ($qs['relStatus'] == false)
                    {
                        $sgqas = explode('|',$qs['sgqa']);
                        foreach ($sgqas as $sgqa)
                        {
                            $_SESSION[$sgqa] = NULL;    // TMSW - or should it be unset?
                            $updatedValues[$sgqa] = NULL;
                        }
                    }
                }
                // Update these values in the database
                if (count($updatedValues) > 0)
                {
                    $query = 'UPDATE survey_' . $LEM->sid . " SET ";
                    $setter = array();
                    foreach ($updatedValues as $key=>$value)
                    {
                        if (is_null($value))
                        {
                            $setter[] = '`' . $key . "`=NULL";
                        }
                        else
                        {
                            $setter[] = '`' . $key . "`='" . addslashes($value) . "'";     // TMSW - what is preferred way to escape entries for DB?
                        }
                    }
                    $query .= implode(', ', $setter);
                    $query .= " WHERE ID=?";

                    if ($debug)
                    {
                        $debug_message .= '----** Page is relevant but hidden, so NULL irrelevant values and save relevant Equation results:</br>' . $query . "<br/>\n";
                    }
                }
            }
        }
        else
        {
            // This group contains no relevant questions
            // NULL all of the Group's values in the database
            $updatedValues=array();
            foreach ($currentQset as $qs)
            {
                $sgqas = explode('|',$qs['sgqa']);
                foreach ($sgqas as $sgqa)
                {
                    $_SESSION[$sgqa] = NULL;
                    $updatedValues[$sgqa] = NULL;
                }
            }
            // Update these values in the database
            if (count($updatedValues) > 0)
            {
                $query = 'UPDATE survey_' . $LEM->sid . " SET ";
                $setter = array();
                foreach ($updatedValues as $key=>$value)
                {
                    if (is_null($value))
                    {
                        $setter[] = '`' . $key ."`=NULL";
                    }
                    else
                    {
                        $setter[] = '`' . $key . "`='" . addslashes($value) . "'";     // TMSW - what is preferred way to escape entries for DB?
                    }
                }
                $query .= implode(', ', $setter);
                $query .= " WHERE ID=?";

                if ($debug)
                {
                    $debug_message .= '----** Page is irrelevant, so NULL all questions in this group:<br/>' . $query . "<br/>\n";
                }
            }
        }

        $currentGroupInfo = array(
            'gid' => $gid,
            'gseq' => $groupSeq,
            'message' => $debug_message,
            'relevant' => $grel,
            'hidden' => $ghidden,
            'mandViolation' => $gmandViolation,
            'valid' => $gvalid,
            'qset' => $currentQset,
        );

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
     * @param <type> $debug - if true, generate detailed debug messages
     * @return <array> of information about this question and its sub-questions
     */

    function _ValidateQuestion($questionSeq, $debug=false)
    {
        $LEM =& $this;
        $qInfo = $LEM->questionSeq2relevance[$questionSeq];   // this array is by group and question sequence
        // Check relevance - via equation, or from $SESSION?
        $qrel=true;   // assume relevant unless discover otherwise
        $prettyPrintRelEqn='';    //  assume no relevance eqn by default
        $qid=$qInfo['qid'];
        $gid=$qInfo['gid'];
        $debug_qmessage='';

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
        if (isset($LEM->RelevanceResultCache[$relevanceEqn]))
        {
            $qrel = $LEM->RelevanceResultCache[$relevanceEqn]['result'];
            if ($debug)
            {
                $prettyPrintRelEqn = $LEM->RelevanceResultCache[$relevanceEqn]['prettyPrint'];
            }
        }
        else
        {
            $qrel = $LEM->em->ProcessBooleanExpression($relevanceEqn,$qInfo['groupSeq'], $qInfo['questionSeq']);    // assumes safer to re-process relevance and not trust POST values
            if ($LEM->em->HasErrors())
            {
                $qrel=false;  // default to invalid so that can show the error
            }
            if ($debug)
            {
                $prettyPrintRelEqn = $LEM->em->GetPrettyPrintString();
            }
            $LEM->RelevanceResultCache[$relevanceEqn] = array(
                'result'=>$qrel,
                'prettyPrint'=>$prettyPrintRelEqn,
                );            
        }

        // Check always-hidden status
        $qhidden = $qInfo['hidden'];
        if ($qrel)
        {
            $grel = true;   // at least one question is relevant
        }
        if (!$qhidden && $qrel)
        {
            $ghidden = false;    // at least one relevant question should be shown
        }

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
            $sgqas = explode('|',$LEM->qid2code[$qid]);
            foreach ($sgqas as $sgqa)
            {
                // for each subq, see if it is part of an array_filter or array_filter_exclude
                if (!isset($LEM->subQrelInfo))
                {
                    $relevantSQs[] = $sgqa;
                    continue;
                }
                $foundSQrelevance=false;
                foreach ($LEM->subQrelInfo as $sq)
                {
                    if ($sq['qid'] == $qid)
                    {
                        switch ($sq['qtype'])
                        {
                            case '1':   //Array (Flexible Labels) dual scale
                                if ($sgqa == ($sq['rowdivid'] . '#0') || $sgqa == ($sq['rowdivid'] . '#1')) {
                                    $foundSQrelevance=true;
                                    if (isset($LEM->RelevanceResultCache[$sq['eqn']]))
                                    {
                                        $sqrel = $LEM->RelevanceResultCache[$sq['eqn']]['result'];
                                        if ($debug)
                                        {
                                            $prettyPrintSQRelEqns[] = $LEM->RelevanceResultCache[$sq['eqn']]['prettyPrint'];
                                        }
                                    }
                                    else
                                    {
                                        $stringToParse = htmlspecialchars_decode($sq['eqn'],ENT_QUOTES);  // TODO is this needed?
                                        $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['groupSeq'], $qInfo['questionSeq']);
                                        if ($LEM->em->HasErrors())
                                        {
                                            $sqrel=false;  // default to invalid so that can show the error
                                        }
                                        if ($debug)
                                        {
                                            $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                            $prettyPrintSQRelEqns[] = $prettyPrintSQRelEqn;
                                        }
                                        $LEM->RelevanceResultCache[$sq['eqn']] = array(
                                            'result'=>$sqrel,
                                            'prettyPrint'=>$prettyPrintSQRelEqn,
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
                                    if (isset($LEM->RelevanceResultCache[$sq['eqn']]))
                                    {
                                        $sqrel = $LEM->RelevanceResultCache[$sq['eqn']]['result'];
                                        if ($debug)
                                        {
                                            $prettyPrintSQRelEqns[] = $LEM->RelevanceResultCache[$sq['eqn']]['prettyPrint'];
                                        }
                                    }
                                    else
                                    {
                                        $stringToParse = htmlspecialchars_decode($sq['eqn'],ENT_QUOTES);  // TODO is this needed?
                                        $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['groupSeq'], $qInfo['questionSeq']);
                                        if ($LEM->em->HasErrors())
                                        {
                                            $sqrel=false;  // default to invalid so that can show the error
                                        }
                                        if ($debug)
                                        {
                                            $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                            $prettyPrintSQRelEqns[] = $prettyPrintSQRelEqn;
                                        }
                                        $LEM->RelevanceResultCache[$sq['eqn']] = array(
                                            'result'=>$sqrel,
                                            'prettyPrint'=>$prettyPrintSQRelEqn,
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
                                    if (isset($LEM->RelevanceResultCache[$sq['eqn']]))
                                    {
                                        $sqrel = $LEM->RelevanceResultCache[$sq['eqn']]['result'];
                                        if ($debug)
                                        {
                                            $prettyPrintSQRelEqns[] = $LEM->RelevanceResultCache[$sq['eqn']]['prettyPrint'];
                                        }
                                    }
                                    else
                                    {
                                        $stringToParse = htmlspecialchars_decode($sq['eqn'],ENT_QUOTES);  // TODO is this needed?
                                        $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['groupSeq'], $qInfo['questionSeq']);
                                        if ($LEM->em->HasErrors())
                                        {
                                            $sqrel=false;  // default to invalid so that can show the error
                                        }
                                        if ($debug)
                                        {
                                            $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                            $prettyPrintSQRelEqns[] = $prettyPrintSQRelEqn;
                                        }
                                        $LEM->RelevanceResultCache[$sq['eqn']] = array(
                                            'result'=>$sqrel,
                                            'prettyPrint'=>$prettyPrintSQRelEqn,
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
                                // TODO - these filters are supposed to ensure that the value doesn't equal a filtered value
                                // TODO - isn't catching 'other' filter
                                if ($sgqa == $sq['sgqa'])
                                {
                                    $foundSQrelevance=true;
                                    if (isset($LEM->RelevanceResultCache[$sq['eqn']]))
                                    {
                                        $sqrel = $LEM->RelevanceResultCache[$sq['eqn']]['result'];
                                        if ($debug)
                                        {
                                            $prettyPrintSQRelEqns[] = $LEM->RelevanceResultCache[$sq['eqn']]['prettyPrint'];
                                        }
                                    }
                                    else
                                    {
                                        $stringToParse = htmlspecialchars_decode($sq['eqn'],ENT_QUOTES);  // TODO is this needed?
                                        $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['groupSeq'], $qInfo['questionSeq']);
                                        if ($LEM->em->HasErrors())
                                        {
                                            $sqrel=false;  // default to invalid so that can show the error
                                        }
                                        if ($debug)
                                        {
                                            $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                            $prettyPrintSQRelEqns[] = $prettyPrintSQRelEqn;
                                        }
                                        $LEM->RelevanceResultCache[$sq['eqn']] = array(
                                            'result'=>$sqrel,
                                            'prettyPrint'=>$prettyPrintSQRelEqn,
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
                    }
                }   // end foreach($LEM->subQrelInfo) [checking array-filters]
                if (!$foundSQrelevance)
                {
                    // then this question is relevant
                    $relevantSQs[] = $sgqa; // TODO - check this
                }
            } // end foreach($sgqa) [assessing each sub-question]
        } // end of processing relevant question

        if ($debug)
        {
            $prettyPrintSQRelEqns = array_unique($prettyPrintSQRelEqns);
        }
        // These array_unique only apply to array_filter of type L (list)
        $relevantSQs = array_unique($relevantSQs);
        $irrelevantSQs = array_unique($irrelevantSQs);

        // TODO - NULL out values of any irrelevant subquestions

        // check that all mandatories have been fully answered (but don't require answers for sub-questions that are irrelevant
        $unansweredSQs = array();   // list of sub-questions that weren't answered
        foreach ($relevantSQs as $sgqa)
        {
            if (!isset($_SESSION[$sgqa]) || ($_SESSION[$sgqa] == '' || is_null($_SESSION[$sgqa])))
            {
                // then a relevant, visible, mandatory question hasn't been answered
                $unansweredSQs[] = $sgqa;
            }
        }

        // Detect any violations of  mandatory rules
        $qmandViolation = false;    // assume there is no mandatory violation until discover otherwise
        $mandatoryTip = '';
        if ($qrel && !$qhidden && ($qInfo['mandatory'] == 'Y'))
        {
            switch ($qInfo['type'])
            {
                case 'M':
                case 'P':
                    // If at least one checkbox is checked, we're OK
                    if (count($relevantSQs) > 0 && (count($relevantSQs) == count($unansweredSQs)))
                    {
                        $qmandViolation = true;
                        $mandatoryTip = $LEM->gT('Please check at least one item.');
                    }
                    break;
                default:
                    // In general, if any relevant questions aren't answered, then it violates the mandatory rule
                    if (count($unansweredSQs) > 0)
                    {
                        $qmandViolation = true; // TODO - what about 'other'?
                        $mandatoryTip = $LEM->gT('Please complete all parts');
                    }
                    break;
            }
        }

        // check validity of responses
        $qvalid=true;   // assume valid unless discover otherwise
        $hasValidationEqn=false;
        $prettyPrintValidEqn='';    //  assume no validation eqn by default
        if (isset($LEM->qid2validationEqn[$qid]))
        {
            $hasValidationEqn=true;
            if ($qrel && !$qhidden)
            {
                $stringToParse = $LEM->qid2validationEqn[$qid]['eqn'];
                $qvalid = $LEM->em->ProcessBooleanExpression($stringToParse,$qInfo['groupSeq'], $qInfo['questionSeq']);
                if ($LEM->em->HasErrors())
                {
                    $qvalid=false;  // default to invalid so that can show the error
                }
                if ($debug)
                {
                    $prettyPrintValidEqn = $LEM->em->GetPrettyPrintString();

                    // Also preview the Validation Tip
                    $stringToParse = implode('<br/>',$LEM->qid2validationEqn[$qid]['tips']);
                    // pretty-print them
                    $LEM->ProcessString($stringToParse, $qid);
                    $prettyPrintValidTip = $LEM->GetLastPrettyPrintExpression();                                            }
            }
            else
            {
                if ($debug)
                {
                    $prettyPrintValidEqn = 'Question is Irrelevant, so no need to further validate it';
                }
            }
        }
        if (!$qvalid)
        {
            $gvalid=false;  //so know of at least one validation error for the group
        }

        if ($debug)
        {
            $debug_qmessage .= '--[Q#' . $qInfo['questionSeq'] . ']'
                . "[<a href='../../../admin/admin.php?sid={$LEM->sid}&gid=$gid&qid=$qid'>"
                . 'QID:'. $qid . '</a>][' . $qInfo['type'] . ']: '
                . ($qrel ? 'relevant' : IRRELEVANT)
                . ($qhidden ? ALWAYS_HIDDEN : ' ')
                . (($qInfo['mandatory'] == 'Y')? ' mandatory' : ' ')
                . (($hasValidationEqn) ? (!$qvalid ? INVALID : ' valid') : '')
                . ($qmandViolation ? MANDVIOLATION : ' ')
                . $prettyPrintRelEqn
                . "<br/>\n";

            if ($mandatoryTip != '')
            {
                $debug_qmessage .= '----Mandatory Tip: ' . $mandatoryTip . "<br/>\n";
            }

            if ($prettyPrintValidTip != '')
            {
                $debug_qmessage .= '----Validation Tip: ' . $prettyPrintValidTip . "<br/>\n";
            }

            if ($prettyPrintValidEqn != '')
            {
                $debug_qmessage .= '----Validation Eqn: ' . $prettyPrintValidEqn . "<br/>\n";
            }

            // what are the database question codes for this question?
            $subQList = '{' . implode('}, {', explode('|',$LEM->qid2code[$qid])) . '}';
            // pretty-print them
            $LEM->ProcessString($subQList, $qid);
            $prettyPrintSubQList = $LEM->GetLastPrettyPrintExpression();
            $debug_qmessage .= '----SubQs=> ' . $prettyPrintSubQList . "<br/>\n";

            if (count($prettyPrintSQRelEqns) > 0)
            {
                $debug_qmessage .= "----Array Filters Applied:<br/>\n" . implode('<br/>',$prettyPrintSQRelEqns) . "<br/>\n";
            }

            if (count($relevantSQs) > 0)
            {
                $subQList = '{' . implode('}, {', $relevantSQs) . '}';
                // pretty-print them
                $LEM->ProcessString($subQList, $qid);
                $prettyPrintSubQList = $LEM->GetLastPrettyPrintExpression();
                $debug_qmessage .= '----Relevant SubQs: ' . $prettyPrintSubQList . "<br/>\n";
            }

            if (count($irrelevantSQs) > 0)
            {
                $subQList = '{' . implode('}, {', $irrelevantSQs) . '}';
                // pretty-print them
                $LEM->ProcessString($subQList, $qid);
                $prettyPrintSubQList = $LEM->GetLastPrettyPrintExpression();
                $debug_qmessage .= '----Irrelevant SubQs: ' . $prettyPrintSubQList . "<br/>\n";
            }

            // show which relevant subQs were not answered
            if (count($unansweredSQs) > 0)
            {
                $subQList = '{' . implode('}, {', $unansweredSQs) . '}';
                // pretty-print them
                $LEM->ProcessString($subQList, $qid);
                $prettyPrintSubQList = $LEM->GetLastPrettyPrintExpression();
                $debug_qmessage .= '----Unanswered Relevant SubQs: ' . $prettyPrintSubQList . "<br/>\n";
            }
        }


        // Store metadata needed for subsequent processing and display purposes
        $qStatus = array(
            'info' => $qInfo,   // collect all questions within the group - includes mandatory and always-hiddden status
            'relStatus' => $qrel,
            'relEqn' => $prettyPrintRelEqn,
            'sgqa' => $LEM->qid2code[$qid],
            'unansweredSQs' => implode('|',$unansweredSQs),
            'valid' => $qvalid,
            'validEqn' => $prettyPrintValidEqn,
            'validTip' => $prettyPrintValidTip,
            'relevantSQs' => implode('|',$relevantSQs),
            'irrelevantSQs' => implode('|',$irrelevantSQs),
            'subQrelEqn' => implode('<br/>',$prettyPrintSQRelEqns),
            'qmandViolation' => $qmandViolation,
            'message' => $debug_qmessage,
            );

        return $qStatus;
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
        $LEM->em->StartProcessingGroup();
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
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();
        $LEM->pageTailorInfo[] = $LEM->em->GetCurrentSubstitutionInfo();
        $LEM->pageRelevanceInfo[] = $LEM->groupRelevanceInfo;
        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));

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
        if (isset($LEM->runtimeTimings)) {
            foreach($LEM->runtimeTimings as $unit) {
                $totalTime += $unit[1];
            }
//            echo 'Total time attributable to EM = ' . $totalTime;
            unset($LEM->runtimeTimings);
        }
//        log_message('debug','Total time attributable to EM = ' . $totalTime);
//        log_message('debug',print_r($LEM->runtimeTimings,true));

        if (count($LEM->syntaxErrors) > 0)
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
        $_SESSION['LEMsingleton']=serialize($LEM);
    }

    /**
     * Show the HTML for the logic file if $debugLEM was true
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
     * Show the HTML of the tailorings on this page if $debugLEM was true
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
                foreach ($LEM->subQrelInfo as $subq)
                {
                    if ($subq['qid'] == $arg['qid'])
                    {
                        $subqParts[$subq['rowdivid']] = $subq;
                    }
                }

                $qidList[$arg['qid']] = $arg['qid'];

                $relevance = $arg['relevancejs'];

                if (($relevance == '' || $relevance == '1') && count($tailorParts) == 0 && count($subqParts) == 0)
                {
                    // Only show constitutively true relevances if there is tailoring that should be done.
                    $jsParts[] = "document.getElementById('relevance" . $arg['qid'] . "').value='1'; // always true\n";
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
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "#0').value = 'on';\n";
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "#1').value = 'on';\n";
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
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'on';\n";
                            break;
                        default:
                            break;
                    }
                    $jsParts[] = "  }\n  else {\n";
                    $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . "').hide();\n";
                    switch ($sq['qtype'])
                    {
                        case '1': //Array (Flexible Labels) dual scale
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "#0').value = 'off';\n";
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "#1').value = 'off';\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "#0').val('');\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "#1').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=radio]').attr('checked',false);\n";
                            $jsParts[] = "    $('#answer" . $sq['rowdivid'] . "#0-').attr('checked',true);\n";
                            break;
                        case ';': //ARRAY (Multi Flexi) Text
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=text]').val('');\n";
                            break;
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
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
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=radio]').attr('checked',false);\n";
                            $jsParts[] = "    $('#answer" . $sq['rowdivid'] . "-').attr('checked',true);\n";
                            break;
                        case 'M': //Multiple choice checkbox
                        case 'P': //Multiple choice with comments checkbox + text
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
                            $jsParts[] = "    $('#java" . $sq['rowdivid'] . "').val('');\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=checkbox]').attr('checked',false);\n";
                            $jsParts[] = "    $('#javatbd" . $sq['rowdivid'] . " input[type=text]').val('');\n";
                            break;
                        case 'L': //LIST drop-down/radio-button list
                            $jsParts[] = "    document.getElementById('tbdisp" . $sq['rowdivid'] . "').value = 'off';\n";
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
                    $jsParts[] = "  document.getElementById('display" . $arg['qid'] . "').value='';\n";
                }
                else {
                    $jsParts[] = "  $('#question" . $arg['qid'] . "').show();\n";
                    $jsParts[] = "  document.getElementById('display" . $arg['qid'] . "').value='on';\n";
                }
                // If it is an equation, and relevance is true, then write the value from the question to the answer field storing the result
                if ($arg['type'] == '*')
                {
                    $jsParts[] = "  // Write value from the question into the answer field\n";
                    $jsParts[] = "  document.getElementById('" . $jsResultVar . "').value=escape(jQuery.trim(LEMstrip_tags($('#question" . $arg['qid'] . " .questiontext').find('span').next().next().html()))).replace(/%20/g,' ');\n";

                }
                $jsParts[] = "  document.getElementById('relevance" . $arg['qid'] . "').value='1';\n";
                $jsParts[] = "}\nelse {\n";
                $jsParts[] = "  $('#question" . $arg['qid'] . "').hide();\n";
                $jsParts[] = "  document.getElementById('display" . $arg['qid'] . "').value='';\n";
                $jsParts[] = "  document.getElementById('relevance" . $arg['qid'] . "').value='0';\n";
                $jsParts[] = "}\n";

                $vars = explode('|',$arg['relevanceVars']);
                if (is_array($vars))
                {
                    $allJsVarsUsed = array_merge($allJsVarsUsed,$vars);
                }
            }
        }

        $jsParts[] = "}\n";

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
                        if ($knownVar['gid']!=$LEM->groupNum)
                        {
                            $undeclaredJsVars[] = $jsVar;
                            $code = $knownVar['sgqa'];
                            $codeValue = (isset($_SESSION[$code])) ? $_SESSION[$code] : '';
                            $undeclaredVal[$jsVar] = $codeValue;

                            if (isset($LEM->jsVar2qid[$jsVar])) {
                                $qidList[$LEM->jsVar2qid[$jsVar]] = $LEM->jsVar2qid[$jsVar];
                            }
                            break;
                        }
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
            if (isset($LEM->qid2code[$qid]))
            {
                $jsParts[] = "<input type='hidden' id='relevance" . $qid . "codes' name='relevance" . $qid . "codes' value='" . $LEM->qid2code[$qid] . "'/>\n";
            }
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
'name' => array('codeValue'=>'Peter', 'jsName'=>'java61764X1X1', 'readWrite'=>'N', 'type'=>'X', 'question'=>'What is your first/given name?', 'questionSeq'=>10, 'groupSeq'=>1),
'surname' => array('codeValue'=>'Smith', 'jsName'=>'java61764X1X1', 'readWrite'=>'Y', 'type'=>'X', 'question'=>'What is your last/surname?', 'questionSeq'=>20, 'groupSeq'=>1),
'age' => array('codeValue'=>45, 'jsName'=>'java61764X1X2', 'readWrite'=>'Y', 'type'=>'X', 'question'=>'How old are you?', 'questionSeq'=>30, 'groupSeq'=>2),
'numKids' => array('codeValue'=>2, 'jsName'=>'java61764X1X3', 'readWrite'=>'Y', 'type'=>'X', 'question'=>'How many kids do you have?', 'relevance'=>'1', 'qid'=>'40','questionSeq'=>40, 'groupSeq'=>2),
'numPets' => array('codeValue'=>1, 'jsName'=>'java61764X1X4', 'readWrite'=>'Y', 'type'=>'X','question'=>'How many pets do you have?', 'questionSeq'=>50, 'groupSeq'=>2),
'gender' => array('codeValue'=>'M', 'jsName'=>'java61764X1X5', 'readWrite'=>'Y', 'type'=>'X', 'shown'=>'Male','question'=>'What is your gender (male/female)?', 'questionSeq'=>110, 'groupSeq'=>2),
'notSetYet' => array('codeValue'=>'?', 'jsName'=>'java61764X3X6', 'readWrite'=>'Y', 'type'=>'X', 'shown'=>'Unknown','question'=>'Who will win the next election?', 'questionSeq'=>200, 'groupSeq'=>3),
// Constants
'INSERTANS:61764X1X1'   => array('codeValue'=> '<Sergei>', 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X', 'questionSeq'=>70, 'groupSeq'=>2),
'INSERTANS:61764X1X2'   => array('codeValue'=> 45, 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X', 'questionSeq'=>80, 'groupSeq'=>2),
'INSERTANS:61764X1X3'   => array('codeValue'=> 2, 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X', 'questionSeq'=>15, 'groupSeq'=>1),
'INSERTANS:61764X1X4'   => array('codeValue'=> 1, 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X', 'questionSeq'=>100, 'groupSeq'=>2),
'TOKEN:ATTRIBUTE_1'     => array('codeValue'=> 'worker', 'jsName'=>'', 'readWrite'=>'N', 'type'=>'X'),
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
            if (isset($var['questionSeq'])) {
                $LEM->questionId2questionSeq[$var['questionSeq']] = $var['questionSeq'];
                $LEM->questionId2groupSeq[$var['questionSeq']] = $var['groupSeq'];
                $_SESSION['relevanceStatus'][$var['questionSeq']] = 1;
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


        LimeExpressionManager::StartProcessingPage(false,true,true);
        LimeExpressionManager::StartProcessingGroup(1); // pretending this is group 1

        // collect variables
        $i=0;
        foreach(explode("\n",$tests) as $test)
        {
            $args = explode("~",$test);
            $vars[$args[0]] = array('codeValue'=>'', 'jsName'=>'java_' . $args[0], 'readWrite'=>'Y', 'type'=>'X', 'relevanceNum'=>'relevance' . $i, 'relevanceStatus'=>'1','groupSeq'=>1, 'questionSeq'=>$i);
            $varSeq[] = $args[0];
            $testArgs[] = $args;
            $LEM->questionId2questionSeq[$i] = $i;
            $LEM->questionId2groupSeq[$i] = 1;
            $LEM->questionSeq2relevance[$i] = array(
                'relevance'=>htmlspecialchars(preg_replace('/[[:space:]]/',' ',$args[1]),ENT_QUOTES),
                'qid'=>$i,
                'questionSeq'=>$i,
                'groupSeq'=>1,
                'jsResultVar'=>'java_' . $args[0],
                'type'=>(($args[1]=='expr') ? '*' : ($args[1]=='message') ? 'X' : 'S'),
                'hidden'=>0,
                'gid'=>1,
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
                . "','qid':" . $i
                . ",'gid':". ($i % 3)   // so have 3 possible group numbers
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
            print "<input type='hidden' id='relevance" . $arg['num'] . "' name='" . $arg['num'] . "' value='" . $rel . "'/>\n";
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
            $where = " qid = ".$qid." and ";
        }
        else if (!is_null($surveyid)) {
            $where = " qid in (select qid from ".db_table_name('questions')." where sid = ".$surveyid.") and ";
        }
        else {
            $where = "";
        }

        // TODO - does this need to be filtered by language?
        $query = "select distinct qid, attribute, value"
                ." from ".db_table_name('question_attributes')
                ." where " . $where
                ." attribute in ('hidden', 'array_filter', 'array_filter_exclude', 'code_filter', 'equals_num_value', 'exclude_all_others', 'exclude_all_others_auto', 'max_answers', 'max_num_value', 'max_num_value_n', 'max_num_value_sgqa', 'min_answers', 'min_num_value', 'min_num_value_n', 'min_num_value_sgqa', 'multiflexible_max', 'multiflexible_min', 'num_value_equals_sgqa', 'show_totals')"
                ." order by qid, attribute";

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

        $query = "SELECT a.qid, a.code, a.answer, a.scale_id"
            ." FROM ".db_table_name('answers')." AS a, ".db_table_name('questions')." as q"
            ." WHERE ".$where
            .$lang
            ." ORDER BY qid, scale_id, sortorder";

        $data = db_execute_assoc($query);

        $qans = array();

        foreach($data->GetRows() as $row) {
            if (!isset($qans[$row['qid']])) {
                $qans[$row['qid']] = array();
            }
            $qans[$row['qid']][$row['scale_id'].'~'.$row['code']] = $row['answer'];
        }

        return $qans;
    }

    /**
     * Validate and/or save
     */
    function ProcessCurrentResponses()
    {

    }
}
?>
