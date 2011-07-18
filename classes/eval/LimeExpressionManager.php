<?php
/**
 * Description of LimeExpressionManager
 * This is a wrapper class around ExpressionManager that implements a Singleton and eases
 * passing of LimeSurvey variable values into ExpressionManager
 *
 * @author Thomas M. White
 */
include_once('ExpressionManager.php');

class LimeExpressionManager {
    private static $instance;
    private $em;    // Expression Manager
    
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
     * @param <type> $sid
     * @param <type> $forceRefresh
     * @return boolean - true if $fieldmap had been re-created, so ExpressionManager variables need to be re-set
     */

    public function setVariableAndTokenMappingsForExpressionManager($sid,$forceRefresh=false,$anonymized=false,$groupId=NULL)
    {
        global $globalfieldmap, $clang, $surveyid;

        //checks to see if fieldmap has already been built for this page.
        if (isset($globalfieldmap[$surveyid]['expMgr_varMap'][$clang->langcode])&& !$forceRefresh) {
            return false;   // means the mappings have already been set and don't need to be re-created
        }
        if (isset($globalfieldmap[$surveyid]['exprMgr_groupId']))
        {
            $global_groupId = $globalfieldmap[$surveyid]['exprMgr_groupId'];
        }
        else {
            $global_groupId = NULL;
        }

        $fieldmap=createFieldMap($surveyid,$style='full',$forceRefresh);
        if (!isset($fieldmap)) {
            return false; // implies an error occurred
        }

        $sgqaMap = array();  // mapping of SGQA to Value
        $knownVars = array();   // mapping of VarName to Value
        $debugLog = array();    // array of mappings among values to confirm their accuracy
        foreach($fieldmap as $fielddata)
        {
            $code = $fielddata['fieldname'];
            if (!preg_match('#^\d+X\d+X\d+#',$code))
            {
                continue;   // not an SGQA value
            }
            $fieldNameParts = explode('X',$code);
            $groupId = $fieldNameParts[1];
            $isOnCurrentPage = ($groupId != NULL && $groupId == $global_groupId) ? 'Y' : 'N';

            $questionId = $fieldNameParts[2];
            $questionAttributes = getQuestionAttributes($questionId,$fielddata['type']);
            $relevance = (isset($questionAttributes['relevance'])) ? $questionAttributes['relevance'] : 1;
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
                    $varName = $fielddata['title'] . '.' . $fielddata['aid'] . '.' . $fielddata['scale_id'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion'] . ': ' . $fielddata['scale'];
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
                    $varName = $fielddata['title'] . '.' . $fielddata['aid'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion'];
                    break;
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case ';': //ARRAY (Multi Flexi) Text
                    $varName = $fielddata['title'] . '.' . $fielddata['aid'];
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion1'] . ': ' . $fielddata['subquestion2'];
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
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                case 'X': //BOILERPLATE QUESTION
                case 'Y': //YES/NO radio-buttons
                case '|': //File Upload
                case '*': //Equation
                case '1': //Array (Flexible Labels) dual scale
                case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                case 'F': //ARRAY (Flexible) - Row Format
                case 'H': //ARRAY (Flexible) - Column Format
                case 'M': //Multiple choice checkbox
                case 'P': //Multiple choice with comments checkbox + text
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case ';': //ARRAY (Multi Flexi) Text
                    $jsVarName = 'java' . $code;
                    break;
            }
            $readWrite = 'N';
            if (isset($_SESSION[$code]))
            {
                $codeValue = $_SESSION[$code];
                $displayValue= retrieve_Answer($code, $_SESSION['dateformats']['phpdate']);
                $varInfo_Code = array(
                    'codeValue'=>$codeValue,
                    'jsName'=>$jsVarName,
                    'readWrite'=>$readWrite,
                    'isOnCurrentPage',$isOnCurrentPage,
                    'displayValue'=>$displayValue,
                    'question'=>$question,
                    'relevance'=>$relevance,
                    );
                $varInfo_DisplayVal = array(
                    'codeValue'=>$displayValue,
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'isOnCurrentPage'=>$isOnCurrentPage,
                    );
                $varInfo_Question = array(
                    'codeValue'=>$question,
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'isOnCurrentPage'=>$isOnCurrentPage,
                    );
                $knownVars[$varName] = $varInfo_Code;
                $knownVars[$varName . '.shown'] = $varInfo_DisplayVal;
                $knownVars[$varName . '.question']= $varInfo_Question;
                $knownVars['INSERTANS:' . $code] = $varInfo_DisplayVal;
            }
            else
            {
                unset($codeValue);
                unset($displayValue);
                $knownVars['INSERTANS:' . $code] = array(
                    'codeValue'=>'',
                    'jsName'=>'',
                    'readWrite'=>$readWrite,
                    'isOnCurrentPage'=>$isOnCurrentPage,
                );
            }
            $debugLog[] = array(
                'code' => $code,
                'type' => $fielddata['type'],
                'varname' => $varName,
                'jsName' => $jsVarName,
                'question' => $question,
                'codeValue' => isset($codeValue) ? $codeValue : '&nbsp;',
                'displayValue' => isset($displayValue) ? $displayValue : '&nbsp;',
                'readWrite' => $readWrite,
                'isOnCurrentPage' => $isOnCurrentPage,
                'relevance' => $relevance,
                );

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
                $knownVars[$key] = array(
                    'codeValue'=>$val,
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'isOnCurrentPage'=>'N',
                    );

                $debugLog[] = array(
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
        else
        {
            // Explicitly set all tokens to blank
            $blankVal = array(
                    'codeValue'=>'',
                    'jsName'=>'',
                    'readWrite'=>'N',
                    'isOnCurrentPage'=>'N',
                    );
            $knownVars['TOKEN:FIRSTNAME'] = $blankVal;
            $knownVars['TOKEN:LASTNAME'] = $blankVal;
            $knownVars['TOKEN:EMAIL'] = $blankVal;
            $knownVars['TOKEN:USESLEFT'] = $blankVal;
            for ($i=1;$i<=100;++$i) // TODO - is there a way to know  how many attributes are set?  Looks like max is 100
            {
                $knownVars['TOKEN:ATTRIBUTE_' . $i] = $blankVal;
            }
        }

        $debugLog_html = "<table border='1'>";
        $debugLog_html .= "<tr><th>Code</th><th>Type</th><th>VarName</th><th>CodeVal</th><th>DisplayVal</th><th>JSname</th><th>Writable?</th><th>Set On This Page?</th><th>Relevance</th><th>Question</th></tr>";
        foreach ($debugLog as $t)
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
        file_put_contents('/tmp/LimeExpressionManager-page.html',$debugLog_html);
        
        $globalfieldmap[$surveyid]['expMgr_varMap'][$clang->langcode] = $knownVars;

        return true;
    }

    /**
     * Translate all Expressions, Macros, registered variables, etc. in $string
     * @global <type> $surveyid
     * @global <type> $clang
     * @global <type> $globalfieldmap
     * @param <type> $string - the string to be replaced
     * @param <type> $replacementFields - optional replacement values
     * @param boolean $debug - if true,write translations for this page to html-formatted log file
     * @param boolean $forceRefresh - if true, reset $fieldMap and the derived arrays of registered variables and values
     * @param boolean $anonymized - if true, then don't translate TOKEN values
     * @param <type> $groupId - the current Group ID number - this is needed to properly name the answer IDs in JavaScript
     * @param <type> $numRecursionLevels - the number of times to recursively subtitute values in this string
     * @param <type> $whichPrettyPrintIteration - if want to pretty-print the source string, which recursion  level should be pretty-printed
     * @return <type> - the original $string with all replacements done.
     */

    static function ProcessString($string, $replacementFields=array(), $debug=false, $forceRefresh=false, $anonymized=false, $groupId=NULL, $numRecursionLevels=1, $whichPrettyPrintIteration=1)
    {
        global $surveyid, $clang, $globalfieldmap;

        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;
        if (!is_null($groupId))
        {
            $globalfieldmap[$surveyid]['exprMgr_groupId']=$groupId;
        }
        if (!is_null($surveyid) && $lem->setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh,$anonymized))
        {
            // means that some values changed, so need to update what was registered to ExpressionManager
            $em->RegisterVarnamesUsingReplace($globalfieldmap[$surveyid]['expMgr_varMap'][$clang->langcode]);
            if ($debug)
            {
                $debugLog_html = '<tr><th>Group</th><th>Source</th><th>Pretty Print</th><th>Result</th></tr>';
                file_put_contents('/tmp/LimeExpressionManager-Debug-ThisPage.html',$debugLog_html); // replace the value
            }
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
            $em->RegisterVarnamesUsingMerge($replaceArray);   // TODO - is it safe to just merge these in each time, or should a refresh be forced?
        }
        $result = $em->sProcessStringContainingExpressions(htmlspecialchars_decode($string),$numRecursionLevels, $whichPrettyPrintIteration);

        if ($debug)
        {
            if (isset($globalfieldmap[$surveyid]['exprMgr_groupId']))
            {
                $global_groupId = $globalfieldmap[$surveyid]['exprMgr_groupId'];
            }
            else {
                $global_groupId = 'NULL';
            }
            $debugLog_html = '<tr><td>' . $global_groupId . '</td><td>' . $string . '</td><td>' . $em->GetLastPrettyPrintExpression() . '</td><td>' . $result . "</td></tr>\n";
            file_put_contents('/tmp/LimeExpressionManager-Debug-ThisPage.html',$debugLog_html,FILE_APPEND);
        }

        return $result;
    }


    /**
     * Compute Relevance, processing $string to get a boolean value.  If there are syntax errors, currently returns true.  My change to returning null so can look for errors?
     * @param <type> $string
     * @return <type>
     */
    static function ProcessRelevance($string)
    {
        if (!isset($string) || trim($string=='') || trim($string)=='1')
        {
            return true;
        }
        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;
        return $em->ProcessBooleanExpression(htmlspecialchars_decode($string));
    }


    /**
     * Unit test
     */
    static function UnitTestProcessStringContainingExpressions()
    {
        $vars = array(
'name' => array('codeValue'=>'Sergei', 'jsName'=>'java61764X1X1', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
'age' => array('codeValue'=>45, 'jsName'=>'java61764X1X2', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
'numKids' => array('codeValue'=>2, 'jsName'=>'java61764X1X3', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
'numPets' => array('codeValue'=>1, 'jsName'=>'java61764X1X4', 'readWrite'=>'Y', 'isOnCurrentPage'=>'Y'),
// Constants
'INSERTANS:61764X1X1'   => array('codeValue'=> 'Sergei', 'jsName'=>'', 'readWrite'=>'N', 'isOnCurrentPage'=>'Y'),
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
This line has a &lt;script&gt; section, but if you look at the source, you will see that it has errors: <script type="text/javascript" language="Javascript">var job='{TOKEN:ATTRIBUTE_1}'; if (job=='worker') { document.write('BOSSES') } else { document.write('WORKERS') } </script>.
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
        $alltests[] = 'This line has a hidden &lt;script&gt;: <script type="text/javascript" language="Javascript">' . $javascript1 . '</script>';
        $alltests[] = 'This line has a hidden &lt;script&gt;: <script type="text/javascript" language="Javascript">' . $javascript2 . '</script>';

        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;

        $em->RegisterVarnamesUsingMerge($vars);

        print '<table border="1"><tr><th>Test</th><th>Result</th><th>VarName(jsName, readWrite, isOnCurrentPage)</th></tr>';
        foreach($alltests as $test)
        {
            $result = $em->sProcessStringContainingExpressions($test,2,1);
            $prettyPrint = $em->GetLastPrettyPrintExpression();
            print "<tr><td>" . $prettyPrint . "</td>\n";
            print "<td>" . $result . "</td>\n";
            $varsUsed = $em->getAllVarsUsed();
            if (is_array($varsUsed) and count($varsUsed) > 0) {
                $varDesc = array();
                foreach ($varsUsed as $v) {
                    $varInfo = $em->GetVarInfo($v);
                    $varDesc[] = $v . '(' . $varInfo['jsName'] . ',' . $varInfo['readWrite'] . ',' . $varInfo['isOnCurrentPage'] . ')';
                }
                print '<td>' . implode(',<br/>', $varDesc) . "</td>\n";
            }
            else {
                print "<td>&nbsp;</td>\n";
            }
            print "</tr>\n";
        }
        print '</table>';
    }
}
?>
