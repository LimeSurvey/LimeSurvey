<?php
/**
 * Description of LimeExpressionManager
 * This is a wrapper class around ExpressionManager that implements a Singleton and eases
 * passing of LimeSurvey variable values into ExpressionManager
 *
 * @author Thomas M. White
 */
include_once('em_core_helper.php');

class LimeExpressionManager {
    private static $instance;
    private static $em;    // Expression Manager
    private $groupRelevanceInfo;
    private $sid;
    private $groupNum;
    private $debugLEM = false;   // set this to false to turn off debugging
    private $knownVars;
    private $pageRelevanceInfo;
    private $pageTailorInfo;
    private $allOnOnePage=false;    // internally set to true for survey.php so get group-specific logging but keep javascript variable namings consistent on the page.
    private $qid2code;  // array of mappings of Question # to list of SGQA codes used within it
    private $jsVar2qid; // reverse mapping of JavaScript Variable name to Question
    private $alias2varName; // JavaScript array of mappings of aliases to the JavaScript variable names
    private $varNameAttr;   // JavaScript array of mappings of canonical JavaScript variable name to key attributes.
    private $pageTailoringLog;  // Debug log of tailorings done on this page
    private $surveyLogicFile;   // Shows current configuration and data from most recent $fieldmap

    private $maxGroup;  // ID of the maximum group reached
    private $questionId2questionSeq;    // map question # to an incremental count of question order across the whole survey
    private $questionId2groupSeq;   // map question  # to the group it is within, using an incremental count of group order

    private $gid2relevanceStatus;   // tracks which groups have at least one relevant, non-hidden question

    private $questionSeq2relevance; // keeps relevance in proper sequence so can minimize relevance processing to see what should be see on page and in indexes
    private $currentGroupSeq;
    private $maxGroupSeq;  // the maximum groupSeq reached -  this is needed for Index
    private $navigationIndex=false; // whether to build an index showing groups that have relevant questions // TODO - color code whether any visible questions are unanswered?
    private $slang='en';
    private $q2subqInfo;
    private $qattr;
    private $syntaxErrors=array();

    private $runtimeTimings;

    // A private constructor; prevents direct creation of object
    private function __construct()
    {
        self::$instance =& $this;
        $this->em = new ExpressionManager();
    }

    // The singleton method
    public static function &singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public static function UpgradeRelevanceAttributeToQuestion()
    {
        $CI =& get_instance();
        $data = $CI->db->where('attribute','relevance')->select('qid')->select('value')->get('question_attributes');

        $queries = array();
        foreach($data->result_array() as $row)
        {
            $info['relevance'] = $row['value'];
            $CI->db->where('qid',$row['qid'])->update('questions',$info);
            $queries[] = $CI->db->last_query();
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

        $data = array();
        foreach ($releqns as $key=>$value) {
            $data[] = array(
                'qid' => $key,
                'attribute' => 'relevance',
                'value' => $value,
            );
        }

        $CI =& get_instance();
        $CI->db->where('attribute','relevance')->where_in('qid',array_keys($releqns))->delete('question_attributes');
        $CI->db->insert_batch('question_attributes',$data);

        return $releqns;
    }

    /**
     * This partially reverses UpgradeConditionsToRelevance().  It removes Relevance for questions that have Conditions
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

        $CI =& get_instance();
        $CI->db->where('attribute','relevance')->where_in('qid',array_keys($releqns))->delete('question_attributes');

        return $CI->db->affected_rows();
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
        $CI =& get_instance();
    	$CI->load->model('conditions_model');

        $query = $CI->conditions_model->getAllRecordsForSurvey($surveyId,$qid);

        $_qid = -1;
        $relevanceEqns = array();
        $scenarios = array();
        $relAndList = array();
        $relOrList = array();
        foreach($query->result_array() as $row)
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
                return $relevanceEqns[$qid];
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

    public function _CreateSubQLevelRelevanceAndValidationEqns()
    {
        $now = microtime(true);

        $subQrels = array();    // array of sub-question-level relevance equations
        $validationEqn = array();

//        log_message('debug',print_r($this->q2subqInfo,true));
//        log_message('debug',print_r($this->qattr,true));

        foreach ($this->q2subqInfo as $qinfo)
        {
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
                                'type' => 'array_filter',
                                'rowdivid' => $sq['rowdivid'],
                                'eqn' => '(' . $sq_name . ' != "")',
                                'qid' => $questionNum
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
                                'type' => 'array_filter_exclude',
                                'rowdivid' => $sq['rowdivid'],
                                'eqn' => '(' . $sq_name . ' == "")',
                                'qid' => $questionNum
                            );
                        }
                    }
                }
            }

            // code_filter
            // TMSW Conditions->Relevance:  How is this supposed to work?

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
                            'type' => 'equals_num_value',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') == (' . $equals_num_value . '))',
                            'qid' => $questionNum
                            );
                    }
                }
            }

            // exclude_all_others
            // TODO

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
                            'type' => 'max_answers',
                            'eqn' => '(count(' . implode(', ', $sq_names) . ') <= (' . $max_answers . '))',
                            'qid' => $questionNum
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
                            'type' => 'max_num_value',
                            'eqn' =>  '(sum(' . implode(', ', $sq_names) . ') <= (' . $max_num_value . '))',
                            'qid' => $questionNum
                        );
                    }
                }
            }

            // max_num_value_n
            // TODO - note, has no subqs

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
                            'type' => 'max_num_value_sgqa',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') <= (' . $max_num_value_sgqa . '))',
                            'qid' => $questionNum
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
                            'type' => 'min_answers',
                            'eqn' => '(count(' . implode(', ', $sq_names) . ') >= (' . $min_answers . '))',
                            'qid' => $questionNum
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
                            'type' => 'min_num_value',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') >= (' . $min_num_value . '))',
                            'qid' => $questionNum
                        );
                    }
                }
            }

            // min_num_value_n
            // TODO - note has no subqs

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
                            'type' => 'min_num_value_sgqa',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') >= (' . $min_num_value_sgqa . '))',
                            'qid' => $questionNum
                        );
                    }
                }
            }

            /* Mistake - multiflexible_max specifies the top value for the pick list, not a sum of values across the entries
            // multiflexible_max
            // Validation:= sum(sq1,...,sqN) <= value (which could be an expression).
            if (isset($qattr['multiflexible_max']) && trim($qattr['multiflexible_max']) != '')
            {
                $multiflexible_max = $qattr['multiflexible_max'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case ':': //ARRAY (Multi Flexi) 1 to 10
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
                            'type' => 'multiflexible_max',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') <= (' . $multiflexible_max . '))',
                            'qid' => $questionNum
                        );
                    }
                }
            }

            // multiflexible_min
            // Validation:= sum(sq1,...,sqN) >= value (which could be an expression).
            if (isset($qattr['multiflexible_min']) && trim($qattr['multiflexible_min']) != '')
            {
                $multiflexible_min = $qattr['multiflexible_min'];
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = NULL;
                        switch ($type)
                        {
                            case ':': //ARRAY (Multi Flexi) 1 to 10
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
                            'type' => 'multiflexible_min',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') >= (' . $multiflexible_min . '))',
                            'qid' => $questionNum
                        );
                    }
                }
            }
             */

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
                            'type' => 'num_value_equals_sgqa',
                            'eqn' => '(sum(' . implode(', ', $sq_names) . ') == (' . $num_value_equals_sgqa . '))',
                            'qid' => $questionNum
                        );
                    }
                }
            }

            // show_totals
            // TODO - create equations for these?

            // assessment_value
            // TODO?  How does it work?
        }
