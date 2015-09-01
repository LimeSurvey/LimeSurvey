<?php
    /**
    * LimeSurvey
    * Copyright (C) 2007-2015 The LimeSurvey Project Team / Carsten Schmitz
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
    * LimeExpressionManager
    * This is a wrapper class around ExpressionManager that implements a Singleton and eases
    * passing of LimeSurvey variable values into ExpressionManager
    *
    * @author LimeSurvey Team (limesurvey.org)
    * @author Thomas M. White (TMSWhite)
    * @author Denis Chenu <http://sondages.pro>
    */
    include_once('em_core_helper.php');
    Yii::app()->loadHelper('database');


    Yii::import("application.libraries.Date_Time_Converter");
    define('LEM_DEBUG_VALIDATION_SUMMARY',2);   // also includes  SQL error messages
    define('LEM_DEBUG_VALIDATION_DETAIL',4);
    define('LEM_PRETTY_PRINT_ALL_SYNTAX',32);

    define('LEM_DEFAULT_PRECISION',12);

    class LimeExpressionManager
    {
        /**
         * LimeExpressionManager is a singleton.  $instance is its storage location.
         * @var LimeExpressionManager
         */
        private static $instance;
        /**
         * Implements the recursive descent parser that processes expressions
         * @var ExpressionManager
         */
        private $em;

        /**
         * sum of LEM_DEBUG constants - use bitwise AND comparisons to identify which parts to use
         * @var type
         */
        private $debugLevel = 0;
        /**
         * sPreviewMode used for relevance equation force to 1 in preview mode
         * Maybe we can set it public
         * @var string
         */
        private $sPreviewMode = false;
        /**
         * Collection of variable attributes, indexed by SGQA code
         *

         /**
         * variables temporarily set for substitution purposes
         *
         * These are typically the LimeReplacement Fields passed in via \ls\helpers\Replacements::templatereplace()
         * Each has the following structure:  array(
         * 'code' => // the static value of the variable
         * 'jsName_on' => // ''
         * 'jsName' => // ''
         * 'readWrite'  => // 'N'
         * );
         *
         * @var type
         */
        private $tempVars;
        /**
         * Array of relevance information for each page (gseq), indexed by gseq.
         * Within a page, it contains a sequential list of the results of each relevance equation processed
         * array(
         * 'qid' => // question id -- e.g. 154
         * 'gseq' => // 0-based group sequence -- e.g. 2
         * 'eqn' => // the raw relevance equation parsed -- e.g. "!is_empty(p2_sex)"
         * 'result' => // the Boolean result of parsing that equation in the current context -- e.g. 0
         * 'numJsVars' => // the number of dynamic JavaScript variables used in that equation -- e.g. 1
         * 'relevancejs' => // the actual JavaScript to insert for that relevance equation -- e.g. "LEMif(LEManyNA('p2_sex'),'',( ! LEMempty(EM.val('p2_sex') )))"
         * 'relevanceVars' => // a pipe-delimited list of JavaScript variables upon which that equation depends -- e.g. "java38612X12X153"
         * 'jsResultVar' => // the JavaScript variable in which that result will be stored -- e.g. "java38612X12X154"
         * 'type' => // the single character type of the question -- e.g. 'S'
         * 'hidden' => // 1 if the question should always be hidden
         * 'hasErrors' => // 1 if there were parsing errors processing that relevance equation
         * @var type
         */
        private $pageRelevanceInfo;


        /**
         * last result of NavigateForwards, NavigateBackwards, or JumpTo
         * Array of status information about last movement, whether at question, group, or survey level
         *
         * @example = array(
         * 'finished' => 0  // 1 if the survey has been completed and needs to be finalized
         * 'message' => ''  // any error message that needs to be displayed
         * 'seq' => 1   // the sequence count, using gseq, or qseq units if in 'group' or 'question' mode, respectively
         * 'mandViolation' => 0 // whether there was any violation of mandatory constraints in the last movement
         * 'valid' => 0 // 1 if the last movement passed all validation constraints.  0 if there were any validation errors
         * 'unansweredSQs' => // pipe-separated list of any sub-questions that were not answered
         * 'invalidSQs' => // pipe-separated list of any sub-questions that failed validation constraints
         * );
         *
         * @var type
         */
        private $lastMoveResult = null;


        /**
         * /**
         * mapping of questions to information about their subquestions.
         * One entry per question, indexed on qid
         *
         * @example [702] = array(
         * 'qid' => 702 // the question id
         * 'qseq' => 6 // the question sequence
         * 'gseq' => 0 // the group sequence
         * 'sgqa' => '26626X34X702' // the root of the SGQA code (reallly just the SGQ)
         * 'varName' => 'afSrcFilter_sq1' // the full qcode variable name - note, if there are sub-questions, don't use this one.
         * 'type' => 'M' // the one-letter question type
         * 'fieldname' => '26626X34X702sq1' // the fieldname (used as JavaScript variable name, and also as database column name
         * 'rootVarName' => 'afDS'  // the root variable name
         * 'preg' => '/[A-Z]+/' // regular expression validation equation, if any
         * 'subqs' => array() of sub-questions, where each contains:
         *     'rowdivid' => '26626X34X702sq1' // the javascript id identifying the question row (so array_filter can hide rows)
         *     'varName' => 'afSrcFilter_sq1' // the full variable name for the sub-question
         *     'jsVarName_on' => 'java26626X34X702sq1' // the JavaScript variable name if the variable is defined on the current page
         *     'jsVarName' => 'java26626X34X702sq1' // the JavaScript variable name to use if the variable is defined on a different page
         *     'csuffix' => 'sq1' // the SGQ suffix to use for a fieldname
         *     'sqsuffix' => '_sq1' // the suffix to use for a qcode variable name
         *  );
         *
         * @var type
         */
        private $q2subqInfo;
        /**
         * array of advanced question attributes for each question
         * Indexed by qid; available for all quetions
         *
         * @example [784] = array(
         * 'array_filter_exclude' => 'afSrcFilter'
         * 'exclude_all_others' => 'sq5'
         * 'max_answers' => '3'
         * 'min_answers' => '1'
         * 'other_replace_text' => '{afSrcFilter_other}'
         * );
         *
         * @var type
         */
        private $qattr;
        /**
         * list of needed sub-question relevance (e.g. array_filter)
         * Indexed by qid then sgqa; only generated for current group of questions
         *
         * @example [708][26626X37X708sq2] = array(
         * 'qid' => '708' // the question id
         * 'eqn' => "((26626X34X702sq2 != ''))" // the auto-generated sub-question-level relevance equation
         * 'prettyPrintEqn' => '' // only generated if there errors - shows syntax highlighting of them
         * 'result' => 0 // result of processing the sub-question-level relevance equation in the current context
         * 'numJsVars' => 1 // the number of on-page javascript variables in 'eqn'
         * 'relevancejs' => // the generated javascript from 'eqn' -- e.g. "LEMif(LEManyNA('26626X34X702sq2'),'',(((EM.val('26626X34X702sq2')  != ""))))"
         * 'relevanceVars' => "java26626X34X702sq2" // the pipe-separated list of on-page javascript variables in 'eqn'
         * 'rowdivid' => "26626X37X708sq2" // the javascript id of the question row (so can apply array_filter)
         * 'type' => 'array_filter' // semicolon delimited list of types of subquestion relevance filters applied
         * 'qtype' => 'A' // the single character question type
         * 'sgqa' => "26626X37X708" // the SGQ portion of the fieldname
         * 'hasErrors' => 0 // 1 if there are any parse errors in the sub-question validation equations
         * );
         *
         * @var type
         */
        private $subQrelInfo = array();
        /**
         * array of Group-level relevance status
         * Indexed by gseq; only shows groups that have been visited
         *
         * @example [1] = array(
         * 'gseq' => 1 // group sequence
         * 'eqn' => '' // the group-level relevance
         * 'result' => 1 // result of processing the group-level relevance
         * 'numJsVars' => 0 // the number of on-page javascript variables in the group-level relevance equation
         * 'relevancejs' => '' // the javascript version of the relevance equation
         * 'relevanceVars' => '' // the pipe-delimited list of on-page javascript variable names used within the group-level relevance equation
         * 'prettyPrint' => '' // a pretty-print version of the group-level relevance equation, only if there are errors
         * );
         *
         * @var type
         */
        private $gRelInfo = array();


        /**
         * True (1) if calling LimeExpressionManager functions between StartSurvey and FinishProcessingPage
         * Used (mostly deprecated) to detect calls to LEM which happen outside of the normal processing scope
         * @var Boolean
         */
        private $initialized = false;
        /**
         * temporary variable to reduce need to parse same equation multiple times.  Used for relevance and validation
         * Array, indexed on equation, providing the following information:
         *
         * @example ['!is_empty(num)'] = array(
         * 'result' => 1 // result of processing the equation in the current scope
         * 'prettyPrint' => '' // syntax-highlighted version of equation if there are any errors
         * 'hasErrors' => 0 // 1 if there are any syntax errors
         * );
         *
         * @var type
         */
        private $ParseResultCache;
        /**
         * array of 2nd scale answer lists for types ':' and ';' -- needed for convenient print of logic file
         * Indexed on qid; available for all questions
         *
         * @example [706] = array(
         * '1~1' => '1|Never',
         * '1~2' => '2|Sometimes',
         * '1~3' => '3|Always'
         * );
         *
         * @var type
         */
        private $multiflexiAnswers;

        /**
         * used to specify whether to  generate equations using SGQA codes or qcodes
         * Default is to convert all qcode naming to sgqa naming when generating javascript, as that provides the greatest backwards compatibility
         * TSV export of survey structure sets this to false so as to force use of qcode naming
         *
         * @var Boolean
         */
        private $sgqaNaming = true;



        /**
         * Linked list of array filters
         * @var array
         */
        private $qrootVarName2arrayFilter = array();
        /**
         * Array, keyed on qid, to JavaScript and list of variables needed to implement exclude_all_others_auto
         * @var type
         */
        private $qid2exclusiveAuto = array();

        /**
         * A private constructor; prevents direct creation of object
         */
        private function __construct()
        {
            self::$instance =& $this;

            $this->em = new ExpressionManager([$this, 'GetVarAttribute'], [App()->surveySessionManager->current, 'getQuestionByCode']);
        }

        /**
         * Ensures there is only one instances of LEM.  Note, if switch between surveys, have to clear this cache
         * @return LimeExpressionManager
         */
        public static function &singleton()
        {
            if (!isset(self::$instance)) {
                bP();
                self::$instance = new static();
                eP();
            }

            return self::$instance;
        }



        /**
         * Prevent users to clone the instance
         */
        public function __clone()
        {
            throw new \Exception('Clone is not allowed.');
        }


        /**
         * Do bulk-update/save of Condition to Relevance
         * @param <integer> $surveyId - if NULL, processes the entire database, otherwise just the specified survey
         * @param <integer> $qid - if specified, just updates that one question
         * @return array of query strings
         */
        public static function UpgradeConditionsToRelevance($surveyId = null, $qid = null)
        {
            LimeExpressionManager::SetDirtyFlag();  // set dirty flag even if not conditions, since must have had a DB change
            // Cheat and upgrade question attributes here too.
            self::UpgradeQuestionAttributes(true, $surveyId, $qid);

            if (is_null($surveyId)) {
                $sQuery = 'SELECT sid FROM {{surveys}}';
                $aSurveyIDs = Yii::app()->db->createCommand($sQuery)->queryColumn();
            } else {
                $aSurveyIDs = array($surveyId);
            }
            foreach ($aSurveyIDs as $surveyId) {
                // echo $surveyId.'<br>';flush();@ob_flush();
                $releqns = self::ConvertConditionsToRelevance($surveyId, $qid);
                if (!empty($releqns)) {
                    foreach ($releqns as $key => $value) {
                        $sQuery = "UPDATE {{questions}} SET relevance=" . Yii::app()->db->quoteValue($value) . " WHERE qid=" . $key;
                        Yii::app()->db->createCommand($sQuery)->execute();
                    }
                }
            }

            LimeExpressionManager::SetDirtyFlag();
        }

        /**
         * This reverses UpgradeConditionsToRelevance().  It removes Relevance for questions that have Condition
         * @param <integer> $surveyId
         * @param <integer> $qid
         */
        public static function RevertUpgradeConditionsToRelevance($surveyId = null, $qid = null)
        {
            LimeExpressionManager::SetDirtyFlag();  // set dirty flag even if not conditions, since must have had a DB change
            $releqns = self::ConvertConditionsToRelevance($surveyId, $qid);
            $num = count($releqns);
            if ($num == 0) {
                return null;
            }

            foreach ($releqns as $key => $value) {
                $query = "UPDATE {{questions}} SET relevance=1 WHERE qid=" . $key;
                dbExecuteAssoc($query);
            }

            return count($releqns);
        }

        /**
         * If $qid is set, returns the relevance equation generated from conditions (or NULL if there are no conditions for that $qid)
         * If $qid is NULL, returns an array of relevance equations generated from Condition, keyed on the question ID
         * @param <integer> $surveyId
         * @param <integer> $qid - if passed, only generates relevance equation for that question - otherwise genereates for all questions with conditions
         * @return array of generated relevance strings, indexed by $qid
         */
        public static function ConvertConditionsToRelevance($surveyId = null, $qid = null)
        {
            $query = LimeExpressionManager::getConditionsForEM($surveyId, $qid);

            $_qid = -1;
            $relevanceEqns = array();
            $scenarios = array();
            $relAndList = array();
            $relOrList = array();
            foreach ($query->readAll() as $row) {
                $row['method'] = trim($row['method']); //For Postgres
                if ($row['qid'] != $_qid) {
                    // output the values for prior question is there was one
                    if ($_qid != -1) {
                        if (count($relOrList) > 0) {
                            $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
                        }
                        if (count($relAndList) > 0) {
                            $scenarios[] = '(' . implode(' and ', $relAndList) . ')';
                        }
                        $relevanceEqn = implode(' or ', $scenarios);
                        $relevanceEqns[$_qid] = $relevanceEqn;
                    }

                    // clear for next question
                    $_qid = $row['qid'];
                    $_scenario = $row['scenario'];
                    $_cqid = $row['cqid'];
                    $_subqid = -1;
                    $relAndList = array();
                    $relOrList = array();
                    $scenarios = array();
                    $releqn = '';
                }
                if ($row['scenario'] != $_scenario) {
                    if (count($relOrList) > 0) {
                        $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
                    }
                    $scenarios[] = '(' . implode(' and ', $relAndList) . ')';
                    $relAndList = array();
                    $relOrList = array();
                    $_scenario = $row['scenario'];
                    $_cqid = $row['cqid'];
                    $_subqid = -1;
                }
                if ($row['cqid'] != $_cqid) {
                    $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
                    $relOrList = array();
                    $_cqid = $row['cqid'];
                    $_subqid = -1;
                }

                // fix fieldnames
                if ($row['type'] == '' && preg_match('/^{.+}$/', $row['cfieldname'])) {
                    $fieldname = substr($row['cfieldname'], 1, -1);    // {TOKEN:xxxx}
                    $subqid = $fieldname;
                    $value = $row['value'];
                } else {
                    if ($row['type'] == 'M' || $row['type'] == 'P') {
                        if (substr($row['cfieldname'], 0, 1) == '+') {
                            // if prefixed with +, then a fully resolved name
                            $fieldname = substr($row['cfieldname'], 1) . '.NAOK';
                            $subqid = $fieldname;
                            $value = $row['value'];
                        } else {
                            // else create name by concatenating two parts together
                            $fieldname = $row['cfieldname'] . $row['value'] . '.NAOK';
                            $subqid = $row['cfieldname'];
                            $value = 'Y';
                        }
                    } else {
                        $fieldname = $row['cfieldname'] . '.NAOK';
                        $subqid = $fieldname;
                        $value = $row['value'];
                    }
                }
                if ($_subqid != -1 && $_subqid != $subqid) {
                    $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
                    $relOrList = array();
                }
                $_subqid = $subqid;

                // fix values
                if (preg_match('/^@\d+X\d+X\d+.*@$/', $value)) {
                    $value = substr($value, 1, -1);
                } else {
                    if (preg_match('/^{.+}$/', $value)) {
                        $value = substr($value, 1, -1);
                    } else {
                        if ($row['method'] == 'RX') {
                            if (!preg_match('#^/.*/$#', $value)) {
                                $value = '"/' . $value . '/"';  // if not surrounded by slashes, add them.
                            }
                        } else {
                            $value = '"' . $value . '"';
                        }
                    }
                }

                // add equation
                if ($row['method'] == 'RX') {
                    $relOrList[] = "regexMatch(" . $value . "," . $fieldname . ")";
                } else {
                    // Condition uses ' ' to mean not answered, but internally it is really stored as ''.  Fix this
                    if ($value === '" "' || $value == '""') {
                        if ($row['method'] == '==') {
                            $relOrList[] = "is_empty(" . $fieldname . ")";
                        } else {
                            if ($row['method'] == '!=') {
                                $relOrList[] = "!is_empty(" . $fieldname . ")";
                            } else {
                                $relOrList[] = $fieldname . " " . $row['method'] . " " . $value;
                            }
                        }
                    } else {
                        if ($value == '"0"' || !preg_match('/^".+"$/', $value)) {
                            switch ($row['method']) {
                                case '==':
                                case '<':
                                case '<=':
                                case '>=':
                                    $relOrList[] = '(!is_empty(' . $fieldname . ') && (' . $fieldname . " " . $row['method'] . " " . $value . '))';
                                    break;
                                case '!=':
                                    $relOrList[] = '(is_empty(' . $fieldname . ') || (' . $fieldname . " != " . $value . '))';
                                    break;
                                default:
                                    $relOrList[] = $fieldname . " " . $row['method'] . " " . $value;
                                    break;
                            }
                        } else {
                            switch ($row['method']) {
                                case '<':
                                case '<=':
                                    $relOrList[] = '(!is_empty(' . $fieldname . ') && (' . $fieldname . " " . $row['method'] . " " . $value . '))';
                                    break;
                                default:
                                    $relOrList[] = $fieldname . " " . $row['method'] . " " . $value;
                                    break;
                            }
                        }
                    }
                }

                if (($row['cqid'] == 0 && !preg_match('/^{TOKEN:([^}]*)}$/',
                            $row['cfieldname'])) || substr($row['cfieldname'], 0, 1) == '+'
                ) {
                    $_cqid = -1;    // forces this statement to be ANDed instead of being part of a cqid OR group (except for TOKEN fields)
                }
            }
            // output last one
            if ($_qid != -1) {
                if (count($relOrList) > 0) {
                    $relAndList[] = '(' . implode(' or ', $relOrList) . ')';
                }
                if (count($relAndList) > 0) {
                    $scenarios[] = '(' . implode(' and ', $relAndList) . ')';
                }
                $relevanceEqn = implode(' or ', $scenarios);
                $relevanceEqns[$_qid] = $relevanceEqn;
            }
            if (is_null($qid)) {
                return $relevanceEqns;
            } else {
                if (isset($relevanceEqns[$qid])) {
                    $result = array();
                    $result[$qid] = $relevanceEqns[$qid];

                    return $result;
                } else {
                    return null;
                }
            }
        }

        /**
         * Return list of relevance equations generated from conditions
         * @param <integer> $surveyId
         * @param <integer> $qid
         * @return array of relevance equations, indexed by $qid
         */
        public static function UnitTestConvertConditionsToRelevance($surveyId = null, $qid = null)
        {
            $LEM =& LimeExpressionManager::singleton();

            return $LEM->ConvertConditionsToRelevance($surveyId, $qid);
        }


        /**
         * Process all question attributes that apply to EM
         * (1) Sub-question-level relevance:  e.g. array_filter, array_filter_exclude, relevance equations entered in SQ-mask
         * (2) Validations: e.g. min/max number of answers; min/max/eq sum of answers
         * @param Question $question The base question
         */
        public function _CreateSubQLevelRelevanceAndValidationEqns(Question $question)
        {
            /**
             * @todo Implement this.
             */
            $session = App()->surveySessionManager->current;
            $subQrels = array();    // array of sub-question-level relevance equations
            $validationEqn = array();
            $validationTips = array();    // array of visible tips for validation criteria, indexed by $qid

            $knownVars = $this->getKnownVars();
            // Associate these with $qid so that can be nested under appropriate question-level relevance

            $questionNum = $question->primaryKey;
            $hasSubqs = (isset($qinfo['subqs']) && count($qinfo['subqs'] > 0));
            $input_boxes = isset($question->bool_input_boxes) && $question->bool_input_boxes;

            $value_range_allows_missing = isset($question->bool_value_range_allows_missing) && $question->bool_value_range_allows_missing;

            // array_filter
            // If want to filter question Q2 on Q1, where each have subquestions SQ1-SQ3, this is equivalent to relevance equations of:
            // relevance for Q2_SQ1 is Q1_SQ1!=''
            $array_filter = null;
            if (isset($question->array_filter) && trim($question->array_filter) != '') {
                $array_filter = $question->array_filter;
                $this->qrootVarName2arrayFilter[$qinfo['rootVarName']]['array_filter'] = $array_filter;
            }

        // array_filter_exclude
        // If want to filter question Q2 on Q1, where each have subquestions SQ1-SQ3, this is equivalent to relevance equations of:
        // relevance for Q2_SQ1 is Q1_SQ1==''
        $array_filter_exclude = null;
        if (isset($question->array_filter_exclude) && trim($question->array_filter_exclude) != '') {
            $array_filter_exclude = $question->array_filter_exclude;
            $this->qrootVarName2arrayFilter[$qinfo['rootVarName']]['array_filter_exclude'] = $array_filter_exclude;
        }

        // array_filter and array_filter_exclude get processed together
        if (!is_null($array_filter) || !is_null($array_filter_exclude)) {
            if ($hasSubqs) {
                $cascadedAF = array();
                $cascadedAFE = array();

                list($cascadedAF, $cascadedAFE) = $this->_recursivelyFindAntecdentArrayFilters($qinfo['rootVarName'],
                    array(), array());

                $cascadedAF = array_reverse($cascadedAF);
                $cascadedAFE = array_reverse($cascadedAFE);

                $subqs = $qinfo['subqs'];
                if ($question->type == Question::TYPE_RANKING) {
                    $subqs = array();
                    foreach ($this->qans[$question->primaryKey] as $k => $v) {
                        $_code = explode('~', $k);
                        $subqs[] = array(
                            'rowdivid' => $question->sgqa . $_code[1],
                            'sqsuffix' => '_' . $_code[1],
                        );
                    }
                }
                $last_rowdivid = '--';
                foreach ($subqs as $sq) {
                    if ($sq['rowdivid'] == $last_rowdivid) {
                        continue;
                    }
                    $last_rowdivid = $sq['rowdivid'];
                    $af_names = array();
                    $afe_names = array();
                    switch ($question->type) {
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
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                        case 'Q': //MULTIPLE SHORT TEXT
                        case 'R': //Ranking
//                                    if ($this->sgqaNaming)
//                                    {
                            foreach ($cascadedAF as $_caf) {
                                $sgq = ((isset($this->qcode2sgq[$_caf])) ? $this->qcode2sgq[$_caf] : $_caf);
                                $fqid = explode('X', $sgq);
                                if (!isset($fqid[2])) {
                                    continue;
                                }
                                $fqid = $fqid[2];
                                if ($this->q2subqInfo[$fqid]['type'] == 'R') {
                                    $rankables = array();
                                    foreach ($this->qans[$fqid] as $k => $v) {
                                        $rankable = explode('~', $k);
                                        $rankables[] = '_' . $rankable[1];
                                    }
                                    if (array_search($sq['sqsuffix'], $rankables) === false) {
                                        continue;
                                    }
                                }
                                $fsqs = array();
                                foreach ($this->q2subqInfo[$fqid]['subqs'] as $fsq) {
                                    if (!isset($fsq['csuffix'])) {
                                        $fsq['csuffix'] = '';
                                    }
                                    if ($this->q2subqInfo[$fqid]['type'] == 'R') {
                                        // we know the suffix exists
                                        $fsqs[] = '(' . $sgq . $fsq['csuffix'] . ".NAOK == '" . substr($sq['sqsuffix'],
                                                1) . "')";
                                    } else {
                                        if ($this->q2subqInfo[$fqid]['type'] == ':' && isset($this->qattr[$fqid]['multiflexible_checkbox']) && $this->qattr[$fqid]['multiflexible_checkbox'] == '1') {
                                            if ($fsq['sqsuffix'] == $sq['sqsuffix']) {
                                                $fsqs[] = $sgq . $fsq['csuffix'] . '.NAOK=="1"';
                                            }
                                        } else {
                                            if ($fsq['sqsuffix'] == $sq['sqsuffix']) {
                                                $fsqs[] = '!is_empty(' . $sgq . $fsq['csuffix'] . '.NAOK)';
                                            }
                                        }
                                    }
                                }
                                if (count($fsqs) > 0) {
                                    $af_names[] = '(' . implode(' or ', $fsqs) . ')';
                                }
                            }
                            foreach ($cascadedAFE as $_cafe) {
                                $sgq = ((isset($this->qcode2sgq[$_cafe])) ? $this->qcode2sgq[$_cafe] : $_cafe);
                                $fqid = explode('X', $sgq);
                                if (!isset($fqid[2])) {
                                    continue;
                                }
                                $fqid = $fqid[2];
                                if ($this->q2subqInfo[$fqid]['type'] == 'R') {
                                    $rankables = array();
                                    foreach ($this->qans[$fqid] as $k => $v) {
                                        $rankable = explode('~', $k);
                                        $rankables[] = '_' . $rankable[1];
                                    }
                                    if (array_search($sq['sqsuffix'], $rankables) === false) {
                                        continue;
                                    }
                                }
                                $fsqs = array();
                                foreach ($this->q2subqInfo[$fqid]['subqs'] as $fsq) {
                                    if ($this->q2subqInfo[$fqid]['type'] == 'R') {
                                        // we know the suffix exists
                                        $fsqs[] = '(' . $sgq . $fsq['csuffix'] . ".NAOK != '" . substr($sq['sqsuffix'],
                                                1) . "')";
                                    } else {
                                        if ($this->q2subqInfo[$fqid]['type'] == ':' && isset($this->qattr[$fqid]['multiflexible_checkbox']) && $this->qattr[$fqid]['multiflexible_checkbox'] == '1') {
                                            if ($fsq['sqsuffix'] == $sq['sqsuffix']) {
                                                $fsqs[] = $sgq . $fsq['csuffix'] . '.NAOK!="1"';
                                            }
                                        } else {
                                            if ($fsq['sqsuffix'] == $sq['sqsuffix']) {
                                                $fsqs[] = 'is_empty(' . $sgq . $fsq['csuffix'] . '.NAOK)';
                                            }
                                        }
                                    }
                                }
                                if (count($fsqs) > 0) {
                                    $afe_names[] = '(' . implode(' and ', $fsqs) . ')';
                                }
                            }
                            break;
                        default:
                            break;
                    }
                    $af_names = array_unique($af_names);
                    $afe_names = array_unique($afe_names);

                    if (count($af_names) > 0 || count($afe_names) > 0) {
                        $afs_eqn = '';
                        if (count($af_names) > 0) {
                            $afs_eqn .= implode(' && ', $af_names);
                        }
                        if (count($afe_names) > 0) {
                            if ($afs_eqn != '') {
                                $afs_eqn .= ' && ';
                            }
                            $afs_eqn .= implode(' && ', $afe_names);
                        }

                        $subQrels[] = array(
                            'qtype' => $question->type,
                            'type' => 'array_filter',
                            'rowdivid' => $sq['rowdivid'],
                            'eqn' => '(' . $afs_eqn . ')',
                            'qid' => $questionNum,
                            'sgqa' => $qinfo['sgqa'],
                        );
                    }
                }
            }
        }

        // individual subquestion relevance
        if ($hasSubqs &&
            $question->type != '|' && $question->type != '!' && $question->type != 'L' && $question->type != 'O'
        ) {
            $subqs = $qinfo['subqs'];
            $last_rowdivid = '--';
            foreach ($subqs as $sq) {
                if ($sq['rowdivid'] == $last_rowdivid) {
                    continue;
                }
                $last_rowdivid = $sq['rowdivid'];
                $rowdivid = null;
                $rowdivid = $sq['rowdivid'];
                switch ($question->type) {
                    case '1': //Array (Flexible Labels) dual scale
                        $rowdivid = $rowdivid . '#0';
                        break;
                    case ':': //ARRAY Numbers
                    case ';': //ARRAY Text
                        $aCsuffix = (explode('_', $sq['csuffix']));
                        $rowdivid = $rowdivid . '_' . $aCsuffix[1];
                        break;
                    case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                    case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                    case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                    case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                    case 'F': //ARRAY (Flexible) - Row Format
                    case 'M': //Multiple choice checkbox
                    case 'P': //Multiple choice with comments checkbox + text
                    case 'K': //MULTIPLE NUMERICAL QUESTION
                    case 'Q': //MULTIPLE SHORT TEXT
                        break;
                    default:
                        break;
                }
                if (isset($knownVars[$rowdivid]['SQrelevance']) & $knownVars[$rowdivid]['SQrelevance'] != '') {
                    $subQrels[] = array(
                        'qtype' => $question->type,
                        'type' => 'SQ_relevance',
                        'rowdivid' => $sq['rowdivid'],
                        'eqn' => $knownVars[$rowdivid]['SQrelevance'],
                        'qid' => $questionNum,
                        'sgqa' => $qinfo['sgqa'],
                    );
                }
            }
        }

         // Default validation for question type
        switch ($question->type) {
            case Question::TYPE_MULTIPLE_NUMERICAL_INPUT: //MULTI NUMERICAL QUESTION TYPE
                if ($hasSubqs) {
                    $subqs = $qinfo['subqs'];
                    $sq_equs = array();
                    $subqValidEqns = array();
                    foreach ($subqs as $sq) {
                        $sq_name = ($this->sgqaNaming) ? $sq['rowdivid'] . ".NAOK" : $sq['varName'] . ".NAOK";
                        $sq_equ = '( is_numeric(' . $sq_name . ') || is_empty(' . $sq_name . ') )';// Leave mandatory to mandatory attribute
                        $subqValidSelector = $sq['jsVarName_on'];
                        if (!is_null($sq_name)) {
                            $sq_equs[] = $sq_equ;
                            $subqValidEqns[$subqValidSelector] = array(
                                'subqValidEqn' => $sq_equ,
                                'subqValidSelector' => $subqValidSelector,
                            );
                        }
                    }
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'default',
                        'class' => 'default',
                        'eqn' => implode(' and ', $sq_equs),
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
                break;
            default:
                break;
        }

        // date_min
        // Maximum date allowed in date question
        if (isset($qattr['date_min']) && trim($qattr['date_min']) != '') {
            $date_min = $qattr['date_min'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'D': //DATE QUESTION TYPE
                            // date_min: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
                            if (trim($qattr['date_min']) != '') {
                                $mindate = $qattr['date_min'];
                                if ((strlen($mindate) == 4) && ($mindate >= 1900) && ($mindate <= 2099)) {
                                    // backward compatibility: if only a year is given, add month and day
                                    $date_min = '\'' . $mindate . '-01-01' . ' 00:00\'';
                                } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/",
                                    $mindate)) {
                                    $date_min = '\'' . $mindate . ' 00:00\'';
                                } elseif (array_key_exists($date_min,
                                    $this->qcode2sgqa))  // refers to another question
                                {
                                    $date_min = $date_min . '.NAOK';
                                }
                            }

                            $sq_name = ($this->sgqaNaming) ? $sq['rowdivid'] . ".NAOK" : $sq['varName'] . ".NAOK";
                            $sq_name = '(is_empty(' . $sq_name . ') || (' . $sq_name . ' >= date("Y-m-d H:i", strtotime(' . $date_min . ')) ))';
                            $subqValidSelector = '';
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                        $subqValidEqns[$subqValidSelector] = array(
                            'subqValidEqn' => $sq_name,
                            'subqValidSelector' => $subqValidSelector,
                        );
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'date_min',
                        'class' => 'value_range',
                        'eqn' => implode(' && ', $sq_names),
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $date_min = '';
        }

        // date_max
        // Maximum date allowed in date question
        if (isset($qattr['date_max']) && trim($qattr['date_max']) != '') {
            $date_max = $qattr['date_max'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'D': //DATE QUESTION TYPE
                            // date_max: Determine whether we have an expression, a full date (YYYY-MM-DD) or only a year(YYYY)
                            if (trim($qattr['date_max']) != '') {
                                $maxdate = $qattr['date_max'];
                                if ((strlen($maxdate) == 4) && ($maxdate >= 1900) && ($maxdate <= 2099)) {
                                    // backward compatibility: if only a year is given, add month and day
                                    $date_max = '\'' . $maxdate . '-12-31 23:59' . '\'';
                                } elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/",
                                    $maxdate)) {
                                    $date_max = '\'' . $maxdate . ' 23:59\'';
                                } elseif (array_key_exists($date_max,
                                    $this->qcode2sgqa))  // refers to another question
                                {
                                    $date_max = $date_max . '.NAOK';
                                }
                            }

                            $sq_name = ($this->sgqaNaming) ? $sq['rowdivid'] . ".NAOK" : $sq['varName'] . ".NAOK";
                            $sq_name = '(is_empty(' . $sq_name . ') || is_empty(' . $date_max . ') || (' . $sq_name . ' <= date("Y-m-d H:i", strtotime(' . $date_max . ')) ))';
                            $subqValidSelector = '';
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                        $subqValidEqns[$subqValidSelector] = array(
                            'subqValidEqn' => $sq_name,
                            'subqValidSelector' => $subqValidSelector,
                        );
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'date_max',
                        'class' => 'value_range',
                        'eqn' => implode(' && ', $sq_names),
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $date_max = '';
        }

        // equals_num_value
        // Validation:= sum(sq1,...,sqN) == value (which could be an expression).
        if (isset($qattr['equals_num_value']) && trim($qattr['equals_num_value']) != '') {
            $equals_num_value = $qattr['equals_num_value'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                            if ($this->sgqaNaming) {
                                $sq_name = $sq['rowdivid'] . '.NAOK';
                            } else {
                                $sq_name = $sq['varName'] . '.NAOK';
                            }
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    // sumEqn and sumRemainingEqn may need to be rounded if using sliders
                    $precision = LEM_DEFAULT_PRECISION;    // default is not to round
                    if (isset($qattr['slider_layout']) && $qattr['slider_layout'] == '1') {
                        $precision = 0;   // default is to round to whole numbers
                        if (isset($qattr['slider_accuracy']) && trim($qattr['slider_accuracy']) != '') {
                            $slider_accuracy = $qattr['slider_accuracy'];
                            $_parts = explode('.', $slider_accuracy);
                            if (isset($_parts[1])) {
                                $precision = strlen($_parts[1]);    // number of digits after mantissa
                            }
                        }
                    }
                    $sumEqn = 'sum(' . implode(', ', $sq_names) . ')';
                    $sumRemainingEqn = '(' . $equals_num_value . ' - sum(' . implode(', ', $sq_names) . '))';
                    $mainEqn = 'sum(' . implode(', ', $sq_names) . ')';

                    if (!is_null($precision)) {
                        $sumEqn = 'round(' . $sumEqn . ', ' . $precision . ')';
                        $sumRemainingEqn = 'round(' . $sumRemainingEqn . ', ' . $precision . ')';
                        $mainEqn = 'round(' . $mainEqn . ', ' . $precision . ')';
                    }

                    $noanswer_option = '';
                    if ($value_range_allows_missing) {
                        $noanswer_option = ' || count(' . implode(', ', $sq_names) . ') == 0';
                    }

                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'equals_num_value',
                        'class' => 'sum_range',
                        'eqn' => ($question->bool_mandatory) ? '(' . $mainEqn . ' == (' . $equals_num_value . '))' : '(' . $mainEqn . ' == (' . $equals_num_value . ')' . $noanswer_option . ')',
                        'qid' => $questionNum,
                        'sumEqn' => $sumEqn,
                        'sumRemainingEqn' => $sumRemainingEqn,
                    );
                }
            }
        } else {
            $equals_num_value = '';
        }

        // exclude_all_others
        // If any excluded options are true (and relevant), then disable all other input elements for that question
        if (isset($qattr['exclude_all_others']) && trim($qattr['exclude_all_others']) != '') {
            $exclusive_options = explode(';', $qattr['exclude_all_others']);
            if ($hasSubqs) {
                foreach ($exclusive_options as $exclusive_option) {
                    $exclusive_option = trim($exclusive_option);
                    if ($exclusive_option == '') {
                        continue;
                    }
                    $subqs = $qinfo['subqs'];
                    $sq_names = array();
                    foreach ($subqs as $sq) {
                        $sq_name = null;
                        if ($sq['csuffix'] == $exclusive_option) {
                            continue;   // so don't make the excluded option irrelevant
                        }
                        switch ($question->type) {
                            case ':': //ARRAY (Multi Flexi) 1 to 10
                            case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                            case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                            case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                            case 'F': //ARRAY (Flexible) - Row Format
                            case 'M': //Multiple choice checkbox
                            case 'P': //Multiple choice with comments checkbox + text
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                            case 'Q': //MULTIPLE SHORT TEXT
                                if ($this->sgqaNaming) {
                                    $sq_name = $qinfo['sgqa'] . trim($exclusive_option) . '.NAOK';
                                } else {
                                    $sq_name = $qinfo['sgqa'] . trim($exclusive_option) . '.NAOK';
                                }
                                break;
                            default:
                                break;
                        }
                        if (!is_null($sq_name)) {
                            $subQrels[] = array(
                                'qtype' => $question->type,
                                'type' => 'exclude_all_others',
                                'rowdivid' => $sq['rowdivid'],
                                'eqn' => 'is_empty(' . $sq_name . ')',
                                'qid' => $questionNum,
                                'sgqa' => $qinfo['sgqa'],
                            );
                        }
                    }
                }
            }
        } else {
            $exclusive_option = '';
        }

        // exclude_all_others_auto
        // if (count(this.relevanceStatus) == count(this)) { set exclusive option value to "Y" and call checkconditions() }
        // However, note that would need to blank the values, not use relevance, otherwise can't unclick the _auto option without having it re-enable itself
        if (isset($qattr['exclude_all_others_auto']) && trim($qattr['exclude_all_others_auto']) == '1'
            && isset($qattr['exclude_all_others']) && trim($qattr['exclude_all_others']) != '' && count(explode(';',
                trim($qattr['exclude_all_others']))) == 1
        ) {
            $exclusive_option = trim($qattr['exclude_all_others']);
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'M': //Multiple choice checkbox
                        case 'P': //Multiple choice with comments checkbox + text
                            if ($this->sgqaNaming) {
                                $sq_name = substr($sq['jsVarName'], 4);
                            } else {
                                $sq_name = $sq['varName'];
                            }
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        if ($sq['csuffix'] == $exclusive_option) {
                            $eoVarName = substr($sq['jsVarName'], 4);
                        } else {
                            $sq_names[] = $sq_name;
                        }
                    }
                }
                if (count($sq_names) > 0) {
                    $relpart = "sum(" . implode(".relevanceStatus, ", $sq_names) . ".relevanceStatus)";
                    $checkedpart = "count(" . implode(".NAOK, ", $sq_names) . ".NAOK)";
                    $eoRelevantAndUnchecked = "(" . $eoVarName . ".relevanceStatus && is_empty(" . $eoVarName . "))";
                    $eoEqn = "(" . $eoRelevantAndUnchecked . " && (" . $relpart . " == " . $checkedpart . "))";

                    $this->em->ProcessBooleanExpression($eoEqn, $session->getGroupIndex($question->gid), $session->getQuestionIndex($question->primaryKey));

                    $relevanceVars = implode('|', $this->em->GetJSVarsUsed());
                    $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();

                    // Unset all checkboxes and hidden values for this question (irregardless of whether they are array filtered)
                    $eosaJS = "if (" . $relevanceJS . ") {\n";
                    $eosaJS .= "  $('#question" . $questionNum . " [type=checkbox]').attr('checked',false);\n";
                    $eosaJS .= "  $('#java" . $qinfo['sgqa'] . "other').val('');\n";
                    $eosaJS .= "  $('#answer" . $qinfo['sgqa'] . "other').val('');\n";
                    $eosaJS .= "  $('[id^=java" . $qinfo['sgqa'] . "]').val('');\n";
                    $eosaJS .= "  $('#answer" . $eoVarName . "').attr('checked',true);\n";
                    $eosaJS .= "  $('#java" . $eoVarName . "').val('Y');\n";
                    $eosaJS .= "  LEMrel" . $questionNum . "();\n";
                    $eosaJS .= "  relChange" . $questionNum . "=true;\n";
                    $eosaJS .= "}\n";

                    $this->qid2exclusiveAuto[$questionNum] = array(
                        'js' => $eosaJS,
                        'relevanceVars' => $relevanceVars,
                        // so that EM knows which variables to declare
                        'rowdivid' => $eoVarName,
                        // to ensure that EM creates a hidden relevanceSGQA input for the exclusive option
                    );
                }
            }
        }
        // input_boxes
        if (isset($qattr['input_boxes']) && $qattr['input_boxes'] == 1) {
            $input_boxes = 1;
            switch ($question->type) {
                case ':': //Array Numbers
                    if ($hasSubqs) {
                        $subqs = $qinfo['subqs'];
                        $sq_equs = array();
                        $subqValidEqns = array();
                        foreach ($subqs as $sq) {
                            $sq_name = ($this->sgqaNaming) ? substr($sq['jsVarName'],
                                    4) . ".NAOK" : $sq['varName'] . ".NAOK";
                            $sq_equ = '( is_numeric(' . $sq_name . ') || is_empty(' . $sq_name . ') )';// Leave mandatory to mandatory attribute (see #08665)
                            $subqValidSelector = $sq['jsVarName_on'];
                            if (!is_null($sq_name)) {
                                $sq_equs[] = $sq_equ;
                                $subqValidEqns[$subqValidSelector] = array(
                                    'subqValidEqn' => $sq_equ,
                                    'subqValidSelector' => $subqValidSelector,
                                );
                            }
                        }
                        if (!isset($validationEqn[$questionNum])) {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $question->type,
                            'type' => 'input_boxes',
                            'class' => 'input_boxes',
                            'eqn' => implode(' and ', $sq_equs),
                            'qid' => $questionNum,
                            'subqValidEqns' => $subqValidEqns,
                        );
                    }
                    break;
                default:
                    break;
            }
        } else {
            $input_boxes = "";
        }

        // min_answers
        // Validation:= count(sq1,...,sqN) >= value (which could be an expression).
        if (isset($qattr['min_answers']) && trim($qattr['min_answers']) != '' && trim($qattr['min_answers']) != '0') {
            $min_answers = $qattr['min_answers'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case '1':   //Array (Flexible Labels) dual scale
                            if (substr($sq['varName'], -1, 1) == '0') {
                                if ($this->sgqaNaming) {
                                    $base = $sq['rowdivid'] . "#";
                                    $sq_name = "if(count(" . $base . "0.NAOK," . $base . "1.NAOK)==2,1,'')";
                                } else {
                                    $base = substr($sq['varName'], 0, -1);
                                    $sq_name = "if(count(" . $base . "0.NAOK," . $base . "1.NAOK)==2,1,'')";
                                }
                            }
                            break;
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case ';': //ARRAY (Multi Flexi) Text
                        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                        case 'F': //ARRAY (Flexible) - Row Format
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                        case 'Q': //MULTIPLE SHORT TEXT
                        case 'M': //Multiple choice checkbox
                        case 'R': //RANKING STYLE
                            if ($this->sgqaNaming) {
                                $sq_name = substr($sq['jsVarName'], 4) . '.NAOK';
                            } else {
                                $sq_name = $sq['varName'] . '.NAOK';
                            }
                            break;
                        case 'P': //Multiple choice with comments checkbox + text
                            if (!preg_match('/comment$/', $sq['varName'])) {
                                if ($this->sgqaNaming) {
                                    $sq_name = $sq['rowdivid'] . '.NAOK';
                                } else {
                                    $sq_name = $sq['rowdivid'] . '.NAOK';
                                }
                            }
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'min_answers',
                        'class' => 'num_answers',
                        'eqn' => 'if(is_empty(' . $min_answers . '),1,(count(' . implode(', ',
                                $sq_names) . ') >= (' . $min_answers . ')))',
                        'qid' => $questionNum,
                    );
                }
            }
        } else {
            $min_answers = '';
        }

        // max_answers
        // Validation:= count(sq1,...,sqN) <= value (which could be an expression).
        if (isset($qattr['max_answers']) && trim($qattr['max_answers']) != '') {
            $max_answers = $qattr['max_answers'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case '1':   //Array (Flexible Labels) dual scale
                            if (substr($sq['varName'], -1, 1) == '0') {
                                if ($this->sgqaNaming) {
                                    $base = $sq['rowdivid'] . "#";
                                    $sq_name = "if(count(" . $base . "0.NAOK," . $base . "1.NAOK)==2,1,'')";
                                } else {
                                    $base = substr($sq['varName'], 0, -1);
                                    $sq_name = "if(count(" . $base . "0.NAOK," . $base . "1.NAOK)==2,1,'')";
                                }
                            }
                            break;
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case ';': //ARRAY (Multi Flexi) Text
                        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                        case 'F': //ARRAY (Flexible) - Row Format
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                        case 'Q': //MULTIPLE SHORT TEXT
                        case 'M': //Multiple choice checkbox
                        case 'R': //RANKING STYLE
                            if ($this->sgqaNaming) {
                                $sq_name = substr($sq['jsVarName'], 4) . '.NAOK';
                            } else {
                                $sq_name = $sq['varName'] . '.NAOK';
                            }
                            break;
                        case 'P': //Multiple choice with comments checkbox + text
                            if (!preg_match('/comment$/', $sq['varName'])) {
                                if ($this->sgqaNaming) {
                                    $sq_name = $sq['rowdivid'] . '.NAOK';
                                } else {
                                    $sq_name = $sq['varName'] . '.NAOK';
                                }
                            }
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'max_answers',
                        'class' => 'num_answers',
                        'eqn' => '(if(is_empty(' . $max_answers . '),1,count(' . implode(', ',
                                $sq_names) . ') <= (' . $max_answers . ')))',
                        'qid' => $questionNum,
                    );
                }
            }
        } else {
            $max_answers = '';
        }

        // Fix min_num_value_n and max_num_value_n for multinumeric with slider: see bug #7798
        if ($question->type == "K" && isset($qattr['slider_min']) && (!isset($qattr['min_num_value_n']) || trim($qattr['min_num_value_n']) == '')) {
            $qattr['min_num_value_n'] = $qattr['slider_min'];
        }
        // min_num_value_n
        // Validation:= N >= value (which could be an expression).
        if (isset($qattr['min_num_value_n']) && trim($qattr['min_num_value_n']) != '') {
            $min_num_value_n = $qattr['min_num_value_n'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                            if ($this->sgqaNaming) {
                                $sq_name = '(is_empty(' . $sq['rowdivid'] . '.NAOK) || ' . $sq['rowdivid'] . '.NAOK >= (' . $min_num_value_n . '))';
                            } else {
                                $sq_name = '(is_empty(' . $sq['varName'] . '.NAOK) || ' . $sq['varName'] . '.NAOK >= (' . $min_num_value_n . '))';
                            }
                            $subqValidSelector = $sq['jsVarName_on'];
                            break;
                        case 'N': //NUMERICAL QUESTION TYPE
                            if ($this->sgqaNaming) {
                                $sq_name = '(is_empty(' . $sq['rowdivid'] . '.NAOK) || ' . $sq['rowdivid'] . '.NAOK >= (' . $min_num_value_n . '))';
                            } else {
                                $sq_name = '(is_empty(' . $sq['varName'] . '.NAOK) || ' . $sq['varName'] . '.NAOK >= (' . $min_num_value_n . '))';
                            }
                            $subqValidSelector = '';
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                        $subqValidEqns[$subqValidSelector] = array(
                            'subqValidEqn' => $sq_name,
                            'subqValidSelector' => $subqValidSelector,
                        );
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'min_num_value_n',
                        'class' => 'value_range',
                        'eqn' => implode(' && ', $sq_names),
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $min_num_value_n = '';
        }

        // Fix min_num_value_n and max_num_value_n for multinumeric with slider: see bug #7798
        if ($question->type == "K" && isset($qattr['slider_max']) && (!isset($qattr['max_num_value_n']) || trim($qattr['max_num_value_n']) == '')) {
            $qattr['max_num_value_n'] = $qattr['slider_max'];
        }
        // max_num_value_n
        // Validation:= N <= value (which could be an expression).
        if (isset($qattr['max_num_value_n']) && trim($qattr['max_num_value_n']) != '') {
            $max_num_value_n = $qattr['max_num_value_n'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                            if ($this->sgqaNaming) {
                                $sq_name = '(is_empty(' . $sq['rowdivid'] . '.NAOK) || ' . $sq['rowdivid'] . '.NAOK <= (' . $max_num_value_n . '))';
                            } else {
                                $sq_name = '(is_empty(' . $sq['varName'] . '.NAOK) || ' . $sq['varName'] . '.NAOK <= (' . $max_num_value_n . '))';
                            }
                            $subqValidSelector = $sq['jsVarName_on'];
                            break;
                        case 'N': //NUMERICAL QUESTION TYPE
                            if ($this->sgqaNaming) {
                                $sq_name = '(is_empty(' . $sq['rowdivid'] . '.NAOK) || ' . $sq['rowdivid'] . '.NAOK <= (' . $max_num_value_n . '))';
                            } else {
                                $sq_name = '(is_empty(' . $sq['varName'] . '.NAOK) || ' . $sq['varName'] . '.NAOK <= (' . $max_num_value_n . '))';
                            }
                            $subqValidSelector = '';
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                        $subqValidEqns[$subqValidSelector] = array(
                            'subqValidEqn' => $sq_name,
                            'subqValidSelector' => $subqValidSelector,
                        );
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'max_num_value_n',
                        'class' => 'value_range',
                        'eqn' => implode(' && ', $sq_names),
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $max_num_value_n = '';
        }

        // min_num_value
        // Validation:= sum(sq1,...,sqN) >= value (which could be an expression).
        if (isset($qattr['min_num_value']) && trim($qattr['min_num_value']) != '') {
            $min_num_value = $qattr['min_num_value'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                            if ($this->sgqaNaming) {
                                $sq_name = $sq['rowdivid'] . '.NAOK';
                            } else {
                                $sq_name = $sq['varName'] . '.NAOK';
                            }
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }

                    $sumEqn = 'sum(' . implode(', ', $sq_names) . ')';
                    $precision = LEM_DEFAULT_PRECISION;
                    if (!is_null($precision)) {
                        $sumEqn = 'round(' . $sumEqn . ', ' . $precision . ')';
                    }

                    $noanswer_option = '';
                    if ($value_range_allows_missing) {
                        $noanswer_option = ' || count(' . implode(', ', $sq_names) . ') == 0';
                    }

                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'min_num_value',
                        'class' => 'sum_range',
                        'eqn' => '(sum(' . implode(', ',
                                $sq_names) . ') >= (' . $min_num_value . ')' . $noanswer_option . ')',
                        'qid' => $questionNum,
                        'sumEqn' => $sumEqn,
                    );
                }
            }
        } else {
            $min_num_value = '';
        }

        // max_num_value
        // Validation:= sum(sq1,...,sqN) <= value (which could be an expression).
        if (isset($qattr['max_num_value']) && trim($qattr['max_num_value']) != '') {
            $max_num_value = $qattr['max_num_value'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                            if ($this->sgqaNaming) {
                                $sq_name = $sq['rowdivid'] . '.NAOK';
                            } else {
                                $sq_name = $sq['varName'] . '.NAOK';
                            }
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }

                    $sumEqn = 'sum(' . implode(', ', $sq_names) . ')';
                    $precision = LEM_DEFAULT_PRECISION;
                    if (!is_null($precision)) {
                        $sumEqn = 'round(' . $sumEqn . ', ' . $precision . ')';
                    }

                    $noanswer_option = '';
                    if ($value_range_allows_missing) {
                        $noanswer_option = ' || count(' . implode(', ', $sq_names) . ') == 0';
                    }

                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'max_num_value',
                        'class' => 'sum_range',
                        'eqn' => '(sum(' . implode(', ',
                                $sq_names) . ') <= (' . $max_num_value . ')' . $noanswer_option . ')',
                        'qid' => $questionNum,
                        'sumEqn' => $sumEqn,
                    );
                }
            }
        } else {
            $max_num_value = '';
        }

        // multiflexible_min
        // Validation:= sqN >= value (which could be an expression).
        if (isset($qattr['multiflexible_min']) && trim($qattr['multiflexible_min']) != '' && $input_boxes) {
            $multiflexible_min = $qattr['multiflexible_min'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case ':': //MULTIPLE NUMERICAL QUESTION
                            if ($this->sgqaNaming) {
                                $sgqa = substr($sq['jsVarName'], 4);
                                $sq_name = '(is_empty(' . $sgqa . '.NAOK) || ' . $sgqa . '.NAOK >= (' . $multiflexible_min . '))';
                            } else {
                                $sq_name = '(is_empty(' . $sq['varName'] . '.NAOK) || ' . $sq['varName'] . '.NAOK >= (' . $multiflexible_min . '))';
                            }
                            $subqValidSelector = $sq['jsVarName_on'];
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                        $subqValidEqns[$subqValidSelector] = array(
                            'subqValidEqn' => $sq_name,
                            'subqValidSelector' => $subqValidSelector,
                        );
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'multiflexible_min',
                        'class' => 'value_range',
                        'eqn' => implode(' && ', $sq_names),
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $multiflexible_min = '';
        }

        // multiflexible_max
        // Validation:= sqN <= value (which could be an expression).
        if (isset($qattr['multiflexible_max']) && trim($qattr['multiflexible_max']) != '' && $input_boxes) {
            $multiflexible_max = $qattr['multiflexible_max'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case ':': //MULTIPLE NUMERICAL QUESTION
                            if ($this->sgqaNaming) {
                                $sgqa = substr($sq['jsVarName'], 4);
                                $sq_name = '(is_empty(' . $sgqa . '.NAOK) || ' . $sgqa . '.NAOK <= (' . $multiflexible_max . '))';
                            } else {
                                $sq_name = '(is_empty(' . $sq['varName'] . '.NAOK) || ' . $sq['varName'] . '.NAOK <= (' . $multiflexible_max . '))';
                            }
                            $subqValidSelector = $sq['jsVarName_on'];
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                        $subqValidEqns[$subqValidSelector] = array(
                            'subqValidEqn' => $sq_name,
                            'subqValidSelector' => $subqValidSelector,
                        );
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'multiflexible_max',
                        'class' => 'value_range',
                        'eqn' => implode(' && ', $sq_names),
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $multiflexible_max = '';
        }

        // min_num_of_files
        // Validation:= sq_filecount >= value (which could be an expression).
        if (isset($qattr['min_num_of_files']) && trim($qattr['min_num_of_files']) != '' && trim($qattr['min_num_of_files']) != '0') {
            $min_num_of_files = $qattr['min_num_of_files'];

            $eqn = '';
            $sgqa = $qinfo['sgqa'];
            switch ($question->type) {
                case '|': //List - dropdown
                    $eqn = "(" . $sgqa . "_filecount >= (" . $min_num_of_files . "))";
                    break;
                default:
                    break;
            }
            if ($eqn != '') {
                if (!isset($validationEqn[$questionNum])) {
                    $validationEqn[$questionNum] = array();
                }
                $validationEqn[$questionNum][] = array(
                    'qtype' => $question->type,
                    'type' => 'min_num_of_files',
                    'class' => 'num_answers',
                    'eqn' => $eqn,
                    'qid' => $questionNum,
                );
            }
        } else {
            $min_num_of_files = '';
        }
        // max_num_of_files
        // Validation:= sq_filecount <= value (which could be an expression).
        if (isset($qattr['max_num_of_files']) && trim($qattr['max_num_of_files']) != '') {
            $max_num_of_files = $qattr['max_num_of_files'];
            $eqn = '';
            $sgqa = $qinfo['sgqa'];
            switch ($question->type) {
                case '|': //List - dropdown
                    $eqn = "(" . $sgqa . "_filecount <= (" . $max_num_of_files . "))";
                    break;
                default:
                    break;
            }
            if ($eqn != '') {
                if (!isset($validationEqn[$questionNum])) {
                    $validationEqn[$questionNum] = array();
                }
                $validationEqn[$questionNum][] = array(
                    'qtype' => $question->type,
                    'type' => 'max_num_of_files',
                    'class' => 'num_answers',
                    'eqn' => $eqn,
                    'qid' => $questionNum,
                );
            }
        } else {
            $max_num_of_files = '';
        }

        // num_value_int_only
        // Validation fixnum(sqN)==int(fixnum(sqN)) : fixnum or not fix num ..... 10.00 == 10
        if (isset($qattr['num_value_int_only']) && trim($qattr['num_value_int_only']) == "1") {
            $num_value_int_only = "1";
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_eqns = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_eqn = null;
                    $subqValidSelector = '';
                    switch ($question->type) {
                        case 'K': //MULTI NUMERICAL QUESTION TYPE (Need a attribute, not set in 131014)
                            $subqValidSelector = $sq['jsVarName_on'];
                        case 'N': //NUMERICAL QUESTION TYPE
                            $sq_name = ($this->sgqaNaming) ? $sq['rowdivid'] . ".NAOK" : $sq['varName'] . ".NAOK";
                            $sq_eqn = 'is_int(' . $sq_name . ') || is_empty(' . $sq_name . ')';
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_eqn)) {
                        $sq_eqns[] = $sq_eqn;
                        $subqValidEqns[$subqValidSelector] = array(
                            'subqValidEqn' => $sq_eqn,
                            'subqValidSelector' => $subqValidSelector,
                        );
                    }
                }
                if (count($sq_eqns) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'num_value_int_only',
                        'class' => 'value_integer',
                        'eqn' => implode(' and ', $sq_eqns),
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $num_value_int_only = '';
        }

        // num_value_int_only
        // Validation is_numeric(sqN)
        if (isset($qattr['numbers_only']) && trim($qattr['numbers_only']) == "1") {
            $numbers_only = 1;
            switch ($question->type) {
                case 'S': // Short text
                    if ($hasSubqs) {
                        $subqs = $qinfo['subqs'];
                        $sq_equs = array();
                        foreach ($subqs as $sq) {
                            $sq_name = ($this->sgqaNaming) ? $sq['rowdivid'] . ".NAOK" : $sq['varName'] . ".NAOK";
                            $sq_equs[] = '( is_numeric(' . $sq_name . ') || is_empty(' . $sq_name . ') )';
                        }
                        if (!isset($validationEqn[$questionNum])) {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $question->type,
                            'type' => 'numbers_only',
                            'class' => 'numbers_only',
                            'eqn' => implode(' and ', $sq_equs),
                            'qid' => $questionNum,
                        );
                    }
                    break;
                case 'Q': // multi text
                    if ($hasSubqs) {
                        $subqs = $qinfo['subqs'];
                        $sq_equs = array();
                        $subqValidEqns = array();
                        foreach ($subqs as $sq) {
                            $sq_name = ($this->sgqaNaming) ? $sq['rowdivid'] . ".NAOK" : $sq['varName'] . ".NAOK";
                            $sq_equ = '( is_numeric(' . $sq_name . ') || is_empty(' . $sq_name . ') )';// Leave mandatory to mandatory attribute
                            $subqValidSelector = $sq['jsVarName_on'];
                            if (!is_null($sq_name)) {
                                $sq_equs[] = $sq_equ;
                                $subqValidEqns[$subqValidSelector] = array(
                                    'subqValidEqn' => $sq_equ,
                                    'subqValidSelector' => $subqValidSelector,
                                );
                            }
                        }
                        if (!isset($validationEqn[$questionNum])) {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $question->type,
                            'type' => 'numbers_only',
                            'class' => 'numbers_only',
                            'eqn' => implode(' and ', $sq_equs),
                            'qid' => $questionNum,
                            'subqValidEqns' => $subqValidEqns,
                        );
                    }
                    break;
                case ';': // Array of text
                    if ($hasSubqs) {
                        $subqs = $qinfo['subqs'];
                        $sq_equs = array();
                        $subqValidEqns = array();
                        foreach ($subqs as $sq) {
                            $sq_name = ($this->sgqaNaming) ? substr($sq['jsVarName'],
                                    4) . ".NAOK" : $sq['varName'] . ".NAOK";
                            $sq_equ = '( is_numeric(' . $sq_name . ') || is_empty(' . $sq_name . ') )';// Leave mandatory to mandatory attribute
                            $subqValidSelector = $sq['jsVarName_on'];
                            if (!is_null($sq_name)) {
                                $sq_equs[] = $sq_equ;
                                $subqValidEqns[$subqValidSelector] = array(
                                    'subqValidEqn' => $sq_equ,
                                    'subqValidSelector' => $subqValidSelector,
                                );
                            }
                        }
                        if (!isset($validationEqn[$questionNum])) {
                            $validationEqn[$questionNum] = array();
                        }
                        $validationEqn[$questionNum][] = array(
                            'qtype' => $question->type,
                            'type' => 'numbers_only',
                            'class' => 'numbers_only',
                            'eqn' => implode(' and ', $sq_equs),
                            'qid' => $questionNum,
                            'subqValidEqns' => $subqValidEqns,
                        );
                    }
                    break;
                case '*': // Don't think we need equation ?
                default:
                    break;
            }
        } else {
            $numbers_only = "";
        }

        // other_comment_mandatory
        // Validation:= sqN <= value (which could be an expression).
        if (isset($qattr['other_comment_mandatory']) && trim($qattr['other_comment_mandatory']) == '1') {
            $other_comment_mandatory = $qattr['other_comment_mandatory'];
            $eqn = '';
            if ($other_comment_mandatory == '1' && $this->questionSeq2relevance[$session->getQuestionIndex($question->primaryKey)]['other'] == 'Y') {
                $sgqa = $qinfo['sgqa'];
                switch ($question->type) {
                    case '!': //List - dropdown
                    case 'L': //LIST drop-down/radio-button list
                        $eqn = "(" . $sgqa . ".NAOK!='-oth-' || (" . $sgqa . ".NAOK=='-oth-' && !is_empty(trim(" . $sgqa . "other.NAOK))))";
                        break;
                    case 'P': //Multiple choice with comments
                        $eqn = "(is_empty(trim(" . $sgqa . "other.NAOK)) || (!is_empty(trim(" . $sgqa . "other.NAOK)) && !is_empty(trim(" . $sgqa . "othercomment.NAOK))))";
                        break;
                    default:
                        break;
                }
            }
            if ($eqn != '') {
                if (!isset($validationEqn[$questionNum])) {
                    $validationEqn[$questionNum] = array();
                }
                $validationEqn[$questionNum][] = array(
                    'qtype' => $question->type,
                    'type' => 'other_comment_mandatory',
                    'class' => 'other_comment_mandatory',
                    'eqn' => $eqn,
                    'qid' => $questionNum,
                );
            }
        } else {
            $other_comment_mandatory = '';
        }

        // other_numbers_only
        // Validation:= is_numeric(sqN).
        if (isset($qattr['other_numbers_only']) && trim($qattr['other_numbers_only']) == '1') {
            $other_numbers_only = 1;
            $eqn = '';
            if ($this->questionSeq2relevance[$session->getQuestionIndex($question->primaryKey)]['other'] == 'Y') {
                $sgqa = $qinfo['sgqa'];
                switch ($question->type) {
                    //case '!': //List - dropdown
                    case 'L': //LIST drop-down/radio-button list
                    case 'M': //Multiple choice
                    case 'P': //Multiple choice with
                        $eqn = "(is_empty(trim(" . $sgqa . "other.NAOK)) ||is_numeric(" . $sgqa . "other.NAOK))";
                        break;
                    default:
                        break;
                }
            }
            if ($eqn != '') {
                if (!isset($validationEqn[$questionNum])) {
                    $validationEqn[$questionNum] = array();
                }
                $validationEqn[$questionNum][] = array(
                    'qtype' => $question->type,
                    'type' => 'other_numbers_only',
                    'class' => 'other_numbers_only',
                    'eqn' => $eqn,
                    'qid' => $questionNum,
                );
            }
        } else {
            $other_numbers_only = '';
        }


        // show_totals
        // TODO - create equations for these?

        // assessment_value
        // TODO?  How does it work?
        // The assessment value (referenced how?) = count(sq1,...,sqN) * assessment_value
        // Since there are easy work-arounds to this, skipping it for now

        // preg - a PHP Regular Expression to validate text input fields
        if (isset($qinfo['preg']) && !is_null($qinfo['preg'])) {
            $preg = $qinfo['preg'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    $subqValidSelector = null;
                    $sgqa = substr($sq['jsVarName'], 4);
                    switch ($question->type) {
                        case 'N': //NUMERICAL QUESTION TYPE
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                        case 'Q': //MULTIPLE SHORT TEXT
                        case ';': //ARRAY (Multi Flexi) Text
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case 'S': //SHORT FREE TEXT
                        case 'T': //LONG FREE TEXT
                        case 'U': //HUGE FREE TEXT
                            if ($this->sgqaNaming) {
                                $sq_name = '(if(is_empty(' . $sgqa . '.NAOK),0,!regexMatch("' . $preg . '", ' . $sgqa . '.NAOK)))';
                            } else {
                                $sq_name = '(if(is_empty(' . $sq['varName'] . '.NAOK),0,!regexMatch("' . $preg . '", ' . $sq['varName'] . '.NAOK)))';
                            }
                            break;
                        default:
                            break;
                    }
                    switch ($question->type) {
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                        case 'Q': //MULTIPLE SHORT TEXT
                        case ';': //ARRAY (Multi Flexi) Text
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                            if ($this->sgqaNaming) {
                                $subqValidEqn = '(is_empty(' . $sgqa . '.NAOK) || regexMatch("' . $preg . '", ' . $sgqa . '.NAOK))';
                            } else {
                                $subqValidEqn = '(is_empty(' . $sq['varName'] . '.NAOK) || regexMatch("' . $preg . '", ' . $sq['varName'] . '.NAOK))';
                            }
                            $subqValidSelector = $sq['jsVarName_on'];
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                        if (isset($subqValidSelector)) {
                            $subqValidEqns[$subqValidSelector] = array(
                                'subqValidEqn' => $subqValidEqn,
                                'subqValidSelector' => $subqValidSelector,
                            );
                        }
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'preg',
                        'class' => 'regex_validation',
                        'eqn' => '(sum(' . implode(', ', $sq_names) . ') == 0)',
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $preg = '';
        }

        // em_validation_q_tip - a description of the EM validation equation that must be satisfied for the whole question.
        if (isset($qattr['em_validation_q_tip']) && !is_null($qattr['em_validation_q_tip']) && trim($qattr['em_validation_q_tip']) != '') {
            $em_validation_q_tip = trim($qattr['em_validation_q_tip']);
        } else {
            $em_validation_q_tip = '';
        }


        // em_validation_q - an EM validation equation that must be satisfied for the whole question.  Uses 'this' in the equation
        if (isset($qattr['em_validation_q']) && !is_null($qattr['em_validation_q']) && trim($qattr['em_validation_q']) != '') {
            $em_validation_q = $qattr['em_validation_q'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                        case 'F': //ARRAY (Flexible) - Row Format
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                        case 'Q': //MULTIPLE SHORT TEXT
                        case ';': //ARRAY (Multi Flexi) Text
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case 'M': //Multiple choice checkbox
                        case 'N': //NUMERICAL QUESTION TYPE
                        case 'O':
                        case 'P': //Multiple choice with comments checkbox + text
                        case 'R': //RANKING STYLE
                        case 'S': //SHORT FREE TEXT
                        case 'T': //LONG FREE TEXT
                        case 'U': //HUGE FREE TEXT
                        case 'D': //DATE
                            if ($this->sgqaNaming) {
                                $sq_name = '!(' . preg_replace('/\bthis\b/', substr($sq['jsVarName'], 4),
                                        $em_validation_q) . ')';
                            } else {
                                $sq_name = '!(' . preg_replace('/\bthis\b/', $sq['varName'],
                                        $em_validation_q) . ')';
                            }
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'em_validation_q',
                        'class' => 'q_fn_validation',
                        'eqn' => '(sum(' . implode(', ', array_unique($sq_names)) . ') == 0)',
                        'qid' => $questionNum,
                    );
                }
            }
        } else {
            $em_validation_q = '';
        }

        // em_validation_sq_tip - a description of the EM validation equation that must be satisfied for each subquestion.
        if (isset($qattr['em_validation_sq_tip']) && !is_null($qattr['em_validation_sq_tip']) && trim($qattr['em_validation_sq']) != '') {
            $em_validation_sq_tip = trim($qattr['em_validation_sq_tip']);
        } else {
            $em_validation_sq_tip = '';
        }


        // em_validation_sq - an EM validation equation that must be satisfied for each subquestion.  Uses 'this' in the equation
        if (isset($qattr['em_validation_sq']) && !is_null($qattr['em_validation_sq']) && trim($qattr['em_validation_sq']) != '') {
            $em_validation_sq = $qattr['em_validation_sq'];
            if ($hasSubqs) {
                $subqs = $qinfo['subqs'];
                $sq_names = array();
                $subqValidEqns = array();
                foreach ($subqs as $sq) {
                    $sq_name = null;
                    switch ($question->type) {
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                        case 'Q': //MULTIPLE SHORT TEXT
                        case ';': //ARRAY (Multi Flexi) Text
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case 'N': //NUMERICAL QUESTION TYPE
                        case 'S': //SHORT FREE TEXT
                        case 'T': //LONG FREE TEXT
                        case 'U': //HUGE FREE TEXT
                            if ($this->sgqaNaming) {
                                $sq_name = '!(' . preg_replace('/\bthis\b/', substr($sq['jsVarName'], 4),
                                        $em_validation_sq) . ')';
                            } else {
                                $sq_name = '!(' . preg_replace('/\bthis\b/', $sq['varName'],
                                        $em_validation_sq) . ')';
                            }
                            break;
                        default:
                            break;
                    }
                    switch ($question->type) {
                        case 'K': //MULTIPLE NUMERICAL QUESTION
                        case 'Q': //MULTIPLE SHORT TEXT
                        case ';': //ARRAY (Multi Flexi) Text
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case 'N': //NUMERICAL QUESTION TYPE
                        case 'S': //SHORT FREE TEXT
                        case 'T': //LONG FREE TEXT
                        case 'U': //HUGE FREE TEXT
                            if ($this->sgqaNaming) {
                                $subqValidEqn = '(' . preg_replace('/\bthis\b/', substr($sq['jsVarName'], 4),
                                        $em_validation_sq) . ')';
                            } else {
                                $subqValidEqn = '(' . preg_replace('/\bthis\b/', $sq['varName'],
                                        $em_validation_sq) . ')';
                            }
                            $subqValidSelector = $sq['jsVarName_on'];
                            break;
                        default:
                            break;
                    }
                    if (!is_null($sq_name)) {
                        $sq_names[] = $sq_name;
                        if (isset($subqValidSelector)) {
                            $subqValidEqns[$subqValidSelector] = array(
                                'subqValidEqn' => $subqValidEqn,
                                'subqValidSelector' => $subqValidSelector,
                            );
                        }
                    }
                }
                if (count($sq_names) > 0) {
                    if (!isset($validationEqn[$questionNum])) {
                        $validationEqn[$questionNum] = array();
                    }
                    $validationEqn[$questionNum][] = array(
                        'qtype' => $question->type,
                        'type' => 'em_validation_sq',
                        'class' => 'sq_fn_validation',
                        'eqn' => '(sum(' . implode(', ', $sq_names) . ') == 0)',
                        'qid' => $questionNum,
                        'subqValidEqns' => $subqValidEqns,
                    );
                }
            }
        } else {
            $em_validation_sq = '';
        }

        ////////////////////////////////////////////
        // COMPOSE USER FRIENDLY MIN/MAX MESSAGES //
        ////////////////////////////////////////////

        // Put these in the order you with them to appear in messages.
        $qtips = array();

        // Default validation qtip without attribute
        switch ($question->type) {
            case 'N':
                $qtips['default'] = gT("Only numbers may be entered in this field.");
                break;
            case 'K':
                $qtips['default'] = gT("Only numbers may be entered in these fields.");
                break;
            case 'R':
                $qtips['default'] = gT("All your answers must be different and you must rank in order.");
                break;
// Helptext is added in qanda_help.php
            /*                  case 'D':
                $qtips['default']=gT("Please complete all parts of the date.");
                break;
*/
            default:
                break;
        }

        if (isset($question->commented_checkbox)) {
            switch ($question->commented_checkbox) {
                case 'checked':
                    $qtips['commented_checkbox'] = gT("Comment only when you choose an answer.");
                    break;
                case 'unchecked':
                    $qtips['commented_checkbox'] = gT("Comment only when you don't choose an answer.");
                    break;
                case 'allways':
                default:
                    $qtips['commented_checkbox'] = gT("Comment your answers.");
                    break;
            }
        }

        // equals_num_value
        if ($equals_num_value != '') {
            $qtips['sum_range'] = sprintf(gT("The sum must equal %s."),
                '{fixnum(' . $equals_num_value . ')}');
        }

        if ($input_boxes && $question->type == Question::TYPE_ARRAY_NUMBERS) {
            $qtips['input_boxes'] = gT("Only numbers may be entered in these fields.");
        }

        // min/max answers
        if ($min_answers != '' || $max_answers != '') {
            $_minA = (($min_answers == '') ? "''" : $min_answers);
            $_maxA = (($max_answers == '') ? "''" : $max_answers);
            /* different messages for text and checkbox questions */
            if ($question->type == 'Q' || $question->type == 'K' || $question->type == ';' || $question->type == ':') {
                $_msgs = array(
                    'atleast_m' => gT("Please fill in at least %s answers"),
                    'atleast_1' => gT("Please fill in at least one answer"),
                    'atmost_m' => gT("Please fill in at most %s answers"),
                    'atmost_1' => gT("Please fill in at most one answer"),
                    '1' => gT("Please fill in at most one answer"),
                    'n' => gT("Please fill in %s answers"),
                    'between' => gT("Please fill in between %s and %s answers")
                );
            } else {
                $_msgs = array(
                    'atleast_m' => gT("Please select at least %s answers"),
                    'atleast_1' => gT("Please select at least one answer"),
                    'atmost_m' => gT("Please select at most %s answers"),
                    'atmost_1' => gT("Please select at most one answer"),
                    '1' => gT("Please select one answer"),
                    'n' => gT("Please select %s answers"),
                    'between' => gT("Please select between %s and %s answers")
                );
            }
            $qtips['num_answers'] =
                "{if(!is_empty($_minA) && is_empty($_maxA) && ($_minA)!=1,sprintf('" . $_msgs['atleast_m'] . "',fixnum($_minA)),'')}" .
                "{if(!is_empty($_minA) && is_empty($_maxA) && ($_minA)==1,sprintf('" . $_msgs['atleast_1'] . "',fixnum($_minA)),'')}" .
                "{if(is_empty($_minA) && !is_empty($_maxA) && ($_maxA)!=1,sprintf('" . $_msgs['atmost_m'] . "',fixnum($_maxA)),'')}" .
                "{if(is_empty($_minA) && !is_empty($_maxA) && ($_maxA)==1,sprintf('" . $_msgs['atmost_1'] . "',fixnum($_maxA)),'')}" .
                "{if(!is_empty($_minA) && !is_empty($_maxA) && ($_minA) == ($_maxA) && ($_minA) == 1,'" . $_msgs['1'] . "','')}" .
                "{if(!is_empty($_minA) && !is_empty($_maxA) && ($_minA) == ($_maxA) && ($_minA) != 1,sprintf('" . $_msgs['n'] . "',fixnum($_minA)),'')}" .
                "{if(!is_empty($_minA) && !is_empty($_maxA) && ($_minA) != ($_maxA),sprintf('" . $_msgs['between'] . "',fixnum($_minA),fixnum($_maxA)),'')}";
        }

        // min/max value for each numeric entry
        if ($min_num_value_n != '' || $max_num_value_n != '') {
            $_minV = (($min_num_value_n == '') ? "''" : $min_num_value_n);
            $_maxV = (($max_num_value_n == '') ? "''" : $max_num_value_n);
            if ($question->type != 'N') {
                $qtips['value_range'] =
                    "{if(!is_empty($_minV) && is_empty($_maxV), sprintf('" . gT("Each answer must be at least %s") . "',fixnum($_minV)), '')}" .
                    "{if(is_empty($_minV) && !is_empty($_maxV), sprintf('" . gT("Each answer must be at most %s") . "',fixnum($_maxV)), '')}" .
                    "{if(!is_empty($_minV) && ($_minV) == ($_maxV),sprintf('" . gT("Each answer must be %s") . "', fixnum($_minV)), '')}" .
                    "{if(!is_empty($_minV) && !is_empty($_maxV) && ($_minV) != ($_maxV), sprintf('" . gT("Each answer must be between %s and %s") . "', fixnum($_minV), fixnum($_maxV)), '')}";
            } else {
                $qtips['value_range'] =
                    "{if(!is_empty($_minV) && is_empty($_maxV), sprintf('" . gT("Your answer must be at least %s") . "',fixnum($_minV)), '')}" .
                    "{if(is_empty($_minV) && !is_empty($_maxV), sprintf('" . gT("Your answer must be at most %s") . "',fixnum($_maxV)), '')}" .
                    "{if(!is_empty($_minV) && ($_minV) == ($_maxV),sprintf('" . gT("Your answer must be %s") . "', fixnum($_minV)), '')}" .
                    "{if(!is_empty($_minV) && !is_empty($_maxV) && ($_minV) != ($_maxV), sprintf('" . gT("Your answer must be between %s and %s") . "', fixnum($_minV), fixnum($_maxV)), '')}";
            }
        }

        // min/max value for dates
        if ($date_min != '' || $date_max != '') {
            //Get date format of current question and convert date in help text accordingly
            $LEM =& LimeExpressionManager::singleton();
            $aAttributes = $session->getQuestion($questionNum)->questionAttributes;
            $aDateFormatData = \ls\helpers\SurveyTranslator::getDateFormatDataForQID($aAttributes[$questionNum], $LEM->surveyOptions);
            $_minV = (($date_min == '') ? "''" : "if((strtotime(" . $date_min . ")), date('" . $aDateFormatData['phpdate'] . "', strtotime(" . $date_min . ")),'')");
            $_maxV = (($date_max == '') ? "''" : "if((strtotime(" . $date_max . ")), date('" . $aDateFormatData['phpdate'] . "', strtotime(" . $date_max . ")),'')");
            $qtips['value_range'] =
                "{if(!is_empty($_minV) && is_empty($_maxV), sprintf('" . gT("Answer must be greater or equal to %s") . "',$_minV), '')}" .
                "{if(is_empty($_minV) && !is_empty($_maxV), sprintf('" . gT("Answer must be less or equal to %s") . "',$_maxV), '')}" .
                "{if(!is_empty($_minV) && ($_minV) == ($_maxV),sprintf('" . gT("Answer must be %s") . "', $_minV), '')}" .
                "{if(!is_empty($_minV) && !is_empty($_maxV) && ($_minV) != ($_maxV), sprintf('" . gT("Answer must be between %s and %s") . "', ($_minV), ($_maxV)), '')}";
        }

        // min/max value for each numeric entry - for multi-flexible question type
        if ($multiflexible_min != '' || $multiflexible_max != '') {
            $_minV = (($multiflexible_min == '') ? "''" : $multiflexible_min);
            $_maxV = (($multiflexible_max == '') ? "''" : $multiflexible_max);
            $qtips['value_range'] =
                "{if(!is_empty($_minV) && is_empty($_maxV), sprintf('" . gT("Each answer must be at least %s") . "',fixnum($_minV)), '')}" .
                "{if(is_empty($_minV) && !is_empty($_maxV), sprintf('" . gT("Each answer must be at most %s") . "',fixnum($_maxV)), '')}" .
                "{if(!is_empty($_minV) && ($_minV) == ($_maxV),sprintf('" . gT("Each answer must be %s") . "', fixnum($_minV)), '')}" .
                "{if(!is_empty($_minV) && !is_empty($_maxV) && ($_minV) != ($_maxV), sprintf('" . gT("Each answer must be between %s and %s") . "', fixnum($_minV), fixnum($_maxV)), '')}";
        }

        // min/max sum value
        if ($min_num_value != '' || $max_num_value != '') {
            $_minV = (($min_num_value == '') ? "''" : $min_num_value);
            $_maxV = (($max_num_value == '') ? "''" : $max_num_value);
            $qtips['sum_range'] =
                "{if(!is_empty($_minV) && is_empty($_maxV), sprintf('" . gT("The sum must be at least %s") . "',fixnum($_minV)), '')}" .
                "{if(is_empty($_minV) && !is_empty($_maxV), sprintf('" . gT("The sum must be at most %s") . "',fixnum($_maxV)), '')}" .
                "{if(!is_empty($_minV) && ($_minV) == ($_maxV),sprintf('" . gT("The sum must equal %s") . "', fixnum($_minV)), '')}" .
                "{if(!is_empty($_minV) && !is_empty($_maxV) && ($_minV) != ($_maxV), sprintf('" . gT("The sum must be between %s and %s") . "', fixnum($_minV), fixnum($_maxV)), '')}";
        }

        // min/max num files
        if ($min_num_of_files != '' || $max_num_of_files != '') {
            $_minA = (($min_num_of_files == '') ? "''" : $min_num_of_files);
            $_maxA = (($max_num_of_files == '') ? "''" : $max_num_of_files);
            // TODO - create em_num_files class so can sepately style num_files vs. num_answers
            $qtips['num_answers'] =
                "{if(!is_empty($_minA) && is_empty($_maxA) && ($_minA)!=1,sprintf('" . gT("Please upload at least %s files") . "',fixnum($_minA)),'')}" .
                "{if(!is_empty($_minA) && is_empty($_maxA) && ($_minA)==1,sprintf('" . gT("Please upload at least one file") . "',fixnum($_minA)),'')}" .
                "{if(is_empty($_minA) && !is_empty($_maxA) && ($_maxA)!=1,sprintf('" . gT("Please upload at most %s files") . "',fixnum($_maxA)),'')}" .
                "{if(is_empty($_minA) && !is_empty($_maxA) && ($_maxA)==1,sprintf('" . gT("Please upload at most one file") . "',fixnum($_maxA)),'')}" .
                "{if(!is_empty($_minA) && !is_empty($_maxA) && ($_minA) == ($_maxA) && ($_minA) == 1,'" . gT("Please upload one file") . "','')}" .
                "{if(!is_empty($_minA) && !is_empty($_maxA) && ($_minA) == ($_maxA) && ($_minA) != 1,sprintf('" . gT("Please upload %s files") . "',fixnum($_minA)),'')}" .
                "{if(!is_empty($_minA) && !is_empty($_maxA) && ($_minA) != ($_maxA),sprintf('" . gT("Please upload between %s and %s files") . "',fixnum($_minA),fixnum($_maxA)),'')}";
        }


        // integer for numeric
        if ($num_value_int_only != '') {
            switch ($question->type) {
                case 'N':
                    $qtips['default'] = '';
                    $qtips['value_integer'] = gT("Only an integer value may be entered in this field.");
                    break;
                case 'K':
                    $qtips['default'] = '';
                    $qtips['value_integer'] = gT("Only integer values may be entered in these fields.");
                    break;
                default:
                    break;
            }
        }

        // numbers only
        if ($numbers_only) {
            switch ($question->type) {
                case 'S':
                    $qtips['numbers_only'] = gT("Only numbers may be entered in this field.");
                    break;
                case 'Q':
                case ';':
                    $qtips['numbers_only'] = gT("Only numbers may be entered in these fields.");
                    break;
                default:
                    break;
            }
        }

        // other comment mandatory
        if ($other_comment_mandatory != '') {
            if (isset($qattr['other_replace_text']) && trim($qattr['other_replace_text']) != '') {
                $othertext = trim($qattr['other_replace_text']);
            } else {
                $othertext = gT('Other:');
            }
            $qtips['other_comment_mandatory'] = sprintf(gT("If you choose '%s' please also specify your choice in the accompanying text field."),
                $othertext);
        }

        // other comment mandatory
        if ($other_numbers_only != '') {
            if (isset($qattr['other_replace_text']) && trim($qattr['other_replace_text']) != '') {
                $othertext = trim($qattr['other_replace_text']);
            } else {
                $othertext = gT('Other:');
            }
            $qtips['other_numbers_only'] = sprintf(gT("Only numbers may be entered in '%s' accompanying text field."),
                $othertext);
        }

        // regular expression validation
        if ($preg != '') {
            // do string replacement here so that curly braces within the regular expression don't trigger an EM error
            //                $qtips['regex_validation']=sprintf(gT('Each answer must conform to this regular expression: %s'), str_replace(array('{','}'),array('{ ',' }'), $preg));
            $qtips['regex_validation'] = gT('Please check the format of your answer.');
        }

        if ($em_validation_sq != '') {
            if ($em_validation_sq_tip == '') {
                //                    $stringToParse = htmlspecialchars_decode($em_validation_sq,ENT_QUOTES);
                //                    $gseq = $this->questionId2groupSeq[$question->primaryKey];
                //                    $result = $this->em->ProcessBooleanExpression($stringToParse,$gseq,  $session->getQuestionIndex($question->primaryKey));
                //                    $_validation_tip = $this->em->GetPrettyPrintString();
                //                    $qtips['sq_fn_validation']=sprintf(gT('Each answer must conform to this expression: %s'),$_validation_tip);
            } else {
                $qtips['sq_fn_validation'] = $em_validation_sq_tip;
            }

            }

            // em_validation_q - whole-question validation equation
            if ($em_validation_q != '') {
                if ($em_validation_q_tip == '') {
                    //                    $stringToParse = htmlspecialchars_decode($em_validation_q,ENT_QUOTES);
                    //                    $gseq = $this->questionId2groupSeq[$question->primaryKey];
                    //                    $result = $this->em->ProcessBooleanExpression($stringToParse,$gseq,  $session->getQuestionIndex($question->primaryKey));
                    //                    $_validation_tip = $this->em->GetPrettyPrintString();
                    //                    $qtips['q_fn_validation']=sprintf(gT('The question must conform to this expression: %s'), $_validation_tip);
                } else {
                    $qtips['q_fn_validation'] = $em_validation_q_tip;
                }
            }

            if (count($qtips) > 0) {
                $validationTips[$questionNum] = $qtips;
            }


            // Consolidate logic across array filters
            $rowdivids = array();
            $order = 0;
            foreach ($subQrels as $sq) {
                $oldeqn = (isset($rowdivids[$sq['rowdivid']]['eqns']) ? $rowdivids[$sq['rowdivid']]['eqns'] : array());
                $oldtype = (isset($rowdivids[$sq['rowdivid']]['type']) ? $rowdivids[$sq['rowdivid']]['type'] : '');
                $neweqn = (($sq['type'] == 'exclude_all_others') ? array() : array($sq['eqn']));
                $oldeo = (isset($rowdivids[$sq['rowdivid']]['exclusive_options']) ? $rowdivids[$sq['rowdivid']]['exclusive_options'] : array());
                $neweo = (($sq['type'] == 'exclude_all_others') ? array($sq['eqn']) : array());
                $rowdivids[$sq['rowdivid']] = array(
                    'order' => $order++,
                    'qid' => $sq['qid'],
                    'rowdivid' => $sq['rowdivid'],
                    'type' => $sq['type'] . ';' . $oldtype,
                    'qtype' => $sq['qtype'],
                    'sgqa' => $sq['sgqa'],
                    'eqns' => array_merge($oldeqn, $neweqn),
                    'exclusive_options' => array_merge($oldeo, $neweo),
                );
            }

            foreach ($rowdivids as $sq) {
                $sq['eqn'] = implode(' and ', array_unique(array_merge($sq['eqns'],
                    $sq['exclusive_options'])));   // without array_unique, get duplicate of filters for question types 1, :, and ;
                $eos = array_unique($sq['exclusive_options']);
                $isExclusive = '';
                $irrelevantAndExclusive = '';
                if (count($eos) > 0) {
                    $isExclusive = '!(' . implode(' and ', $eos) . ')';
                    $noneos = array_unique($sq['eqns']);
                    if (count($noneos) > 0) {
                        $irrelevantAndExclusive = '(' . implode(' and ', $noneos) . ') and ' . $isExclusive;
                    }
                }
                $this->_ProcessSubQRelevance($sq['eqn'], $sq['qid'], $sq['rowdivid'], $sq['type'], $sq['qtype'],
                    $sq['sgqa'], $isExclusive, $irrelevantAndExclusive);
            }

            foreach ($validationEqn as $qid => $eqns) {
                $parts = array();
                $tips = (isset($validationTips[$qid]) ? $validationTips[$qid] : array());
                $subqValidEqns = array();
                $sumEqn = '';
                $sumRemainingEqn = '';
                foreach ($eqns as $v) {
                    if (!isset($parts[$v['class']])) {
                        $parts[$v['class']] = array();
                    }
                    $parts[$v['class']][] = $v['eqn'];
                    // even if there are min/max/preg, the count or total will always be the same
                    $sumEqn = (isset($v['sumEqn'])) ? $v['sumEqn'] : $sumEqn;
                    $sumRemainingEqn = (isset($v['sumRemainingEqn'])) ? $v['sumRemainingEqn'] : $sumRemainingEqn;
                    if (isset($v['subqValidEqns'])) {
                        $subqValidEqns[] = $v['subqValidEqns'];
                    }
                }
                // combine the sub-question level validation equations into a single validation equation per sub-question
                $subqValidComposite = array();
                foreach ($subqValidEqns as $sqs) {
                    foreach ($sqs as $sq) {
                        if (!isset($subqValidComposite[$sq['subqValidSelector']])) {
                            $subqValidComposite[$sq['subqValidSelector']] = array(
                                'subqValidSelector' => $sq['subqValidSelector'],
                                'subqValidEqns' => array(),
                            );
                        }
                        $subqValidComposite[$sq['subqValidSelector']]['subqValidEqns'][] = $sq['subqValidEqn'];
                    }
                }
                $csubqValidEqns = array();
                foreach ($subqValidComposite as $csq) {
                    $csubqValidEqns[$csq['subqValidSelector']] = array(
                        'subqValidSelector' => $csq['subqValidSelector'],
                        'subqValidEqn' => implode(' && ', $csq['subqValidEqns']),
                    );
                }
                // now combine all classes of validation equations
                $veqns = [];
                foreach ($parts as $vclass => $eqns) {
                    $veqns[$vclass] = '(' . implode(' and ', $eqns) . ')';
                }
                $this->qid2validationEqn[$qid] = array(
                    'eqn' => $veqns,
                    'tips' => $tips,
                    'subqValidEqns' => $csubqValidEqns,
                    'sumEqn' => $sumEqn,
                    'sumRemainingEqn' => $sumRemainingEqn,
                );
            }


        }

        /**
         * Recursively find all questions that logically preceded the current array_filter or array_filter_exclude request
         * Note, must support:
         * (a) semicolon-separated list of $qroot codes for either array_filter or array_filter_exclude
         * (b) mixed history of array_filter and array_filter_exclude values
         * @param type $qroot - the question root variable name
         * @param type $aflist - the list of array_filter $qroot codes
         * @param type $afelist - the list of array_filter_exclude $qroot codes
         * @return type
         */
        private function _recursivelyFindAntecdentArrayFilters($qroot, $aflist, $afelist)
        {
            if (isset($this->qrootVarName2arrayFilter[$qroot])) {
                if (isset($this->qrootVarName2arrayFilter[$qroot]['array_filter'])) {
                    $_afs = explode(';', $this->qrootVarName2arrayFilter[$qroot]['array_filter']);
                    foreach ($_afs as $_af) {
                        if (in_array($_af, $aflist)) {
                            continue;
                        }
                        $aflist[] = $_af;
                        list($aflist, $afelist) = $this->_recursivelyFindAntecdentArrayFilters($_af, $aflist, $afelist);
                    }
                }
                if (isset($this->qrootVarName2arrayFilter[$qroot]['array_filter_exclude'])) {
                    $_afes = explode(';', $this->qrootVarName2arrayFilter[$qroot]['array_filter_exclude']);
                    foreach ($_afes as $_afe) {
                        if (in_array($_afe, $afelist)) {
                            continue;
                        }
                        $afelist[] = $_afe;
                        list($aflist, $afelist) = $this->_recursivelyFindAntecdentArrayFilters($_afe, $aflist,
                            $afelist);
                    }
                }
            }

            return array($aflist, $afelist);
        }

        /**
         * Return whether a sub-question is relevant
         * @param <type> $sgqa
         * @return <boolean>
         */
        public static function SubQuestionIsRelevant($sgqa)
        {
            $LEM =& LimeExpressionManager::singleton();
            $knownVars = $LEM->getKnownVars();
            if (!isset($knownVars[$sgqa])) {
                return false;
            }
            $var = $knownVars[$sgqa];
            $sqrel = 1;
            if (isset($var['rowdivid']) && $var['rowdivid'] != '') {
                $sqrel = (isset($_SESSION[$LEM->sessid]['relevanceStatus'][$var['rowdivid']]) ? $_SESSION[$LEM->sessid]['relevanceStatus'][$var['rowdivid']] : 1);
            }
            $qid = $var['qid'];
            $qrel = (isset($_SESSION[$LEM->sessid]['relevanceStatus'][$qid]) ? $_SESSION[$LEM->sessid]['relevanceStatus'][$qid] : 1);
            $gseq = $var['gseq'];
            $grel = (isset($_SESSION[$LEM->sessid]['relevanceStatus']['G' . $gseq]) ? $_SESSION[$LEM->sessid]['relevanceStatus']['G' . $gseq] : 1);   // group-level relevance based upon grelevance equation
            return ($grel && $qrel && $sqrel);
        }

        /**
         * Translate all Expressions, Macros, registered variables, etc. in $string
         * @param string $string - the string to be replaced
         * @param integer $questionNum - the $qid of question being replaced - needed for properly alignment of question-level relevance and tailoring
         * @param array $replacementFields - optional replacement values
         * @param integer $numRecursionLevels - the number of times to recursively subtitute values in this string
         * @param integer $whichPrettyPrintIteration - if want to pretty-print the source string, which recursion  level should be pretty-printed
         * @return string - the original $string with all replacements done.
         * @internal param bool $debug - deprecated
         * @internal param bool $staticReplacement - return HTML string without the system to update by javascript
         * @internal param bool $noReplacements - true if we already know that no replacements are needed (e.g. there are no curly braces)
         * @internal param bool $timeit
         */

        public static function ProcessString(
            $string,
            $questionNum = null,
            $replacementFields = [],
            $numRecursionLevels = 1,
            $whichPrettyPrintIteration = 1
        ) {
            bP();
            $session = App()->surveySessionManager->current;
            $LEM =& LimeExpressionManager::singleton();

            if (count($replacementFields) > 0) {
                $replaceArray = array();
                foreach ($replacementFields as $key => $value) {
                    $replaceArray[$key] = array(
                        'code' => $value,
                        'jsName_on' => '',
                        'jsName' => '',
                        'readWrite' => 'N',
                    );
                }
                $LEM->tempVars = $replaceArray;
            }

            if (isset($questionNum)) {
                $questionSeq = $session->getQuestionIndex($questionNum);
                $groupSeq = $session->getGroupIndex($session->getQuestion($questionNum)->gid);
            } else {
                $questionSeq = -1;
                $groupSeq = -1;
            }

            $result = $LEM->em->sProcessStringContainingExpressions($string, $numRecursionLevels,
                $whichPrettyPrintIteration, $groupSeq, $questionSeq);
//            vd($result);
            eP();
            return $result;
        }


        /**
         * Compute Relevance, processing $eqn to get a boolean value.  If there are syntax errors, return false.
         * @param <type> $eqn - the relevance equation
         * @param <type> $questionNum - needed to align question-level relevance and tailoring
         * @param <type> $jsResultVar - this variable determines whether irrelevant questions are hidden
         * @param <type> $question->type - question type
         * @param <type> $hidden - whether question should always be hidden
         * @return <type>
         */
        static function ProcessRelevance($eqn, $questionNum = null, $jsResultVar = null, $type = null, $hidden = 0)
        {
            $LEM =& LimeExpressionManager::singleton();

            return $LEM->_ProcessRelevance($eqn, $questionNum, null, $jsResultVar, $type, $hidden);
        }

        /**
         * Compute Relevance, processing $eqn to get a boolean value.  If there are syntax errors, return false.
         * @param <type> $eqn - the relevance equation
         * @param <type> $questionNum - needed to align question-level relevance and tailoring
         * @param <type> $jsResultVar - this variable determines whether irrelevant questions are hidden
         * @param <type> $question->type - question type
         * @param <type> $hidden - whether question should always be hidden
         * @return <type>
         */
        private function _ProcessRelevance(
            $eqn,
            $questionNum = null,
            $gseq = null,
            $jsResultVar = null,
            $type = null,
            $hidden = 0
        ) {
            $session = App()->surveySessionManager->current;
            // These will be called in the order that questions are supposed to be asked
            // TODO - cache results and generated JavaScript equations?

            $questionSeq = -1;
            $groupSeq = -1;
            if (!is_null($questionNum)) {
                $questionSeq = $session->getQuestionIndex($questionNum);
                $groupSeq = isset($this->questionId2groupSeq[$questionNum]) ? $this->questionId2groupSeq[$questionNum] : -1;
            }

            $stringToParse = htmlspecialchars_decode($eqn, ENT_QUOTES);
            $result = $this->em->ProcessBooleanExpression($stringToParse, $groupSeq, $questionSeq);
            $hasErrors = $this->em->HasErrors();

            if (!is_null($questionNum) && !is_null($jsResultVar)) { // so if missing either, don't generate JavaScript for this - means off-page relevance.
                $jsVars = $this->em->GetJSVarsUsed();
                $relevanceVars = implode('|', $this->em->GetJSVarsUsed());
                $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();
                $this->groupRelevanceInfo[] = array(
                    'qid' => $questionNum,
                    'gseq' => $gseq,
                    'eqn' => $eqn,
                    'result' => $result,
                    'numJsVars' => count($jsVars),
                    'relevancejs' => $relevanceJS,
                    'relevanceVars' => $relevanceVars,
                    'jsResultVar' => $jsResultVar,
                    'type' => $type,
                    'hidden' => $hidden,
                    'hasErrors' => $hasErrors,
                );
            }

            return $result;
        }

        /**
         * Create JavaScript needed to process sub-question-level relevance (e.g. for array_filter and  _exclude)
         * @param <type> $eqn - the equation to parse
         * @param <type> $questionNum - the question number - needed to align relavance and tailoring blocks
         * @param <type> $rowdivid - the javascript ID that needs to be shown/hidden in order to control array_filter visibility
         * @param <type> $question->type - the type of sub-question relevance (e.g. 'array_filter', 'array_filter_exclude')
         * @return <type>
         */
        private function _ProcessSubQRelevance(
            $eqn,
            $questionNum = null,
            $rowdivid = null,
            $type = null,
            $qtype = null,
            $sgqa = null,
            $isExclusive = '',
            $irrelevantAndExclusive = ''
        ) {
            // These will be called in the order that questions are supposed to be asked
            if (!isset($eqn) || trim($eqn == '') || trim($eqn) == '1') {
                return true;
            }
            $questionSeq = -1;
            $groupSeq = -1;
            if (!is_null($questionNum)) {
                $questionSeq = $session->getQuestionIndex($questionNum);
                $groupSeq = isset($this->questionId2groupSeq[$questionNum]) ? $this->questionId2groupSeq[$questionNum] : -1;
            }

            $stringToParse = htmlspecialchars_decode($eqn, ENT_QUOTES);
            $result = $this->em->ProcessBooleanExpression($stringToParse, $groupSeq, $questionSeq);
            $hasErrors = $this->em->HasErrors();
            $prettyPrint = '';
            if (($this->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                $prettyPrint = $this->em->GetPrettyPrintString();
            }

            if (!is_null($questionNum)) {
                // make sure subquestions with errors in relevance equations are always shown and answers recorded  #7703
                if ($hasErrors) {
                    $result = true;
                    $relevanceJS = 1;
                } else {
                    $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();
                }
                $jsVars = $this->em->GetJSVarsUsed();
                $relevanceVars = implode('|', $this->em->GetJSVarsUsed());
                $isExclusiveJS = '';
                $irrelevantAndExclusiveJS = '';
                // Only need to extract JS, since will already have Vars and error counts from main equation
                if ($isExclusive != '') {
                    $this->em->ProcessBooleanExpression($isExclusive, $groupSeq, $questionSeq);
                    $isExclusiveJS = $this->em->GetJavaScriptEquivalentOfExpression();
                }
                if ($irrelevantAndExclusive != '') {
                    $this->em->ProcessBooleanExpression($irrelevantAndExclusive, $groupSeq, $questionSeq);
                    $irrelevantAndExclusiveJS = $this->em->GetJavaScriptEquivalentOfExpression();
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
                    'type' => $type,
                    'qtype' => $qtype,
                    'sgqa' => $sgqa,
                    'hasErrors' => $hasErrors,
                    'isExclusiveJS' => $isExclusiveJS,
                    'irrelevantAndExclusiveJS' => $irrelevantAndExclusiveJS,
                );
            }

            return $result;
        }

        /**
         * @param Question $question
         *
         * @throws Exception
         * @return array(
         * 'relevance' => "!is_empty(num)"  // the question-level relevance equation
         * 'grelevance' => ""   // the group-level relevance equation
         * 'qid' => "699" // the question id
         * 'qseq' => 3  // the 0-index question sequence
         * 'gseq' => 0  // the 0-index group sequence
         * 'jsResultVar_on' => 'answer26626X34X699' // the javascript variable holding the input value
         * 'jsResultVar' => 'java26226X34X699'  // the javascript variable (often hidden) holding the value to be submitted
         * 'type' => 'N'    // the one character question type
         * 'hidden' => 0    // 1 if it should be always_hidden
         * 'gid' => "34"    // group id
         * 'mandatory' => 'N'   // 'Y' if mandatory
         * 'eqn' => ""  // TODO ??
         * 'help' => "" // the help text
         * 'qtext' => "Enter a larger number than {num}"    // the question text
         * 'code' => 'afDS_sq5_1' // the full variable name
         * 'other' => 'N'   // whether the question supports the 'other' option - 'Y' if true
         * 'rowdivid' => '2626X37X705sq5'   // the javascript id for the row - in this case, the 5th sub-question
         * 'aid' => 'sq5'   // the answer id
         * 'sqid' => '791' // the sub-question's qid (only populated for some question types)
         * );
         */
        public function getQuestionRelevanceInfo(Question $question)
        {
            static $requestCache = [];
            bP();
            $session = App()->surveySessionManager->current;

            $questionCounter = 0;
            foreach ($question->fields as $sgqa => $details) {


                // Set $jsVarName_on (for on-page variables - e.g. answerSGQA) and $jsVarName (for off-page  variables; the primary name - e.g. javaSGQA)
                $jsVarName = 'java' . $sgqa;
                $jsVarName_on = 'answer' . $sgqa;
                switch ($question->type) {
                    case '!': //List - dropdown
                        if (preg_match("/other$/", $sgqa)) {
                            $jsVarName_on = 'othertext' . substr($sgqa, 0, -5);
                        } else {
                            $jsVarName_on = $jsVarName;
                        }
                        break;
                    case 'L': //LIST drop-down/radio-button list
                        if (preg_match("/other$/", $sgqa)) {
                            $jsVarName_on = 'answer' . $sgqa . "text";
                        } else {
                            $jsVarName_on = $jsVarName;
                        }
                        break;
                    case Question::TYPE_LIST_WITH_COMMENT: //LIST WITH COMMENT drop-down/radio-button list + textarea
                            $jsVarName_on = 'java' . $sgqa;
                        break;
                    case '1': //Array (Flexible Labels) dual scale
                        $jsVarName = 'java' . str_replace('#', '_', $sgqa);
                        $jsVarName_on = $jsVarName;
                        break;
                    case '|': //File Upload
                        $jsVarName_on = $jsVarName;
                        break;
                    case 'P': //Multiple choice with comments checkbox + text
                        if (preg_match("/(other|comment)$/", $sgqa)) {
                            $jsVarName_on = 'answer' . $sgqa;  // is this true for survey.php and not for group.php?
                        } else {
                            $jsVarName_on = $jsVarName;
                        }
                        break;
                }
                // Hidden question are never on same page (except for equation)
                if ($question->bool_hidden && $question->type != Question::TYPE_EQUATION) {
                    $jsVarName_on = '';
                }

                $result = [
                    'jsResultVar_on' => $jsVarName_on,
                    'jsResultVar' => $jsVarName,

                ];

            }
            eP();
            return $result;
        }

        public function getGroupRelevanceInfo(QuestionGroup $group)
        {
            bP();
            $session = App()->surveySessionManager->current;

            $eqn = $group->grelevance;
            if (is_null($eqn) || trim($eqn == '') || trim($eqn) == '1') {
                $result = [
                    'eqn' => '',
                    'result' => 1,
                    'numJsVars' => 0,
                    'relevancejs' => '',
                    'relevanceVars' => '',
                    'prettyprint' => '',
                ];
            } else {
                $stringToParse = htmlspecialchars_decode($eqn, ENT_QUOTES);
                $parseResult = $this->em->ProcessBooleanExpression($stringToParse, $groupSequence);
                $hasErrors = $this->em->HasErrors();

                $jsVars = $this->em->GetJSVarsUsed();
                $relevanceVars = implode('|', $this->em->GetJSVarsUsed());
                $relevanceJS = $this->em->GetJavaScriptEquivalentOfExpression();
                $prettyPrint = $this->em->GetPrettyPrintString();

                $result = [
                    'eqn' => $stringToParse,
                    'result' => $parseResult,
                    'numJsVars' => count($jsVars),
                    'relevancejs' => $relevanceJS,
                    'relevanceVars' => $relevanceVars,
                    'prettyprint' => $prettyPrint,
                    'hasErrors' => $hasErrors,
                ];
            }

            $result['gid'] = $group->primaryKey;
            eP();
            return $result;
        }


        /**
         * Used to show potential syntax errors of processing Relevance or Equations.
         * @return <type>
         */
        public static function GetLastPrettyPrintExpression()
        {
            $LEM =& LimeExpressionManager::singleton();

            return $LEM->em->GetLastPrettyPrintExpression();
        }



        /**
         * Should be first function called on each page - sets/clears internally needed variables
         * @param <boolean> $initializeVars - if true, initializes the replacement variables to enable syntax highlighting on admin pages
         */
        public static function StartProcessingPage($initializeVars = false)
        {
            $LEM =& LimeExpressionManager::singleton();
            $session = App()->surveySessionManager->current;
            if ($initializeVars) {
                $LEM->em->StartProcessingGroup(
                    $session->surveyId,
                    '',
                    true
                );
            }
        }

        /**
         * Initialize a survey so can use EM to manage navigation
         * @param int $surveyid
         * @param string $surveyMode
         * @param array $aSurveyOptions
         * @param bool $forceRefresh
         * @param int $debugLevel
         */
        static function StartSurvey(
            $surveyid,
            $forceRefresh = false,
            $debugLevel = 0
        ) {
            $LEM =& LimeExpressionManager::singleton();
            $LEM->em->StartProcessingGroup($surveyid);

            $LEM->debugLevel = $debugLevel;
            $LEM->qrootVarName2arrayFilter = array();
//            if (isset($_SESSION[$LEM->sessid]['startingValues']) && is_array($_SESSION[$surveyid]['startingValues']) && count($_SESSION[$surveyid]['startingValues']) > 0) {
//                $startingValues = array();
//                foreach ($_SESSION[$LEM->sessid]['startingValues'] as $k => $value) {
//                    if (isset($LEM->knownVars[$k])) {
//                        $knownVar = $LEM->knownVars[$k];
//                    } else {
//                        if (isset($LEM->qcode2sgqa[$k])) {
//                            $knownVar = $LEM->knownVars[$LEM->qcode2sgqa[$k]];
//                        } else {
//                            if (isset($LEM->tempVars[$k])) {
//                                $knownVar = $LEM->tempVar[$k];
//                            } else {
//                                continue;
//                            }
//                        }
//                    }
//                    if (!isset($knownVar['jsName'])) {
//                        continue;
//                    }
//                    switch ($knownVar['type']) {
//                        case 'D': //DATE
//                            if (trim($value) == "" | $value=='INVALID') {
//                                $value = null;
//                            } else {
//                                $dateformatdatat = \ls\helpers\SurveyTranslator::getDateFormatData($LEM->surveyOptions['surveyls_dateformat']);
//                                $datetimeobj = new Date_Time_Converter($value, $dateformatdatat['phpdate']);
//                                $value = $datetimeobj->convert("Y-m-d H:i");
//                            }
//                            break;
//                        case 'N': //NUMERICAL QUESTION TYPE
//                        case 'K': //MULTIPLE NUMERICAL QUESTION
//                            if (trim($value) == "") {
//                                $value = null;
//                            } else {
//                                $value = sanitize_float($value);
//                            }
//                            break;
//                        case '|': //File Upload
//                            $value = null;  // can't upload a file via GET
//                            break;
//                    }
//                    $LEM->updatedValues[$knownVar['sgqa']] = array(
//                        'type' => $knownVar['type'],
//                        'value' => $value,
//                    );
//                }
//                $LEM->updateValuesInDatabase(null);
//            }

            return [
                'hasNext' => true,
                'hasPrevious' => false,
            ];
        }

        static function NavigateBackwards()
        {
            $LEM =& LimeExpressionManager::singleton();
            $session = App()->surveySessionManager->current;
            $LEM->ParseResultCache = array();    // to avoid running same test more than once for a given group
            $LEM->updatedValues = [];

            switch ($session->format) {
                case Survey::FORMAT_ALL_IN_ONE:
                    throw new \Exception("Can not move backwards in all in one mode");
                    break;
                case Survey::FORMAT_GROUP:
                    // First validate the current group
                    $LEM->StartProcessingPage();
                    $updatedValues = $LEM->ProcessCurrentResponses();
                    $message = '';
                    while (true) {
                        $LEM->currentQset = [];    // reset active list of questions
                        if (is_null($LEM->currentGroupSeq)) {
                            $LEM->currentGroupSeq = 0;
                        } // If moving backwards in preview mode and a question was removed then $LEM->currentGroupSeq is NULL and an endless loop occurs.
                        if (--$LEM->currentGroupSeq < 0) // Stop at start
                        {
                            $message .= $LEM->updateValuesInDatabase(false);
                            $LEM->lastMoveResult = $result = array(
                                'at_start' => true,
                                'finished' => false,
                                'message' => $message,
                                'unansweredSQs' => (isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                                'invalidSQs' => (isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                            );

                            return $result;
                        }

                        $result = $LEM->validateGroup($LEM->currentGroupSeq);
                        if (is_null($result)) {
                            continue;   // this is an invalid group - skip it
                        }
                        $message .= $result['message'];
                        if (!$result['relevant'] || $result['hidden']) {
                            // then skip this group - assume already saved?
                            continue;
                        } else {
                            // display new group
                            $message .= $LEM->updateValuesInDatabase(false);
                            $LEM->lastMoveResult = $result = array(
                                'at_start' => false,
                                'finished' => false,
                                'message' => $message,
                                'gseq' => $LEM->currentGroupSeq,
                                'seq' => $LEM->currentGroupSeq,
                                'mandViolation' => $result['mandViolation'],
                                'valid' => $result['valid'],
                                'unansweredSQs' => $result['unansweredSQs'],
                                'invalidSQs' => $result['invalidSQs'],
                            );

                            return $result;
                        }
                    }
                    break;
                case Survey::FORMAT_QUESTION:
                    $result = $LEM->navigatePrevQuestion();
                  break;
            }
            return $result;
        }

        private function navigateNextGroup($force) {
            // First validate the current group
            $this->StartProcessingPage();
            $session = App()->surveySessionManager->current;
            $this->processData($session->response, $_POST);
            $group = $session->getCurrentGroup();
            $message = '';
            if (!$force) {
                $validationResults = $this->validateGroup($group);
                $message .= $validationResults->getMessagesAsString();
                if ($group->isRelevant($session->response) && !$validationResults->getSuccess()) {
                    // redisplay the current group
                    $message .= $this->updateValuesInDatabase(false);
                    $result = [
                        'finished' => false,
                        'message' => $message,
                        'gseq' => $session->step,
                        'seq' => $session->step,
                        'validationResults' => $validationResults
                    ];
                }
            }
            if ($force || !isset($result)) {
                $step = $session->step;
                $stepCount = $session->stepCount;
                for ($step = $session->step + 1; $step <= $stepCount; $step++) {
                    if ($step >= $session->stepCount) {// Move next with finished, but without submit.
                        $message .= $this->updateValuesInDatabase(true);
                        $result = [
                            'finished' => true,
                            'message' => $message,
                            'seq' => $step,
                            'validationResults' => $validationResults,
                        ];
                        break;
                    }
                    $group = $session->getGroupByIndex($step);
                    if ($group->isRelevant($session->response)) {
                        // then skip this group
                        continue;
                    } else {

                        $validationResults = $this->validateGroup($group);
                        $message .= $validationResults->getMessagesAsString();
                        // display new group
                        $message .= $this->updateValuesInDatabase(false);
                        $result = [
                            'finished' => false,
                            'message' => $message,
                            'seq' => $step,
                            'validationResults' => $validationResults
                        ];
                        break;
                    }


                }
            }
            return $result;
        }

        private function navigateNextQuestion($force) {
            $this->StartProcessingPage();
            $session = App()->surveySessionManager->current;
            $this->processData($session->response, $_POST);
            $question = $session->getQuestionByIndex($session->step);
            $message = '';
            if (!$force) {
                // Validate current page.
                $valid = $this->validateQuestion($question);
                if ($question->isRelevant($session->response) && !$valid) {
                    // redisplay the current question with all error
                    $message .= $this->updateValuesInDatabase(false);
                    $result = [
                        'finished' => false,
                        'message' => $message,
                        'qseq' => $session->step,
                        'gseq' => $session->getGroupIndex($question->gid),
                        'seq' => $session->step,
                    ];
                }
            }
            if ($force || !isset($result)) {
                $step = $session->step;
                $stepCount = $session->stepCount;

                for ($step = $session->step + 1; $step <= $stepCount; $step++) {
                    if ($step >= $session->stepCount) // Move next with finished, but without submit.
                    {
                        $message .= $this->updateValuesInDatabase(true);
                        $result = [
                            'finished' => true,
                            'message' => $message,
                            'qseq' => $step,
                            'gseq' => $session->getGroupIndex($session->currentGroup->primaryKey),
                            'seq' => $step,
                            'mandViolation' => (($session->maxStep > $step) ? $result['mandViolation'] : false),
                            'valid' => (($session->maxStep > $step) ? $result['valid'] : true),
                            'unansweredSQs' => (isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                            'invalidSQs' => (isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                        ];

                        break;
                    }

                    // Set certain variables normally set by StartProcessingGroup()
                    $question = $session->getQuestionByIndex($step);
                    $this->_CreateSubQLevelRelevanceAndValidationEqns($question);
                    $gRelInfo = $this->getGroupRelevanceInfo($question->group);
                    $grel = $gRelInfo['result'];

                    if ($question->bool_hidden || !$question->isRelevant($session->response)) {
                        // then skip this question, $this->updatedValues updated in _ValidateQuestion
                        continue;
                    } else {
                        // Display new question
                        $message .= $this->updateValuesInDatabase(false);
                        $result = [
                            'finished' => false,
                            'message' => $message,
                            'qseq' => $step,
                            'seq' => $step,
                        ];
                        break;

                    }
                }
            }
            if (!isset($result) || !array_key_exists('finished', $result)) {
                throw new \UnexpectedValueException("Result should not be null, and should contain proper keys");
            }
            return $result;
        }

        private function navigatePrevQuestion() {
            $this->StartProcessingPage();
            $session = App()->surveySessionManager->current;
            $this->processData($session->response, $_POST);
            $question = $session->getQuestionByIndex($session->step);
            $message = '';
            $step = $session->step;
            for ($step = $session->step; $step >= 0; $step--) {
                // Set certain variables normally set by StartProcessingGroup()
                $question = $session->getQuestionByIndex($step);
                $this->_CreateSubQLevelRelevanceAndValidationEqns($question);
                $validateResult = $this->validateQuestion($question);
                $message .= $validateResult->getMessagesAsString();
                $gRelInfo = $this->getGroupRelevanceInfo($question->group);
                $grel = $gRelInfo['result'];

                if ($question->bool_hidden || !$question->isRelevant($session->response)) {
                    // then skip this question, $this->updatedValues updated in _ValidateQuestion
                    continue;
                } else {
                    // Display new question
                    $message .= $this->updateValuesInDatabase(false);
                    $result = [
                        'finished' => false,
                        'message' => $message,
                        'qseq' => $step,
                        'seq' => $step,
                        'mandViolation' => (($session->maxStep >= $step) ? $validateResult->getPassedMandatory() : false),
                        'valid' => (($session->maxStep >= $step) ? $validateResult->getSuccess() : false),
                    ];
                    break;

                }
            }
            if (!isset($result) || !array_key_exists('finished', $result)) {
                throw new \UnexpectedValueException("Result should not be null, and should contain proper keys");
            }
            return $result;
        }

        /**
         *
         * @param <type> $force - if true, continue to go forward even if there are violations to the mandatory and/or validity rules
         */
        static function NavigateForwards($force = false)
        {
            $LEM =& LimeExpressionManager::singleton();
            $session = App()->surveySessionManager->current;
            $LEM->ParseResultCache = array();    // to avoid running same test more than once for a given group
            $LEM->updatedValues = [];

            switch ($session->format) {
                case Survey::FORMAT_ALL_IN_ONE:
                    $LEM->StartProcessingPage();
                    $session = App()->surveySessionManager->current;
                    $LEM->processData($session->response, App()->request->psr7);
                    $message = '';
                    $valid = $LEM->validateSurvey();
                    $finished = $valid;
                    $message .= $LEM->updateValuesInDatabase($finished);
                    $result = [
                        'finished' => $finished,
                        'gseq' => 1,
                        'seq' => 1,
                    ];
                    break;
                case Survey::FORMAT_GROUP:
                    $result = $LEM->navigateNextGroup($force);
                    break;
                case Survey::FORMAT_QUESTION:
                    $result = $LEM->navigateNextQuestion($force);
                    break;
                default: throw new \Exception("Unknown survey format");
            }
            if ($result === null) {
                throw new \UnexpectedValueException("Result should not be null");
            }
            return $result;
        }

        /**
         * Write values to database.
         * @param <type> $updatedValues
         * @param <boolean> $finished - true if the survey needs to be finalized
         */
        private function updateValuesInDatabase($finished = false)
        {
            $session = App()->surveySessionManager->current;
            $response = $session->response;
            if ($finished) {
                $setter = array();
                $thisstep = $session->step;
                $response->lastpage = $session->step;


                if ($session->survey->bool_savetimings) {
                    Yii::import("application.libraries.Save");
                    $cSave = new Save();
                    $cSave->set_answer_time();
                }

                // Delete the save control record if successfully finalize the submission
                $query = "DELETE FROM {{saved_control}} where `srid` = '{$session->responseId}' and `sid` = '{$session->surveyId}'";
                Yii::app()->db->createCommand($query)->execute();

                // Check Quotas
                $aQuotas = checkCompletedQuota('return');
                if ($aQuotas && !empty($aQuotas)) {
                    checkCompletedQuota($this->sid);  // will create a page and quit: why not use it directly ?
                } elseif ($finished) {
                    $session->response->markAsFinished();
                    $session->response->save();
                }

            }
        }

        /**
         * Get last move information, optionally clearing the substitution cache
         * @param type $clearSubstitutionInfo
         * @return type
         */
        static function GetLastMoveResult($clearSubstitutionInfo = false)
        {
            $LEM =& LimeExpressionManager::singleton();
            if ($clearSubstitutionInfo) {
                $LEM->em->ClearSubstitutionInfo();  // need to avoid double-generation of tailoring info
            }

            return (isset($LEM->lastMoveResult) ? $LEM->lastMoveResult : null);
        }

        private function processData(Response $response, \Psr\Http\Message\ServerRequestInterface $request) {

            $response->setAttributes($request->getParsedBody());
            foreach($request->getUploadedFiles() as $field => $files) {
                $response->setFiles($field, $files);
            }
            $response->save();
        }

        private function jumpToGroup($seq, $preview, $processPOST, $force) {
            // First validate the current group
            $this->StartProcessingPage();
            $session = App()->surveySessionManager->current;
            if ($processPOST) {
                $this->processData($session->response, App()->request->psr7);
            } else {
                $updatedValues = array();
            }

            $message = '';
            // Validate if moving forward.
            if (!$force && $seq > $session->step) {
                $validationResults = $this->validateGroup($session->getCurrentGroup());
                $message .= $result['message'];
                $updatedValues = array_merge($updatedValues, $result['updatedValues']);
                if (!is_null($result) && ($result['mandViolation'] || !$result['valid'])) {
                    // redisplay the current group, showing error
                    $message .= $LEM->updateValuesInDatabase(false);
                    $LEM->lastMoveResult = array(
                        'finished' => false,
                        'message' => $message,
                        'gseq' => $LEM->currentGroupSeq,
                        'seq' => $LEM->currentGroupSeq,
                        'mandViolation' => $result['mandViolation'],
                        'valid' => $result['valid'],
                        'unansweredSQs' => $result['unansweredSQs'],
                        'invalidSQs' => $result['invalidSQs'],
                    );

                    return $LEM->lastMoveResult;
                }
            }

            $stepCount = $session->stepCount;
            for ($step = $seq; $step < $stepCount; $step++) {
                $group = $session->getGroupByIndex($step);
                $validationResults = $this->validateGroup($group);
                $message .= $validationResults->getMessagesAsString();
                if (!$preview && !$group->isRelevant($session->response)) {
                    // then skip this group
                    continue;
                } elseif (!$preview && !$validationResults->getSuccess() && $step < $seq) {
                    // if there is a violation while moving forward, need to stop and ask that set of questions
                    // if there are no violations, can skip this group as long as changed values are saved.
                    die('skip2');
                    continue;
                } else {
                    // Display new group
                    // Showing error if question are before the maxstep
                    $message .= $this->updateValuesInDatabase(false);
                    $result = [
                        'finished' => false,
                        'message' => $message,
                        'gseq' => $step,
                        'seq' => $step,
                        'mandViolation' => (($session->maxStep > $step) ? $validateResult['mandViolation'] : false),
                        'valid' => (($session->maxStep > $step) ? $validateResult['vaslid'] : true),
                    ];
                    break;
                }

                if ($step >= $session->stepCount) {
                    die('noo finished?');
                    $message .= $this->updateValuesInDatabase(true);
                    $result = [
                        'finished' => true,
                        'message' => $message,
                        'gseq' => $step,
                        'seq' => $step,
                        'mandViolation' => (isset($result['mandViolation']) ? $result['mandViolation'] : false),
                        'valid' => (isset($result['valid']) ? $result['valid'] : false),
                        'unansweredSQs' => (isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                        'invalidSQs' => (isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                    ];
                }

            }
            return $result;
        }

        private function jumpToQuestion($seq, $preview, $processPOST, $force) {
            $this->StartProcessingPage();
            $session = App()->surveySessionManager->current;
            if ($processPOST) {
                $this->processData($session->response, $_POST);
            } else {
                $updatedValues = array();
            }
            $message = '';
            // Validate if moving forward.
            if (!$force && $seq > $session->step) {
                $valid = $this->validateQuestion($session->step, $force);
                $gRelInfo = $this->getGroupRelevanceInfo($session->getQuestion($session->step)->group);
                $grel = $gRelInfo['result'];
                if ($grel && !$valid) {
                    // Redisplay the current question, qhowning error
                    $message .= $this->updateValuesInDatabase(false);
                    $result = $this->lastMoveResult = [
                        'finished' => false,
                        'message' => $message,
                        'mandViolation' => (($session->maxStep > $session->step) ? $validateResult['mandViolation'] : false),
                        'valid' => (($session->maxStep > $session->step) ? $validateResult['valid'] : true),
                        'unansweredSQs' => $validateResult['unansweredSQs'],
                        'invalidSQs' => $validateResult['invalidSQs'],
                    ];
                }
            }
            $stepCount = $session->stepCount;
            for ($step = $seq; $step < $stepCount; $step++) {
                $question = $session->getQuestionByIndex($step);
                /** @var QuestionValidationResult $validationResult */
                $valid = $this->validateQuestion($question, $force);
                $gRelInfo = $this->getGroupRelevanceInfo($session->getQuestionByIndex($step)->group);
                $grel = $gRelInfo['result'];

                if (!$preview && ($question->bool_hidden || !$question->isRelevant($session->response))) {
                    // then skip this question
                    continue;
                } elseif (!$preview && !$valid && $step < $seq) {
                    // if there is a violation while moving forward, need to stop and ask that set of questions
                    // if there are no violations, can skip this group as long as changed values are saved.
                    die('skip2');
                    continue;
                } else {
//                    die('break');
                    // Display new question
                    // Showing error if question are before the maxstep
                    $message .= $this->updateValuesInDatabase(false);
                    $result = [
                        'finished' => false,
                        'message' => $message,
                        'qseq' => $step,
                        'gseq' => $session->getGroupIndex($session->getQuestionByIndex($step)->gid),
                        'seq' => $step,
                    ];
                    break;
                }
            }
            if ($step >= $session->stepCount) {
                die('noo finished?');
                $message .= $this->updateValuesInDatabase(true);
                $result = [
                    'finished' => true,
                    'message' => $message,
                    'qseq' => $step,
                    'gseq' => $this->currentGroupSeq,
                    'seq' => $step,
                    'mandViolation' => (isset($result['mandViolation']) ? $result['mandViolation'] : false),
                    'valid' => (isset($result['valid']) ? $result['valid'] : false),
                    'unansweredSQs' => (isset($result['unansweredSQs']) ? $result['unansweredSQs'] : ''),
                    'invalidSQs' => (isset($result['invalidSQs']) ? $result['invalidSQs'] : ''),
                ];



            }
            return $result;
        }

        /**
         * Jump to a specific question or group sequence.  If jumping forward, it re-validates everything in between
         * @param <type> $seq
         * @param bool $processPOST
         * @param bool $force
         * @param bool $changeLang
         * @return array|type <type>
         * @throws Exception
         */
        static function JumpTo($seq, $processPOST = true, $force = false, $changeLang = false)
        {
//            bP();
            if ($seq < 0) {
                throw new \InvalidArgumentException("Sequence must be >= 0");
            }
            $session = App()->surveySessionManager->current;
            $LEM =& LimeExpressionManager::singleton();
            $LEM->ParseResultCache = [];    // to avoid running same test more than once for a given group
            $LEM->updatedValues = [];
            switch ($session->format) {
                case Survey::FORMAT_ALL_IN_ONE:
                    // This only happens if saving data so far, so don't want to submit it, just validate and return
                    $LEM->StartProcessingPage(true);
                    $updatedValues = $processPOST ? $LEM->ProcessCurrentResponses() : [];
                    $valid = $LEM->validateSurvey($force);
                    $LEM->lastMoveResult = array(
                        'finished' => false,
                        'gseq' => 1,
                        'seq' => 1,
                        'valid' => $valid,
                    );

                    $result = $LEM->lastMoveResult;
                    break;
                case Survey::FORMAT_GROUP:
                    $result = $LEM->jumpToGroup($seq, $preview, $processPOST, $force);
                    break;
                case Survey::FORMAT_QUESTION:
                    $result = $LEM->jumpToQuestion($seq, $preview, $processPOST, $force);
                    break;
                default:
                    throw new \Exception("Unknown survey mode: " . $session->format);
            }


            if ($result === null) {
                throw new \UnexpectedValueException("Result should not be null");
            }
            return $result;
        }

        /**
         * Check the entire survey
         * @param boolean $force : force validation to true, even if there are error, used at survey start to fill EM
         * @return QuestionValidationResultCollection with information on validated question
         */
        private function validateSurvey($force = false)
        {
            $session = App()->surveySessionManager->current;

            foreach($session->getGroups() as $group) {
                if (!$group->isRelevant($session->response)) {
                    continue;
                }
                if (!$this->validateGroup($group, $force)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Check a group and all of the questions it contains
         * @param QuestionGroup $group The group to be validated.
         * @param boolean $force : force validation to true, even if there are error
         * @return QuestionValidationResultCollection Validation result for all relevant and visible questions.
         */
        public function validateGroup(QuestionGroup $group, $force = false)
        {
            $session = App()->surveySessionManager->current;

            foreach ($session->getQuestions($group) as $question) {
                if (!$question->bool_hidden && $question->isRelevant($session->response)) {
                    if (!$this->validateQuestion($question, $force)) {
                        return false;
                    }
                }
            }

            return true;
        }


        /**
         * For the current set of questions (whether in survey, gtoup, or question-by-question mode), assesses the following:
         * (a) mandatory - if so, then all relevant sub-questions must be answered (e.g. pay attention to array_filter and array_filter_exclude)
         * (b) always-hidden
         * (c) relevance status - including sub-question-level relevance
         * (d) answered - if $_SESSION[$LEM->sessid][sgqa]=='' or NULL, then it is not answered
         * (e) validity - whether relevant questions pass their validity tests
         * @param integer $questionSeq - the 0-index sequence number for this question
         * @param boolean $force : force validation to true, even if there are error, this allow to save in DB even with error
         * @return QuestionValidationResult
         */

        private function validateQuestion(\Question $question, $force = false)
        {
            $session = App()->surveySessionManager->current;
            $result =  $question->validateResponse($session->response);
            return $result;
            $LEM =& $this;
            $knownVars = $this->getKnownVars();

            $qrel = true;   // assume relevant unless discover otherwise
            $prettyPrintRelEqn = '';    //  assume no relevance eqn by default
            $qid = $question->qid;
            $gid = $question->gid;
            $gseq = $session->getGroupIndex($gid);
            $debug_qmessage = '';

            $gRelInfo = $LEM->getGroupRelevanceInfo($question->group);
            $grel = $gRelInfo['result'];

            ///////////////////////////
            // IS QUESTION RELEVANT? //
            ///////////////////////////
            $relevanceEqn = isset($question->relevance) && !empty($question->relevance) ? $question->relevance : 1;
            $relevanceEqn = htmlspecialchars_decode($relevanceEqn, ENT_QUOTES);  // TODO is this needed?
            // assumes safer to re-process relevance and not trust POST values
            $qrel = $LEM->em->ProcessBooleanExpression($relevanceEqn, $gseq, $session->getQuestionIndex($question->primaryKey));

            $hasErrors = $LEM->em->HasErrors();
            if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                $prettyPrintRelEqn = $LEM->em->GetPrettyPrintString();
            }

            //////////////////////////////////////
            // ARE ANY SUB-QUESTION IRRELEVANT? //
            //////////////////////////////////////
            // identify the relevant subquestions (array_filter and array_filter_exclude may make some irrelevant)
            $relevantSQs = array();
            $irrelevantSQs = array();
            $prettyPrintSQRelEqns = array();
            $prettyPrintSQRelEqn = '';
             $prettyPrintValidTip = '';
            $anyUnanswered = false;

            if (!$qrel) {
                // All sub-questions are irrelevant

                $irrelevantSQs = array_keys($question->getFields());
            } else {
                foreach ($question->getFields() as $fieldName => $details) {
                    // for each subq, see if it is part of an array_filter or array_filter_exclude
                    if (!isset($LEM->subQrelInfo[$question->primaryKey])) {
                        $relevantSQs[] = $question->sgqa;
                        continue;
                    }
                    $foundSQrelevance = false;
                    if ($question->type == Question::TYPE_RANKING) {
                        // Relevance of subquestion for ranking question depend of the count of relevance of answers.
                        $iCountRank = (isset($iCountRank) ? $iCountRank + 1 : 1);
                        $iCountRelevant = isset($iCountRelevant) ? $iCountRelevant : count(array_filter($LEM->subQrelInfo[$qid],
                            function ($sqRankAnwsers) {
                                return $sqRankAnwsers['result'];
                            }));
                        if ($iCountRank > $iCountRelevant) {
                            $foundSQrelevance = true;
                            $irrelevantSQs[] = $question->sgqa;
                        } else {
                            $relevantSQs[] = $question->sgqa;
                        }
                        continue;
                    }

                    foreach ($LEM->subQrelInfo[$qid] as $sq) {
                        switch ($sq['qtype']) {
                            case '1':   //Array (Flexible Labels) dual scale
                                if ($sgqa == ($sq['rowdivid'] . '#0') || $sgqa == ($sq['rowdivid'] . '#1')) {
                                    $foundSQrelevance = true;
                                    if (isset($LEM->ParseResultCache[$sq['eqn']])) {
                                        $sqrel = $LEM->ParseResultCache[$sq['eqn']]['result'];
                                        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                                            $prettyPrintSQRelEqns[$sq['rowdivid']] = $LEM->ParseResultCache[$sq['eqn']]['prettyprint'];
                                        }
                                    } else {
                                        $stringToParse = htmlspecialchars_decode($sq['eqn'],
                                            ENT_QUOTES);  // TODO is this needed?
                                        $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse, $session->getGroupIndex($question->gid),
                                            $session->getQuestionIndex($question->primaryKey));
                                        $hasErrors = $LEM->em->HasErrors();
                                        // make sure subquestions with errors in relevance equations are always shown and answers recorded  #7703
                                        if ($hasErrors) {
                                            $sqrel = true;
                                        }
                                        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                                            $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                            $prettyPrintSQRelEqns[$sq['rowdivid']] = $prettyPrintSQRelEqn;
                                        }
                                        $LEM->ParseResultCache[$sq['eqn']] = array(
                                            'result' => $sqrel,
                                            'prettyprint' => $prettyPrintSQRelEqn,
                                            'hasErrors' => $hasErrors,
                                        );
                                    }
                                    if ($sqrel) {
                                        $relevantSQs[] = $sgqa;
                                        $_SESSION[$LEM->sessid]['relevanceStatus'][$sq['rowdivid']] = true;
                                    } else {
                                        $irrelevantSQs[] = $sgqa;
                                        $_SESSION[$LEM->sessid]['relevanceStatus'][$sq['rowdivid']] = false;
                                    }
                                }
                                break;
                            case ':': //ARRAY (Multi Flexi) 1 to 10
                            case ';': //ARRAY (Multi Flexi) Text
                                if (preg_match('/^' . $sq['rowdivid'] . '_/', $sgqa)) {
                                    $foundSQrelevance = true;
                                    if (isset($LEM->ParseResultCache[$sq['eqn']])) {
                                        $sqrel = $LEM->ParseResultCache[$sq['eqn']]['result'];
                                        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                                            $prettyPrintSQRelEqns[$sq['rowdivid']] = $LEM->ParseResultCache[$sq['eqn']]['prettyprint'];
                                        }
                                    } else {
                                        $stringToParse = htmlspecialchars_decode($sq['eqn'],
                                            ENT_QUOTES);  // TODO is this needed?
                                        $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse, $session->getGroupIndex($question->gid),
                                            $session->getQuestionIndex($question->primaryKey));
                                        $hasErrors = $LEM->em->HasErrors();
                                        // make sure subquestions with errors in relevance equations are always shown and answers recorded  #7703
                                        if ($hasErrors) {
                                            $sqrel = true;
                                        }
                                        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                                            $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                            $prettyPrintSQRelEqns[$sq['rowdivid']] = $prettyPrintSQRelEqn;
                                        }
                                        $LEM->ParseResultCache[$sq['eqn']] = array(
                                            'result' => $sqrel,
                                            'prettyprint' => $prettyPrintSQRelEqn,
                                            'hasErrors' => $hasErrors,
                                        );
                                    }
                                    if ($sqrel) {
                                        $relevantSQs[] = $sgqa;
                                        $_SESSION[$LEM->sessid]['relevanceStatus'][$sq['rowdivid']] = true;
                                    } else {
                                        $irrelevantSQs[] = $sgqa;
                                        $_SESSION[$LEM->sessid]['relevanceStatus'][$sq['rowdivid']] = false;
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
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                            case 'Q': //MULTIPLE SHORT TEXT
                                if ($sgqa == $sq['rowdivid'] || $sgqa == ($sq['rowdivid'] . 'comment'))     // to catch case 'P'
                                {
                                    $foundSQrelevance = true;
                                    if (isset($LEM->ParseResultCache[$sq['eqn']])) {
                                        $sqrel = $LEM->ParseResultCache[$sq['eqn']]['result'];
                                        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                                            $prettyPrintSQRelEqns[$sq['rowdivid']] = $LEM->ParseResultCache[$sq['eqn']]['prettyprint'];
                                        }
                                    } else {
                                        $stringToParse = htmlspecialchars_decode($sq['eqn'],
                                            ENT_QUOTES);  // TODO is this needed?
                                        $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse, $session->getGroupIndex($question->gid),
                                            $session->getQuestionIndex($question->primaryKey));
                                        $hasErrors = $LEM->em->HasErrors();
                                        // make sure subquestions with errors in relevance equations are always shown and answers recorded  #7703
                                        if ($hasErrors) {
                                            $sqrel = true;
                                        }
                                        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                                            $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                            $prettyPrintSQRelEqns[$sq['rowdivid']] = $prettyPrintSQRelEqn;
                                        }
                                        $LEM->ParseResultCache[$sq['eqn']] = array(
                                            'result' => $sqrel,
                                            'prettyprint' => $prettyPrintSQRelEqn,
                                            'hasErrors' => $hasErrors,
                                        );
                                    }
                                    if ($sqrel) {
                                        $relevantSQs[] = $sgqa;
                                        $_SESSION[$LEM->sessid]['relevanceStatus'][$sq['rowdivid']] = true;
                                    } else {
                                        $irrelevantSQs[] = $sgqa;
                                        $_SESSION[$LEM->sessid]['relevanceStatus'][$sq['rowdivid']] = false;
                                    }
                                }
                                break;
                            case 'L': //LIST drop-down/radio-button list
                                if ($sgqa == ($sq['sgqa'] . 'other') && $sgqa == $sq['rowdivid'])   // don't do sub-q level validition to main question, just to other option
                                {
                                    $foundSQrelevance = true;
                                    if (isset($LEM->ParseResultCache[$sq['eqn']])) {
                                        $sqrel = $LEM->ParseResultCache[$sq['eqn']]['result'];
                                        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                                            $prettyPrintSQRelEqns[$sq['rowdivid']] = $LEM->ParseResultCache[$sq['eqn']]['prettyprint'];
                                        }
                                    } else {
                                        $stringToParse = htmlspecialchars_decode($sq['eqn'],
                                            ENT_QUOTES);  // TODO is this needed?
                                        $sqrel = $LEM->em->ProcessBooleanExpression($stringToParse, $session->getGroupIndex($question->gid),
                                            $session->getQuestionIndex($question->primaryKey));
                                        $hasErrors = $LEM->em->HasErrors();
                                        // make sure subquestions with errors in relevance equations are always shown and answers recorded  #7703
                                        if ($hasErrors) {
                                            $sqrel = true;
                                        }
                                        if (($LEM->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX) {
                                            $prettyPrintSQRelEqn = $LEM->em->GetPrettyPrintString();
                                            $prettyPrintSQRelEqns[$sq['rowdivid']] = $prettyPrintSQRelEqn;
                                        }
                                        $LEM->ParseResultCache[$sq['eqn']] = array(
                                            'result' => $sqrel,
                                            'prettyprint' => $prettyPrintSQRelEqn,
                                            'hasErrors' => $hasErrors,
                                        );
                                    }
                                    if ($sqrel) {
                                        $relevantSQs[] = $sgqa;
                                    } else {
                                        $irrelevantSQs[] = $sgqa;
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }   // end foreach($LEM->subQrelInfo) [checking array-filters]
                    if (!$foundSQrelevance) {
                        // then this question is relevant
                        $relevantSQs[] = $sgqa; // TODO - check this
                    }
                }
            } // end of processing relevant question for sub-questions
            // These array_unique only apply to array_filter of type L (list)
            $relevantSQs = array_unique($relevantSQs);
            $irrelevantSQs = array_unique($irrelevantSQs);

            //////////////////////////////////////////////
            // DETECT ANY VIOLATIONS OF MANDATORY RULES //
            //////////////////////////////////////////////

            $qmandViolation = !$question->validateResponse($session->response)->getPassedMandatory();
            $mandatoryTip = '';
            if ($qrel && !$question->bool_hidden && $qmandViolation) {
                $mandatoryTip = "<strong><br /><span class='errormandatory'>" . gT('This question is mandatory') . '.  ';
                switch ($question->type) {
                    case Question::TYPE_MULTIPLE_CHOICE:
                    case Question::TYPE_MULTIPLE_CHOICE_WITH_COMMENT:
                        $mandatoryTip .= gT('Please check at least one item.');
                    case Question::TYPE_DROPDOWN_LIST:
                    case Question::TYPE_RADIO_LIST:

                        // If at least one checkbox is checked, we're OK
                        if ($question->bool_other) {
                            $qattr = isset($LEM->qattr[$qid]) ? $LEM->qattr[$qid] : array();
                            if (isset($qattr['other_replace_text']) && trim($qattr['other_replace_text']) != '') {
                                $othertext = trim($qattr['other_replace_text']);
                            } else {
                                $othertext = gT('Other:');
                            }
                            $mandatoryTip .= "<br />\n" . sprintf(gT("If you choose '%s' please also specify your choice in the accompanying text field."),
                                    $othertext);
                        }
                        break;
                    case Question::TYPE_DISPLAY:   // Boilerplate can never be mandatory
                    case Question::TYPE_EQUATION:   // Equation is auto-computed, so can't violate mandatory rules
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
                    case ';':
                    case '1':
                        $mandatoryTip .= gT('Please complete all parts') . '.';
                        break;
                    case Question::TYPE_ARRAY_NUMBERS:
                        if ($question->multiflexible_checkbox) {
                            $mandatoryTip .= gT('Please check at least one box per row') . '.';
                        } else {
                            $mandatoryTip .= gT('Please complete all parts') . '.';
                        }
                        break;
                    case 'R':
                        $mandatoryTip .= gT('Please rank all items') . '.';
                        break;
                }
                $mandatoryTip .= "</span></strong>\n";
            }

            ///////////////////////////////////////////////
            // DETECT ANY VIOLATIONS OF VALIDATION RULES //
            ///////////////////////////////////////////////
            $qvalid = true;   // assume valid unless discover otherwise
            $hasValidationEqn = false;
            $prettyPrintValidEqn = '';    //  assume no validation eqn by default
            $validationEqn = '';
            $validationJS = '';       // assume can't generate JavaScript to validate equation
            $hasValidationEqn = true;
            if (!$question->bool_hidden)  // do this even is starts irrelevant, else will never show this information.
            {
                $validationEqns = $question->getValidationExpressions();
                $validationEqn = implode(' and ', $validationEqns);
                $qvalid = $LEM->em->ProcessBooleanExpression($validationEqn, $gseq, $session->getQuestionIndex($question->primaryKey));
                $hasErrors = $LEM->em->HasErrors();
                if (!$hasErrors) {
                    $validationJS = $LEM->em->GetJavaScriptEquivalentOfExpression();
                }
                $prettyPrintValidEqn = $validationEqn;
                if ((($this->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)) {
                    $prettyPrintValidEqn = $LEM->em->GetPrettyPrintString();
                }

                $stringToParse = '';
                $tips = []; // $LEM->qid2validationEqn[$qid]['tips']
                foreach ($tips as $vclass => $vtip) {
                    $stringToParse .= "<div id='vmsg_" . $qid . '_' . $vclass . "' class='em_" . $vclass . " emtip'>" . $vtip . "</div>\n";
                }
                $prettyPrintValidTip = $stringToParse;
                $validTip = $LEM->ProcessString($stringToParse, $qid, null, false, 1, 1, false, false);
                // TODO check for errors?
                if ((($this->debugLevel & LEM_PRETTY_PRINT_ALL_SYNTAX) == LEM_PRETTY_PRINT_ALL_SYNTAX)) {
                    $prettyPrintValidTip = $LEM->GetLastPrettyPrintExpression();
                }
//                $sumEqn = $LEM->qid2validationEqn[$qid]['sumEqn'];
//                $sumRemainingEqn = $LEM->qid2validationEqn[$qid]['sumRemainingEqn'];
                //                $countEqn = $LEM->qid2validationEqn[$qid]['countEqn'];
                //                $countRemainingEqn = $LEM->qid2validationEqn[$qid]['countRemainingEqn'];

            }

            if (!$qvalid) {
                $invalidSQs = $question->title; // TODO - currently invalidates all - should only invalidate those that truly fail validation rules.
            }
            /////////////////////////////////////////////////////////
            // OPTIONALLY DISPLAY (DETAILED) DEBUGGING INFORMATION //
            /////////////////////////////////////////////////////////
            if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY) {
                $editlink = Yii::app()->getController()->createUrl('admin/survey/sa/view/surveyid/' . $LEM->sid . '/gid/' . $gid . '/qid/' . $qid);
                $debug_qmessage .= '--[Q#' . $session->getQuestionIndex($question->primaryKey) . ']'
                    . "[<a href='$editlink'>"
                    . 'QID:' . $qid . '</a>][' . $question->type . ']: '
                    . ($qrel ? 'relevant' : " <span style='color:red'>irrelevant</span> ")
                    . ($question->bool_hidden ? " <span style='color:red'>always-hidden</span> " : ' ')
                    . (($question->bool_mandatory) ? ' mandatory' : ' ')
                    . (($hasValidationEqn) ? (!$qvalid ? " <span style='color:red'>(fails validation rule)</span> " : ' valid') : '')
                    . ($qmandViolation ? " <span style='color:red'>(missing a relevant mandatory)</span> " : ' ')
                    . $prettyPrintRelEqn
                    . "<br />\n";

            }


            /////////////////////////////////////////////////////////////
            // CREATE ARRAY OF VALUES THAT NEED TO BE SILENTLY UPDATED //
            /////////////////////////////////////////////////////////////
            $updatedValues = array();
            if ((!$qrel || !$grel) && SettingGlobal::get('deletenonvalues')) {
                // If not relevant, then always NULL it in the database
                $sgqas = explode('|', $LEM->qid2code[$qid]);
                foreach ($sgqas as $sgqa) {
                    $session->response->$sgqa = null;
                    if ($sgqa == '') {
                        throw new \Exception("Invalid sgqa: ''");
                    }
                }
            } elseif ($question->type == Question::TYPE_EQUATION) {
                // Process relevant equations, even if hidden, and write the result to the database
                $textToParse = $question->question;
                $result = flattenText($LEM->ProcessString($textToParse, $question->primaryKey,NULL,false,1,1,false,false,true));// More numRecursionLevels ?
                $sgqa = $question->sgqa;
                $redata = array();
                $result = flattenText(\ls\helpers\Replacements::templatereplace( // Why flattenText ? htmlspecialchars($string,ENT_NOQUOTES) seem better ?
                    $textToParse, array('QID' => $question->primaryKey, 'GID' => $question->gid, 'SGQ' => $sgqa),
                    $redata, $question->primaryKey // Static replace
                ));
                if ($LEM->getKnownVars()[$sgqa]['onlynum']) {
                    $result = (is_numeric($result) ? $result : "");
                }
                // Store the result of the Equation in the SESSION
                App()->surveySessionManager->current->response->$sgqa = $result;
                die('ok")');
                $_update = array(
                    'type' => '*',
                    'value' => $result,
                );
                $updatedValues[$sgqa] = $_update;
                $LEM->updatedValues[$sgqa] = $_update;

                if (($LEM->debugLevel & LEM_DEBUG_VALIDATION_DETAIL) == LEM_DEBUG_VALIDATION_DETAIL) {
                    $prettyPrintEqn = $LEM->em->GetPrettyPrintString();
                    $debug_qmessage .= '** Process Hidden but Relevant Equation [' . $sgqa . '](' . $prettyPrintEqn . ') => ' . $result . "<br />\n";
                }
            }

            if (SettingGlobal::get('deletenonvalues')) {
                foreach ($irrelevantSQs as $sq) {
                    // NULL irrelevant sub-questions
                    $session->response->$sq = null;
                }
            }

            //////////////////////////////////////////////////////////////////////////
            // STORE METADATA NEEDED FOR SUBSEQUENT PROCESSING AND DISPLAY PURPOSES //
            //////////////////////////////////////////////////////////////////////////

            $qStatus = array(
                // collect all questions within the group - includes mandatory and always-hiddden status
                'relevant' => $qrel,
                'hidden' => $question->bool_hidden,
                'relEqn' => $prettyPrintRelEqn,
                'valid' => $force || $qvalid,
                'validEqn' => $validationEqn,
                'prettyValidEqn' => $prettyPrintValidEqn,
                'validTip' => $validTip,
                'prettyValidTip' => $prettyPrintValidTip,
                'validJS' => $validationJS,
                'invalidSQs' => (isset($invalidSQs) && !$force) ? $invalidSQs : '',
                'relevantSQs' => implode('|', $relevantSQs),
                'irrelevantSQs' => implode('|', $irrelevantSQs),
                'subQrelEqn' => implode('<br />', $prettyPrintSQRelEqns),
                'mandViolation' => (!$force) ? $qmandViolation : false,
                'anyUnanswered' => $anyUnanswered,
                'mandTip' => (!$force) ? $mandatoryTip : '',
                'message' => $debug_qmessage,
                'updatedValues' => $updatedValues,
                'sumEqn' => (isset($sumEqn) ? $sumEqn : ''),
                'sumRemainingEqn' => (isset($sumRemainingEqn) ? $sumRemainingEqn : ''),
                //            'countEqn' => (isset($countEqn) ? $countEqn : ''),
                //            'countRemainingEqn' => (isset($countRemainingEqn) ? $countRemainingEqn : ''),

            );

            return $qStatus;
        }

        public function getQuestionNavIndex($questionSeq) {
            bP();
            $session = App()->surveySessionManager->current;
            $question = $session->getQuestionByIndex($questionSeq);
            $validationEqn = implode(' and ', $question->getValidationExpressions());
            $result = [
                'qid' => $question->primaryKey,
                'qtext' => $question->question,
                'qcode' => $question->title,
                'qhelp' => $question->help,
                'gid' => $question->gid,
                'valid' => $this->em->ProcessBooleanExpression($validationEqn, $session->getGroupIndex($question->gid), $questionSeq)
            ];
            eP();
            return $result;
        }

        /**
         * Get array of info needed to display the Group Index
         * @return <type>
         */
        static function GetGroupIndexInfo($gseq = null)
        {
            if (is_null($gseq)) {
                return LimeExpressionManager::singleton()->indexGseq;
            } else {
                return LimeExpressionManager::singleton()->indexGseq[$gseq];
            }
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
         * @param int $step - return a single value, otherwise return entire array
         * @return array  - will be either question or group-level, depending upon $surveyMode
         */
        static function GetStepIndexInfo($step)
        {
            bP();
            if (!is_int($step)) {
                throw new \InvalidArgumentException("Step argument must be an integer");
            }
            $LEM =& LimeExpressionManager::singleton();
            $session = App()->surveySessionManager->current;
            switch ($session->format) {
                case Survey::FORMAT_ALL_IN_ONE:
                    throw new \Exception("Question indexes don't apply to all in one surveys.");
                    break;
                case Survey::FORMAT_GROUP:
                    $result = null;
                    break;
                case Survey::FORMAT_QUESTION:
                    $result = $LEM->getQuestionNavIndex($step);
                    break;
            }
            eP();
            return $result;
        }

        /**
         * This should be called each time a new group is started, whether on same or different pages. Sets/Clears needed internal parameters.
         * @param <type> $gseq - the group sequence
         * @param <type> $anonymized - whether anonymized
         * @param <type> $forceRefresh - whether to force refresh of setting variable and token mappings (should be done rarely)
         */
        public static function StartProcessingGroup($gseq, $anonymized = false, $surveyid = null, $forceRefresh = false)
        {
            $session = App()->surveySessionManager->current;
            self::singleton()->em->StartProcessingGroup(
                $session->surveyId,
                '',
                isset($LEM->surveyOptions['hyperlinkSyntaxHighlighting']) ? $LEM->surveyOptions['hyperlinkSyntaxHighlighting'] : false
            );
        }

        /**
         * Returns an array of string parts, splitting out expressions
         * @param type $src
         * @return type
         */
        static function SplitStringOnExpressions($src)
        {
            $LEM =& LimeExpressionManager::singleton();

            return $LEM->em->asSplitStringOnExpressions($src);
        }

        public static function getScript(Survey $survey) {
            $cache = App()->cache;
            $key = __CLASS__ . 'EM_script' . $survey->primaryKey;
            if (false === $result = $cache->get($key)) {
                $fields = [];
                foreach($survey->questions as $question) {
                    foreach($question->getFields() as $field) {
                        if (!$field instanceof QuestionResponseField) {
                            throw new \Exception("getFields() must return an array of QuestionResponseField");
                        }
                        $fields[$field->code] = $field;
                    }
                }
                $script = "var EM = new ExpressionManager(" . json_encode($fields, JSON_PRETTY_PRINT) . ");";
                $cache->set($key, $script);
                $result = $script;
            }
            return $result;
        }
        /*
        * Generate JavaScript needed to do dynamic relevance and tailoring
        * Also create list of variables that need to be declared
        */
        public static function registerScripts(SurveySession $session)
        {
            bP();
            /** @var CClientScript $clientScript */
            $clientScript = App()->getClientScript();
            $clientScript->registerCoreScript('ExpressionManager');
            $clientScript->registerScriptFile(SettingGlobal::get('generalscripts', '/scripts') . "/expressions/em_javascript.js");
            $clientScript->registerScriptFile(App()->createUrl('surveys/script', ['id' => $session->surveyId]));
            $values = [];
            foreach ($session->response->getAttributes() as $name => $value) {
                if (isset($value) && strpos($name, 'X') !== false) {
                    $values[$name] = $value;
                }
            }
            bP('json_encode');
            $script = "EM.setValues(" . json_encode($values) . ");";
            eP('json_encode');
            $clientScript->registerScript(__CLASS__ .'setValues', $script);
            eP();
        }

        static function setTempVars($vars)
        {
            $LEM =& LimeExpressionManager::singleton();
            $LEM->tempVars = $vars;
        }



        /**
         * Set the 'this' variable as an alias for SGQA within the code.
         * @param <type> $sgqa
         */
        public static function SetThisAsAliasForSGQA($sgqa)
        {
            $LEM =& LimeExpressionManager::singleton();
            if (isset($LEM->knownVars[$sgqa])) {
                $LEM->qcode2sgqa['this'] = $sgqa;
            }
        }

        public static function ShowStackTrace($msg = null, &$args = null)
        {
            $LEM =& LimeExpressionManager::singleton();

            $msg = array("**Stack Trace**" . (is_null($msg) ? '' : ' - ' . $msg));

            $count = 0;
            foreach (debug_backtrace(false) as $log) {
                if ($count++ == 0) {
                    continue;   // skip this call
                }
                $LEM->debugStack = array();

                $subargs = array();
                if (!is_null($args) && $log['function'] == '\ls\helpers\Replacements::templatereplace') {
                    foreach ($args as $arg) {
                        if (isset($log['args'][2][$arg])) {
                            $subargs[$arg] = $log['args'][2][$arg];
                        }
                    }
                    if (count($subargs) > 0) {
                        $arglist = print_r($subargs, true);
                    } else {
                        $arglist = '';
                    }
                } else {
                    $arglist = '';
                }
                $msg[] = '  '
                    . (isset($log['file']) ? '[' . basename($log['file']) . ']' : '')
                    . (isset($log['class']) ? $log['class'] : '')
                    . (isset($log['type']) ? $log['type'] : '')
                    . (isset($log['function']) ? $log['function'] : '')
                    . (isset($log['line']) ? '[' . $log['line'] . ']' : '')
                    . $arglist;
            }
        }

        
        /**
         * Returns true if the survey is using comma as the radix
         * @return type
         */
        public static function usingCommaAsRadix()
        {
            $LEM =& LimeExpressionManager::singleton();
            $usingCommaAsRadix = (($LEM->surveyOptions['radix'] == ',') ? true : false);

            return $usingCommaAsRadix;
        }

        private static function getConditionsForEM($surveyid = null, $qid = null)
        {
            if (!is_null($qid)) {
                $where = " c.qid = " . $qid . " AND ";
            } else {
                if (!is_null($surveyid)) {
                    $where = " qa.sid = {$surveyid} AND ";
                } else {
                    $where = "";
                }
            }

            $query = "SELECT DISTINCT c.*, q.sid, q.type
                FROM {{conditions}} AS c
                LEFT JOIN {{questions}} q ON c.cqid=q.qid
                LEFT JOIN {{questions}} qa ON c.qid=qa.qid
                WHERE {$where} 1=1
                UNION
                SELECT DISTINCT c.*, q.sid, NULL AS TYPE
                FROM {{conditions}} AS c
                LEFT JOIN {{questions}} q ON c.cqid=q.qid
                LEFT JOIN {{questions}} qa ON c.qid=qa.qid
                WHERE {$where} c.cqid = 0";

            $databasetype = Yii::app()->db->getDriverName();
            if ($databasetype == 'mssql' || $databasetype == 'dblib') {
                $query .= " order by c.qid, sid, scenario, cqid, cfieldname, value";
            } else {
                $query .= " order by qid, sid, scenario, cqid, cfieldname, value";
            }

            return Yii::app()->db->createCommand($query)->query();
        }









        static public function GetVarAttribute($name, $attr, $default, $gseq, $qseq)
        {
            return LimeExpressionManager::singleton()->_GetVarAttribute($name, $attr, $default, $gseq, $qseq);
        }


        private function _GetVarAttribute($name, $attr, $default, $gseq, $qseq)
        {
            $session = App()->surveySessionManager->current;
            $response = $session->response;
            $parts = explode(".", $name);
            if (!isset($attr)) {
                $attr = isset($parts[1]) ? $parts[1] : 'code';
            }

            // Check if this is a valid field in the response.
            if ($response->canGetProperty($parts[0])) {
                vdd('what now');
            } elseif ($knownVars = $this->getKnownVars() && isset($knownVars[$parts[0]])) {
                $var = $knownVars[$parts[0]];
            } elseif (isset($this->tempVars[$parts[0]])) {
                return $this->tempVars[$parts[0]]['code'];
            } else {
                return '{' . $name . '}';
            }


            // Like JavaScript, if an answer is irrelevant, always return ''
            if (preg_match('/^code|NAOK|shown|valueNAOK|value$/', $attr) && isset($var['qid']) && $var['qid'] != '') {
                if (!$this->_GetVarAttribute($varName, 'relevanceStatus', false, $gseq, $qseq)) {
                    return '';
                }
            }
            switch ($attr) {
                case 'varName':
                    return $name;
                    break;
                case 'code':
                case 'NAOK':
                    if (isset($var['code'])) {
                        return $var['code'];    // for static values like TOKEN
                    } else {
                        if (isset($response->{$question->sgqa})) {
                            $question->type = $var['type'];
                            switch ($question->type) {
                                case 'Q': //MULTIPLE SHORT TEXT
                                case ';': //ARRAY (Multi Flexi) Text
                                case 'S': //SHORT FREE TEXT
                                case 'D': //DATE
                                case 'T': //LONG FREE TEXT
                                case 'U': //HUGE FREE TEXT
                                    // Minimum sanitizing the string entered by user
                                    return htmlspecialchars($response->$sgqa, ENT_NOQUOTES);
                                case '!': //List - dropdown
                                case 'L': //LIST drop-down/radio-button list
                                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                                case 'M': //Multiple choice checkbox
                                case 'P': //Multiple choice with comments checkbox + text
                                    if (preg_match('/comment$/', $sgqa) || preg_match('/other$/',
                                            $sgqa) || preg_match('/_other$/', $name)
                                    ) {
                                        // Minimum sanitizing the string entered by user
                                        return htmlspecialchars($response->$sgqa, ENT_NOQUOTES);
                                    }
                                default:
                                    return $response->$sgqa;
                            }
                        } elseif (isset($var['default']) && !is_null($var['default'])) {
                            return $var['default'];
                        }
                        return $default;
                    }
                    break;
                case 'value':
                case 'valueNAOK': {
                    $question->type = $var['type'];
                    $code = $this->_GetVarAttribute($name, 'code', $default, $gseq, $qseq);
                    switch ($question->type) {
                        case '!': //List - dropdown
                        case 'L': //LIST drop-down/radio-button list
                        case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                        case '1': //Array (Flexible Labels) dual scale  // need scale
                        case 'H': //ARRAY (Flexible) - Column Format
                        case 'F': //ARRAY (Flexible) - Row Format
                        case 'R': //RANKING STYLE
                            if ($question->type == 'O' && preg_match('/comment\.value/', $name)) {
                                $value = $code;
                            } else {
                                if (($question->type == 'L' || $question->type == '!') && preg_match('/_other\.value/', $name)) {
                                    $value = $code;
                                } else {
                                    $scale_id = $this->_GetVarAttribute($name, 'scale_id', '0', $gseq, $qseq);
                                    $which_ans = $scale_id . '~' . $code;
                                    $ansArray = $var['ansArray'];
                                    if (is_null($ansArray)) {
                                        $value = $default;
                                    } else {
                                        if (isset($ansArray[$which_ans])) {
                                            $answerInfo = explode('|', $ansArray[$which_ans]);
                                            $answer = $answerInfo[0];
                                        } else {
                                            $answer = $default;
                                        }
                                        $value = $answer;
                                    }
                                }
                            }
                            break;
                        default:
                            $value = $code;
                            break;
                    }

                    return $value;
                }
                    break;
                case 'jsName':
                    if ($session->format == Survey::FORMAT_ALL_IN_ONE
                        || ($session->format == Survey::FORMAT_GROUP && $gseq != -1 && isset($var['gseq']) && $gseq == $var['gseq'])
                        || ($session->format == Survey::FORMAT_QUESTION && $qseq != -1 && isset($var['qseq']) && $qseq == $var['qseq'])
                    ) {
                        return (isset($var['jsName_on']) ? $var['jsName_on'] : (isset($var['jsName'])) ? $var['jsName'] : $default);
                    } else {
                        return (isset($var['jsName']) ? $var['jsName'] : $default);
                    }
                    break;
                case 'sgqa':
                case 'mandatory':
                case 'qid':
                case 'gid':
                case 'grelevance':
                case 'question':
                case 'readWrite':
                case 'relevance':
                case 'rowdivid':
                case 'type':
                case 'qcode':
                case 'gseq':
                case 'qseq':
                case 'ansList':
                case 'scale_id':
                    return (isset($var[$attr])) ? $var[$attr] : $default;
                case 'shown':
                    if (isset($var['shown'])) {
                        return $var['shown'];    // for static values like TOKEN
                    } else {
                        $question->type = $var['type'];
                        $code = $this->_GetVarAttribute($name, 'code', $default, $gseq, $qseq);
                        switch ($question->type) {
                            case '!': //List - dropdown
                            case 'L': //LIST drop-down/radio-button list
                            case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                            case '1': //Array (Flexible Labels) dual scale  // need scale
                            case 'H': //ARRAY (Flexible) - Column Format
                            case 'F': //ARRAY (Flexible) - Row Format
                            case 'R': //RANKING STYLE
                                if ($question->type == 'O' && preg_match('/comment$/', $name)) {
                                    $shown = $code;
                                } else {
                                    if (($question->type == 'L' || $question->type == '!') && preg_match('/_other$/', $name)) {
                                        $shown = $code;
                                    } else {
                                        $scale_id = $this->_GetVarAttribute($name, 'scale_id', '0', $gseq, $qseq);
                                        $which_ans = $scale_id . '~' . $code;
                                        $ansArray = $var['ansArray'];
                                        if (is_null($ansArray)) {
                                            $shown = $code;
                                        } else {
                                            if (isset($ansArray[$which_ans])) {
                                                $answerInfo = explode('|', $ansArray[$which_ans]);
                                                array_shift($answerInfo);
                                                $answer = join('|', $answerInfo);
                                            } else {
                                                $answer = $code;
                                            }
                                            $shown = $answer;
                                        }
                                    }
                                }
                                break;
                            case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                            case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                            case ':': //ARRAY (Multi Flexi) 1 to 10
                            case '5': //5 POINT CHOICE radio-buttons
                                $shown = $code;
                                break;
                            case 'D': //DATE
                                $LEM =& LimeExpressionManager::singleton();
                                $aDateFormatData = \ls\helpers\SurveyTranslator::getDateFormatDataForQID($var['qid'], $LEM->surveyOptions);
                                $shown = '';
                                if (strtotime($code)) {
                                    $shown = date($aDateFormatData['phpdate'], strtotime($code));
                                }
                                break;
                            case 'N': //NUMERICAL QUESTION TYPE
                            case 'K': //MULTIPLE NUMERICAL QUESTION
                            case 'Q': //MULTIPLE SHORT TEXT
                            case ';': //ARRAY (Multi Flexi) Text
                            case 'S': //SHORT FREE TEXT
                            case 'T': //LONG FREE TEXT
                            case 'U': //HUGE FREE TEXT
                            case '*': //Equation
                            case 'I': //Language Question
                            case '|': //File Upload
                            case 'X': //BOILERPLATE QUESTION
                                $shown = $code;
                                break;
                            case 'M': //Multiple choice checkbox
                            case 'P': //Multiple choice with comments checkbox + text
                                if ($code == 'Y' && isset($var['question']) && !preg_match('/comment$/', $sgqa)) {
                                    $shown = $var['question'];
                                } elseif (preg_match('/comment$/', $sgqa)) {
                                    $shown = $code; // This one return sgqa.code
                                } else {
                                    $shown = $default;
                                }
                                break;
                            case 'G': //GENDER drop-down list
                            case 'Y': //YES/NO radio-buttons
                            case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                            case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                                $ansArray = $var['ansArray'];
                                if (is_null($ansArray)) {
                                    $shown = $default;
                                } else {
                                    if (isset($ansArray[$code])) {
                                        $answer = $ansArray[$code];
                                    } else {
                                        $answer = $default;
                                    }
                                    $shown = $answer;
                                }
                                break;
                        }

                        return $shown;
                    }
                case 'relevanceStatus':
                    $gseq = (isset($var['gseq'])) ? $var['gseq'] : -1;
                    $qid = (isset($var['qid'])) ? $var['qid'] : -1;
                    $rowdivid = (isset($var['rowdivid']) && $var['rowdivid'] != '') ? $var['rowdivid'] : -1;
                    if ($qid == -1 || $gseq == -1) {
                        return true;
                    }
                    if (isset($args[1]) && $args[1] == 'NAOK') {
                        return true;
                    }
                    return true;
                case 'onlynum':
                    if (isset($args[1]) && ($args[1] == 'value' || $args[1] == 'valueNAOK')) {
                        return 1;
                    }

                    return (isset($var[$attr])) ? $var[$attr] : $default;
                    break;
                default:
                    print 'UNDEFINED ATTRIBUTE: ' . $attr . "<br />\n";

                    return $default;
            }

            return $default;    // and throw and error?
        }

        public static function SetVariableValue($op, $name, $value)
        {
            $LEM =& LimeExpressionManager::singleton();

            if (isset($LEM->tempVars[$name])) {
                switch ($op) {
                    case '=':
                        $LEM->tempVars[$name]['code'] = $value;
                        break;
                    case '*=':
                        $LEM->tempVars[$name]['code'] *= $value;
                        break;
                    case '/=':
                        $LEM->tempVars[$name]['code'] /= $value;
                        break;
                    case '+=':
                        $LEM->tempVars[$name]['code'] += $value;
                        break;
                    case '-=':
                        $LEM->tempVars[$name]['code'] -= $value;
                        break;
                }
                $_result = $LEM->tempVars[$name]['code'];
                $session->response->$name = $_result;


                return $_result;
            } else {
                if (!isset($LEM->knownVars[$name])) {
                    if (isset($LEM->qcode2sgqa[$name])) {
                        $name = $LEM->qcode2sgqa[$name];
                    } else {
                        return '';  // shouldn't happen
                    }
                }
                if (isset($session->response->$name)) {
                    $_result = $session->response->$name;
                } else {
                    $_result = (isset($LEM->knownVars[$name]['default']) ? $LEM->knownVars[$name]['default'] : 0);
                }

                switch ($op) {
                    case '=':
                        $_result = $value;
                        break;
                    case '*=':
                        $_result *= $value;
                        break;
                    case '/=':
                        $_result /= $value;
                        break;
                    case '+=':
                        $_result += $value;
                        break;
                    case '-=':
                        $_result -= $value;
                        break;
                }
                $session->response->$name = $_result;
                $_type = $LEM->knownVars[$name]['type'];

                return $_result;
            }
        }


        /**
         * Create HTML view of the survey, showing everything that uses EM
         * @param <type> $sid
         * @param <type> $gid
         * @param <type> $qid
         */
        static public function ShowSurveyLogicFile(
            $sid,
            $gid = null,
            $qid = null,
            $LEMdebugLevel = 0,
            $assessments = false
        ) {
            // Title
            // Welcome
            // G1, name, relevance, text
            // *Q1, name [type], relevance [validation], text, help, default, help_msg
            // SQ1, name [scale], relevance [validation], text
            // A1, code, assessment_value, text
            // End Message

            $LEM =& LimeExpressionManager::singleton();
            $LEM->sPreviewMode = 'logic';
            $aSurveyInfo = getSurveyInfo($sid, $LEM->lang);

            $allErrors = array();
            $warnings = 0;

            $surveyOptions = array(
                'assessments' => ($aSurveyInfo['assessments'] == 'Y'),
                'hyperlinkSyntaxHighlighting' => true,
            );

            $varNamesUsed = array(); // keeps track of whether variables have been declared

            if (!is_null($qid)) {
                $surveyMode = 'question';
                LimeExpressionManager::StartSurvey($sid, Survey::FORMAT_QUESTION, $surveyOptions, false, $LEMdebugLevel);
                $qseq = LimeExpressionManager::GetQuestionSeq($qid);
                $moveResult = LimeExpressionManager::JumpTo($qseq + 1, false, true);
            } else {
                if (!is_null($gid)) {
                    $surveyMode = 'group';
                    LimeExpressionManager::StartSurvey($sid, Survey::FORMAT_GROUP, $surveyOptions, false, $LEMdebugLevel);
                    $gseq = LimeExpressionManager::GetGroupSeq($gid);
                    $moveResult = LimeExpressionManager::JumpTo($gseq + 1, false, true);
                } else {
                    $surveyMode = 'survey';
                    LimeExpressionManager::StartSurvey($sid, Survey::FORMAT_ALL_IN_ONE, $surveyOptions, false, $LEMdebugLevel);
                    $moveResult = LimeExpressionManager::NavigateForwards();
                }
            }

            $qtypes = getQuestionTypeList('', 'array');

            if (is_null($moveResult) || is_null($LEM->currentQset) || count($LEM->currentQset) == 0) {
                return array(
                    'errors' => 1,
                    'html' => sprintf(gT('Invalid question - probably missing sub-questions or language-specific settings for language %s'),
                        $LEM->lang)
                );
            }

            $surveyname = \ls\helpers\Replacements::templatereplace('{SURVEYNAME}', array('SURVEYNAME' => $aSurveyInfo['surveyls_title']));

            $out = '<div id="showlogicfilediv" ><H3>' . gT('Logic File for Survey # ') . '[' . $LEM->sid . "]: $surveyname</H3>\n";
            $out .= "<table id='logicfiletable'>";

            if (is_null($gid) && is_null($qid)) {
                if ($aSurveyInfo['surveyls_description'] != '') {
                    $LEM->ProcessString($aSurveyInfo['surveyls_description'], 0);
                    $sPrint = $LEM->GetLastPrettyPrintExpression();
                    $errClass = ($LEM->em->HasErrors() ? 'LEMerror' : '');
                    $out .= "<tr class='LEMgroup $errClass'><td colspan=2>" . gT("Description:") . "</td><td colspan=2>" . $sPrint . "</td></tr>";
                }
                if ($aSurveyInfo['surveyls_welcometext'] != '') {
                    $LEM->ProcessString($aSurveyInfo['surveyls_welcometext'], 0);
                    $sPrint = $LEM->GetLastPrettyPrintExpression();
                    $errClass = ($LEM->em->HasErrors() ? 'LEMerror' : '');
                    $out .= "<tr class='LEMgroup $errClass'><td colspan=2>" . gT("Welcome:") . "</td><td colspan=2>" . $sPrint . "</td></tr>";
                }
                if ($aSurveyInfo['surveyls_endtext'] != '') {
                    $LEM->ProcessString($aSurveyInfo['surveyls_endtext']);
                    $sPrint = $LEM->GetLastPrettyPrintExpression();
                    $errClass = ($LEM->em->HasErrors() ? 'LEMerror' : '');
                    $out .= "<tr class='LEMgroup $errClass'><td colspan=2>" . gT("End message:") . "</td><td colspan=2>" . $sPrint . "</td></tr>";
                }
                if ($aSurveyInfo['surveyls_url'] != '') {
                    $LEM->ProcessString($aSurveyInfo['surveyls_urldescription'] . " - " . $aSurveyInfo['surveyls_url']);
                    $sPrint = $LEM->GetLastPrettyPrintExpression();
                    $errClass = ($LEM->em->HasErrors() ? 'LEMerror' : '');
                    $out .= "<tr class='LEMgroup $errClass'><td colspan=2>" . gT("End URL:") . "</td><td colspan=2>" . $sPrint . "</td></tr>";
                }
            }

            $out .= "<tr><th>#</th><th>" . gT('Name [ID]') . "</th><th>" . gT('Relevance [Validation] (Default value)') . "</th><th>" . gT('Text [Help] (Tip)') . "</th></tr>\n";

            $_gseq = -1;
            foreach ($LEM->currentQset as $q) {
                $gseq = $q['info']['gseq'];
                $gid = $q['info']['gid'];
                $qid = $q['info']['qid'];
                $qseq = $q['info']['qseq'];

                $errorCount = 0;

                //////
                // SHOW GROUP-LEVEL INFO
                //////
                if ($gseq != $_gseq) {
                    $LEM->ParseResultCache = array(); // reset for each group so get proper color coding?
                    $_gseq = $gseq;
                    $ginfo = $LEM->gseq2info[$gseq];

                    $grelevance = '{' . (($ginfo['grelevance'] == '') ? 1 : $ginfo['grelevance']) . '}';
                    $gtext = ((trim($ginfo['description']) == '') ? '&nbsp;' : $ginfo['description']);

                    $editlink = Yii::app()->getController()->createUrl('admin/survey/sa/view/surveyid/' . $LEM->sid . '/gid/' . $gid);
                    $groupRow = "<tr class='LEMgroup'>"
                        . "<td>G-$gseq</td>"
                        . "<td><b>" . $ginfo['group_name'] . "</b><br />[<a target='_blank' href='$editlink'>GID " . $gid . "</a>]</td>"
                        . "<td>" . $grelevance . "</td>"
                        . "<td>" . $gtext . "</td>"
                        . "</tr>\n";

                    $LEM->ProcessString($groupRow, $qid, null, 1, 1);
                    $out .= $LEM->GetLastPrettyPrintExpression();
                    if ($LEM->em->HasErrors()) {
                        ++$errorCount;
                    }
                }

                //////
                // SHOW QUESTION-LEVEL INFO
                //////
                $mandatory = (($q['info']['mandatory'] == 'Y') ? "<span class='mandatory'>*</span>" : '');
                $question->type = $q['info']['type'];
                $question->typedesc = $qtypes[$question->type]['description'];

                $sgqas = explode('|', $q['sgqa']);
                if (count($sgqas) == 1 && !is_null($q['info']['default'])) {
                    $LEM->ProcessString(htmlspecialchars($q['info']['default']), $qid, null, 1, 1);// Default value is Y or answer code or go to input/textarea, then we can filter it
                    $_default = $LEM->GetLastPrettyPrintExpression();
                    if ($LEM->em->HasErrors()) {
                        ++$errorCount;
                    }
                    $default = '<br />(' . gT('Default:') . '  ' . viewHelper::filterScript($_default) . ')';
                } else {
                    $default = '';
                }

                $qtext = (($q['info']['qtext'] != '') ? $q['info']['qtext'] : '&nbsp');
                $help = (($q['info']['help'] != '') ? '<hr/>[' . gT("Help:") . ' ' . $q['info']['help'] . ']' : '');
                $prettyValidTip = (($q['prettyValidTip'] == '') ? '' : '<hr/>(' . gT("Tip:") . ' ' . $q['prettyValidTip'] . ')');

                //////
                // SHOW QUESTION ATTRIBUTES THAT ARE PROCESSED BY EM
                //////
                $attrTable = '';

                $attrs = (isset($LEM->qattr[$qid]) ? $LEM->qattr[$qid] : array());
                if (isset($LEM->q2subqInfo[$qid]['preg'])) {
                    $attrs['regex_validation'] = $LEM->q2subqInfo[$qid]['preg'];
                }
                if (isset($LEM->questionSeq2relevance[$qseq]['other'])) {
                    $attrs['other'] = $LEM->questionSeq2relevance[$qseq]['other'];
                }
                if (count($attrs) > 0) {
                    $attrTable = "<table id='logicfileattributetable'><tr><th>" . gT("Question attribute") . "</th><th>" . gT("Value") . "</th></tr>\n";
                    $count = 0;
                    foreach ($attrs as $key => $value) {
                        if (is_null($value) || trim($value) == '') {
                            continue;
                        }
                        switch ($key) {
                            // @todo: Rather compares the current attribute value to the defaults in the question attributes array to decide which ones should show (only the ones that are non-standard)
                            default:
                            case 'exclude_all_others':
                            case 'exclude_all_others_auto':
                            case 'hidden':
                                if ($value == false || $value == '0') {
                                    $value = null; // so can skip this one - just using continue here doesn't work.
                                }
                                break;
                            case 'time_limit_action':
                                if ($value == '1') {
                                    $value = null; // so can skip this one - just using continue here doesn't work.
                                }
                            case 'relevance':
                                $value = null;  // means an outdate database structure
                                break;
                            case 'array_filter':
                            case 'array_filter_exclude':
                            case 'code_filter':
                            case 'date_max':
                            case 'date_min':
                            case 'em_validation_q_tip':
                            case 'em_validation_sq_tip':
                                break;
                            case 'equals_num_value':
                            case 'em_validation_q':
                            case 'em_validation_sq':
                            case 'max_answers':
                            case 'max_num_value':
                            case 'max_num_value_n':
                            case 'min_answers':
                            case 'min_num_value':
                            case 'min_num_value_n':
                            case 'min_num_of_files':
                            case 'max_num_of_files':
                            case 'multiflexible_max':
                            case 'multiflexible_min':
                            case 'slider_accuracy':
                            case 'slider_min':
                            case 'slider_max':
                            case 'slider_default':
                                $value = '{' . $value . '}';
                                break;
                            case 'other_replace_text':
                            case 'show_totals':
                            case 'regex_validation':
                                break;
                            case 'other':
                                if ($value == 'N') {
                                    $value = null; // so can skip this one
                                }
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

                $LEM->ProcessString($qtext . $help . $prettyValidTip . $attrTable, $qid, null, 1, 1);
                $qdetails = viewHelper::filterScript($LEM->GetLastPrettyPrintExpression());
                if ($LEM->em->HasErrors()) {
                    ++$errorCount;
                }

                //////
                // SHOW RELEVANCE
                //////
                // Must parse Relevance this way, otherwise if try to first split expressions, regex equations won't work
                $relevanceEqn = (($q['info']['relevance'] == '') ? 1 : $q['info']['relevance']);
                if (!isset($LEM->ParseResultCache[$relevanceEqn])) {
                    $result = $LEM->em->ProcessBooleanExpression($relevanceEqn, $gseq, $qseq);
                    $prettyPrint = $LEM->em->GetPrettyPrintString();
                    $hasErrors = $LEM->em->HasErrors();
                    $LEM->ParseResultCache[$relevanceEqn] = array(
                        'result' => $result,
                        'prettyprint' => $prettyPrint,
                        'hasErrors' => $hasErrors,
                    );
                }
                $relevance = $LEM->ParseResultCache[$relevanceEqn]['prettyprint'];
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
                    if (!isset($LEM->ParseResultCache[$validationEqn])) {
                        $result = $LEM->em->ProcessBooleanExpression($validationEqn, $gseq, $qseq);
                        $prettyPrint = $LEM->em->GetPrettyPrintString();
                        $hasErrors = $LEM->em->HasErrors();
                        $LEM->ParseResultCache[$validationEqn] = array(
                            'result' => $result,
                            'prettyprint' => $prettyPrint,
                            'hasErrors' => $hasErrors,
                        );
                    }
                    $prettyValidEqn = '<hr/>(VALIDATION: ' . $LEM->ParseResultCache[$validationEqn]['prettyprint'] . ')';
                    if ($LEM->ParseResultCache[$validationEqn]['hasErrors']) {
                        ++$errorCount;
                    }
                }

                //////
                // TEST VALIDITY OF ROOT VARIABLE NAME AND WHETHER HAS BEEN USED
                //////
                $rootVarName = $q['info']['rootVarName'];
                $varNameErrorMsg = '';
                $varNameError = null;
                if (isset($varNamesUsed[$rootVarName])) {
                    $varNameErrorMsg .= gT('This variable name has already been used.');
                } else {
                    $varNamesUsed[$rootVarName] = array(
                        'gseq' => $gseq,
                        'qid' => $qid
                    );
                }

                if (!preg_match('/^[a-zA-Z][0-9a-zA-Z]*$/', $rootVarName)) {
                    $varNameErrorMsg .= gT('Starting in 2.05, variable names should only contain letters and numbers; and may not start with a number. This variable name is deprecated.');
                }
                if ($varNameErrorMsg != '') {
                    $varNameError = array(
                        'message' => $varNameErrorMsg,
                        'gseq' => $varNamesUsed[$rootVarName]['gseq'],
                        'qid' => $varNamesUsed[$rootVarName]['qid'],
                        'gid' => $gid,
                    );
                    if (!$LEM->sgqaNaming) {
                        ++$errorCount;
                    } else {
                        ++$warnings;
                    }
                }

                //////
                // SHOW ALL SUB-QUESTIONS
                //////
                $sqRows = '';
                $i = 0;
                $sawThis = array(); // array of rowdivids already seen so only show them once
                foreach ($sgqas as $sgqa) {
                    if ($LEM->knownVars[$sgqa]['qcode'] == $rootVarName) {
                        continue;   // so don't show the main question as a sub-question too
                    }
                    $rowdivid = $sgqa;
                    $varName = $LEM->knownVars[$sgqa]['qcode'];
                    switch ($q['info']['type']) {
                        case '1':
                            if (preg_match('/#1$/', $sgqa)) {
                                $rowdivid = null;   // so that doesn't show same message for second scale
                            } else {
                                $rowdivid = substr($sgqa, 0, -2); // strip suffix
                                $varName = substr($LEM->knownVars[$sgqa]['qcode'], 0, -2);
                            }
                            break;
                        case 'P':
                            if (preg_match('/comment$/', $sgqa)) {
                                $rowdivid = null;
                            }
                            break;
                        case ':':
                        case ';':
                            $_rowdivid = $LEM->knownVars[$sgqa]['rowdivid'];
                            if (isset($sawThis[$qid . '~' . $_rowdivid])) {
                                $rowdivid = null;   // so don't show again
                            } else {
                                $sawThis[$qid . '~' . $_rowdivid] = true;
                                $rowdivid = $_rowdivid;
                                $sgqa_len = strlen($sid . 'X' . $gid . 'X' . $qid);
                                $varName = $rootVarName . '_' . substr($_rowdivid, $sgqa_len);
                            }
                    }
                    if (is_null($rowdivid)) {
                        continue;
                    }
                    ++$i;
                    $subQeqn = '&nbsp;';
                    if (isset($LEM->subQrelInfo[$qid][$rowdivid])) {
                        $sq = $LEM->subQrelInfo[$qid][$rowdivid];
                        $subQeqn = $sq['prettyPrintEqn'];   // {' . $sq['eqn'] . '}';  // $sq['prettyPrintEqn'];
                        if ($sq['hasErrors']) {
                            ++$errorCount;
                        }
                    }

                    $sgqaInfo = $LEM->knownVars[$sgqa];
                    $subqText = $sgqaInfo['subqtext'];
                    if (isset($sgqaInfo['default']) && $sgqaInfo['default'] !== '') {
                        $LEM->ProcessString(htmlspecialchars($sgqaInfo['default']), $qid, null, 1, 1);
                        $_default = viewHelper::filterScript($LEM->GetLastPrettyPrintExpression());
                        if ($LEM->em->HasErrors()) {
                            ++$errorCount;
                        }
                        $subQeqn .= '<br />(' . gT('Default:') . '  ' . $_default . ')';
                    }

                    $sqRows .= "<tr class='LEMsubq'>"
                        . "<td>SQ-$i</td>"
                        . "<td><b>" . $varName . "</b></td>"
                        . "<td>$subQeqn</td>"
                        . "<td>" . $subqText . "</td>"
                        . "</tr>";
                }
                $LEM->ProcessString($sqRows, $qid, null, 1, 1);
                $sqRows = viewHelper::filterScript($LEM->GetLastPrettyPrintExpression());
                if ($LEM->em->HasErrors()) {
                    ++$errorCount;
                }

                //////
                // SHOW ANSWER OPTIONS FOR ENUMERATED LISTS, AND FOR MULTIFLEXI
                //////
                $answerRows = '';
                if (isset($LEM->qans[$qid]) || isset($LEM->multiflexiAnswers[$qid])) {
                    $_scale = -1;
                    if (isset($LEM->multiflexiAnswers[$qid])) {
                        $ansList = $LEM->multiflexiAnswers[$qid];
                    } else {
                        $ansList = $LEM->qans[$qid];
                    }
                    foreach ($ansList as $ans => $value) {
                        $ansInfo = explode('~', $ans);
                        $valParts = explode('|', $value);
                        $valInfo[0] = array_shift($valParts);
                        $valInfo[1] = implode('|', $valParts);
                        if ($_scale != $ansInfo[0]) {
                            $i = 1;
                            $_scale = $ansInfo[0];
                        }

                        $subQeqn = '';
                        $rowdivid = $sgqas[0] . $ansInfo[1];
                        if ($q['info']['type'] == 'R') {
                            $rowdivid = $LEM->sid . 'X' . $gid . 'X' . $qid . $ansInfo[1];
                        }
                        if (isset($LEM->subQrelInfo[$qid][$rowdivid])) {
                            $sq = $LEM->subQrelInfo[$qid][$rowdivid];
                            $subQeqn = ' ' . $sq['prettyPrintEqn'];
                            if ($sq['hasErrors']) {
                                ++$errorCount;
                            }
                        }

                        $answerRows .= "<tr class='LEManswer'>"
                            . "<td>A[" . $ansInfo[0] . "]-" . $i++ . "</td>"
                            . "<td><b>" . $ansInfo[1] . "</b></td>"
                            . "<td>[VALUE: " . $valInfo[0] . "]" . $subQeqn . "</td>"
                            . "<td>" . $valInfo[1] . "</td>"
                            . "</tr>\n";
                    }
                    $LEM->ProcessString($answerRows, $qid, null, 1, 1);
                    $answerRows = viewHelper::filterScript($LEM->GetLastPrettyPrintExpression());
                    if ($LEM->em->HasErrors()) {
                        ++$errorCount;
                    }
                }

                //////
                // FINALLY, SHOW THE QUESTION ROW(S), COLOR-CODING QUESTIONS THAT CONTAIN ERRORS
                //////
                $errclass = ($errorCount > 0) ? "class='LEMerror' title='" . sprintf($LEM->ngT("This question has at least %s error.|This question has at least %s errors.",
                        $errorCount), $errorCount) . "'" : '';

                $questionRow = "<tr class='LEMquestion'>"
                    . "<td $errclass>Q-" . $q['info']['qseq'] . "</td>"
                    . "<td><b>" . $mandatory;

                if ($varNameErrorMsg == '') {
                    $questionRow .= $rootVarName;
                } else {
                    $editlink = Yii::app()->getController()->createUrl('admin/survey/sa/view/surveyid/' . $LEM->sid . '/gid/' . $varNameError['gid'] . '/qid/' . $varNameError['qid']);
                    $questionRow .= "<span class='highlighterror' title='" . $varNameError['message'] . "' "
                        . "onclick='window.open(\"$editlink\",\"_blank\")'>"
                        . $rootVarName . "</span>";
                }
                $editlink = Yii::app()->getController()->createUrl('admin/survey/sa/view/surveyid/' . $sid . '/gid/' . $gid . '/qid/' . $qid);
                $questionRow .= "</b><br />[<a target='_blank' href='$editlink'>QID $qid</a>]<br/>$question->typedesc [$question->type]</td>"
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

            if (count($allErrors) > 0) {
                $out = "<p class='LEMerror'>" . sprintf($LEM->ngT("%s question contains errors that need to be corrected.|%s questions contain errors that need to be corrected.",
                        count($allErrors)), count($allErrors)) . "</p>\n" . $out;
            } else {
                switch ($surveyMode) {
                    case 'survey':
                        $message = gT('No syntax errors detected in this survey.');
                        break;
                    case 'group':
                        $message = gT('This group, by itself, does not contain any syntax errors.');
                        break;
                    case 'question':
                        $message = gT('This question, by itself, does not contain any syntax errors.');
                        break;
                }
                $out = "<p class='LEMheading'>$message</p>\n" . $out . "</div>";
            }

            return [
                'errors' => $allErrors,
                'html' => $out
            ];
        }

        /**
         * TSV survey definition in format readable by TSVSurveyImport
         * one line each per group, question, sub-question, and answer
         * does not use SGQA naming at all.
         * @param type $sid
         * @return type
         */
        static public function TSVSurveyExport($sid)
        {
            $fields = array(
                'class',
                'type/scale',
                'name',
                'relevance',
                'text',
                'help',
                'language',
                'validation',
                'mandatory',
                'other',
                'default',
                'same_default',
                // Advanced question attributes
                'allowed_filetypes',
                'alphasort',
                'answer_width',
                'array_filter',
                'array_filter_exclude',
                'array_filter_style',
                'assessment_value',
                'category_separator',
                'choice_title',
                'code_filter',
                'commented_checkbox',
                'commented_checkbox_auto',
                'date_format',
                'date_max',
                'date_min',
                'display_columns',
                'display_rows',
                'dropdown_dates',
                'dropdown_dates_minute_step',
                'dropdown_dates_month_style',
                'dropdown_prefix',
                'dropdown_prepostfix',
                'dropdown_separators',
                'dropdown_size',
                'dualscale_headerA',
                'dualscale_headerB',
                'em_validation_q',
                'em_validation_q_tip',
                'em_validation_sq',
                'em_validation_sq_tip',
                'equals_num_value',
                'exclude_all_others',
                'exclude_all_others_auto',
                'hidden',
                'hide_tip',
                'input_boxes',
                'location_city',
                'location_country',
                'location_defaultcoordinates',
                'location_mapheight',
                'location_mapservice',
                'location_mapwidth',
                'location_mapzoom',
                'location_nodefaultfromip',
                'location_postal',
                'location_state',
                'max_answers',
                'max_filesize',
                'max_num_of_files',
                'max_num_value',
                'max_num_value_n',
                'maximum_chars',
                'min_answers',
                'min_num_of_files',
                'min_num_value',
                'min_num_value_n',
                'multiflexible_checkbox',
                'multiflexible_max',
                'multiflexible_min',
                'multiflexible_step',
                'num_value_int_only',
                'numbers_only',
                'other_comment_mandatory',
                'other_numbers_only',
                'other_replace_text',
                'page_break',
                'parent_order',
                'prefix',
                'printable_help',
                'public_statistics',
                'random_group',
                'random_order',
                'rank_title',
                'repeat_headings',
                'reverse',
                'samechoiceheight',
                'samelistheight',
                'scale_export',
                'show_comment',
                'show_grand_total',
                'show_title',
                'show_totals',
                'showpopups',
                'slider_accuracy',
                'slider_default',
                'slider_layout',
                'slider_max',
                'slider_middlestart',
                'slider_min',
                'slider_rating',
                'slider_reset',
                'slider_separator',
                'slider_showminmax',
                'statistics_graphtype',
                'statistics_showgraph',
                'statistics_showmap',
                'suffix',
                'text_input_width',
                'time_limit',
                'time_limit_action',
                'time_limit_countdown_message',
                'time_limit_disable_next',
                'time_limit_disable_prev',
                'time_limit_message',
                'time_limit_message_delay',
                'time_limit_message_style',
                'time_limit_timer_style',
                'time_limit_warning',
                'time_limit_warning_2',
                'time_limit_warning_2_display_time',
                'time_limit_warning_2_message',
                'time_limit_warning_2_style',
                'time_limit_warning_display_time',
                'time_limit_warning_message',
                'time_limit_warning_style',
                'thousands_separator',
                'use_dropdown',

            );

            $rows = array();
            $primarylang = 'en';
            $otherlangs = '';
            $langs = array();

            // Export survey-level information
            $query = "select * from {{surveys}} where sid = " . $sid;
            $data = dbExecuteAssoc($query);
            foreach ($data->readAll() as $r) {
                foreach ($r as $key => $value) {
                    if ($value != '') {
                        $row['class'] = 'S';
                        $row['name'] = $key;
                        $row['text'] = $value;
                        $rows[] = $row;
                    }
                    if ($key == 'language') {
                        $primarylang = $value;
                    }
                    if ($key == 'additional_languages') {
                        $otherlangs = $value;
                    }
                }
            }
            $langs = explode(' ', $primarylang . ' ' . $otherlangs);
            $langs = array_unique($langs);

            // Export survey language settings
            $query = "select * from {{surveys_languagesettings}} where surveyls_survey_id = " . $sid;
            $data = dbExecuteAssoc($query);
            foreach ($data->readAll() as $r) {
                $_lang = $r['surveyls_language'];
                foreach ($r as $key => $value) {
                    if ($value != '' && $key != 'surveyls_language' && $key != 'surveyls_survey_id') {
                        $row['class'] = 'SL';
                        $row['name'] = $key;
                        $row['text'] = $value;
                        $row['language'] = $_lang;
                        $rows[] = $row;
                    }
                }
            }

            $surveyinfo = getSurveyInfo($sid);
            $assessments = false;
            if (isset($surveyinfo['assessments']) && $surveyinfo['assessments'] == 'Y') {
                $assessments = true;
            }

            foreach ($langs as $lang) {
                if (trim($lang) == '') {
                    continue;
                }
                LimeExpressionManager::StartSurvey($sid, Survey::FORMAT_ALL_IN_ONE,
                    array('sgqaNaming' => 'N', 'assessments' => $assessments), true);
                $moveResult = LimeExpressionManager::NavigateForwards();
                $LEM =& LimeExpressionManager::singleton();

                if (is_null($moveResult) || is_null($LEM->currentQset) || count($LEM->currentQset) == 0) {
                    continue;
                }

                $_gseq = -1;
                foreach ($LEM->currentQset as $q) {
                    $gseq = $q['info']['gseq'];
                    $gid = $q['info']['gid'];
                    $qid = $q['info']['qid'];

                    //////
                    // SHOW GROUP-LEVEL INFO
                    //////
                    if ($gseq != $_gseq) {
                        $_gseq = $gseq;
                        $ginfo = $LEM->gseq2info[$gseq];

                        // if relevance equation is using SGQA coding, convert to qcoding
                        $grelevance = (($ginfo['grelevance'] == '') ? 1 : $ginfo['grelevance']);
                        $LEM->em->ProcessBooleanExpression($grelevance, $gseq, 0);    // $qseq
                        $grelevance = trim(strip_tags($LEM->em->GetPrettyPrintString()));
                        $gtext = ((trim($ginfo['description']) == '') ? '' : $ginfo['description']);

                        $row = array();
                        $row['class'] = 'G';
                        //create a group code to allow proper importing of multi-lang survey TSVs
                        $row['type/scale'] = 'G' . $gseq;
                        $row['name'] = $ginfo['group_name'];
                        $row['relevance'] = $grelevance;
                        $row['text'] = $gtext;
                        $row['language'] = $lang;
                        $row['random_group'] = $ginfo['randomization_group'];
                        $rows[] = $row;
                    }

                    //////
                    // SHOW QUESTION-LEVEL INFO
                    //////
                    $row = array();

                    $mandatory = (($q['info']['mandatory'] == 'Y') ? 'Y' : '');
                    $question->type = $q['info']['type'];

                    $sgqas = explode('|', $q['sgqa']);
                    if (count($sgqas) == 1 && !is_null($q['info']['default'])) {
                        $default = $q['info']['default'];
                    } else {
                        $default = '';
                    }

                    $qtext = (($q['info']['qtext'] != '') ? $q['info']['qtext'] : '');
                    $help = (($q['info']['help'] != '') ? $q['info']['help'] : '');

                    //////
                    // SHOW QUESTION ATTRIBUTES THAT ARE PROCESSED BY EM
                    //////
                    if (isset($LEM->qattr[$qid]) && count($LEM->qattr[$qid]) > 0) {
                        foreach ($LEM->qattr[$qid] as $key => $value) {
                            if (is_null($value) || trim($value) == '') {
                                continue;
                            }
                            switch ($key) {
                                default:
                                case 'exclude_all_others':
                                case 'exclude_all_others_auto':
                                case 'hidden':
                                    if ($value == false || $value == '0') {
                                        $value = null; // so can skip this one - just using continue here doesn't work.
                                    }
                                    break;
                                case 'relevance':
                                    $value = null;  // means an outdate database structure
                                    break;
                            }
                            if (is_null($value) || trim($value) == '') {
                                continue;   // since continuing from within a switch statement doesn't work
                            }
                            $row[$key] = $value;
                        }
                    }

                    // if relevance equation is using SGQA coding, convert to qcoding
                    $relevanceEqn = (($q['info']['relevance'] == '') ? 1 : $q['info']['relevance']);
                    $LEM->em->ProcessBooleanExpression($relevanceEqn, $gseq, $q['info']['qseq']);    // $qseq
                    $relevanceEqn = trim(strip_tags($LEM->em->GetPrettyPrintString()));
                    $rootVarName = $q['info']['rootVarName'];
                    $preg = '';
                    if (isset($LEM->q2subqInfo[$q['info']['qid']]['preg'])) {
                        $preg = $LEM->q2subqInfo[$q['info']['qid']]['preg'];
                        if (is_null($preg)) {
                            $preg = '';
                        }
                    }

                    $row['class'] = 'Q';
                    $row['type/scale'] = $question->type;
                    $row['name'] = $rootVarName;
                    $row['relevance'] = $relevanceEqn;
                    $row['text'] = $qtext;
                    $row['help'] = $help;
                    $row['language'] = $lang;
                    $row['validation'] = $preg;
                    $row['mandatory'] = $mandatory;
                    $row['other'] = $q['info']['other'];
                    $row['default'] = $default;
                    $row['same_default'] = 1;   // TODO - need this: $q['info']['same_default'];

                    $rows[] = $row;

                    //////
                    // SHOW ALL SUB-QUESTIONS
                    //////
                    $sawThis = array(); // array of rowdivids already seen so only show them once
                    foreach ($sgqas as $sgqa) {
                        if ($LEM->knownVars[$sgqa]['qcode'] == $rootVarName) {
                            continue;   // so don't show the main question as a sub-question too
                        }
                        $rowdivid = $sgqa;
                        $varName = $LEM->knownVars[$sgqa]['qcode'];

                        // if SQrelevance equation is using SGQA coding, convert to qcoding
                        $SQrelevance = (($LEM->knownVars[$sgqa]['SQrelevance'] == '') ? 1 : $LEM->knownVars[$sgqa]['SQrelevance']);
                        $LEM->em->ProcessBooleanExpression($SQrelevance, $gseq, $q['info']['qseq']);
                        $SQrelevance = trim(strip_tags($LEM->em->GetPrettyPrintString()));

                        switch ($q['info']['type']) {
                            case '1':
                                if (preg_match('/#1$/', $sgqa)) {
                                    $rowdivid = null;   // so that doesn't show same message for second scale
                                } else {
                                    $rowdivid = substr($sgqa, 0, -2); // strip suffix
                                    $varName = substr($LEM->knownVars[$sgqa]['qcode'], 0, -2);
                                }
                                break;
                            case 'P':
                                if (preg_match('/comment$/', $sgqa)) {
                                    $rowdivid = null;
                                }
                                break;
                            case ':':
                            case ';':
                                $_rowdivid = $LEM->knownVars[$sgqa]['rowdivid'];
                                if (isset($sawThis[$qid . '~' . $_rowdivid])) {
                                    $rowdivid = null;   // so don't show again
                                } else {
                                    $sawThis[$qid . '~' . $_rowdivid] = true;
                                    $rowdivid = $_rowdivid;
                                    $sgqa_len = strlen($sid . 'X' . $gid . 'X' . $qid);
                                    $varName = $rootVarName . '_' . substr($_rowdivid, $sgqa_len);
                                }
                                break;
                        }
                        if (is_null($rowdivid)) {
                            continue;
                        }

                        $sgqaInfo = $LEM->knownVars[$sgqa];
                        $subqText = $sgqaInfo['subqtext'];

                        if (isset($sgqaInfo['default'])) {
                            $default = $sgqaInfo['default'];
                        } else {
                            $default = '';
                        }

                        $row = array();
                        $row['class'] = 'SQ';
                        $row['type/scale'] = 0;
                        $row['name'] = substr($varName, strlen($rootVarName) + 1);
                        $row['relevance'] = $SQrelevance;
                        $row['text'] = $subqText;
                        $row['language'] = $lang;
                        $row['default'] = $default;
                        $rows[] = $row;
                    }

                    //////
                    // SHOW ANSWER OPTIONS FOR ENUMERATED LISTS, AND FOR MULTIFLEXI
                    //////
                    if (isset($LEM->qans[$qid]) || isset($LEM->multiflexiAnswers[$qid])) {
                        $_scale = -1;
                        if (isset($LEM->multiflexiAnswers[$qid])) {
                            $ansList = $LEM->multiflexiAnswers[$qid];
                        } else {
                            $ansList = $LEM->qans[$qid];
                        }
                        foreach ($ansList as $ans => $value) {
                            $ansInfo = explode('~', $ans);
                            $valParts = explode('|', $value);
                            $valInfo[0] = array_shift($valParts);
                            $valInfo[1] = implode('|', $valParts);
                            if ($_scale != $ansInfo[0]) {
                                $_scale = $ansInfo[0];
                            }

                            $row = array();
                            if ($question->type == ':' || $question->type == ';') {
                                $row['class'] = 'SQ';
                            } else {
                                $row['class'] = 'A';
                            }
                            $row['type/scale'] = $_scale;
                            $row['name'] = $ansInfo[1];
                            $row['relevance'] = $assessments == true ? $valInfo[0] : '';
                            $row['text'] = $valInfo[1];
                            $row['language'] = $lang;
                            $rows[] = $row;
                        }
                    }
                }
            }
            // Now generate the array out output data
            $out = array();
            $out[] = $fields;

            foreach ($rows as $row) {
                $tsv = array();
                foreach ($fields as $field) {
                    $val = (isset($row[$field]) ? $row[$field] : '');
                    $tsv[] = $val;
                }
                $out[] = $tsv;
            }

            return $out;
        }



        public function getKnownVars() {
            $result = [];
            if (null !== $session = App()->surveySessionManager->current) {
                foreach($session->getGroups() as $group) {
                    foreach($session->getQuestions($group) as $question) {
                        foreach($question->getFields() as $field) {
                            $result[] = $field;
                        }
                    }
                }
            }
            return $result;

        }





    }

