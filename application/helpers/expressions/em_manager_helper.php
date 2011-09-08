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
    private $em;    // Expression Manager
    private $groupRelevanceInfo;
    private $groupNum;
    private $debugLEM = true;   // set this to false to turn off debugging
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

    // A private constructor; prevents direct creation of object
    private function __construct()
    {
        $this->em = new ExpressionManager();
    }

    // The singleton method
    public static function singleton()
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

    /**
     * Create the arrays needed by ExpressionManager to process LimeSurvey strings.
     * The long part of this function should only be called once per page display (e.g. only if $fieldMap changes)
     *
     * @param <type> $forceRefresh
     * @param <type> $anonymized
     * @return boolean - true if $fieldmap had been re-created, so ExpressionManager variables need to be re-set
     */

    public function setVariableAndTokenMappingsForExpressionManager($forceRefresh=false,$anonymized=false,$allOnOnePage=false,$surveyid=NULL)
    {
        // TODO - this is called multiple times per page - can it be reduced to once per page?
        $fieldmap=createFieldMap($surveyid,$style='full',$forceRefresh);
        if (!isset($fieldmap)) {
            return false; // implies an error occurred
        }

        $this->knownVars = array();   // mapping of VarName to Value
        $this->debugLog = array();    // array of mappings among values to confirm their accuracy
        $this->qid2code = array();    // List of codes for each question - needed to know which to NULL if a question is irrelevant
        $this->jsVar2qid = array();
        $this->alias2varName = array();
        $this->varNameAttr = array();

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
            $questionAttributes = getQuestionAttributes($questionId,$fielddata['type']);
            $relevance = (isset($questionAttributes['relevance'])) ? $questionAttributes['relevance'] : 1;

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

            // Check off-page relevance status
            if (isset($_SESSION['relevanceStatus'])) {
                $relStatus = (isset($_SESSION['relevanceStatus'][$questionId]) ? $_SESSION['relevanceStatus'][$questionId] : 1);
            }
            else {
                $relStatus = 1;
            }

            $readWrite = 'N';
            if (isset($_SESSION[$code]))
            {
                $codeValue = $_SESSION[$code];
                $displayValue= retrieve_Answer($surveyid, $code);
            }
            else
            {
                $codeValue = '';
                $displayValue = '';
            }

            switch($fielddata['type'])
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
                    $varName = $fielddata['title'];
                    $question = $fielddata['question'];
                    break;
                case '1': //Array (Flexible Labels) dual scale
                    $varName = $fielddata['title'] . '_' . $fielddata['aid'] . '_' . $fielddata['scale_id'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion'] . '[' . $fielddata['scale'] . ']';
                    break;
                case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                case 'F': //ARRAY (Flexible) - Row Format
                case 'H': //ARRAY (Flexible) - Column Format
                case 'K': //MULTIPLE NUMERICAL QUESTION
                case 'M': //Multiple choice checkbox
                case 'P': //Multiple choice with comments checkbox + text
                case 'Q': //MULTIPLE SHORT TEXT
                case 'R': //RANKING STYLE
                    $varName = $fielddata['title'] . '_' . $fielddata['aid'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion'];
                    break;
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case ';': //ARRAY (Multi Flexi) Text
                    $varName = $fielddata['title'] . '_' . $fielddata['aid'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion1'] . '[' . $fielddata['subquestion2'] . ']';
                    break;
            }
            switch($fielddata['type'])
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
            $this->SetVariableAttributes($type, $varName, $code, $codeValue, $jsVarName, $readWrite, $isOnCurrentPage, $displayValue, $question, $relevance, $questionNum, $relStatus,  $mandatory);
        }

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
                    'relevanceStatus'=>'1',
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
                    'relevanceStatus'=>'1',
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

        if ($this->debugLEM)
        {
            $debugLog_html = "<table border='1'>";
            $debugLog_html .= "<tr><th>Code</th><th>Type</th><th>VarName</th><th>CodeVal</th><th>DisplayVal</th><th>JSname</th><th>Writable?</th><th>Set On This Page?</th><th>Relevance</th><th>Question</th></tr>";
            foreach ($this->debugLog as $t)
            {
                $debugLog_html .= "<tr><td>" . $t['code']
                    . "</td><td>" . $t['type']
                    . "</td><td>" . $t['varname']
                    . "</td><td>" . $t['codeValue']
                    . "</td><td>" . $t['displayValue']
                    . "</td><td>" . $t['jsName']
                    . "</td><td>" . $t['readWrite']
                    . "</td><td>" . $t['isOnCurrentPage']
                    . "</td><td>" . $t['relevance']
                    . "</td><td>" . $t['question']
                    . "</td></tr>";
            }
            $debugLog_html .= "</table>";
            $this->surveyLogicFile = $debugLog_html;
        }

        return true;
    }

    /**
     *
     * @param <type> $type
     * @param <type> $varName
     * @param <type> $code
     * @param <type> $codeValue
     * @param <type> $jsVarName
     * @param <type> $readWrite
     * @param <type> $isOnCurrentPage
     * @param <type> $displayValue
     * @param <type> $question
     * @param <type> $relevance
     * @param <type> $questionNum
     * @param <type> $relStatus
     * @param <type> $mandatory
     */
    private function SetVariableAttributes($type, $varName, $code, $codeValue, $jsVarName, $readWrite, $isOnCurrentPage, $displayValue, $question, $relevance, $questionNum, $relStatus, $mandatory)
    {
        // Set mappings of variable names to needed attributes
        $varInfo_Code = array(
            'codeValue'=>$codeValue,
            'jsName'=>$jsVarName,
            'readWrite'=>$readWrite,
            'isOnCurrentPage'=>$isOnCurrentPage,
            'displayValue'=>$displayValue,
            'question'=>$question,
            'qid'=>$questionNum,
            'relevance'=>$relevance,
            'relevanceNum'=>'relevance' . $questionNum,
            'relevanceStatus'=>$relStatus,
            );
        $this->knownVars[$varName] = $varInfo_Code;
        $this->knownVars['INSERTANS:' . $code] = $varInfo_Code; // $varInfo_DisplayVal;

        $this->jsVar2qid[$jsVarName] = $questionNum;

        // Create JavaScript arrays
        $this->alias2varName[$varName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $varName . "':'" . $jsVarName . "'");
        $this->alias2varName[$jsVarName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $jsVarName . "':'" . $jsVarName . "'");
        $this->alias2varName[$code] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $code . "':'" . $jsVarName . "'");
        $this->alias2varName['INSERTANS:' . $code] = array('jsName'=>$jsVarName, 'jsPart' => "'INSERTANS:" . $code . "':'" . $jsVarName . "'");

        $this->varNameAttr[$jsVarName] = "'" . $jsVarName . "':{"
            . "'jsName':'" . $jsVarName
//            . "','code':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$codeValue),ENT_QUOTES)
            . "','qid':" . $questionNum
            . ",'mandatory':'" . $mandatory
            . "','question':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$question),ENT_QUOTES)
            . "','type':'" . $type
            . "','relevance':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$relevance),ENT_QUOTES)
            . "','shown':'" . htmlspecialchars(preg_replace('/[[:space:]]/',' ',$displayValue),ENT_QUOTES)
            . "'}";

        if ($this->debugLEM)
        {
            $this->debugLog[] = array(
                'code' => $code,
                'type' => $type,
                'varname' => $varName,
                'jsName' => $jsVarName,
                'question' => $question,
                'codeValue' => ($codeValue=='') ? '&nbsp;' : $codeValue,
                'displayValue' => ($displayValue=='') ? '&nbsp;' : $displayValue,
                'readWrite' => $readWrite,
                'isOnCurrentPage' => $isOnCurrentPage,
                'relevance' => $relevance,
                );
        }
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

    static function ProcessString($string, $questionNum=NULL, $replacementFields=array(), $debug=false, $numRecursionLevels=1, $whichPrettyPrintIteration=1)
    {
        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;

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
            $em->RegisterVarnamesUsingMerge($replaceArray);   // TODO - is it safe to just merge these in each time, or should a refresh be forced?
        }
        $result = $em->sProcessStringContainingExpressions(htmlspecialchars_decode($string,ENT_QUOTES),(is_null($questionNum) ? 0 : $questionNum), $numRecursionLevels, $whichPrettyPrintIteration);

        if ($lem->debugLEM)
        {
                $varsUsed = $em->GetJSVarsUsed();
                if (is_array($varsUsed) and count($varsUsed) > 0) {
                    $lem->pageTailoringLog .= '<tr><td>' . $lem->groupNum . '</td><td>' . $string . '</td><td>' . $em->GetLastPrettyPrintExpression() . '</td><td>' . $result . "</td></tr>\n";
                }
        }

        return $result;
    }


    /**
     * Compute Relevance, processing $eqn to get a boolean value.  If there are syntax errors, currently returns true.  My change to returning null so can look for errors?
     * @param <type> $eqn
     * @return <type>
     */
    static function ProcessRelevance($eqn,$questionNum=NULL,$jsResultVar=NULL,$type=NULL,$hidden=0)
    {
        // These will be called in the order that questions are supposed to be asked
        $lem = LimeExpressionManager::singleton();
        if (!isset($eqn) || trim($eqn=='') || trim($eqn)=='1')
        {
            $lem->groupRelevanceInfo[] = array(
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
        $em = $lem->em;
        $result = $em->ProcessBooleanExpression(htmlspecialchars_decode($eqn,ENT_QUOTES));
        $jsVars = $em->GetJSVarsUsed();
        $relevanceVars = implode('|',$em->GetJSVarsUsed());
        $relevanceJS = $lem->em->GetJavaScriptEquivalentOfExpression();
        $lem->groupRelevanceInfo[] = array(
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
        return $result;
    }

    /**
     * Used to show potential syntax errors of processing Relevance or Equations.
     * @return <type>
     */
    static function GetLastPrettyPrintExpression()
    {
        $lem = LimeExpressionManager::singleton();
        return $lem->em->GetLastPrettyPrintExpression();
    }

    static function StartProcessingPage($debug=true,$allOnOnePage=false)
    {
        $lem = LimeExpressionManager::singleton();
        $lem->pageRelevanceInfo=array();
        $lem->pageTailorInfo=array();
        $lem->alias2varName=array();
        $lem->varNameAttr=array();
        $lem->allOnOnePage=$allOnOnePage;
        $lem->pageTailoringLog='';
        $lem->surveyLogicFile='';

        if ($debug && $lem->debugLEM)
        {
            $lem->pageTailoringLog .= '<tr><th>Group</th><th>Source</th><th>Pretty Print</th><th>Result</th></tr>';
        }
    }

    static function StartProcessingGroup($groupNum=NULL,$anonymized=false,$surveyid=NULL)
    {
        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;
        $em->StartProcessingGroup();
        if (!is_null($groupNum))
        {
            $lem->groupNum = $groupNum;
            $lem->qid2code = array();   // List of codes for each question - needed to know which to NULL if a question is irrelevant
            $lem->jsVar2qid = array();

            if (!is_null($surveyid) && $lem->setVariableAndTokenMappingsForExpressionManager(true,$anonymized,$lem->allOnOnePage,$surveyid))
            {
                // means that some values changed, so need to update what was registered to ExpressionManager
                $em->RegisterVarnamesUsingMerge($lem->knownVars);
            }
        }
        $lem->groupRelevanceInfo = array();
    }

    static function FinishProcessingGroup()
    {
        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;
        $lem->pageTailorInfo[] = $em->GetCurrentSubstitutionInfo();
        $lem->pageRelevanceInfo[] = $lem->groupRelevanceInfo;
    }

    static function FinishProcessingPage()
    {
        $lem = LimeExpressionManager::singleton();
        $_SESSION['EM_pageTailoringLog'] = $lem->pageTailoringLog;
        $_SESSION['EM_surveyLogicFile'] = $lem->surveyLogicFile;
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
        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;

        $knownVars = $lem->knownVars;

        $jsParts=array();
        $allJsVarsUsed=array();
        $jsParts[] = '<script type="text/javascript" src="' . base_url() . '/scripts/admin/expressions/em_javascript.js"></script>';
        $jsParts[] = "<script type='text/javascript'>\n<!--\n";
        $jsParts[] = "function ExprMgr_process_relevance_and_tailoring(){\n";

        // flatten relevance array, keeping proper order

        $pageRelevanceInfo=array();
        $qidList = array(); // list of questions used in relevance and tailoring

        if (is_array($lem->pageRelevanceInfo))
        {
            foreach($lem->pageRelevanceInfo as $prel)
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
                // First check if there is any tailoring  and construct the tailoring JavaScript if needed
                $tailorParts = array();
                foreach ($lem->pageTailorInfo as $tailor)
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
                if (($relevance == '' || $relevance == '1') && count($tailorParts) == 0)
                {
                    // Only show constitutively true relevances if there is tailoring that should be done.
                    $jsParts[] = "document.getElementById('relevance" . $arg['qid'] . "').value='1'; // always true\n";
                    continue;
                }
                $relevance = ($relevance == '') ? '1' : $relevance;
                $jsResultVar = $lem->em->GetJsVarFor($arg['jsResultVar']);
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
        if (isset($lem->alias2varName) && count($lem->alias2varName) > 0)
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
                foreach ($lem->alias2varName as $key=>$value)
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
                $neededCanonicalAttr[] = $lem->varNameAttr[$nc];
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

                            if (isset($lem->jsVar2qid[$jsVar])) {
                                $qidList[$lem->jsVar2qid[$jsVar]] = $lem->jsVar2qid[$jsVar];
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
                $jsParts[] = "<input type='hidden' id='" . $jsVar .  "' value='" . htmlspecialchars($undeclaredVal[$jsVar],ENT_QUOTES) . "'/>\n";
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
            $jsParts[] = "<input type='hidden' id='relevance" . $qid . "' value='" . $relStatus . "'/>\n";
            if (isset($lem->qid2code[$qid]))
            {
                $jsParts[] = "<input type='hidden' id='relevance" . $qid . "codes' value='" . $lem->qid2code[$qid] . "'/>\n";
            }
        }

        return implode('',$jsParts);
    }

    /**
     * Unit test
     */
    static function UnitTestProcessStringContainingExpressions()
    {
        $vars = array(
'name' => array('codeValue'=>'"<Sergei>\'', 'jsName'=>'java61764X1X1', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
'age' => array('codeValue'=>45, 'jsName'=>'java61764X1X2', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
'numKids' => array('codeValue'=>2, 'jsName'=>'java61764X1X3', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
'numPets' => array('codeValue'=>1, 'jsName'=>'java61764X1X4', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
// Constants
'INSERTANS:61764X1X1'   => array('codeValue'=> '<Sergei>', 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'Y'),
'INSERTANS:61764X1X2'   => array('codeValue'=> 45, 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'Y'),
'INSERTANS:61764X1X3'   => array('codeValue'=> 2, 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'N'),
'INSERTANS:61764X1X4'   => array('codeValue'=> 1, 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'N'),
'TOKEN:ATTRIBUTE_1'     => array('codeValue'=> 'worker', 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'N'),
        );

        $tests = <<<EOD
{name}
{age}
{numKids}
{numPets}
{INSERTANS:61764X1X1}
{INSERTANS:61764X1X2}
{INSERTANS:61764X1X3}
{INSERTANS:61764X1X4}
{TOKEN:ATTRIBUTE_1}
{name}, you said that you are {age} years old, and that you have {numKids} {if((numKids==1),'child','children')} and {numPets} {if((numPets==1),'pet','pets')} running around the house. So, you have {numKids + numPets} wild {if((numKids + numPets ==1),'beast','beasts')} to chase around every day.
Since you have more {if((numKids > numPets),'children','pets')} than you do {if((numKids > numPets),'pets','children')}, do you feel that the {if((numKids > numPets),'pets','children')} are at a disadvantage?
{INSERTANS:61764X1X1}, you said that you are {INSERTANS:61764X1X2} years old, and that you have {INSERTANS:61764X1X3} {if((INSERTANS:61764X1X3==1),'child','children')} and {INSERTANS:61764X1X4} {if((INSERTANS:61764X1X4==1),'pet','pets')} running around the house.  So, you have {INSERTANS:61764X1X3 + INSERTANS:61764X1X4} wild {if((INSERTANS:61764X1X3 + INSERTANS:61764X1X4 ==1),'beast','beasts')} to chase around every day.
Since you have more {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'children','pets')} than you do {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'pets','children')}, do you feel that the {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'pets','children')} are at a disadvantage?
{name2}, you said that you are {age + 5)} years old, and that you have {abs(numKids) -} {if((numKids==1),'child','children')} and {numPets} {if((numPets==1),'pet','pets')} running around the house. So, you have {numKids + numPets} wild {if((numKids + numPets ==1),'beast','beasts')} to chase around every day.
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

        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;
        $em->RegisterVarnamesUsingMerge($vars);

        print '<table border="1"><tr><th>Test</th><th>Result</th></tr>';    // <th>VarName(jsName, readWrite, isOnCurrentPage)</th></tr>';
        for ($i=0;$i<count($alltests);++$i)
        {
            $test = $alltests[$i];
            $result = LimeExpressionManager::ProcessString($test, $i, NULL, false, 1, 1);
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
        $tests = <<<EOT
junk~1~text~Enter "junk" here to test XSS - will show below
info2~1~message~Here is a messy string: {info}<br/>Here is the "junk" you entered: {junk}
name~1~text~What is your name?
age~1~text~How old are you?
badage~1~expr~{badage=((age<16) || (age>80))}
agestop~!is_empty(age) && ((age<16) || (age>80))~message~Sorry, {name}, you are too {if((age<16),'young',if((age>80),'old','middle-aged'))} for this test.
kids~!((age<16) || (age>80))~yesno~Do you have children?
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

        // collect variables
        $i=0;
        foreach(explode("\n",$tests) as $test)
        {
            $args = explode("~",$test);
            $vars[$args[0]] = array('codeValue'=>'', 'jsName'=>'java_' . $args[0], 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y', 'relevanceNum'=>'relevance' . $i++, 'relevanceStatus'=>'1');
            $varSeq[] = $args[0];
            $testArgs[] = $args;
        }

        LimeExpressionManager::StartProcessingPage(true,true);

        LimeExpressionManager::StartProcessingGroup();

        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;
        $em->RegisterVarnamesUsingMerge($vars);

        // collect relevance
        $alias2varName = array();
        $varNameAttr = array();
        for ($i=0;$i<count($testArgs);++$i)
        {
            $testArg = $testArgs[$i];
            $var = $testArg[0];
            LimeExpressionManager::ProcessRelevance(htmlspecialchars_decode($testArg[1],ENT_QUOTES),$i,$var);
            $question = LimeExpressionManager::ProcessString($testArg[3], $i, NULL, true, 1, 1);

            $jsVarName='java_' . $testArg[0];

            $argInfo[] = array(
                'num' => $i,
                'name' => $jsVarName,
                'type' => $testArg[2],
                'question' => $question,
            );
            $alias2varName[$var] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $var . "':'" . $jsVarName . "'");
            $alias2varName[$jsVarName] = array('jsName'=>$jsVarName, 'jsPart' => "'" . $jsVarName . "':'" . $jsVarName . "'");
            $varNameAttr[$jsVarName] = "'" . $jsVarName . "':{"
                . "'jsName':'" . $jsVarName
                . "','qid':'" . $i
            . "'}";
        }
        $lem->alias2varName = $alias2varName;
        $lem->varNameAttr = $varNameAttr;
        LimeExpressionManager::FinishProcessingGroup();

        print LimeExpressionManager::GetRelevanceAndTailoringJavaScript();

        // Print Table of questions
        print "<table border='1'><tr><td>";
        foreach ($argInfo as $arg)
        {
            print "<input type='hidden' id='display" . $arg['num'] . "' value='on'/>\n";    // set all as  On by default - relevance processing will blank them as needed
            print "<input type='hidden' id='relevance" . $arg['num'] . "' value='1'/>\n";    // set all as  On by default - relevance processing will blank them as needed
            print "<div id='question" . $arg['num'] . "'>\n";
            if ($arg['type'] == 'expr')
            {
                // Hack for testing purposes - rather than using LimeSurvey internals to store the results of equations, process them via a hidden <div>
                print "<div style='display: none' id='hack_" . $arg['name'] . "'>" . $arg['question'];
                print "<input type='hidden' id='" . $arg['name'] . "' value=''/></div>\n";
            }
            else {
                print "<table border='1' width='100%'>\n<tr>\n<td>[Q" . $arg['num'] . "] " . $arg['question'] . "</td>\n";
                switch($arg['type'])
                {
                    case 'yesno':
                    case 'text':
                        print "<td><input type='text' id='" . $arg['name'] . "' value='' onchange='ExprMgr_process_relevance_and_tailoring()'/></td>\n";
                        break;
                    case 'message':
                        print "<input type='hidden' id='" . $arg['name'] . "' value=''/>\n";
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
