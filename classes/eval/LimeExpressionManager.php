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

    public function setVariableAndTokenMappingsForExpressionManager($sid,$forceRefresh=false,$anonymized=false)
    {
        global $globalfieldmap, $clang, $surveyid;

        //checks to see if fieldmap has already been built for this page.
        if (isset($globalfieldmap[$surveyid]['expMgr_sgqaMap'][$clang->langcode])&& !$forceRefresh) {
            return false;   // means the mappings have already been set and don't need to be re-created
        }

        $fieldmap=createFieldMap($surveyid,$style='full',$forceRefresh);
        if (!isset($fieldmap)) {
            return false; // implies an error occurred
        }

        $sgqaMap = array();  // mapping of SGQA to Value
        $knownVars = array();   // mapping of VarName to Value
        $knownNamedConstants = array(); // mapping of read-only values to Value
        $debugLog = array();    // array of mappings among values to confirm their accuracy
        foreach($fieldmap as $fielddata)
        {
            $code = $fielddata['fieldname'];
            if (!preg_match('#^\d+X\d+X\d+#',$code))
            {
                continue;   // not an SGQA value
            }
               // TODO:  Which of these question types are read-write variables vs. named constants?
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
                    $varName = $fielddata['title'];
                    $jsVarName = 'java' . $code;
                    $question = $fielddata['question'];
                    break;
                case '1': //Array (Flexible Labels) dual scale
                    $varName = $fielddata['title'] . '.' . $fielddata['aid'] . '.' . $fielddata['scale_id'];
                    $jsVarName = 'java' . $code;
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
                    $jsVarName = 'java' . $code;
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion'];
                    break;
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case ';': //ARRAY (Multi Flexi) Text
                    $varName = $fielddata['title'] . '.' . $fielddata['aid'];
                    $jsVarName = 'java' . $code;
                    $question = $fielddata['question'] . ': ' . $fielddata['subquestion1'] . ': ' . $fielddata['subquestion2'];
                    break;
            }
            if (isset($_SESSION[$code]))
            {
                $codeValue = $_SESSION[$code];
                $displayValue= retrieve_Answer($code, $_SESSION['dateformats']['phpdate']);
                $knownVars[$varName] = array(
                    'codeValue'=>$codeValue,
                    'jsName'=>$jsVarName,
                    'displayValue'=>$displayValue,
                    'question'=>$question,
                    );
                $knownNamedConstants[$varName . '.shown'] = $displayValue;
                $knownNamedConstants[$varName . '.question']= $question;
                $sgqaMap['INSERTANS:' . $code] = $displayValue;
            }
            else
            {
                unset($codeValue);
                unset($displayValue);
                $sgqaMap['INSERTANS:' . $code] = ""; // so if know value has been set, replace with blank - is that the desired behavior?
            }
            $debugLog[] = array(
                'code' => $code,
                'type' => $fielddata['type'],
                'varname' => $varName,
                'jsName' => $jsVarName,
                'question' => $question,
                'codeValue' => isset($codeValue) ? $codeValue : '&nbsp;',
                'displayValue' => isset($displayValue) ? $displayValue : '&nbsp;',
                );

        }

        // Now set tokens
        $tokenMap = array();      // mapping of TOKENS to values
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
                $tokenMap[$key] = $val;
                $debugLog[] = array(
                    'code' => $key,
                    'type' => '&nbsp;',
                    'varname' => '&nbsp;',
                    'jsName' => '&nbsp;',
                    'question' => '&nbsp;',
                    'codeValue' => '&nbsp;',
                    'displayValue' => $val,
                );
            }
        }
        else
        {
            // Explicitly set all tokens to blank
            $tokenMap['TOKEN:FIRSTNAME'] = "";
            $tokenMap['TOKEN:LASTNAME'] = "";
            $tokenMap['TOKEN:EMAIL'] = "";
            $tokenMap['TOKEN:USESLEFT'] = "";
            for ($i=1;$i<=100;++$i) // TODO - is there a way to know  how many attributes are set?  Looks like max is 100
            {
                $tokenMap['TOKEN:ATTRIBUTE_' . $i] = "";
            }
        }

        $debugLog_html = "<table border='1'>";
        $debugLog_html .= "<tr><th>Code</th><th>Type</th><th>VarName</th><th>JSname</th><th>Question</th><th>CodeVal</th><th>DisplayVal</th></tr>";
        foreach ($debugLog as $t)
        {
            $debugLog_html .= "<tr><td>" . $t['code']
                . "</td><td>" . $t['type']
                . "</td><td>" . $t['varname']
                . "</td><td>" . $t['jsName']
                . "</td><td>" . $t['question']
                . "</td><td>" . $t['codeValue']
                . "</td><td>" . $t['displayValue']
                . "</td></tr>";
        }
        $debugLog_html .= "</table>";
        file_put_contents('/tmp/LimeExpressionManager-page.html',$debugLog_html);
        
        $globalfieldmap[$surveyid]['expMgr_varMap'][$clang->langcode] = $knownVars;
        $globalfieldmap[$surveyid]['expMgr_sgqaMap'][$clang->langcode] = $sgqaMap;
        $globalfieldmap[$surveyid]['expMgr_constants'][$clang->langcode] = $knownNamedConstants;
        $globalfieldmap[$surveyid]['expMgr_tokens'][$clang->langcode] = $tokenMap;

        return true;
    }

    /**
     * Translate all Expressions, Macros, registered variables, etc. in $string
     * @param <type> $string
     * @param boolean $forceRefresh - if true, reset $fieldMap and the derived arrays of registered variables and values
     * @return string - the original $string with all replacements done.
     */

    static function ProcessString($string,array $replacementFields=array(), $forceRefresh=false, $anonymized=false)
    {
        global $surveyid, $clang, $globalfieldmap;

        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;
        if (!is_null($surveyid) && $lem->setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh,$anonymized))
        {
            // means that some values changed, so need to update what was registered to ExpressionManager
            $em->RegisterVarnamesUsingReplace($globalfieldmap[$surveyid]['expMgr_varMap'][$clang->langcode]);
            $em->RegisterNamedConstantsUsingReplace($globalfieldmap[$surveyid]['expMgr_sgqaMap'][$clang->langcode]);
            $em->RegisterNamedConstantsUsingMerge($globalfieldmap[$surveyid]['expMgr_constants'][$clang->langcode]);
            $em->RegisterNamedConstantsUsingMerge($globalfieldmap[$surveyid]['expMgr_tokens'][$clang->langcode]);
        }
        if (isset($replacementFields) && is_array($replacementFields) && count($replacementFields) > 0)
        {
            $em->RegisterNamedConstantsUsingMerge($replacementFields);   // TODO - is it safe to just merge these in each time, or should a refresh be forced?
        }
        return $em->sProcessStringContainingExpressions(htmlspecialchars_decode($string));
    }


    /**
     * Unit test
     */
    static function UnitTestProcessStringContainingExpressions()
    {
        $vars = array(
'name' => array('codeValue'=>'Sergei', 'jsName'=>'java61764X1X1'),
'age' => array('codeValue'=>45, 'jsName'=>'java61764X1X2'),
'numKids' => array('codeValue'=>2, 'jsName'=>'java61764X1X3'),
'numPets' => array('codeValue'=>1, 'jsName'=>'java61764X1X4'),
        );
        $namedConstants = array(
            'INSERTANS:61764X1X1'   => 'Sergei',
            'INSERTANS:61764X1X2'   => 45,
            'INSERTANS:61764X1X3'   => 2,
            'INSERTANS:61764X1X4'   => 1,
            'TOKEN:ATTRIBUTE_1'     => 'worker',
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
        $em->RegisterNamedConstantsUsingMerge($namedConstants);

        print '<table border="1"><tr><th>Test</th><th>Result</th><th>VarNames Used</th><th>Javascript VarNames</th><th>Named Constants Used</th></tr>';
        foreach($alltests as $test)
        {
            print "<tr><td>" . $test . "</td>\n";
            print "<td>" . $em->sProcessStringContainingExpressions($test) . "</td>\n";
            $allVarsUsed = $em->getAllVarsUsed();
            if (is_array($allVarsUsed) and count($allVarsUsed) > 0) {
                print "<td>" . implode(', ', $allVarsUsed) . "</td>\n";
            }
            else {
                print "<td>&nbsp;</td>\n";
            }
            $allJsVarsUsed = $em->getAllJsVarsUsed();
            if (is_array($allJsVarsUsed) and count($allJsVarsUsed) > 0) {
                print "<td>" . implode(', ', $allJsVarsUsed) . "</td>\n";
            }
            else {
                print "<td>&nbsp;</td>\n";
            }            
            $allNamedConstantsUsed = $em->getAllNamedConstantsUsed();
            if (is_array($allNamedConstantsUsed) and count($allNamedConstantsUsed) > 0) {
                print "<td>" . implode(', ', $allNamedConstantsUsed) . "</td>\n";
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
