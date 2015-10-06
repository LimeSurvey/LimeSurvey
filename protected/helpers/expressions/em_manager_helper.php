<?php
use ls\components\QuestionResponseField;
use ls\components\QuestionValidationResult;
use ls\components\QuestionValidationResultCollection;
use ls\components\SurveySession;
use ls\models\Question;
use ls\models\QuestionGroup;
use ls\models\SettingGlobal;
use ls\models\Survey;

/**
 * LimeExpressionManager
 * This is a wrapper class around ExpressionManager that implements a Singleton and eases
 * passing of LimeSurvey variable values into ExpressionManager
 *
 * @author LimeSurvey Team (limesurvey.org)
 * @author Thomas M. White (TMSWhite)
 * @author Denis Chenu <http://sondages.pro>
 */


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
     * A private constructor; prevents direct creation of object
     */
    private function __construct()
    {
        if (null !== $session = App()->surveySessionManager->current) {
            $callback = [$session, 'getQuestionByCode'];
        } else {
            $callback = function() {};
        }
        $this->em = new ExpressionManager([$this, 'GetVarAttribute'], $callback);
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


    public static function ProcessString(
        $string,
        $questionNum = null,
        $replacementFields = [],
        $numRecursionLevels = 1,
        $whichPrettyPrintIteration = 1,
        SurveySession $session
    ) {
        bP();
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
     * @param string> $eqn - the relevance equation
     * @return <type>
     */
    public static function ProcessRelevance($eqn)
    {
        $session = App()->surveySessionManager->current;
        $groupSeq = -1;
        $questionSeq = -1;

        $stringToParse = htmlspecialchars_decode($eqn, ENT_QUOTES);
        return self::singleton()->em->ProcessBooleanExpression($stringToParse, $groupSeq, $questionSeq);

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



    public static function NavigateBackwards()
    {
        $LEM = LimeExpressionManager::singleton();
        $session = App()->surveySessionManager->current;

        switch ($session->format) {
            case Survey::FORMAT_ALL_IN_ONE:
                throw new \Exception("Can not move backwards in all in one mode");
                break;
            case Survey::FORMAT_GROUP:
                $result = $LEM->navigatePrevGroup();
                break;
            case Survey::FORMAT_QUESTION:
                $result = $LEM->navigatePrevQuestion();
                break;
            default:
                throw new \Exception("Invalid format");
                break;
        }
        return $result;
    }

    private function navigatePrevGroup()
    {
        $session = App()->surveySessionManager->current;
        for ($step = $session->step - 1; $step >= 0; $step--) {
            $group = $session->getGroupByIndex($step);
            if (!$group->isRelevant($session->response)) {
                continue;
            } else {
                return [
                    'finished' => false,
                    'seq' => $step,
                ];
            }
        }
        throw new \Exception("No group found.");
    }

    private function navigateNextGroup($force) {
        // First validate the current group
        $session = App()->surveySessionManager->current;
        $this->processData($session->response, App()->request->psr7);
        $group = $session->getCurrentGroup();
        if (!$force
            && !$this->validateGroup($group)
            && $group->isRelevant($session->response)
        ) {
            return [
                'finished' => false,
                'gseq' => $session->step,
                'seq' => $session->step,
            ];
        } else {
            $stepCount = $session->stepCount;
            for ($step = $session->step + 1; $step < $stepCount; $step++) {
                $group = $session->getGroupByIndex($step);
                if (!$group->isRelevant($session->response)) {
                    continue;
                } else {
                    // display new group
                    return [
                        'finished' => false,
                        'seq' => $step,
                    ];
                }


            }

            // Step >= $session->stepCount
            $this->finishResponse();
            return [
                'finished' => true,
                'seq' => $step,
            ];
        }
    }

    private function navigateNextQuestion($force) {
        $this->StartProcessingPage();
        $session = App()->surveySessionManager->current;
        $this->processData($session->response, App()->request->psr7);
        $question = $session->getQuestionByIndex($session->step);
        if (!$force) {
            // Validate current page.
            $valid = $this->validateQuestion($question);
            if ($question->isRelevant($session->response) && !$valid) {
                // redisplay the current question with all error
                $result = [
                    'finished' => false,
                ];
            }
        }
        if ($force || !isset($result)) {
            $stepCount = $session->stepCount;

            for ($step = $session->step + 1; $step <= $stepCount; $step++) {
                if ($step >= $session->stepCount) // Move next with finished, but without submit.
                {
                    $this->finishResponse();
                    $result = [
                        'finished' => true,
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

                if ($question->bool_hidden || !$question->isRelevant($session->response)) {
                    continue;
                } else {
                    // Display new question
                    $result = [
                        'finished' => false,
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
            $validateResult = $this->validateQuestion($question);
            $message .= $validateResult->getMessagesAsString();

            if ($question->bool_hidden || !$question->isRelevant($session->response)) {
                // then skip this question, $this->updatedValues updated in _ValidateQuestion
                continue;
            } else {
                // Display new question
                $result = [
                    'finished' => false,
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
     * @param boolean $force Continue to go forward even if there are violations to the validity rules
     * @return mixed
     */
    static function NavigateForwards($force = false)
    {
        $LEM = LimeExpressionManager::singleton();
        $session = App()->surveySessionManager->current;

        switch ($session->format) {
            case Survey::FORMAT_ALL_IN_ONE:
                $LEM->StartProcessingPage();
                $session = App()->surveySessionManager->current;
                $LEM->processData($session->response, App()->request->psr7);
                $valid = $LEM->validateSurvey();
                $finished = $valid;
                if ($finished) {
                    $LEM->finishResponse();
                }
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
     * Mark response as finished.
     * @return void
     */
    private function finishResponse()
    {
        $session = App()->surveySessionManager->current;
        $response = $session->response;
        $response->lastpage = $session->step;

        if ($session->survey->bool_savetimings) {
            $cSave = new Save();
            $cSave->set_answer_time();
        }

        // Delete the save control record if successfully finalize the submission
        \ls\models\SavedControl::model()->deleteAllByAttributes([
            'srid' => $session->responseId,
            'sid' => $session->surveyId
        ]);

        // Check Quotas
        $aQuotas = \ls\helpers\FrontEnd::checkCompletedQuota('return');
        if ($aQuotas && !empty($aQuotas)) {
            \ls\helpers\FrontEnd::checkCompletedQuota($this->sid);  // will create a page and quit: why not use it directly ?
        } else {
            $session->response->markAsFinished();
            $session->response->save();

        }
    }

    /**
     * Get last move information, optionally clearing the substitution cache
     * @param type $clearSubstitutionInfo
     * @return type
     */
    public static function GetLastMoveResult($clearSubstitutionInfo = false)
    {
        $LEM =& LimeExpressionManager::singleton();
        if ($clearSubstitutionInfo) {
            $LEM->em->ClearSubstitutionInfo();  // need to avoid double-generation of tailoring info
        }

        return (isset($LEM->lastMoveResult) ? $LEM->lastMoveResult : null);
    }

    private function processData(\ls\interfaces\iResponse $response, \Psr\Http\Message\ServerRequestInterface $request)
    {

        foreach ($request->getParsedBody() as $key => $value) {
            $response->setResponseValue($key, $value);
        }
        foreach ($request->getUploadedFiles() as $field => $files) {
            $response->setFiles($field, $files);
        }
        return $response->save();
    }

    private function jumpToGroup($seq, $processPOST, $force) {
        // First validate the current group
        $session = App()->surveySessionManager->current;
        if ($processPOST) {
            $this->processData($session->response, App()->request->psr7);
        } else {
            $updatedValues = array();
        }

        $message = '';
        // Validate if moving forward.
        if (!$force && $seq > $session->step) {
            $valid = $this->validateGroup($session->getCurrentGroup());
            if (!$valid) {
                return [
                    'finished' => false,
                    'message' => $message,
                    'valid' => $valid,
                ];
            }
        }

        $stepCount = $session->stepCount;
        for ($step = $seq; $step < $stepCount; $step++) {
            $group = $session->getGroupByIndex($step);
            $valid = $this->validateGroup($group);
            if (!$group->isRelevant($session->response)) {
                // then skip this group
                continue;
            } else {
                // Display new group
                // Showing error if question are before the maxstep
                $result = [
                    'finished' => false,
                    'gseq' => $step,
                    'seq' => $step,
                    'valid' => $valid,
                ];
                break;
            }

            if ($step >= $session->stepCount) {
                die('noo finished?');
                $result = [
                    'finished' => true,
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

    private function jumpToQuestion($seq, $processPOST, $force) {
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
            if (!$valid) {
                // Redisplay the current question, qhowning error
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
            if (($question->bool_hidden || !$question->isRelevant($session->response))) {
                // then skip this question
                continue;
            } elseif (!$valid && $step < $seq) {
                // if there is a violation while moving forward, need to stop and ask that set of questions
                // if there are no violations, can skip this group as long as changed values are saved.
                die('skip2');
                continue;
            } else {
//                    die('break');
                // Display new question
                // Showing error if question are before the maxstep
                $result = [
                    'finished' => false,
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
        if ($seq < 0) {
            throw new \InvalidArgumentException("Sequence must be >= 0");
        }
        $session = App()->surveySessionManager->current;
        $LEM = LimeExpressionManager::singleton();
        switch ($session->format) {
            case Survey::FORMAT_ALL_IN_ONE:
                // This only happens if saving data so far, so don't want to submit it, just validate and return
                $LEM->StartProcessingPage(true);
                $valid = $LEM->validateSurvey($force);
                $LEM->lastMoveResult = [
                    'finished' => false,
                    'gseq' => 1,
                    'seq' => 1,
                    'valid' => $valid,
                ];
                $result = $LEM->lastMoveResult;
                break;
            case Survey::FORMAT_GROUP:
                $result = $LEM->jumpToGroup($seq, $processPOST, $force);
                break;
            case Survey::FORMAT_QUESTION:
                $result = $LEM->jumpToQuestion($seq, $processPOST, $force);
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
     * @return boolean
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
     * @return boolean
     */

    private function validateQuestion(\ls\models\Question $question, $force = false)
    {
        $session = App()->surveySessionManager->current;
        $result =  $question->validateResponse($session->response);
        return $result;
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
     * Get array of info needed to display the ls\models\Question Index
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
                throw new \Exception("ls\models\Question indexes don't apply to all in one surveys.");
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
                        throw new \Exception("getFields() must return an array of ls\components\QuestionResponseField");
                    }
                    $fields[$field->code] = $field;
                    if (YII_DEBUG) {
                        $field->jsonSerialize();
                    }
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
                        case 'I': //Language ls\models\Question
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