//        log_message('debug','**SUBQUESTION RELEVANCE**' . print_r($subQrels,true));
//        log_message('debug','**VALIDATION EQUATIONS**' . print_r($validationEqn,true));

        foreach ($subQrels as $sq)
        {
            $result = $this->_ProcessSubQRelevance($sq['eqn'], $sq['qid'], $sq['rowdivid'], $sq['type']);
        }
        foreach ($validationEqn as $qvals)
        {
            // HACK - this is to generate the error messages
            foreach ($qvals as $val)
            {
//                $result = $this->_ProcessSubQRelevance($val['eqn'], $val['qid'], 'bogus', $val['type']);
            }
        }

        $this->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
    }

    /**
     * Create the arrays needed by ExpressionManager to process LimeSurvey strings.
     * The long part of this function should only be called once per page display (e.g. only if $fieldMap changes)
     *
     * @param <type> $forceRefresh
     * @param <type> $anonymized
     * @return boolean - true if $fieldmap had been re-created, so ExpressionManager variables need to be re-set
     */

    public function setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh=false,$anonymized=false,$allOnOnePage=false)
    {
        $now = microtime(true);
        $fieldmap=createFieldMap($surveyid,$style='full',$forceRefresh);
        $this->sid= $surveyid;

        $this->runtimeTimings[] = array(__METHOD__ . '.createFieldMap',(microtime(true) - $now));
        $now = microtime(true);

        if (!isset($fieldmap)) {
            return false; // implies an error occurred
        }
        if ($allOnOnePage && isset($this->knownVars)) {
            return false;   // so that does not try to re-compute relevance
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

        $CI =& get_instance();
        $clang = $CI->limesurvey_lang;

        $presets = array();
        $presets['G'] = array(  //GENDER drop-down list
            'M' => $clang->gT("Male"),
            'F' => $clang->gT("Female"),
        );
        $presets['Y'] = array(  //YES/NO radio-buttons
            'Y' => $clang->gT("Yes"),
            'N' => $clang->gT("No"),
        );
        $presets['C'] = array(   //ARRAY (YES/UNCERTAIN/NO) radio-buttons
            'Y' => $clang->gT("Yes"),
            'N' => $clang->gT("No"),
            'U' => $clang->gT("Uncertain"),
        );
        $presets['E'] = array(  //ARRAY (Increase/Same/Decrease) radio-buttons
            'I' => $clang->gT("Increase"),
            'S' => $clang->gT("Same"),
            'D' => $clang->gT("Decrease"),
        );

        $CI->load->model('question_attributes_model');
        $qattr = $CI->question_attributes_model->getEMRelatedRecordsForSurvey($surveyid);   // what happens if $surveyid is null?
        $this->qattr = $qattr;

//        log_message('debug', print_r($qattr, true));

        $this->runtimeTimings[] = array(__METHOD__ . ' - question_attributes_model->getEMRelatedRecordsForSurvey',(microtime(true) - $now));
        $now = microtime(true);

        $CI->load->model('answers_model');
        $qans = $CI->answers_model->getAllAnswersForEM($surveyid);

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
            $isOnCurrentPage = ($allOnOnePage || ($groupNum != NULL && $groupNum == $this->groupNum)) ? 'Y' : 'N';

            $questionId = $fieldNameParts[2];
            $questionNum = $fielddata['qid'];
            $relevance = (isset($fielddata['relevance'])) ? $fielddata['relevance'] : 1;
            $hidden = (isset($qattr[$questionNum]['hidden'])) ? $qattr[$questionNum]['hidden'] : 'N';
            $scale_id = (isset($fielddata['scale_id'])) ? $fielddata['scale_id'] : '0';

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
            if ($groupNum == $this->groupNum) {
                $this->currentGroupSeq = $groupSeq;
            }
            if ($groupNum == $this->maxGroup) {
                $this->maxGroupSeq = $groupSeq;
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

            $codeValue = (isset($_SESSION[$code])) ? $_SESSION[$code] : '';

            // Set $displayValue and $ansArray
            $displayValue = ''; // default to blank or $clang->gT("No Answer")?
            switch($type)
            {
                case '!': //List - dropdown
                case 'L': //LIST drop-down/radio-button list
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                case '1': //Array (Flexible Labels) dual scale  // need scale
                case 'H': //ARRAY (Flexible) - Column Format
                case 'F': //ARRAY (Flexible) - Row Format
                case 'R': //RANKING STYLE
                    $which_ans = $scale_id . '~' . $codeValue;
                    $displayValue = (isset($qans[$questionNum][$which_ans])) ? $qans[$questionNum][$which_ans] : '';    // what should default be?
                    $ansArray = $qans[$questionNum];
                    break;
                case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case '5': //5 POINT CHOICE radio-buttons
                    $displayValue = $codeValue; // what about "no answer"?
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
                    $displayValue = $codeValue; // TODO - is this correct?
                    $ansArray = NULL;
                    break;
                case 'G': //GENDER drop-down list
                case 'Y': //YES/NO radio-buttons
                case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                    $displayValue = (isset($presets[$type][$codeValue])) ? $presets[$type][$codeValue] : '';
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
                    $rowdivid = substr($code,0,-2); // TMSW Check this
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

            // Set $jsVarName (e.g. javaSGQA vs. answerSGQA) - depends upon whether $isOncurrentPage
            switch($type)
            {
                case 'R': //RANKING STYLE
                    if ($isOnCurrentPage=='Y')
                    {
                        $jsVarName = 'fvalue_' . $fieldNameParts[2];
                    }
                    else
                    {
                        $jsVarName = 'java' . $code;
                    }
                    break;
                case 'D': //DATE
                case 'N': //NUMERICAL QUESTION TYPE
                case 'S': //SHORT FREE TEXT
                case 'T': //LONG FREE TEXT
                case 'U': //HUGE FREE TEXT
                case 'Q': //MULTIPLE SHORT TEXT
                case 'K': //MULTIPLE NUMERICAL QUESTION
                case 'X': //BOILERPLATE QUESTION
                    if ($isOnCurrentPage=='Y')
                    {
                        $jsVarName = 'answer' . $code;
                    }
                    else
                    {
                        $jsVarName = 'java' . $code;
                    }
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
                    break;
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                    if (preg_match("/comment$/", $code)) {
                        $jsVarName = 'java' . $code;
                        $varName = $varName . "_comment";
                    }
                    else {
                        $jsVarName = 'java' . $code;
                    }
                    break;
                case '|': //File Upload
                    // Only want the use the one that ends in '_filecount'
                    $goodcode = preg_replace("/^(.*?)(_filecount)?$/","$1",$code);
                    $jsVarName = $goodcode . '_filecount';
                    break;
                case 'P': //Multiple choice with comments checkbox + text
                    if (preg_match("/comment$/",$code) && $isOnCurrentPage=='Y')
                    {
                        $jsVarName = 'answer' . $code;  // is this true for survey.php and not for group.php?
                    }
                    else
                    {
                        $jsVarName = 'java' . $code;
                    }
                    break;
            }
            if (!is_null($rowdivid) || $type == 'L') {
                if (!isset($q2subqInfo[$questionNum])) {
                    $q2subqInfo[$questionNum] = array(
                        'qid' => $questionNum,
                        'gid' => $groupNum,
                        'sgqa' => $surveyid . 'X' . $groupNum . 'X' . $questionNum,
                        'varName' => $varName,
                        'type' => $type,
                        'fieldname' => $code
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
                else
                {
                    $q2subqInfo[$questionNum]['subqs'][] = array(
                        'rowdivid' => $rowdivid,
                        'varName' => $varName,
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
                'codeValue'=>$codeValue,      // TODO - comment
                'jsName'=>$jsVarName,
                'readWrite'=>$readWrite,
                'isOnCurrentPage'=>$isOnCurrentPage,
                'displayValue'=>$displayValue,
                'hidden'=>$hidden,
                'question'=>$question,
                'qid'=>$questionNum,
                'relevance'=>$relevance,
                'relevanceNum'=>'relevance' . $questionNum,
                'qcode'=>$varName,
                'questionSeq'=>$questionSeq,
                'groupSeq'=>$groupSeq,
                'type'=>$type,
                'sgqa'=>$code,
                'rowdivid'=>$rowdivid,
                'ansList'=>$ansList,
                );

//            log_message('debug','$varInfoCode:=' . print_r($varInfo_Code,true));
//            log_message('debug','$fielddata:=' . print_r($fielddata,true));

            $this->questionSeq2relevance[$questionSeq] = array(
                'relevance'=>$relevance,
                'qid'=>$questionNum,
                'questionSeq'=>$questionSeq,
                'groupSeq'=>$groupSeq,
                'jsResultVar'=>$jsVarName,
                'type'=>$type,
                'hidden'=>$hidden,
                'gid'=>$groupNum,
                );

            $this->knownVars[$varName] = $varInfo_Code;
            $this->knownVars['INSERTANS:' . $code] = $varInfo_Code; // $varInfo_DisplayVal;
            $this->knownVars[$code] = $varInfo_Code;

            $this->jsVar2qid[$jsVarName] = $questionNum;

            // Create JavaScript arrays
            $this->alias2varName[$varName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $varName . "':'" . $jsVarName . "'");
            $this->alias2varName[$jsVarName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $jsVarName . "':'" . $jsVarName . "'");
            $this->alias2varName[$code] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $code . "':'" . $jsVarName . "'");
            $this->alias2varName['INSERTANS:' . $code] = array('jsName'=>$jsVarName, 'jsPart' => "'INSERTANS:" . $code . "':'" . $jsVarName . "'");

            $this->varNameAttr[$jsVarName] = "'" . $jsVarName . "':{ "
                . "'jsName':'" . $jsVarName
    //            . "','code':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$codeValue),ENT_QUOTES)
                . "','sgqa':'" . $code
                . "','qid':" . $questionNum
                . ",'mandatory':'" . $mandatory
                . "','question':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$question),ENT_QUOTES)
                . "','type':'" . $type
                . "','relevance':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$relevance),ENT_QUOTES)
                . "','shown':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$displayValue),ENT_QUOTES)
                . "'".$ansList."}";

            if ($this->debugLEM)
            {
                $this->debugLog[] = array(
                    'code' => $code,
                    'type' => $type,
                    'varname' => $varName,
                    'jsName' => $jsVarName,
                    'question' => $question,
                    'codeValue' => ($codeValue=='') ? '&nbsp;' : $codeValue,  // TODO  - comment
                    'displayValue' => ($displayValue=='') ? '&nbsp;' : $displayValue,
                    'readWrite' => $readWrite,
                    'isOnCurrentPage' => $isOnCurrentPage,
                    'relevance' => $relevance,
                    'hidden' => $hidden,
                    );
            }
        }

//        log_message('debug',print_r($q2subqInfo,true));
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
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'isOnCurrentPage'=>'N',
                    'relevanceNum'=>'',
                    );

                if ($this->debugLEM)
                {
                    $this->debugLog[] = array(
                        'code' => $key,
                        'type' => '&nbsp;',
                        'varname' => '&nbsp;',
                        'jsName' => '&nbsp;',
                        'question' => '&nbsp;',
                        'codeValue' => '&nbsp;',
                        'displayValue' => $val,
                        'readWrite'=>'N',
                        'isOnCurrentPage'=>'N',
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
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'isOnCurrentPage'=>'N',
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
                    . "</td><td>" . $t['codeValue']     // TODO - comment
                    . "</td><td>" . $t['displayValue']
                    . "</td><td>" . $t['jsName']
                    . "</td><td>" . $t['readWrite']
                    . "</td><td>" . $t['isOnCurrentPage']
                    . "</td><td>" . $t['relevance']
                    . "</td><td>" . $t['hidden']
                    . "</td><td>" . $t['question']
                    . "</td></tr>";
            }
            $debugLog_html .= "</table>";
            $this->surveyLogicFile = $debugLog_html;
        }

        return true;
    }

    static function QuestionIsRelevant($qid)
    {
        if (isset($_SESSION['relevanceStatus'][$qid])) {
            return $_SESSION['relevanceStatus'][$qid];
        }
        return trues;    // TODO - is this the right default?
    }

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
     * (1) If using index, check all
     */
    function ProcessAllNeededRelevance()
    {
        $now = microtime(true);

        // TODO - refactor this to not call a static function
        $this->gid2relevanceStatus = array();
        $_groupSeq = -1;
        usort($this->questionSeq2relevance,'self::cmpQuestionSeq');
        foreach($this->questionSeq2relevance as $rel)
        {
            $qid = $rel['qid'];
            if ($this->allOnOnePage) {
                ;   // process relevance for all questions
            }
            else {
                $gid = $rel['gid'];
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
                        // only process until know there is at least one non-hidden relevant question to answer
                        // TODO - is this valid logic?
                        //  - if prior-page relevance can change based upon subsequent values, then question and group-level relevance status may change
                        //  - if that is a concern, should re-compute relevance for all questions each page flip
                        if ($_groupSeqVisibility == true) {
                            continue;   // if at least one in the group is visible, then skip relevance check
                        }
                        else {
                            $result = $this->_ProcessRelevance(htmlspecialchars_decode($rel['relevance'],ENT_QUOTES));
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
                    $rel['jsResultVar'],
                    $rel['type'],
                    $rel['hidden']
                    );
            $_SESSION['relevanceStatus'][$qid] = $result;
        }
        $this->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
//        log_message('debug',print_r($_SESSION['relevanceStatus'],true));
    }

    /**
     * Translate all Expressions, Macros, registered variables, etc. in $string
     * @param <type> $string - the string to be replaced
     * @param <type> $replacementFields - optional replacement values
     * @param boolean $debug - if true,write translations for this page to html-formatted log file
     * @param <type> $numRecursionLevels - the number of times to recursively subtitute values in this string
     * @param <type> $whichPrettyPrintIteration - if want to pretty-print the source string, which recursion  level should be pretty-printed
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
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'isOnCurrentPage'=>'N',
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
//            $CI =& get_instance();
//            $CI->db->insert('expression_errors',$error);
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
     * @param <type> $eqn
     * @return <type>
     */
    static function ProcessRelevance($eqn,$questionNum=NULL,$jsResultVar=NULL,$type=NULL,$hidden=0)
    {
        $LEM =& LimeExpressionManager::singleton();
        return $LEM->_ProcessRelevance($eqn,$questionNum,$jsResultVar,$type,$hidden);
    }

    /**
     * Compute Relevance, processing $eqn to get a boolean value.  If there are syntax errors, return false.
     * @param <type> $eqn
     * @param <type> $questionNum
     * @param <type> $jsResultVar
     * @param <type> $type
     * @param <type> $hidden
     * @return <type>
     */
    private function _ProcessRelevance($eqn,$questionNum=NULL,$jsResultVar=NULL,$type=NULL,$hidden=0)
    {
        // These will be called in the order that questions are supposed to be asked
        if (!isset($eqn) || trim($eqn=='') || trim($eqn)=='1')
        {
            $this->groupRelevanceInfo[] = array(
                'qid' => $questionNum,
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
                'gid' => $this->groupNum,
                'gseq' => $groupSeq,
                'qid' => $questionNum,
                'qseq' => $questionSeq,
                'eqn' => $stringToParse,
                'prettyPrint' => $prettyPrint,
            );
            $this->syntaxErrors[] = $error;
//            $CI =& get_instance();
//            $CI->db->insert('expression_errors',$error);
        }

        if (!is_null($questionNum)) {
            $jsVars = $this->em->GetJSVarsUsed();
            $relevanceVars = implode('|',$this->em->GetJSVarsUsed());
            $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();
            $this->groupRelevanceInfo[] = array(
                'qid' => $questionNum,
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

   private function _ProcessSubQRelevance($eqn,$questionNum=NULL,$rowdivid=NULL, $type=NULL)
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
//            $CI =& get_instance();
//            $CI->db->insert('expression_errors',$error);
        }
        else if (!is_null($questionNum)) {
            $jsVars = $this->em->GetJSVarsUsed();
            $relevanceVars = implode('|',$this->em->GetJSVarsUsed());
            $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();
//            $hasErrors = $this->em->HasErrors();
            $this->groupRelevanceInfo[] = array(
                'qid' => $questionNum,
                'eqn' => $eqn,
                'result' => $result,
                'numJsVars' => count($jsVars),
                'relevancejs' => $relevanceJS,
                'relevanceVars' => $relevanceVars,
                'jsResultVar' => $rowdivid, // TMSW - check this
                'rowdivid' => $rowdivid,
//                'prettyPrint' => $prettyPrint,
//                'hasErrors' => $hasErrors,
                'type'=>$type,
                'hidden'=>0
            );
//            log_message('debug',print_r($info,true));
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

    static function StartProcessingPage($navigationIndex=false,$allOnOnePage=false,$debug=true)
    {
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();
        $LEM->pageRelevanceInfo=array();
        $LEM->pageTailorInfo=array();
        $LEM->alias2varName=array();
        $LEM->varNameAttr=array();
        $LEM->allOnOnePage=$allOnOnePage;
        $LEM->pageTailoringLog='';
        $LEM->surveyLogicFile='';
        $LEM->navigationIndex=$navigationIndex;
        $LEM->slang = (isset($_SESSION['s_lang']) ? $_SESSION['s_lang'] : 'en');
        $LEM->q2subqInfo=array();

        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));

        if ($debug && $LEM->debugLEM)
        {
            $LEM->pageTailoringLog .= '<tr><th>Group</th><th>Source</th><th>Pretty Print</th><th>Result</th></tr>';
        }
    }

    static function StartProcessingGroup($groupNum=NULL,$anonymized=false,$surveyid=NULL,$forceRefresh=false)
    {
        $LEM =& LimeExpressionManager::singleton();
        $LEM->em->StartProcessingGroup();
        $LEM->groupRelevanceInfo = array();
        if (!is_null($groupNum))
        {
            $LEM->groupNum = $groupNum;
            if ($groupNum > $LEM->maxGroup) {
                $LEM->maxGroup = $groupNum;
            }
            $LEM->qid2code = array();   // List of codes for each question - needed to know which to NULL if a question is irrelevant
            $LEM->jsVar2qid = array();

            if (!is_null($surveyid) && $LEM->setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh,$anonymized,$LEM->allOnOnePage))
            {
                // means that some values changed, so need to update what was registered to ExpressionManager
                $LEM->em->RegisterVarnamesUsingMerge($LEM->knownVars);
                $LEM->ProcessAllNeededRelevance();  // TODO - what if this is called using Survey or Data Entry format?
                $LEM->_CreateSubQLevelRelevanceAndValidationEqns();
            }
        }
    }

    static function FinishProcessingGroup()
    {
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();
        $LEM->pageTailorInfo[] = $LEM->em->GetCurrentSubstitutionInfo();
        $LEM->pageRelevanceInfo[] = $LEM->groupRelevanceInfo;
        $LEM->runtimeTimings[] = array(__METHOD__,(microtime(true) - $now));
    }

    static function FinishProcessingPage()
    {
        $LEM =& LimeExpressionManager::singleton();
        $_SESSION['EM_pageTailoringLog'] = $LEM->pageTailoringLog;
        $_SESSION['EM_surveyLogicFile'] = $LEM->surveyLogicFile;

        $totalTime = 0.;
        foreach($LEM->runtimeTimings as $unit) {
            $totalTime += $unit[1];
        }
        log_message('debug','Total time attributable to EM = ' . $totalTime);
//        log_message('debug',print_r($LEM->runtimeTimings,true));
//        log_message('debug','**ERRORS**' . print_r($LEM->syntaxErrors,true));
        if (count($LEM->syntaxErrors) > 0)
        {
            $CI =& get_instance();
            $CI->db->insert_batch('expression_errors',$LEM->syntaxErrors);
        }
    }

    static function ShowLogicFile()
    {
        if (isset($_SESSION['EM_surveyLogicFile'])) {
            return $_SESSION['EM_surveyLogicFile'];
        }
        return '';
    }

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
        $now = microtime(true);
        $LEM =& LimeExpressionManager::singleton();

        $knownVars = $LEM->knownVars;

//        log_message('debug',print_r($LEM->pageRelevanceInfo,true));

        $jsParts=array();
        $allJsVarsUsed=array();
        $jsParts[] = '<script type="text/javascript" src="' . base_url() . '/scripts/admin/expressions/em_javascript.js"></script>';
        $jsParts[] = "<script type='text/javascript'>\n<!--\n";
        $jsParts[] = "function ExprMgr_process_relevance_and_tailoring(evt_type){\n";
        $jsParts[] = "if (typeof LEM_initialized == 'undefined') {\nLEM_initialized=true;\nLEMsetTabIndexes();\nreturn;\n}\n";
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

        $subqrels = array();
        if (is_array($pageRelevanceInfo))
        {
            foreach ($pageRelevanceInfo as $arg)
            {
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
                $qidList[$arg['qid']] = $arg['qid'];

                $relevance = $arg['relevancejs'];
                if (isset($arg['rowdivid']))
                {
                    if ($relevance == '' || $relevance == 1) {
                        // always an error?
                        log_message('debug', 'Invalid SubQ Relevance?: ' . print_r($arg,true));
                        continue;
                    }
                    if (!isset($subqrels[$relevance]))
                    {
                        $subqrels[$relevance] = array(
                            'relevance' => $relevance,
                            'targets' => array(),
                            );
                    }
                    $subqrels[$relevance]['targets'][] = $arg['rowdivid'];
                }
                else
                {
                    if (($relevance == '' || $relevance == '1') && count($tailorParts) == 0)
                    {
                        // Only show constitutively true relevances if there is tailoring that should be done.
                        $jsParts[] = "document.getElementById('relevance" . $arg['qid'] . "').value='1'; // always true\n";
                        continue;
                    }
                    $relevance = ($relevance == '') ? '1' : $relevance;
                    $jsResultVar = $LEM->em->GetJsVarFor($arg['jsResultVar']);
                    $jsParts[] = "\n// Process Relevance for Question " . $arg['qid'] . "(" . $arg['jsResultVar'] . "=" . $jsResultVar . "): { " . $arg['eqn'] . " }\n";
                    $jsParts[] = "if (\n";
                    $jsParts[] = $relevance;
                    $jsParts[] = "\n)\n{\n";
                    // Do all tailoring
                    $jsParts[] = implode("\n",$tailorParts);
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
                }

                $vars = explode('|',$arg['relevanceVars']);
                if (is_array($vars))
                {
                    $allJsVarsUsed = array_merge($allJsVarsUsed,$vars);
                }
            }
        }
        // Now process subquestion relevance (e.g. array_filter and array_filter_exclude)
        foreach ($subqrels as $sq)
        {
            $jsParts[] = "if (\n";
            $jsParts[] = $sq['relevance'];
            $jsParts[] = "\n)\n{\n";
            $targets = array_unique($sq['targets']);

            foreach ($targets as $target)
            {
                $jsParts[] = "  $('#javatbd" . $target . "').show();\n";
//                $jsParts[] = "  document.getElementById('tbdisp" . $target . "').value='on';\n";
//                $jsParts[] = "  document.getElementById('relevance" . $target . "').value='1';\n";
            }
            $jsParts[] = "}\nelse {\n";

            foreach ($targets as $target)
            {
                $jsParts[] = "  $('#javatbd" . $target . "').hide();\n";
//                $jsParts[] = "  $('#javatbd" . $target . " input[type=text]').val('');\n";
//                $jsParts[] = "  $('#javatbd" . $target . " input[type=radio]').attr('checked', false);\n";
//                $jsParts[] = "  document.getElementById('tbdisp" . $target . "').value='';\n";
//                $jsParts[] = "  document.getElementById('relevance" . $target . "').value='0';\n";
            }

            $jsParts[] = "}\n";
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
        if (isset($knownVars) && is_array($knownVars))
        {
            foreach ($knownVars as $knownVar)
            {
                foreach ($allJsVarsUsed as $jsVar)
                {
                    if ($jsVar == $knownVar['jsName'])
                    {
                        if ($knownVar['isOnCurrentPage']=='N')
                        {
                            $undeclaredJsVars[] = $jsVar;
                            $undeclaredVal[$jsVar] = $knownVar['codeValue'];

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
        $CI =& get_instance();
        return $CI->db->get('expression_errors')->result_array();
    }

    static function ResetSyntaxErrorLog()
    {
        // truncate the table
        $CI =& get_instance();
        $CI->db->truncate('expression_errors');
    }

    /**
     * Unit test
     */
    static function UnitTestProcessStringContainingExpressions()
    {
        $vars = array(
//'name' => array('codeValue'=>'"<Sergei>\'', 'jsName'=>'java61764X1X1', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
'name' => array('codeValue'=>'Peter', 'jsName'=>'java61764X1X1', 'readWrite'=>'N', 'isOnCurrentPage'=>'N', 'question'=>'What is your first/given name?', 'questionSeq'=>10, 'groupSeq'=>1),
'surname' => array('codeValue'=>'Smith', 'jsName'=>'java61764X1X1', 'readWrite'=>'Y', 'isOnCurrentPage'=>'N', 'question'=>'What is your last/surname?', 'questionSeq'=>20, 'groupSeq'=>1),
'age' => array('codeValue'=>45, 'jsName'=>'java61764X1X2', 'readWrite'=>'Y', 'isOnCurrentPage'=>'N', 'question'=>'How old are you?', 'questionSeq'=>30, 'groupSeq'=>2),
'numKids' => array('codeValue'=>2, 'jsName'=>'java61764X1X3', 'readWrite'=>'Y', 'isOnCurrentPage'=>'N', 'question'=>'How many kids do you have?', 'relevance'=>'1', 'qid'=>'40','questionSeq'=>40, 'groupSeq'=>2),
'numPets' => array('codeValue'=>1, 'jsName'=>'java61764X1X4', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y','question'=>'How many pets do you have?', 'questionSeq'=>50, 'groupSeq'=>2),
'gender' => array('codeValue'=>'M', 'jsName'=>'java61764X1X5', 'readWrite'=>'Y', 'isOnCurrentPage'=>'N', 'shown'=>'Male','question'=>'What is your gender (male/female)?', 'questionSeq'=>110, 'groupSeq'=>2),
'notSetYet' => array('codeValue'=>'?', 'jsName'=>'java61764X3X6', 'readWrite'=>'Y', 'isOnCurrentPage'=>'N', 'shown'=>'Unknown','question'=>'Who will win the next election?', 'questionSeq'=>200, 'groupSeq'=>3),
// Constants
'INSERTANS:61764X1X1'   => array('codeValue'=> '<Sergei>', 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'Y', 'questionSeq'=>70, 'groupSeq'=>2),
'INSERTANS:61764X1X2'   => array('codeValue'=> 45, 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'Y', 'questionSeq'=>80, 'groupSeq'=>2),
'INSERTANS:61764X1X3'   => array('codeValue'=> 2, 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'N', 'questionSeq'=>15, 'groupSeq'=>1),
'INSERTANS:61764X1X4'   => array('codeValue'=> 1, 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'N', 'questionSeq'=>100, 'groupSeq'=>2),
'TOKEN:ATTRIBUTE_1'     => array('codeValue'=> 'worker', 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'N'),
        );

        $tests = <<<EOD
<b>Here is an example of OK syntax with tooltips</b><br/>Hello {if(gender=='M','Mr.','Mrs.')} {surname}, it is now {date('g:i a',time())}.  Do you know where your {sum(numPets,numKids)} chidren and pets are?
<b>Here are common errors so you can see the tooltips</b><br/>Variables used before they are declared:  {notSetYet}<br/>Unknown Function:  {iff(numPets>numKids,1,2)}<br/>Unknown Variable: {sum(age,num_pets,numKids)}<br/>Wrong # parameters: {sprintf()},{if(1,2)},{date()}<br/>Assign read-only-vars:{TOKEN:ATTRIBUTE_1+=10},{name='Sally'}<br/>Unbalanced parentheses: {pow(3,4},{(pow(3,4)},{pow(3,4))}
<b>Here is some of the unsupported syntax</b><br/>No support for '++', '--', '%',';': {min(++age, --age,age % 2);}<br/>Nor '|', '&', '^': {(sum(2 | 3,3 & 4,5 ^ 6)}}<br/>Nor arrays: {name[2], name['mine']}
<b>Values:</b><br/>name={name}; surname={surname}<br/>gender={gender}; age={age}; numPets={numPets}<br/>numKids=INSERTANS:61764X1X3={numKids}={INSERTANS:61764X1X3}<br/>TOKEN:ATTRIBUTE_1={TOKEN:ATTRIBUTE_1}
<b>Question Attributes:</b><br/>numKids.question={numKids.question}; Question#={numKids.qid}; .relevance={numKids.relevance}
<b>Math:</b><br/>5+7={5+7}; 2*pi={2*pi()}; sin(pi/2)={sin(pi()/2)}; max(age,numKids,numPets)={max(age,numKids,numPets)}
<b>Text Processing:</b><br/>{str_replace('like','love','I like LimeSurvey')}<br/>{ucwords('hi there')}, {name}<br/>{implode('--',name,'this is','a convenient way','way to','concatenate strings')}
<b>Dates:</b><br/>{name}, the current date/time is: {date('F j, Y, g:i a',time())}
<b>Conditional:</b><br/>Hello, {if(gender=='M','Mr.','Mrs.')} {surname}, may I call you {name}?
<b>Tailored Paragraph:</b><br/>{name}, you said that you are {age} years old, and that you have {numKids} {if((numKids==1),'child','children')} and {numPets} {if((numPets==1),'pet','pets')} running around the house. So, you have {numKids + numPets} wild {if((numKids + numPets ==1),'beast','beasts')} to chase around every day.<p>Since you have more {if((numKids > numPets),'children','pets')} than you do {if((numKids > numPets),'pets','children')}, do you feel that the {if((numKids > numPets),'pets','children')} are at a disadvantage?</p>
<b>EM processes within strings:</b><br/>Here is your picture [img src='images/users_{name}_{surname}.jpg' alt='{if(gender=='M','Mr.','Mrs.')} {name} {surname}'/];
<b>EM doesn't process curly braces like these:</b><br/>{name}, { this is not an expression}<br/>{nor is this }, { nor  this }<br/>\{nor this\},{this\},\{or this }
<b>Inline JavaScipt that forgot to add spaces after curly brace</b><br/>[script type="text/javascript" language="Javascript"] var job='{TOKEN:ATTRIBUTE_1}'; if (job=='worker') {document.write('BOSSES');}[/script]
<b>Unknown/Misspelled Variables, Functions, and Operators</b><br/>{if(sex=='M','Mr.','Mrs.')} {surname}, next year you will be {age++} years old.
<b>Warns if use = instead of == or perform value assignments</b><br>Hello, {if(gender='M','Mr.','Mrs.')} {surname}, next year you will be {age+=1} years old.
<b>Wrong number of arguments for functions:</b><br/>{if(gender=='M','Mr.','Mrs.','Other')} {surname}, sum(age,numKids,numPets)={sum(age,numKids,numPets,)}
<b>Mismatched parentheses</b><br/>pow(3,4)={pow(3,4)}<br/>but these are wrong: {pow(3,4}, {(pow(3,4)}, {pow(3,4))}
<b>Unsupported syntax</b><br/>No support for '++', '--', '%',';': {min(++age, --age, age % 2);}<br/>Nor '|', '&', '^':  {(sum(2 | 3, 3 & 4, 5 ^ 6)}}<br/>Nor arrays:  {name[2], name['mine']}
<b>Invalid assignments</b><br/>Assign values to equations or strings:  {(3 + 4)=5}, {'hi'='there'}<br/>Assign read-only vars:  {TOKEN:ATTRIBUTE_1='boss'}, {name='Sally'}
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

        print '<table border="1"><tr><th>Test</th><th>Result</th></tr>';    // <th>VarName(jsName, readWrite, isOnCurrentPage)</th></tr>';
        for ($i=0;$i<count($alltests);++$i)
        {
            $test = $alltests[$i];
            $result = LimeExpressionManager::ProcessString($test, 40, NULL, false, 1, 1);
            $prettyPrint = LimeExpressionManager::GetLastPrettyPrintExpression();
            print "<tr><td>" . $prettyPrint . "</td>\n";
            print "<td>" . $result . "</td>\n";
            print "</tr>\n";
        }
        print '</table>';
        LimeExpressionManager::FinishProcessingGroup();
        LimeExpressionManager::FinishProcessingPage();
        print LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
    }

    static function UnitTestRelevance()
    {
        // Tests:  varName~relevance~inputType~message
//info~1~expr~{info='Can strings have embedded <tags> like <html>, or even unbalanced "quotes, \'single quoted strings\', or entities without terminal semicolons like &amp and  &lt?'}
//junk~1~text~Enter "junk" here to test XSS - will show below
//info2~1~message~Here is a messy string: {info}<br/>Here is the "junk" you entered: {junk}
$tests = <<<EOT
name~1~text~What is your name?
age~1~text~How old are you?
badage~1~expr~{badage=((age<16) || (age>80))}
agestop~!is_empty(age) && ((age<16) || (age>80))~message~Sorry, {name}, you are too {if((age<16),'young',if((age>80),'old','middle-aged'))} for this test.
kids~!((age<16) || (age>80))~yesno~Do you have children (Y/N)?
parents~1~expr~{parents = (!badage && kids=='Y')}
numKids~kids=='Y'~text~How many children do you have?
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
        LimeExpressionManager::StartProcessingGroup();

        // collect variables
        $i=0;
        foreach(explode("\n",$tests) as $test)
        {
            $args = explode("~",$test);
            $vars[$args[0]] = array('codeValue'=>'', 'jsName'=>'java_' . $args[0], 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y', 'relevanceNum'=>'relevance' . $i, 'relevanceStatus'=>'1','groupSeq'=>1, 'questionSeq'=>$i);
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
                . "','qid':'" . $i
            . "'}";
        }
        $LEM->alias2varName = $alias2varName;
        $LEM->varNameAttr = $varNameAttr;
        LimeExpressionManager::FinishProcessingGroup();
        LimeExpressionManager::FinishProcessingPage();

        print LimeExpressionManager::GetRelevanceAndTailoringJavaScript();

        // Print Table of questions
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
}
?>
