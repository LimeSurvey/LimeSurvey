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
    private $fieldmap;
    private $varMap;
    private $sgqaMap;
    private $tokenMap;

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

    public function setVariableAndTokenMappingsForExpressionManager($sid,$forceRefresh=false)
    {
        global $globalfieldmap, $clang;

        //checks to see if fieldmap has already been built for this page.
        if (isset($globalfieldmap[$sid]['full'][$clang->langcode])) {
            if (isset($this->fieldmap) && !$forceRefresh) {
                return false;   // means the mappings have already been set and don't need to be re-created
            }
        }

        $fieldmap=createFieldMap($sid,$style='full');
        $this->fieldmap = $fieldmap;
        if (!isset($fieldmap)) {
            return false;
        }

        $knownVars = array();   // mapping of VarName to Value
        $knownSGQAs = array();  // mapping of SGQA to Value
        foreach($fieldmap as $fielddata)
        {
            $code = $fielddata['fieldname'];
            if (!preg_match('#^\d+X\d+X\d+#',$code))
            {
                continue;
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
            if (isset($_SESSION[$code]))
            {
                $codeValue = $_SESSION[$code];
                $displayValue= retrieve_Answer($code, $_SESSION['dateformats']['phpdate']);
                $knownVars[$varName] = $codeValue;
                $knownVars[$varName . '.shown'] = $displayValue;
                $knownVars[$varName . '.question']= $question;
                $knownSGQAs['INSERTANS:' . $code] = $displayValue;
            }
        }
        $this->varMap = $knownVars;
        $this->sgqaMap = $knownSGQAs;

        // Now set tokens
        $tokens = array();      // mapping of TOKENS to values - how often does this need to be set?
        if (isset($_SESSION['token']) && $_SESSION['token'] != '')
        {
            //Gather survey data for tokenised surveys, for use in presenting questions
            $_SESSION['thistoken']=getTokenData($surveyid, $_SESSION['token']);
        }
        if (isset($_SESSION['thistoken']))
        {
            // TODO - need to explicitly set TOKEN:FIRSTNAME, and related to blank if not using tokens?
            foreach (array_keys($_SESSION['thistoken']) as $tokenkey)
            {
                $tokens["TOKEN:" . strtoupper($tokenkey)] = $_SESSION['thistoken'][$tokenkey];
            }
        }
        $this->tokenMap = $tokens;

        return true;
    }

    /**
     * Translate all Expressions, Macros, registered variables, etc. in $string
     * @param <type> $string
     * @param boolean $forceRefresh - if true, reset $fieldMap and the derived arrays of registered variables and values
     * @return string - the original $string with all replacements done.
     */

    static function ProcessString($string,array $replacementFields=array(), $forceRefresh=false)
    {
        global $surveyid;
        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;
        if ($lem->setVariableAndTokenMappingsForExpressionManager($surveyid,$forceRefresh))
        {
            // means that some values changed, so need to update what was registered to ExpressionManager
            $em->RegisterVarnamesUsingReplace($lem->varMap);
            $em->RegisterReservedWordsUsingReplace($lem->sgqaMap);
            $em->RegisterReservedWordsUsingMerge($lem->tokenMap);
        }
        if (isset($replacementFields) && is_array($replacementFields) && count($replacementFields) > 0)
        {
            $em->RegisterReservedWordsUsingMerge($replacementFields);   // TODO - is it safe to just merge these in each time, or should a refresh be forced?
        }
        return $em->sProcessStringContainingExpressions(htmlspecialchars_decode($string));
    }


    /**
     * Unit test
     */
    static function UnitTestProcessStringContainingExpressions()
    {
        $vars = array(
            'name'      => 'Sergei',
            'age'       => 45,
            'numKids'   => 2,
            'numPets'   => 1,
        );
        $reservedWords = array(
            'INSERTANS:61764X1X1'   => 'Peter',
            'INSERTANS:61764X1X2'   => 27,
            'INSERTANS:61764X1X3'   => 1,
            'INSERTANS:61764X1X4'   => 8
        );

        $tests = <<<EOD
{name}, you said that you are {age} years old, and that you have {numKids} {if((numKids==1),'child','children')} and {numPets} {if((numPets==1),'pet','pets')} running around the house. So, you have {numKids + numPets} wild {if((numKids + numPets ==1),'beast','beasts')} to chase around every day.
Since you have more {if((numKids > numPets),'children','pets')} than you do {if((numKids > numPets),'pets','children')}, do you feel that the {if((numKids > numPets),'pets','children')} are at a disadvantage?
{INSERTANS:61764X1X1}, you said that you are {INSERTANS:61764X1X2} years old, and that you have {INSERTANS:61764X1X3} {if((INSERTANS:61764X1X3==1),'child','children')} and {INSERTANS:61764X1X4} {if((INSERTANS:61764X1X4==1),'pet','pets')} running around the house.  So, you have {INSERTANS:61764X1X3 + INSERTANS:61764X1X4} wild {if((INSERTANS:61764X1X3 + INSERTANS:61764X1X4 ==1),'beast','beasts')} to chase around every day.
Since you have more {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'children','pets')} than you do {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'pets','children')}, do you feel that the {if((INSERTANS:61764X1X3 > INSERTANS:61764X1X4),'pets','children')} are at a disadvantage?
EOD;

        $lem = LimeExpressionManager::singleton();
        $em = $lem->em;

        $em->RegisterVarnamesUsingMerge($vars);
        $em->RegisterReservedWordsUsingMerge($reservedWords);

        print '<table border="1"><tr><th>Test</th><th>Result</th><th>VarsUsed</th><th>ReservedWordsUsed</th></tr>';
        foreach(explode("\n",$tests) as $test)
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
            $allReservedWordsUsed = $em->getAllReservedWordsUsed();
            if (is_array($allReservedWordsUsed) and count($allReservedWordsUsed) > 0) {
                print "<td>" . implode(', ', $allReservedWordsUsed) . "</td>\n";
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
