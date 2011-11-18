<?php
/**
 * Description of ExpressionManager
 * (1) Does safe evaluation of PHP expressions.  Only registered Functions, and known Variables are allowed.
 *   (a) Functions include any math, string processing, conditional, formatting, etc. functions
 *   (b) Variables are typically the question name (question.title) - they can be read/write
 * (2) This class can replace LimeSurvey's current process of resolving strings that contain LimeReplacementFields
 *   (a) String is split by expressions (by curly braces, but safely supporting strings and escaped curly braces)
 *   (b) Expressions (things surrounded by curly braces) are evaluated - thereby doing LimeReplacementField substitution and/or more complex calculations
 *   (c) Non-expressions are left intact
 *   (d) The array of stringParts are re-joined to create the desired final string.
 *
 * @author Thomas M. White (TMSWhite)
 */

class ExpressionManager {
    // These are the allowable suffixes for variables - each represents an attribute of a variable.
    private static $regex_var_attr = 'codeValue|code|displayValue|groupSeq|jsName|mandatory|NAOK|qid|questionSeq|question|readWrite|relevanceNum|relevanceStatus|relevance|sgqa|shown|type';

    // These three variables are effectively static once constructed
    private $sExpressionRegex;
    private $asTokenType;
    private $sTokenizerRegex;
    private $asCategorizeTokensRegex;
    private $amValidFunctions; // names and # params of valid functions
    private $amVars;    // names and values of valid variables

    // Thes variables are used while  processing the equation
    private $expr;  // the source expression
    private $tokens;    // the list of generated tokens
    private $count; // total number of $tokens
    private $pos;   // position within the $token array while processing equation
    private $errs;    // array of syntax errors
    private $onlyparse;
    private $stack; // stack of intermediate results
    private $result;    // final result of evaluating the expression;
    private $evalStatus;    // true if $result is a valid result, and  there are no serious errors
    private $varsUsed;  // list of variables referenced in the equation

    // These  variables are only used by sProcessStringContainingExpressions
    private $allVarsUsed;   // full list of variables used within the string, even if contains multiple expressions
    private $prettyPrintSource; // HTML formatted output of running sProcessStringContainingExpressions
    private $substitutionNum; // Keeps track of number of substitions performed XXX
    private $substitutionInfo; // array of JavaScripts to managing dynamic substitution
    private $jsExpression;  // caches computation of JavaScript equivalent for an Expression

    private $questionSeq;   // sequence order of question - so can detect if try to use variable before it is set
    private $groupSeq;  // sequence order of groups - so can detect if try to use variable before it is set
    private $allOnOnePage=false;

    function __construct()
    {
        // List of token-matching regular expressions
        $regex_dq_string = '(?<!\\\\)".*?(?<!\\\\)"';
        $regex_sq_string = '(?<!\\\\)\'.*?(?<!\\\\)\'';
        $regex_whitespace = '\s+';
        $regex_lparen = '\(';
        $regex_rparen = '\)';
        $regex_comma = ',';
        $regex_not = '!';
        $regex_inc_dec = '\+\+|--';
        $regex_binary = '[+*/-]';
        $regex_compare = '<=|<|>=|>|==|!=|\ble\b|\blt\b|\bge\b|\bgt\b|\beq\b|\bne\b';
        $regex_assign = '=|\+=|-=|\*=|/=';
        $regex_sgqa = '(?:INSERTANS:)?[0-9]+X[0-9]+X[0-9]+[A-Z0-9_]*\#?[01]?';
        $regex_word = '(?:TOKEN:)?(?:[A-Z][A-Z0-9_]*)?(?:\.(?:' . ExpressionManager::$regex_var_attr . '))?';
        $regex_number = '[0-9]+\.?[0-9]*|\.[0-9]+';
        $regex_andor = '\band\b|\bor\b|&&|\|\|';

        $this->sExpressionRegex = '#((?<!\\\\)' . '{' . '(?!\s*\n\|\s*\r\|\s*\r\n|\s+)' .
//                '(' . $regex_dq_string . '|' . $regex_sq_string . '|.*?)*' .    // This line lets you have braces embedded in strings - like RegExp - but it crashes the compiler when there are many tokens
                '.*?' .
                '(?<!\\\\)(?<!\n|\r|\r\n|\s)' . '}' . ')#';


        // asTokenRegex and asTokenType must be kept in sync  (same number and order)
        $asTokenRegex = array(
            $regex_dq_string,
            $regex_sq_string,
            $regex_whitespace,
            $regex_lparen,
            $regex_rparen,
            $regex_comma,
            $regex_andor,
            $regex_compare,
            $regex_sgqa,
            $regex_word,
            $regex_number,
            $regex_not,
            $regex_inc_dec,
            $regex_assign,
            $regex_binary,
            );

        $this->asTokenType = array(
            'DQ_STRING',
            'SQ_STRING',
            'SPACE',
            'LP',
            'RP',
            'COMMA',
            'AND_OR',
            'COMPARE',
            'SGQA',
            'WORD',
            'NUMBER',
            'NOT',
            'OTHER',
            'ASSIGN',
            'BINARYOP',
           );

        // $sTokenizerRegex - a single regex used to split and equation into tokens
        $this->sTokenizerRegex = '#(' . implode('|',$asTokenRegex) . ')#i';

        // $asCategorizeTokensRegex - an array of patterns so can categorize the type of token found - would be nice if could get this from preg_split
        // Adding ability to capture 'OTHER' type, which indicates an error - unsupported syntax element
        $this->asCategorizeTokensRegex = preg_replace("#^(.*)$#","#^$1$#i",$asTokenRegex);
        $this->asCategorizeTokensRegex[] = '/.+/';
        $this->asTokenType[] = 'OTHER';

        // Each allowed function is a mapping from local name to external name + number of arguments
        // Functions can have a list of serveral allowable #s of arguments.
        // If the value is -1, the function must have a least one argument but can have an unlimited number of them
        // -2 means that at least one argument is required.  -3 means at least two arguments are required, etc.
        $this->amValidFunctions = array(
'abs' => array('abs', 'Math.abs', $this->gT('Absolute value'), 'number abs(number)', 'http://www.php.net/manual/en/function.checkdate.php', 1),
'acos' => array('acos', 'Math.acos', $this->gT('Arc cosine'), 'number acos(number)', 'http://www.php.net/manual/en/function.acos.php', 1),
'addslashes' => array('addslashes', $this->gT('addslashes'), 'Quote string with slashes', 'string addslashes(string)', 'http://www.php.net/manual/en/function.addslashes.php', 1),
'asin' => array('asin', 'Math.asin', $this->gT('Arc sine'), 'number asin(number)', 'http://www.php.net/manual/en/function.asin.php', 1),
'atan' => array('atan', 'Math.atan', $this->gT('Arc tangent'), 'number atan(number)', 'http://www.php.net/manual/en/function.atan.php', 1),
'atan2' => array('atan2', 'Math.atan2', $this->gT('Arc tangent of two variables'), 'number atan2(number, number)', 'http://www.php.net/manual/en/function.atan2.php', 2),
'ceil' => array('ceil', 'Math.ceil', $this->gT('Round fractions up'), 'number ceil(number)', 'http://www.php.net/manual/en/function.ceil.php', 1),
'checkdate' => array('checkdate', 'checkdate', $this->gT('Returns true(1) if it is a valid date in gregorian calendar'), 'bool checkdate(month,day,year)', 'http://www.php.net/manual/en/function.checkdate.php', 3),
'cos' => array('cos', 'Math.cos', $this->gT('Cosine'), 'number cos(number)', 'http://www.php.net/manual/en/function.cos.php', 1),
'count' => array('exprmgr_count', 'LEMcount', $this->gT('Count the number of answered questions in the list'), 'number count(arg1, arg2, ... argN)', '', -1),
'date' => array('date', 'date', $this->gT('Format a local date/time'), 'string date(format [, timestamp=time()])', 'http://www.php.net/manual/en/function.date.php', 1,2),
'exp' => array('exp', 'Math.exp', $this->gT('Calculates the exponent of e'), 'number exp(number)', 'http://www.php.net/manual/en/function.exp.php', 1),
'floor' => array('floor', 'Math.floor', $this->gT('Round fractions down'), 'number floor(number)', 'http://www.php.net/manual/en/function.floor.php', 1),
'gmdate' => array('gmdate', 'gmdate', $this->gT('Format a GMT date/time'), 'string gmdate(format [, timestamp=time()])', 'http://www.php.net/manual/en/function.gmdate.php', 1,2),
'html_entity_decode' => array('html_entity_decode', 'html_entity_decode', $this->gT('Convert all HTML entities to their applicable characters (always uses ENT_QUOTES and UTF-8)'), 'string html_entity_decode(string)', 'http://www.php.net/manual/en/function.html-entity-decode.php', 1),
'htmlentities' => array('htmlentities', 'htmlentities', $this->gT('Convert all applicable characters to HTML entities (always uses ENT_QUOTES and UTF-8)'), 'string htmlentities(string)', 'http://www.php.net/manual/en/function.htmlentities.php', 1),
'htmlspecialchars' => array('expr_mgr_htmlspecialchars', 'htmlspecialchars', $this->gT('Convert special characters to HTML entities (always uses ENT_QUOTES and UTF-8)'), 'string htmlspecialchars(string)', 'http://www.php.net/manual/en/function.htmlspecialchars.php', 1),
'htmlspecialchars_decode' => array('expr_mgr_htmlspecialchars_decode', 'htmlspecialchars_decode', $this->gT('Convert special HTML entities back to characters (always uses ENT_QUOTES and UTF-8)'), 'string htmlspecialchars_decode(string)', 'http://www.php.net/manual/en/function.htmlspecialchars-decode.php', 1),
'idate' => array('idate', 'idate', $this->gT('Format a local time/date as integer'), 'string idate(string [, timestamp=time()])', 'http://www.php.net/manual/en/function.idate.php', 1,2),
'if' => array('exprmgr_if', 'LEMif', $this->gT('Conditional processing'), 'if(test,result_if_true,result_if_false)', '', 3),
'implode' => array('exprmgr_implode', 'LEMimplode', $this->gT('Join array elements with a string'), 'string implode(glue,arg1,arg2,...,argN)', 'http://www.php.net/manual/en/function.implode.php', -2),
'intval' => array('intval', 'LEMintval', $this->gT('Get the integer value of a variable'), 'int intval(number [, base=10])', 'http://www.php.net/manual/en/function.intval.php', 1,2),
'is_bool' => array('is_bool', 'is_bool', $this->gT('Finds out whether a variable is a boolean'), 'bool is_bool(var)', 'http://www.php.net/manual/en/function.is-bool.php', 1),
'is_empty' => array('exprmgr_empty', 'LEMempty', $this->gT('Determine whether a variable is considered to be empty'), 'bool is_empty(var)', 'http://www.php.net/manual/en/function.empty.php', 1),
'is_float' => array('is_float', 'LEMis_float', $this->gT('Finds whether the type of a variable is float'), 'bool is_float(var)', 'http://www.php.net/manual/en/function.is-float.php', 1),
'is_int' => array('is_int', 'LEMis_int', $this->gT('Find whether the type of a variable is integer'), 'bool is_int(var)', 'http://www.php.net/manual/en/function.is-int.php', 1),
'is_nan' => array('is_nan', 'isNaN', $this->gT('Finds whether a value is not a number'), 'bool is_nan(var)', 'http://www.php.net/manual/en/function.is-nan.php', 1),
'is_null' => array('is_null', 'LEMis_null', $this->gT('Finds whether a variable is NULL'), 'bool is_null(var)', 'http://www.php.net/manual/en/function.is-null.php', 1),
'is_numeric' => array('is_numeric', 'LEMis_numeric', $this->gT('Finds whether a variable is a number or a numeric string'), 'bool is_numeric(var)', 'http://www.php.net/manual/en/function.is-numeric.php', 1),
'is_string' => array('is_string', 'LEMis_string', $this->gT('Find whether the type of a variable is string'), 'bool is_string(var)', 'http://www.php.net/manual/en/function.is-string.php', 1),
'list' => array('exprmgr_list', 'LEMlist', $this->gT('Return comma-separated list of values'), 'string list(arg1, arg2, ... argN)', '', -2),
'log' => array('log', 'Math.log', $this->gT('Natural logarithm'), 'number log(number)', 'http://www.php.net/manual/en/function.log.php', 1),
'ltrim' => array('ltrim', 'ltrim', $this->gT('Strip whitespace (or other characters) from the beginning of a string'), 'string ltrim(string [, charlist])', 'http://www.php.net/manual/en/function.ltrim.php', 1,2),
'max' => array('max', 'Math.max', $this->gT('Find highest value'), 'number max(arg1, arg2, ... argN)', 'http://www.php.net/manual/en/function.max.php', -2),
'min' => array('min', 'Math.min', $this->gT('Find lowest value'), 'number min(arg1, arg2, ... argN)', 'http://www.php.net/manual/en/function.min.php', -2),
'mktime' => array('mktime', 'mktime', $this->gT('Get UNIX timestamp for a date (each of the 6 arguments are optional)'), 'number mktime([hour [, minute [, second [, month [, day [, year ]]]]]])', 'http://www.php.net/manual/en/function.mktime.php', 0,1,2,3,4,5,6),
'nl2br' => array('nl2br', 'nl2br', $this->gT('Inserts HTML line breaks before all newlines in a string'), 'string nl2br(string)', 'http://www.php.net/manual/en/function.nl2br.php', 1,1),
'number_format' => array('number_format', 'number_format', $this->gT('Format a number with grouped thousands'), 'string number_format(number)', 'http://www.php.net/manual/en/function.number-format.php', 1),
'pi' => array('pi', 'LEMpi', $this->gT('Get value of pi'), 'number pi()', '', 0),
'pow' => array('pow', 'Math.pow', $this->gT('Exponential expression'), 'number pow(base, exp)', 'http://www.php.net/manual/en/function.pow.php', 2),
'quoted_printable_decode' => array('quoted_printable_decode', 'quoted_printable_decode', $this->gT('Convert a quoted-printable string to an 8 bit string'), 'string quoted_printable_decode(string)', 'http://www.php.net/manual/en/function.quoted-printable-decode.php', 1),
'quoted_printable_encode' => array('quoted_printable_encode', 'quoted_printable_encode', $this->gT('Convert a 8 bit string to a quoted-printable string'), 'string quoted_printable_encode(string)', 'http://www.php.net/manual/en/function.quoted-printable-encode.php', 1),
'quotemeta' => array('quotemeta', 'quotemeta', $this->gT('Quote meta characters'), 'string quotemeta(string)', 'http://www.php.net/manual/en/function.quotemeta.php', 1),
'rand' => array('rand', 'Math.random', $this->gT('Generate a random integer'), 'int rand() OR int rand(min, max)', 'http://www.php.net/manual/en/function.rand.php', 0,2),
'regexMatch' => array('exprmgr_regexMatch', 'LEMregexMatch', $this->gT('Compare a string to a regular expression pattern'), 'bool regexMatch(pattern,input)', '', 2),
'round' => array('round', 'LEMround', $this->gT('Rounds a number to an optional precision'), 'number round(val [, precision])', 'http://www.php.net/manual/en/function.round.php', 1,2),
'rtrim' => array('rtrim', 'rtrim', $this->gT('Strip whitespace (or other characters) from the end of a string'), 'string rtrim(string [, charlist])', 'http://www.php.net/manual/en/function.rtrim.php', 1,2),
'sin' => array('sin', 'Math.sin', $this->gT('Sine'), 'number sin(arg)', 'http://www.php.net/manual/en/function.sin.php', 1),
'sprintf' => array('sprintf', 'sprintf', $this->gT('Return a formatted string'), 'string sprintf(format, arg1, arg2, ... argN)', 'http://www.php.net/manual/en/function.sprintf.php', -2),
'sqrt' => array('sqrt', 'Math.sqrt', $this->gT('Square root'), 'number sqrt(arg)', 'http://www.php.net/manual/en/function.sqrt.php', 1),
'stddev' => array('exprmgr_stddev', 'LEMstddev', $this->gT('Calculate the Sample Standard Deviation for the list of numbers'), 'number stddev(arg1, arg2, ... argN)', '', -2),
'str_pad' => array('str_pad', 'str_pad', $this->gT('Pad a string to a certain length with another string'), 'string str_pad(input, pad_length [, pad_string])', 'http://www.php.net/manual/en/function.str-pad.php', 2,3),
'str_repeat' => array('str_repeat', 'str_repeat', $this->gT('Repeat a string'), 'string str_repeat(input, multiplier)', 'http://www.php.net/manual/en/function.str-repeat.php', 2),
'str_replace' => array('str_replace', 'LEMstr_replace', $this->gT('Replace all occurrences of the search string with the replacement string'), 'string str_replace(search,  replace, subject)', 'http://www.php.net/manual/en/function.str-replace.php', 3),
'strcasecmp' => array('strcasecmp', 'strcasecmp', $this->gT('Binary safe case-insensitive string comparison'), 'int strcasecmp(str1, str2)', 'http://www.php.net/manual/en/function.strcasecmp.php', 2),
'strcmp' => array('strcmp', 'strcmp', $this->gT('Binary safe string comparison'), 'int strcmp(str1, str2)', 'http://www.php.net/manual/en/function.strcmp.php', 2),
'strip_tags' => array('strip_tags', 'strip_tags', $this->gT('Strip HTML and PHP tags from a string'), 'string strip_tags(str, allowable_tags)', 'http://www.php.net/manual/en/function.strip-tags.php', 1,2),
'stripos' => array('stripos', 'stripos', $this->gT('Find position of first occurrence of a case-insensitive string'), 'int stripos(haystack, needle [, offset=0])', 'http://www.php.net/manual/en/function.stripos.php', 2,3),
'stripslashes' => array('stripslashes', 'stripslashes', $this->gT('Un-quotes a quoted string'), 'string stripslashes(string)', 'http://www.php.net/manual/en/function.stripslashes.php', 1),
'stristr' => array('stristr', 'stristr', $this->gT('Case-insensitive strstr'), 'string stristr(haystack, needle [, before_needle=false])', 'http://www.php.net/manual/en/function.stristr.php', 2,3),
'strlen' => array('strlen', 'LEMstrlen', $this->gT('Get string length'), 'int strlen(string)', 'http://www.php.net/manual/en/function.strlen.php', 1),
'strpos' => array('strpos', 'LEMstrpos', $this->gT('Find position of first occurrence of a string'), 'int strpos(haystack, needle [ offset=0])', 'http://www.php.net/manual/en/function.strpos.php', 2,3),
'strrev' => array('strrev', 'strrev', $this->gT('Reverse a string'), 'string strrev(string)', 'http://www.php.net/manual/en/function.strrev.php', 1),
'strstr' => array('strstr', 'strstr', $this->gT('Find first occurrence of a string'), 'string strstr(haystack, needle)', 'http://www.php.net/manual/en/function.strstr.php', 2),
'strtolower' => array('strtolower', 'LEMstrtolower', $this->gT('Make a string lowercase'), 'string strtolower(string)', 'http://www.php.net/manual/en/function.strtolower.php', 1),
'strtoupper' => array('strtoupper', 'LEMstrtoupper', $this->gT('Make a string uppercase'), 'string strtoupper(string)', 'http://www.php.net/manual/en/function.strtoupper.php', 1),
'substr' => array('substr', 'substr', $this->gT('Return part of a string'), 'string substr(string, start [, length])', 'http://www.php.net/manual/en/function.substr.php', 2,3),
'sum' => array('array_sum', 'LEMsum', $this->gT('Calculate the sum of values in an array'), 'number sum(arg1, arg2, ... argN)', '', -2),
'tan' => array('tan', 'Math.tan', $this->gT('Tangent'), 'number tan(arg)', 'http://www.php.net/manual/en/function.tan.php', 1),
'time' => array('time', 'time', $this->gT('Return current UNIX timestamp'), 'number time()', 'http://www.php.net/manual/en/function.time.php', 0),
'trim' => array('trim', 'trim', $this->gT('Strip whitespace (or other characters) from the beginning and end of a string'), 'string trim(string [, charlist])', 'http://www.php.net/manual/en/function.trim.php', 1,2),
'ucwords' => array('ucwords', 'ucwords', $this->gT('Uppercase the first character of each word in a string'), 'string ucwords(string)', 'http://www.php.net/manual/en/function.ucwords.php', 1),
        );

        $this->amVars = array();
    }

    /**
     * Add an error to the error log
     *
     * @param <type> $errMsg
     * @param <type> $token
     */
    private function AddError($errMsg, $token)
    {
        $this->errs[] = array($this->gT($errMsg), $token);
    }

    /**
     * EvalBinary() computes binary expressions, such as (a or b), (c * d), popping  the top two entries off the
     * stack and pushing the result back onto the stack.
     *
     * @param array $token
     * @return boolean - false if there is any error, else true
     */

    private function EvalBinary(array $token)
    {
        if (count($this->stack) < 2)
        {
            $this->AddError("Unable to evaluate binary operator - fewer than 2 entries on stack", $token);
            return false;
        }
        $arg2 = $this->StackPop();
        $arg1 = $this->StackPop();
        if (is_null($arg1) or is_null($arg2))
        {
            $this->AddError("Invalid value(s) on the stack", $token);
            return false;
        }
        // TODO:  try to determine datatype?
        switch(strtolower($token[0]))
        {
            case 'or':
            case '||':
                $result = array(($arg1[0] or $arg2[0]),$token[1],'NUMBER');
                break;
            case 'and':
            case '&&':
                $result = array(($arg1[0] and $arg2[0]),$token[1],'NUMBER');
                break;
            case '==':
            case 'eq':
                $result = array(($arg1[0] == $arg2[0]),$token[1],'NUMBER');
                break;
            case '!=':
            case 'ne':
                $result = array(($arg1[0] != $arg2[0]),$token[1],'NUMBER');
                break;
            case '<':
            case 'lt':
                $result = array(($arg1[0] < $arg2[0]),$token[1],'NUMBER');
                break;
            case '<=';
            case 'le':
                $result = array(($arg1[0] <= $arg2[0]),$token[1],'NUMBER');
                break;
            case '>':
            case 'gt':
                $result = array(($arg1[0] > $arg2[0]),$token[1],'NUMBER');
                break;
            case '>=';
            case 'ge':
                $result = array(($arg1[0] >= $arg2[0]),$token[1],'NUMBER');
                break;
            case '+':
                $result = array(($arg1[0] + $arg2[0]),$token[1],'NUMBER');
                break;
            case '-':
                $result = array(($arg1[0] - $arg2[0]),$token[1],'NUMBER');
                break;
            case '*':
                $result = array(($arg1[0] * $arg2[0]),$token[1],'NUMBER');
                break;
            case '/';
                $result = array(($arg1[0] / $arg2[0]),$token[1],'NUMBER');
                break;
        }
        $this->StackPush($result);
        return true;
    }

    /**
     * Processes operations like +a, -b, !c
     * @param array $token
     * @return boolean - true if success, false if any error occurred
     */

    private function EvalUnary(array $token)
    {
        if (count($this->stack) < 1)
        {
            $this->AddError("Unable to evaluate unary operator - no entries on stack", $token);
            return false;
        }
        $arg1 = $this->StackPop();
        if (is_null($arg1))
        {
            $this->AddError("Invalid value(s) on the stack", $token);
            return false;
        }
        // TODO:  try to determine datatype?
        switch($token[0])
        {
            case '+':
                $result = array((+$arg1[0]),$token[1],'NUMBER');
                break;
            case '-':
                $result = array((-$arg1[0]),$token[1],'NUMBER');
                break;
            case '!';
                $result = array((!$arg1[0]),$token[1],'NUMBER');
                break;
        }
        $this->StackPush($result);
        return true;
    }


    /**
     * Main entry function
     * @param <type> $expr
     * @param <type> $onlyparse - if true, then validate the syntax without computing an answer
     * @return boolean - true if success, false if any error occurred
     */

    public function Evaluate($expr, $onlyparse=false)
    {
        $this->expr = $expr;
        $this->tokens = $this->amTokenize($expr);
        $this->count = count($this->tokens);
        $this->pos = -1; // starting position within array (first act will be to increment it)
        $this->errs = array();
        $this->onlyparse = $onlyparse;
        $this->stack = array();
        $this->evalStatus = false;
        $this->result = NULL;
        $this->varsUsed = array();
        $this->jsExpression = NULL;

        if ($this->HasSyntaxErrors()) {
            return false;
        }
        elseif ($this->EvaluateExpressions())
        {
            if ($this->pos < $this->count)
            {
                $this->AddError("Extra tokens found", $this->tokens[$this->pos]);
                return false;
            }
            $this->result = $this->StackPop();
            if (is_null($this->result))
            {
                return false;
            }
            if (count($this->stack) == 0)
            {
                $this->evalStatus = true;
                return true;
            }
            else
            {
                $this-AddError("Unbalanced equation - values left on stack",NULL);
                return false;
            }
        }
        else
        {
            $this->AddError("Not a valid expression",NULL);
            return false;
        }
    }


    /**
     * Process "a op b" where op in (+,-,concatenate)
     * @return boolean - true if success, false if any error occurred
     */
    private function EvaluateAdditiveExpression()
    {
        if (!$this->EvaluateMultiplicativeExpression())
        {
            return false;
        }
        while (($this->pos + 1) < $this->count)
        {
            $token = $this->tokens[++$this->pos];
            if ($token[2] == 'BINARYOP')
            {
                switch ($token[0])
                {
                    case '+':
                    case '-';
                        if ($this->EvaluateMultiplicativeExpression())
                        {
                            if (!$this->EvalBinary($token))
                            {
                                return false;
                            }
                            // else continue;
                        }
                        else
                        {
                            return false;
                        }
                        break;
                    default:
                        --$this->pos;
                        return true;
                }
            }
            else
            {
                --$this->pos;
                return true;
            }
        }
        return true;
    }

    /**
     * Process a Constant (number of string), retrieve the value of a known variable, or process a function, returning result on the stack.
     * @return boolean - true if success, false if any error occurred
     */

    private function EvaluateConstantVarOrFunction()
    {
        if ($this->pos + 1 >= $this->count)
        {
             $this->AddError("Poorly terminated expression - expected a constant or variable", NULL);
             return false;
        }
        $token = $this->tokens[++$this->pos];
        switch ($token[2])
        {
            case 'NUMBER':
            case 'DQ_STRING':
            case 'SQ_STRING':
                $this->StackPush($token);
                return true;
                break;
            case 'WORD':
            case 'SGQA':
                if (($this->pos + 1) < $this->count and $this->tokens[($this->pos + 1)][2] == 'LP')
                {
                    return $this->EvaluateFunction();
                }
                else
                {
                    if ($this->isValidVariable($token[0]))
                    {
                        $this->varsUsed[] = $token[0];  // add this variable to list of those used in this equation
                        $relStatus = $this->GetVarAttribute($token[0],'relevanceStatus',1);
                        if ($relStatus==1)
                        {
                            $result = array($this->GetVarAttribute($token[0],NULL,''),$token[1],'NUMBER');
                        }
                        else
                        {
                            $result = array(NULL,$token[1],'NUMBER');   // was 0 instead of NULL
                        }
                        $this->StackPush($result);

                        // TODO - currently, will try to process value anyway, but want to show a potential error.  Should it be a definitive error (e.g. prevent this behavior)?
                        $groupSeq = $this->GetVarAttribute($token[0],'groupSeq',-1);
                        if (($groupSeq != -1) && ($groupSeq > $this->groupSeq))
                        {
                            $this->AddError("This variable is not declared until a later page",$token);
                            return false;
                        }
                        return true;
                    }
                    else
                    {
                        $this->AddError("Undefined variable", $token);
                        return false;
                    }
                }
                break;
            case 'COMMA':
                --$this->pos;
                $this->AddError("Should never  get to this line?",$token);
                return false;
            default:
                return false;
                break;
        }
    }

    /**
     * Process "a == b", "a eq b", "a != b", "a ne b"
     * @return boolean - true if success, false if any error occurred
     */
    private function EvaluateEqualityExpression()
    {
        if (!$this->EvaluateRelationExpression())
        {
            return false;
        }
        while (($this->pos + 1) < $this->count)
        {
            $token = $this->tokens[++$this->pos];
            switch (strtolower($token[0]))
            {
                case '==':
                case 'eq':
                case '!=':
                case 'ne':
                    if ($this->EvaluateRelationExpression())
                    {
                        if (!$this->EvalBinary($token))
                        {
                            return false;
                        }
                        // else continue;
                    }
                    else
                    {
                        return false;
                    }
                    break;
                default:
                    --$this->pos;
                    return true;
            }
        }
        return true;
    }

    /**
     * Process a single expression (e.g. without commas)
     * @return boolean - true if success, false if any error occurred
     */

    private function EvaluateExpression()
    {
        if ($this->pos + 2 < $this->count)
        {
            $token1 = $this->tokens[++$this->pos];
            $token2 = $this->tokens[++$this->pos];
            if ($token2[2] == 'ASSIGN')
            {
                if ($this->isValidVariable($token1[0]))
                {
                    $this->varsUsed[] = $token1[0];  // add this variable to list of those used in this equation
                    if ($this->isWritableVariable($token1[0]))
                    {
                        $evalStatus = $this->EvaluateLogicalOrExpression();
                        if ($evalStatus)
                        {
                            $result = $this->StackPop();
                            if (!is_null($result))
                            {
                                $newResult = $token2;
                                $newResult[2] = 'NUMBER';
                                $newResult[0] = $this->setVariableValue($token2[0], $token1[0], $result[0]);
                                $this->StackPush($newResult);
                            }
                            else
                            {
                                $evalStatus = false;
                            }
                        }
                        return $evalStatus;
                    }
                    else
                    {
                        $this->AddError('The value of this variable can not be changed', $token1);
                        return false;
                    }
                }
                else
                {
                    $this->AddError('Only variables can be assigned values', $token1);
                    return false;
                }
            }
            else
            {
                // not an assignment expression, so try something else
                $this->pos -= 2;
                return $this->EvaluateLogicalOrExpression();
            }
        }
        else
        {
            return $this->EvaluateLogicalOrExpression();
        }
    }

    /**
     * Process "expression [, expression]*
     * @return boolean - true if success, false if any error occurred
     */

    private function EvaluateExpressions()
    {
        $evalStatus = $this->EvaluateExpression();
        if (!$evalStatus)
        {
            return false;
        }

        while (++$this->pos < $this->count) {
            $token = $this->tokens[$this->pos];
            if ($token[2] == 'RP')
            {
                return true;    // presumbably the end of an expression
            }
            elseif ($token[2] == 'COMMA')
            {
                if ($this->EvaluateExpression())
                {
                    $secondResult = $this->StackPop();
                    $firstResult = $this->StackPop();
                    if (is_null($firstResult))
                    {
                        return false;
                    }
                    $this->StackPush($secondResult);
                    $evalStatus = true;
                }
                else
                {
                    return false;   // an error must have occurred
                }
            }
            else
            {
                $this->AddError("Expected expressions separated by commas",$token);
                $evalStatus = false;
                break;
            }
        }
        while (++$this->pos < $this->count)
        {
            $token = $this->tokens[$this->pos];
            $this->AddError("Extra token found after Expressions",$token);
            $evalStatus = false;
        }
        return $evalStatus;
    }

    /**
     * Process a function call
     * @return boolean - true if success, false if any error occurred
     */
    private function EvaluateFunction()
    {
        $funcNameToken = $this->tokens[$this->pos]; // note that don't need to increment position for functions
        $funcName = $funcNameToken[0];
        if (!$this->isValidFunction($funcName))
        {
            $this->AddError("Undefined Function", $funcNameToken);
            return false;
        }
        $token2 = $this->tokens[++$this->pos];
        if ($token2[2] != 'LP')
        {
            $this->AddError("Expected left parentheses after function name", $token);
        }
        $params = array();  // will just store array of values, not tokens
        while ($this->pos + 1 < $this->count)
        {
            $token3 = $this->tokens[$this->pos + 1];
            if (count($params) > 0)
            {
                // should have COMMA or RP
                if ($token3[2] == 'COMMA')
                {
                    ++$this->pos;   // consume the token so can process next clause
                    if ($this->EvaluateExpression())
                    {
                        $value = $this->StackPop();
                        if (is_null($value))
                        {
                            return false;
                        }
                        $params[] = $value[0];
                        continue;
                    }
                    else
                    {
                        $this->AddError("Extra comma found in function", $token3);
                        return false;
                    }
                }
            }
            if ($token3[2] == 'RP')
            {
                ++$this->pos;   // consume the token so can process next clause
                return $this->RunFunction($funcNameToken,$params);
            }
            else
            {
                if ($this->EvaluateExpression())
                {
                    $value = $this->StackPop();
                    if (is_null($value))
                    {
                        return false;
                    }
                    $params[] = $value[0];
                    continue;
                }
                else
                {
                    return false;
                }
            }
        }
    }

    /**
     * Process "a && b" or "a and b"
     * @return boolean - true if success, false if any error occurred
     */

    private function EvaluateLogicalAndExpression()
    {
        if (!$this->EvaluateEqualityExpression())
        {
            return false;
        }
        while (($this->pos + 1) < $this->count)
        {
            $token = $this->tokens[++$this->pos];
            switch (strtolower($token[0]))
            {
                case '&&':
                case 'and':
                    if ($this->EvaluateEqualityExpression())
                    {
                        if (!$this->EvalBinary($token))
                        {
                            return false;
                        }
                        // else continue
                    }
                    else
                    {
                        return false;   // an error must have occurred
                    }
                    break;
                default:
                    --$this->pos;
                    return true;
            }
        }
        return true;
    }

    /**
     * Process "a || b" or "a or b"
     * @return boolean - true if success, false if any error occurred
     */
    private function EvaluateLogicalOrExpression()
    {
        if (!$this->EvaluateLogicalAndExpression())
        {
            return false;
        }
        while (($this->pos + 1) < $this->count)
        {
            $token = $this->tokens[++$this->pos];
            switch (strtolower($token[0]))
            {
                case '||':
                case 'or':
                    if ($this->EvaluateLogicalAndExpression())
                    {
                        if (!$this->EvalBinary($token))
                        {
                            return false;
                        }
                        // else  continue
                    }
                    else
                    {
                        // an error must have occurred
                        return false;
                    }
                    break;
                default:
                    // no more expressions being  ORed together, so continue parsing
                    --$this->pos;
                    return true;
            }
        }
        // no more tokens to parse
        return true;
    }

    /**
     * Process "a op b" where op in (*,/)
     * @return boolean - true if success, false if any error occurred
     */

    private function EvaluateMultiplicativeExpression()
    {
        if (!$this->EvaluateUnaryExpression())
        {
            return  false;
        }
        while (($this->pos + 1) < $this->count)
        {
            $token = $this->tokens[++$this->pos];
            if ($token[2] == 'BINARYOP')
            {
                switch ($token[0])
                {
                    case '*':
                    case '/';
                        if ($this->EvaluateUnaryExpression())
                        {
                            if (!$this->EvalBinary($token))
                            {
                                return false;
                            }
                            // else  continue
                        }
                        else
                        {
                            // an error must have occurred
                            return false;
                        }
                        break;
                        break;
                    default:
                        --$this->pos;
                        return true;
                }
            }
            else
            {
                --$this->pos;
                return true;
            }
        }
        return true;
    }

    /**
     * Process expressions including functions and parenthesized blocks
     * @return boolean - true if success, false if any error occurred
     */

    private function EvaluatePrimaryExpression()
    {
        if (($this->pos + 1) >= $this->count) {
            $this->AddError("Poorly terminated expression - expected a constant or variable", NULL);
            return false;
        }
        $token = $this->tokens[++$this->pos];
        if ($token[2] == 'LP')
        {
            if (!$this->EvaluateExpressions())
            {
                return false;
            }
            $token = $this->tokens[$this->pos];
            if ($token[2] == 'RP')
            {
                return true;
            }
            else
            {
                $this->AddError("Expected right parentheses", $token);
                return false;
            }
        }
        else
        {
            --$this->pos;
            return $this->EvaluateConstantVarOrFunction();
        }
    }

    /**
     * Process "a op b" where op in (lt, gt, le, ge, <, >, <=, >=)
     * @return boolean - true if success, false if any error occurred
     */
    private function EvaluateRelationExpression()
    {
        if (!$this->EvaluateAdditiveExpression())
        {
            return false;
        }
        while (($this->pos + 1) < $this->count)
        {
            $token = $this->tokens[++$this->pos];
            switch (strtolower($token[0]))
            {
                case '<':
                case 'lt':
                case '<=';
                case 'le':
                case '>':
                case 'gt':
                case '>=';
                case 'ge':
                    if ($this->EvaluateAdditiveExpression())
                    {
                        if (!$this->EvalBinary($token))
                        {
                            return false;
                        }
                        // else  continue
                    }
                    else
                    {
                        // an error must have occurred
                        return false;
                    }
                    break;
                default:
                    --$this->pos;
                    return true;
            }
        }
        return true;
    }

    /**
     * Process "op a" where op in (+,-,!)
     * @return boolean - true if success, false if any error occurred
     */

    private function EvaluateUnaryExpression()
    {
        if (($this->pos + 1) >= $this->count) {
            $this->AddError("Poorly terminated expression - expected a constant or variable", NULL);
            return false;
        }
        $token = $this->tokens[++$this->pos];
        if ($token[2] == 'NOT' || $token[2] == 'BINARYOP')
        {
            switch ($token[0])
            {
                case '+':
                case '-':
                case '!':
                    if (!$this->EvaluatePrimaryExpression())
                    {
                        return false;
                    }
                    return $this->EvalUnary($token);
                    break;
                default:
                    --$this->pos;
                    return $this->EvaluatePrimaryExpression();
            }
        }
        else
        {
            --$this->pos;
            return $this->EvaluatePrimaryExpression();
        }
    }

    /**
     * Returns array of all JavaScript-equivalent variable names used when parsing a string via sProcessStringContainingExpressions
     * @return <type>
     */
    public function GetAllJsVarsUsed()
    {
        if (is_null($this->allVarsUsed)){
            return array();
        }
        $names = array_unique($this->allVarsUsed);
        if (is_null($names)) {
            return array();
        }
        $jsNames = array();
        foreach ($names as $name)
        {
            $val = $this->GetVarAttribute($name,'jsName','');
            if ($val != '') {
                $jsNames[] = $val;
            }
        }
        return array_unique($jsNames);
    }

    /**
     * Return the list of all of the JavaScript variables used by the most recent expression
     * @return <type>
     */
    public function GetJsVarsUsed()
    {
        if (is_null($this->varsUsed)){
            return array();
        }
        $names = array_unique($this->varsUsed);
        if (is_null($names)) {
            return array();
        }
        $jsNames = array();
        foreach ($names as $name)
        {
            $val = $this->GetVarAttribute($name,'jsName','');
            if ($val != '') {
                $jsNames[] = $val;
            }
        }
        return array_unique($jsNames);
    }

    /**
     * Return the JavaScript variable name for a named variable
     * @param <type> $name
     * @return <type>
     */
    public function GetJsVarFor($name)
    {
        return $this->GetVarAttribute($name,'jsName','');
    }

    /**
     * Returns array of all variables used when parsing a string via sProcessStringContainingExpressions
     * @return <type>
     */
    public function GetAllVarsUsed()
    {
        return array_unique($this->allVarsUsed);
    }

    /**
     * Return the result of evaluating the equation - NULL if  error
     * @return mixed
     */
    public function GetResult()
    {
        return $this->result[0];
    }

    /**
     * Return an array of errors
     * @return array
     */
    public function GetErrors()
    {
        return $this->errs;
    }

    /**
     * Converts the most recent expression into a valid JavaScript expression, mapping function and variable names and operators as needed.
     * @return <type> the JavaScript expresssion
     */
    public function GetJavaScriptEquivalentOfExpression()
    {
        if (!is_null($this->jsExpression))
        {
            return $this->jsExpression;
        }
        if ($this->HasErrors())
        {
            $this->jsExpression = '';
            return '';
        }
        $tokens = $this->tokens;
        $stringParts=array();
        $numTokens = count($tokens);
        for ($i=0;$i<$numTokens;++$i)
        {
            $token = $tokens[$i];
            // When do these need to be quoted?

            switch ($token[2])
            {
                case 'DQ_STRING':
                    $stringParts[] = '"' . addcslashes($token[0],'\"') . '"'; // htmlspecialchars($token[0],ENT_QUOTES,'UTF-8',false) . "'";
                    break;
                case 'SQ_STRING':
                    $stringParts[] = "'" . addcslashes($token[0],"\'") . "'"; // htmlspecialchars($token[0],ENT_QUOTES,'UTF-8',false) . "'";
                    break;
                case 'SGQA':
                case 'WORD':
                    if ($i+1<$numTokens && $tokens[$i+1][2] == 'LP')
                    {
                        // then word is a function name
                        $funcInfo = $this->amValidFunctions[$token[0]];
                        if ($funcInfo[1] == 'NA')
                        {
                            return '';  // to indicate that this is trying to use a undefined function.  Need more graceful solution
                        }
                        $stringParts[] = $funcInfo[1];  // the PHP function name
                    }
                    elseif ($i+1<$numTokens && $tokens[$i+1][2] == 'ASSIGN')
                    {
                        $jsName = $this->GetVarAttribute($token[0],'jsName','');
                        $relevanceNum = $this->GetVarAttribute($token[0],'relevanceNum','');
                        $stringParts[] = "document.getElementById('" . $jsName . "').value";
                        if ($tokens[$i+1][0] == '+=')
                        {
                            // Javascript does concatenation unless both left and right side are numbers, so refactor the equation
                            $varName = $this->GetVarAttribute($token[0],'varName',$token[0]);
                            $stringParts[] = " = LEMval('" . $varName . "') + ";
                            ++$i;
                        }
                    }
                    else
                    {
                        $jsName = $this->GetVarAttribute($token[0],'jsName','');
                        $relevanceNum = $this->GetVarAttribute($token[0],'relevanceNum','');
                        $codeValue = $this->GetVarAttribute($token[0],'code','');
                        if ($jsName != '')
                        {
                            $varName = $this->GetVarAttribute($token[0],'varName',$token[0]);
                            $stringParts[] = "LEMval('" . $varName . "') ";
                        }
                        else
                        {
                            $stringParts[] = is_numeric($codeValue) ? $codeValue : ("'" . addcslashes($codeValue,"'") . "'"); // htmlspecialchars($codeValue,ENT_QUOTES,'UTF-8',false) . "'");
                        }
                    }
                    break;
                case 'LP':
                case 'RP':
                    $stringParts[] = $token[0];
                    break;
                case 'NUMBER':
                    $stringParts[] = is_numeric($token[0]) ? $token[0] : ("'" . $token[0] . "'");
                    break;
                case 'COMMA':
                    $stringParts[] = $token[0] . ' ';
                    break;
                default:
                    // don't need to check type of $token[2] here since already handling SQ_STRING and DQ_STRING above
                    switch ($token[0])
                    {
                        case 'and': $stringParts[] = ' && '; break;
                        case 'or':  $stringParts[] = ' || '; break;
                        case 'lt':  $stringParts[] = ' < '; break;
                        case 'le':  $stringParts[] = ' <= '; break;
                        case 'gt':  $stringParts[] = ' > '; break;
                        case 'ge':  $stringParts[] = ' >= '; break;
                        case 'eq':  case '==': $stringParts[] = ' == '; break;
                        case 'ne':  case '!=': $stringParts[] = ' != '; break;
                        default:    $stringParts[] = ' ' . $token[0] . ' '; break;
                    }
                    break;
            }
        }
        // for each variable that does not have a default value, add clause to throw error if any of them are NA
        $nonNAvarsUsed = array();
        foreach ($this->GetVarsUsed() as $var)    // this function wants to see the NAOK suffix
        {
            if (!preg_match("/^.*\.NAOK$/", $var))
            {
                $nonNAvarsUsed[] = $var;
            }
        }
        $mainClause = implode('', $stringParts);
        $varsUsed = implode("', '", $nonNAvarsUsed);
        if ($varsUsed != '')
        {
            $this->jsExpression = "LEMif(LEManyNA('" . $varsUsed . "'),'',(" . $mainClause . "))";
        }
        else
        {
            $this->jsExpression = '(' . $mainClause . ')';
        }
        return $this->jsExpression;
    }

    /**
     * JavaScript Test function - simply writes the result of the current JavaScriptEquivalentFunction to the output buffer.
     * @return <type>
     */
    public function GetJavascriptTestforExpression($expected,$num)
    {
        // assumes that the hidden variables have already been declared
        $expr = $this->GetJavaScriptEquivalentOfExpression();
        if (is_null($expr) || $expr == '') {
            $expr = "'NULL'";
        }
        $jsParts = array();
        $jsParts[] = "val = " . $expr . ";\n";
        $jsParts[] = "klass = (LEMeq(addslashes(val),'" . addslashes($expected) . "')) ? 'ok' : 'error';\n";
        $jsParts[] = "document.getElementById('test_" . $num . "').innerHTML=val;\n";
        $jsParts[] = "document.getElementById('test_" . $num . "').className=klass;\n";
        return implode('',$jsParts);

    }

    /**
     * Generate the function needed to dynamically change the value of a <span> section
     * @param <type> $name - the ID name for the function
     * @return <type>
     */
    public function GetJavaScriptFunctionForReplacement($questionNum, $name,$eqn)
    {
        $jsParts = array();
        $jsParts[] = "\n  // Tailor Question " . $questionNum . " - " . $name . ": { " . $eqn . " }\n";
        $jsParts[] = "  try{\n";
        $jsParts[] = "  document.getElementById('" . $name . "').innerHTML=\n    ";
        $jsParts[] = $this->GetJavaScriptEquivalentOfExpression();
        $jsParts[] = ";\n";
        $jsParts[] = "  } catch (e) { }\n";
        return implode('',$jsParts);
    }

    /**
     * Returns the most recent PrettyPrint string generated by sProcessStringContainingExpressions
     */
    public function GetLastPrettyPrintExpression()
    {
        return $this->prettyPrintSource;
    }

    /**
     * This is only used when there are no needed substitutions
     * @param <type> $expr
     */
    public function SetPrettyPrintSource($expr)
    {
        $this->prettyPrintSource = $expr;
    }

    /**
     * Color-codes Expressions (using HTML <span> tags), showing variable types and values.
     * @return <type>
     */
    public function GetPrettyPrintString()
    {
        // color code the equation, showing not only errors, but also variable attributes
        $errs = $this->errs;
        $tokens = $this->tokens;
        $errCount = count($errs);
        $errIndex = 0;
        if ($errCount > 0)
        {
            usort($errs,"cmpErrorTokens");
        }
        $errSpecificStyle= "style='border-style: solid; border-width: 2px; border-color: red;'";
        $stringParts=array();
        $numTokens = count($tokens);
        $globalErrs=array();
        while ($errIndex < $errCount)
        {
            if ($errs[$errIndex++][1][1]==0)
            {
                // General message, associated with position 0
                $globalErrs[] = $errs[$errIndex-1][0];
            }
            else
            {
                --$errIndex;
                break;
            }
        }
        for ($i=0;$i<$numTokens;++$i)
        {
            $token = $tokens[$i];
            $messages=array();
            $thisTokenHasError=false;
            if ($i==0 && count($globalErrs) > 0)
            {
                $messages = array_merge($messages,$globalErrs);
                $thisTokenHasError=true;
            }
            if ($errIndex < $errCount && $token[1] == $errs[$errIndex][1][1])
            {
                $messages[] = $errs[$errIndex][0];
                $thisTokenHasError=true;
            }
            if ($thisTokenHasError)
            {
                $stringParts[] = "<span title='" . implode('; ',$messages) . "' " . $errSpecificStyle . ">";
            }
            switch ($token[2])
            {
                case 'DQ_STRING':
                    $stringParts[] = "<span title='" . implode('; ',$messages) . "' style='color: gray'>\"";
                    $stringParts[] = $token[0]; // htmlspecialchars($token[0],ENT_QUOTES,'UTF-8',false);
                    $stringParts[] = "\"</span>";
                    break;
                case 'SQ_STRING':
                    $stringParts[] = "<span title='" . implode('; ',$messages) . "' style='color: gray'>'";
                    $stringParts[] = $token[0]; // htmlspecialchars($token[0],ENT_QUOTES,'UTF-8',false);
                    $stringParts[] = "'</span>";
                    break;
                case 'SGQA':
                case 'WORD':
                    if ($i+1<$numTokens && $tokens[$i+1][2] == 'LP')
                    {
                        // then word is a function name
                        if ($this->isValidFunction($token[0])) {
                            $funcInfo = $this->amValidFunctions[$token[0]];
                            $messages[] = $funcInfo[2];
                            $messages[] = $funcInfo[3];
                        }
                        $stringParts[] = "<span title='" . implode('; ',$messages) . "' style='color: blue; font-weight: bold'>";
                        $stringParts[] = $token[0];
                        $stringParts[] = "</span>";
                    }
                    else
                    {
                        if (!$this->isValidVariable($token[0])) {
                            $color = 'red';
                        }
                        else {
                            $jsName = $this->GetVarAttribute($token[0],'jsName','');
                            $codeValue = $this->GetVarAttribute($token[0],'codeValue','');
                            $question = $this->GetVarAttribute($token[0], 'question', '');
                            $qcode= $this->GetVarAttribute($token[0],'qcode','');
                            $questionSeq = $this->GetVarAttribute($token[0],'questionSeq',-1);
                            $groupSeq = $this->GetVarAttribute($token[0],'groupSeq',-1);
                            $ansList = $this->GetVarAttribute($token[0],'ansList','');
                            if ($token[2] == 'SGQA' && $qcode != '') {
                                $descriptor = '[' . $qcode . ']';
                            }
                            else if ($jsName != '') {
                                $descriptor = '[' . $jsName . ']';
                            }
                            else {
                                $descriptor = '';
                            }
                            if ($questionSeq != -1) {
                                $descriptor .= '[G:' . $groupSeq . ']';
                            }
                            if ($groupSeq != -1) {
                                $descriptor .= '[Q:' . $questionSeq . ']';
                            }
                            if (strlen($descriptor) > 0) {
                                $descriptor .= ': ';
                            }

                            $messages[] = $descriptor . htmlspecialchars($question,ENT_QUOTES,'UTF-8',false);
                            if ($ansList != '')
                            {
                                $messages[] = htmlspecialchars($ansList,ENT_QUOTES,'UTF-8',false);
                            }
                            if ($codeValue != '') {
                                if ($token[2] == 'SGQA' && preg_match('/^INSERTANS:/',$token[0])) {
                                    $displayValue = $this->GetVarAttribute($token[0], 'displayValue', '');
                                    $messages[] = 'value=[' . htmlspecialchars($codeValue,ENT_QUOTES,'UTF-8',false) . '] '
                                            . htmlspecialchars($displayValue,ENT_QUOTES,'UTF-8',false);
                                }
                                else {
                                    $messages[] = 'value=' . htmlspecialchars($codeValue,ENT_QUOTES,'UTF-8',false);
                                }
                            }
                            if ($this->groupSeq == -1 || $groupSeq == -1 || $questionSeq == -1 || $this->questionSeq == -1) {
                                $color = '#996600'; // tan
                            }
                            else if ($groupSeq > $this->groupSeq) {
                                $color = '#FF00FF ';     // pink a likely error
                            }
                            else if ($groupSeq < $this->groupSeq) {
                                $color = 'green';
                            }
                            else if ($questionSeq > $this->questionSeq) {
                                $color = 'maroon';  // #228b22 - warning
                            }
                            else {
                                $color = '#4C88BE';    // cyan that goes well with the background color
                            }
                        }

                        $stringParts[] = "<span title='"  . implode('; ',$messages) . "' style='color: ". $color . "; font-weight: bold'>";
                        $stringParts[] = $token[0];
                        $stringParts[] = "</span>";
                    }
                    break;
                case 'ASSIGN':
                    $messages[] = 'Assigning a new value to a variable';
                    $stringParts[] = "<span title='" . implode('; ',$messages) . "' style='color: red; font-weight: bold'>";
                    $stringParts[] = $token[0];
                    $stringParts[] =  "</span>";
                    break;
                case 'LP':
                case 'RP':
                case 'COMMA':
                case 'NUMBER':
                    $stringParts[] = $token[0];
                    break;
                default:
                    $stringParts[] = ' ' . $token[0] . ' ';
                    break;
            }
            if ($thisTokenHasError)
            {
                $stringParts[] = "</span>";
                ++$errIndex;
            }
        }
        return "<span style='background-color: #eee8aa;'>" . implode('', $stringParts) . "</span>";
    }

    /**
     * Get information about the variable, including JavaScript name, read-write status, and whether set on current page.
     * @param <type> $varname
     * @return <type>
     */
    public function GetVarAttribute($name,$attr,$default)
    {
        $args = explode(".", $name);
        $varName = $args[0];
        if (!isset($this->amVars[$varName]))
        {
//            echo 'UNDEFINED VARIABLE: ' . $varName;
//            return $default;    // and throw error?
            return '{' . $name . '}';
        }
        $var = $this->amVars[$varName];
        $sgqa = isset($var['sgqa']) ? $var['sgqa'] : NULL;
        if (is_null($attr))
        {
            // then use the requested attribute, if any
            $attr = (count($args)==2) ? $args[1] : 'code';
        }
        switch ($attr)
        {
            case 'varName':
                return $name;
            case 'code':
            case 'codeValue':
            case 'NAOK':
                if (isset($var['codeValue'])) {
                    return $var['codeValue'];
                }
                else {
                    return (isset($_SESSION[$sgqa])) ? $_SESSION[$sgqa] : $default;
                }
            case 'jsName':
                if ($this->allOnOnePage || ($this->groupSeq != -1 && isset($var['groupSeq']) && $this->groupSeq == $var['groupSeq'])) {
                    // then on the same page, so return the on-page javaScript name if there is one.
                    return (isset($var['jsName_on']) ? $var['jsName_on'] : (isset($var['jsName'])) ? $var['jsName'] : $default);
                }
                return (isset($var['jsName']) ? $var['jsName'] : $default);
            case 'sgqa':
            case 'mandatory':
            case 'qid':
            case 'question':
            case 'readWrite':
            case 'relevance':
            case 'relevanceNum':
            case 'type':
            case 'qcode':
            case 'groupSeq':
            case 'questionSeq':
            case 'ansList':
            case 'scale_id':
                return (isset($var[$attr])) ? $var[$attr] : $default;
            case 'displayValue':
            case 'shown':
                if (isset($var['displayValue']))
                {
                    return $var['displayValue'];    // for static values like TOKEN
                }
                else
                {
                    $type = $var['type'];
                    $codeValue = $this->GetVarAttribute($name,'codeValue',$default);    // TODO - is this correct?
                    switch($type)
                    {
                        case '!': //List - dropdown
                        case 'L': //LIST drop-down/radio-button list
                        case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                        case '1': //Array (Flexible Labels) dual scale  // need scale
                        case 'H': //ARRAY (Flexible) - Column Format
                        case 'F': //ARRAY (Flexible) - Row Format
                        case 'R': //RANKING STYLE
                            $scale_id = $this->GetVarAttribute($name,'scale_id','0');
                            $which_ans = $scale_id . '~' . $codeValue;
                            $ansArray = $var['ansArray'];
                            if (is_null($ansArray))
                            {
                                $displayValue=$default;
                            }
                            else
                            {
                                $displayValue = (isset($ansArray[$which_ans])) ? $ansArray[$which_ans] : $default;
                            }
                            break;
                        case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                        case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                        case ':': //ARRAY (Multi Flexi) 1 to 10
                        case '5': //5 POINT CHOICE radio-buttons
                            $displayValue = $codeValue;
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
                            $displayValue = $codeValue;
                            break;
                        case 'G': //GENDER drop-down list
                        case 'Y': //YES/NO radio-buttons
                        case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                            $ansArray = $var['ansArray'];
                            if (is_null($ansArray))
                            {
                                $displayValue=$default;
                            }
                            else
                            {
                                $displayValue = (isset($ansArray[$codeValue])) ? $ansArray[$codeValue] : $default;
                            }
                            break;
                    }
                    return $displayValue;
                }
            case 'relevanceStatus':
                $qid = (isset($var['qid'])) ? $var['qid'] : -1;
                if ($qid == -1) {
                    return 1;
                }
                if (isset($args[1]) && $args[1]=='NAOK') {
                    return 1;
                }
                return (isset($_SESSION['relevanceStatus'][$qid]) ? $_SESSION['relevanceStatus'][$qid] : 0); // should defualt be to show?
            default:
                print 'UNDEFINED ATTRIBUTE: ' . $attr . "<br/>\n";
                return $default;
        }
        return $default;    // and throw and error?
    }

    /**
     * Return array of the list of variables used  in the equation
     * @return array
     */
    public function GetVarsUsed()
    {
        return array_unique($this->varsUsed);
    }

    /**
     * Return true if there were syntax or processing errors
     * @return boolean
     */
    public function HasErrors()
    {
        return (count($this->errs) > 0);
    }

    /**
     * Return true if there are syntax errors
     * @return boolean
     */
    private function HasSyntaxErrors()
    {
        // check for bad tokens
        // check for unmatched parentheses
        // check for undefined variables
        // check for undefined functions (but can't easily check allowable # elements?)

        $nesting = 0;

        for ($i=0;$i<$this->count;++$i)
        {
            $token = $this->tokens[$i];
            switch ($token[2])
            {
                case 'LP':
                    ++$nesting;
                    break;
                case 'RP':
                    --$nesting;
                    if ($nesting < 0)
                    {
                        $this->AddError("Extra right parentheses detected", $token);
                    }
                    break;
                case 'WORD':
                case 'SGQA':
                    if ($i+1 < $this->count and $this->tokens[$i+1][2] == 'LP')
                    {
                        if (!$this->isValidFunction($token[0]))
                        {
                            $this->AddError("Undefined function", $token);
                        }
                    }
                    else
                    {
                        if (!($this->isValidVariable($token[0])))
                        {
                            $this->AddError("Undefined variable", $token);
                        }
                    }
                    break;
                case 'OTHER':
                    $this->AddError("Unsupported syntax", $token);
                    break;
                default:
                    break;
            }
        }
        if ($nesting != 0)
        {
            $this->AddError("Parentheses not balanced",NULL);
        }
        return (count($this->errs) > 0);
    }

    /**
     * Return true if the function name is registered
     * @param <type> $name
     * @return boolean
     */

    private function isValidFunction($name)
    {
        return array_key_exists($name,$this->amValidFunctions);
    }

    /**
     * Return true if the variable name is registered
     * @param <type> $name
     * @return boolean
     */
    private function isValidVariable($name)
    {
        $varName = preg_replace("/^(.*?)(?:\.(?:" . ExpressionManager::$regex_var_attr . "))?$/", "$1", $name);
        return array_key_exists($varName,$this->amVars);
    }

    /**
     * Return true if the variable name is writable
     * @param <type> $name
     * @return <type>
     */
    private function isWritableVariable($name)
    {
        return ($this->GetVarAttribute($name, 'readWrite', 'N') == 'Y');
    }

    /**
     * Process an expression and return its boolean value
     * @param <type> $expr
     * @param <type> $groupSeq - needed to determine whether using variables before they are declared
     * @param <type> $questionSeq - needed to determine whether using variables before they are declared
     * @return <type>
     */
    public function ProcessBooleanExpression($expr,$groupSeq=-1,$questionSeq=-1)
    {
        $this->groupSeq = $groupSeq;
        $this->questionSeq = $questionSeq;

        $status = $this->Evaluate($expr);
        if (!$status) {
            return false;    // if there are errors in the expression, hide it?
        }
        $result = $this->GetResult();
        if (is_null($result)) {
            return false;    // if there are errors in the expression, hide it?
        }
        return (boolean) $result;
    }

    /**
     * Start processing a group of substitions - will be incrementally numbered
     */

    public function StartProcessingGroup($allOnOnePage=false)
    {
        $this->substitutionNum=0;
        $this->substitutionInfo=array(); // array of JavaScripts for managing each substitution
        $this->allOnOnePage=$allOnOnePage;
    }

    /**
     * Process multiple substitution iterations of a full string, containing multiple expressions delimited by {}, return a consolidated string
     * @param <type> $src
     * @param <type> $questionNum
     * @param <type> $numRecursionLevels - number of levels of recursive substitution to perform
     * @param <type> $whichPrettyPrintIteration - if recursing, specify which pretty-print iteration is desired
     * @param <type> $groupSeq - needed to determine whether using variables before they are declared
     * @param <type> $questionSeq - needed to determine whether using variables before they are declared
     * @return <type>
     */

    public function sProcessStringContainingExpressions($src, $questionNum=0, $numRecursionLevels=1, $whichPrettyPrintIteration=1, $groupSeq=-1, $questionSeq=-1)
    {
        // tokenize string by the {} pattern, properly dealing with strings in quotations, and escaped curly brace values
        $this->allVarsUsed = array();
        $this->questionSeq = $questionSeq;
        $this->groupSeq = $groupSeq;
        $result = $src;
        $prettyPrint = '';

        for($i=1;$i<=$numRecursionLevels;++$i)
        {
            // TODO - Since want to use <span> for dynamic substitution, what if there are recursive substititons?
            $result = $this->sProcessStringContainingExpressionsHelper(htmlspecialchars_decode($result,ENT_QUOTES),$questionNum);
            if ($i == $whichPrettyPrintIteration)
            {
                $prettyPrint = $this->prettyPrintSource;
            }
        }
        $this->prettyPrintSource = $prettyPrint;    // ensure that if doing recursive substition, can get original source to pretty print
        return $result;
    }

    /**
     * Process one substitution iteration of a full string, containing multiple expressions delimited by {}, return a consolidated string
     * @param <type> $src
     * @param <type> $questionNum - used to generate substitution <span>s that indicate to which question they belong
     * @return <type>
     */

    public function sProcessStringContainingExpressionsHelper($src, $questionNum)
    {
        // tokenize string by the {} pattern, properly dealing with strings in quotations, and escaped curly brace values
        $stringParts = $this->asSplitStringOnExpressions($src);

        $resolvedParts = array();
        $prettyPrintParts = array();
        $allErrors=array();

        foreach ($stringParts as $stringPart)
        {
            if ($stringPart[2] == 'STRING') {
                $resolvedParts[] =  $stringPart[0];
                $prettyPrintParts[] = $stringPart[0];
            }
            else {
                ++$this->substitutionNum;
                if ($this->Evaluate(substr($stringPart[0],1,-1)))
                {
                    $resolvedPart = $this->GetResult();
                }
                else
                {
                    // show original and errors in-line
                    $resolvedPart = $this->GetPrettyPrintString();
                    $allErrors[] = $this->GetErrors();
                }
                $jsVarsUsed = $this->GetJsVarsUsed();
                $prettyPrintParts[] = $this->GetPrettyPrintString();
                $this->allVarsUsed = array_merge($this->allVarsUsed,$this->GetVarsUsed());

                if (count($jsVarsUsed) > 0)
                {
                    $idName = "LEMtailor_Q_" . $questionNum . "_" . $this->substitutionNum;
//                    $resolvedParts[] = "<span id='" . $idName . "'>" . htmlspecialchars($resolvedPart,ENT_QUOTES,'UTF-8',false) . "</span>"; // TODO - encode within SPAN?
                    $resolvedParts[] = "<span id='" . $idName . "'>" . $resolvedPart . "</span>";
                    $this->substitutionVars[$idName] = 1;
                    $this->substitutionInfo[] = array(
                        'questionNum' => $questionNum,
                        'num' => $this->substitutionNum,
                        'id' => $idName,
                        'raw' => $stringPart[0],
                        'result' => $resolvedPart,
                        'vars' => implode('|',$this->GetJsVarsUsed()),
                        'js' => $this->GetJavaScriptFunctionForReplacement($questionNum, $idName, substr($stringPart[0],1,-1)),
                    );
                }
                else
                {
                    $resolvedParts[] = $resolvedPart;
                }
            }
        }
        $result = implode('',$this->flatten_array($resolvedParts));
        $this->prettyPrintSource = implode('',$this->flatten_array($prettyPrintParts));
        $this->errs = $allErrors;   // so that has all errors from this string
        return $result;    // recurse in case there are nested ones, avoiding infinite loops?
    }

    /**
     * Get info about all <span> elements needed for dynamic tailoring
     * @return <type>
     */
    public function GetCurrentSubstitutionInfo()
    {
        return $this->substitutionInfo;
    }

    /**
     * Flatten out an array, keeping it in the proper order
     * @param array $a
     * @return array
     */

    private function flatten_array(array $a) {
        $i = 0;
        while ($i < count($a)) {
            if (is_array($a[$i])) {
                array_splice($a, $i, 1, $a[$i]);
            } else {
                $i++;
            }
        }
        return $a;
    }


    /**
     * Run a registered function
     * @param <type> $funcNameToken
     * @param <type> $params
     * @return boolean
     */
    private function RunFunction($funcNameToken,$params)
    {
        $name = $funcNameToken[0];
        if (!$this->isValidFunction($name))
        {
            return false;
        }
        $func = $this->amValidFunctions[$name];
        $funcName = $func[0];
        $numArgs = count($params);
        $result=1;  // default value for $this->onlyparse

        if (function_exists($funcName)) {
            $numArgsAllowed = array_slice($func, 5);    // get array of allowable argument counts from end of $func
            $argsPassed = is_array($params) ? count($params) : 0;

            // for unlimited #  parameters (any value less than 0).
            try
            {
                if ($numArgsAllowed[0] < 0) {
                    $minArgs = abs($numArgsAllowed[0] + 1); // so if value is -2, means that requires at least one argument
                    if ($argsPassed < $minArgs)
                    {
                        $this->AddError("Function must have at least ". $minArgs . " argument(s)", $funcNameToken);
                        return false;
                    }
                    if (!$this->onlyparse) {
                        $result = $funcName($params);
                    }
                // Call  function with the params passed
                } elseif (in_array($argsPassed, $numArgsAllowed)) {

                    switch ($argsPassed) {
                    case 0:
                        if (!$this->onlyparse) {
                            $result = $funcName();
                        }
                        break;
                    case 1:
                        if (!$this->onlyparse) {
                            switch($funcName) {
                                case 'acos':
                                case 'asin':
                                case 'atan':
                                case 'cos':
                                case 'exp':
                                case 'is_nan':
                                case 'log':
                                case 'sin':
                                case 'sqrt':
                                case 'tan':
                                    if (is_float($params[0]))
                                    {
                                        $result = $funcName(floatval($params[0]));
                                    }
                                    else
                                    {
                                        $result = NAN;
                                    }
                                    break;
                                default:
                                    $result = $funcName($params[0]);
                                     break;
                            }
                        }
                        break;
                    case 2:
                        if (!$this->onlyparse) {
                            switch($funcName) {
                                case 'atan2':
                                    if (is_float($params[0]) && is_float($params[1]))
                                    {
                                        $result = $funcName(floatval($params[0]),floatval($params[1]));
                                    }
                                    else
                                    {
                                        $result = NAN;
                                    }
                                    break;
                                default:
                                    $result = $funcName($params[0], $params[1]);
                                     break;
                            }
                        }
                        break;
                    case 3:
                        if (!$this->onlyparse) {
                            $result = $funcName($params[0], $params[1], $params[2]);
                        }
                        break;
                    case 4:
                        if (!$this->onlyparse) {
                            $result = $funcName($params[0], $params[1], $params[2], $params[3]);
                        }
                        break;
                    case 5:
                        if (!$this->onlyparse) {
                            $result = $funcName($params[0], $params[1], $params[2], $params[3], $params[4]);
                        }
                        break;
                    case 6:
                        if (!$this->onlyparse) {
                            $result = $funcName($params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
                        }
                        break;
                    default:
                        $this->AddError("Unsupported number of arguments: " . $argsPassed, $funcNameToken);
                        return false;
                    }

                } else {
                    $this->AddError("Function does not support that number of arguments:  " . $argsPassed .
                            ".  Function supports this many arguments, where -1=unlimited: " . implode(',', $numArgsAllowed), $funcNameToken);
                    return false;
                }
            }
            catch (Exception $e)
            {
                $this->AddError($e->getMessage(),$funcNameToken);
                return false;
            }
            $token = array($result,$funcNameToken[1],'NUMBER');
            $this->StackPush($token);
            return true;
        }
    }

    /**
     * Add user functions to array of allowable functions within the equation.
     * $functions is an array of key to value mappings like this:
     * 'newfunc' => array('my_func_script', 1,3)
     * where 'newfunc' is the name of an allowable function wihtin the  expression, 'my_func_script' is the registered PHP function name,
     * and 1,3 are the list of  allowable numbers of paremeters (so my_func() can take 1 or 3 parameters.
     *
     * @param array $functions
     */

    public function RegisterFunctions(array $functions) {
        $this->amValidFunctions= array_merge($this->amValidFunctions, $functions);
    }

    /**
     * Add list of allowable variable names within the equation
     * $varnames is an array of key to value mappings like this:
     * 'myvar' => value
     * where value is optional (e.g. can be blank), and can be any scalar type (e.g. string, number, but not array)
     * the system will use the values as  fast lookup when doing calculations, but if it needs to set values, it will call
     * the interface function to set the values by name
     *
     * @param array $varnames
     */
    public function RegisterVarnamesUsingMerge(array $varnames) {
        $this->amVars = array_merge($this->amVars, $varnames);
    }

    /**
     * Like RegisterVarnamesUsingMerge, except deletes pre-registered varnames.
     * @param array $varnames
     */
    public function RegisterVarnamesUsingReplace(array $varnames) {
        $this->amVars = array_merge(array(), $varnames);
    }

    /**
     * Set the value of a registered variable
     * @param $op - the operator (=,*=,/=,+=,-=)
     * @param <type> $name
     * @param <type> $value
     */
    private function setVariableValue($op,$name,$value)
    {
        // TODO - set this externally
        if ($this->onlyparse)
        {
            return 1;
        }
        switch($op)
        {
            case '=':
                $this->amVars[$name]['codeValue'] = $value;
                break;
            case '*=':
                $this->amVars[$name]['codeValue'] *= $value;
                break;
            case '/=':
                $this->amVars[$name]['codeValue'] /= $value;
                break;
            case '+=':
                $this->amVars[$name]['codeValue'] += $value;
                break;
            case '-=':
                $this->amVars[$name]['codeValue'] -= $value;
                break;
        }
        return $this->amVars[$name]['codeValue'];
    }

    /**
     * Split a soure string into STRING vs. EXPRESSION, where the latter is surrounded by unescaped curly braces.
     * @param <type> $src
     * @return string
     */
    public function asSplitStringOnExpressions($src)
    {
        // tokenize string by the {} pattern, propertly dealing with strings in quotations, and escaped curly brace values
        $tokens0 = preg_split($this->sExpressionRegex,$src,-1,(PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE));

        $tokens = array();
        // Add token_type to $tokens:  For each token, test each categorization in order - first match will be the best.
        for ($j=0;$j<count($tokens0);++$j)
        {
            $token = $tokens0[$j];
            if (preg_match($this->sExpressionRegex,$token[0]))
            {
                $token[2] = 'EXPRESSION';
            }
            else
            {
                $token[2] = 'STRING';    // does type matter here?
            }
            $tokens[] = $token;
        }
        return $tokens;
    }

    /**
     * Pop a value token off of the stack
     * @return token
     */

    private function StackPop()
    {
        if (count($this->stack) > 0)
        {
            return array_pop($this->stack);
        }
        else
        {
            $this->AddError("Tried to pop value off of empty stack", NULL);
            return NULL;
        }
    }

    /**
     * Stack only holds values (number, string), not operators
     * @param array $token
     */

    private function StackPush(array $token)
    {
        if ($this->onlyparse)
        {
            // If only parsing, still want to validate syntax, so use "1" for all variables
            switch($token[2])
            {
                case 'DQ_STRING':
                case 'SQ_STRING':
                    $this->stack[] = array(1,$token[1],$token[2]);
                    break;
                case 'NUMBER':
                default:
                    $this->stack[] = array(1,$token[1],'NUMBER');
                    break;
            }
        }
        else
        {
            $this->stack[] = $token;
        }
    }

    /**
     * Split the source string into tokens, removing whitespace, and categorizing them by type.
     *
     * @param $src
     * @return array
     */

    private function amTokenize($src)
    {
        // $tokens0 = array of tokens from equation, showing value and offset position.  Will include SPACE, which should be removed
        $tokens0 = preg_split($this->sTokenizerRegex,$src,-1,(PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE));

        // $tokens = array of tokens from equation, showing value, offsete position, and type.  Will not contain SPACE, but will contain OTHER
        $tokens = array();
        // Add token_type to $tokens:  For each token, test each categorization in order - first match will be the best.
        for ($j=0;$j<count($tokens0);++$j)
        {
            for ($i=0;$i<count($this->asCategorizeTokensRegex);++$i)
            {
                $token = $tokens0[$j][0];
                if (preg_match($this->asCategorizeTokensRegex[$i],$token))
                {
                    if ($this->asTokenType[$i] !== 'SPACE') {
                        $tokens0[$j][2] = $this->asTokenType[$i];
                        if ($this->asTokenType[$i] == 'DQ_STRING' || $this->asTokenType[$i] == 'SQ_STRING')
                        {
                            // remove outside quotes
                            $unquotedToken = str_replace(array('\"',"\'","\\\\"),array('"',"'",'\\'),substr($token,1,-1));
                            $tokens0[$j][0] = $unquotedToken;
                        }
                        $tokens[] = $tokens0[$j];   // get first matching non-SPACE token type and push onto $tokens array
                    }
                    break;  // only get first matching token type
                }
            }
        }
        return $tokens;
    }

    /**
     * Unit test the asSplitStringOnExpressions() function to ensure that accurately parses out all expressions
     * surrounded by curly braces, allowing for strings and escaped curly braces.
     */

    static function UnitTestStringSplitter()
    {
       $tests = <<<EOD
"this is a string that contains {something in curly brace}"
This example has escaped curly braces like \{this is not an equation\}
Should the parser check for unmatched { opening curly braces?
What about for unmatched } closing curly braces?
What if there is a { space after the opening brace?}
What about a {space before the closing brace }?
What about an { expression nested {within a string} that has white space after the opening brace}?
Can {expressions contain 'single' or "double" quoted strings}?
Can an expression contain a perl regular expression like this {'/^\d{3}-\d{2}-\d{4}$/'}?
[img src="images/mine_{Q1}.png"/]
[img src="images/mine_" + {Q1} + ".png"/]
[img src={implode('','"images/mine_',Q1,'.png"')}/]
[img src="images/mine_{if(Q1=="Y",'yes','no')}.png"/]
[img src="images/mine_{if(Q1=="Y",'sq with {nested braces}',"dq with {nested braces}")}.png"/]
{name}, you said that you are {age} years old, and that you have {numKids} {if((numKids==1),'child','children')} and {numPets} {if((numPets==1),'pet','pets')} running around the house. So, you have {numKids + numPets} wild {if((numKids + numPets ==1),'beast','beasts')} to chase around every day.
Since you have more {if((INSERT61764X1X3 > INSERT61764X1X4),'children','pets')} than you do {if((INSERT61764X1X3 > INSERT61764X1X4),'pets','children')}, do you feel that the {if((INSERT61764X1X3 > INSERT61764X1X4),'pets','children')} are at a disadvantage?
Here is a String that failed to parse prior to fixing the preg_split() command to avoid recursive search of sub-strings: [{((617167X9X3241 == "Y" or 617167X9X3242 == "Y" or 617167X9X3243 == "Y" or 617167X9X3244 == "Y" or 617167X9X3245 == "Y" or 617167X9X3246 == "Y" or 617167X9X3247 == "Y" or 617167X9X3248 == "Y" or 617167X9X3249 == "Y") and (617167X9X3301 == "Y" or 617167X9X3302 == "Y" or 617167X9X3303 == "Y" or 617167X9X3304 == "Y" or 617167X9X3305 == "Y" or 617167X9X3306 == "Y" or 617167X9X3307 == "Y" or 617167X9X3308 == "Y" or 617167X9X3309 == "Y"))}] Here is the question.
EOD;
// Here is a String that failed to parse prior to fixing the preg_split() command to avoid recursive search of sub-strings: [{((617167X9X3241 == "Y" or 617167X9X3242 == "Y" or 617167X9X3243 == "Y" or 617167X9X3244 == "Y" or 617167X9X3245 == "Y" or 617167X9X3246 == "Y" or 617167X9X3247 == "Y" or 617167X9X3248 == "Y" or 617167X9X3249 == "Y") and (617167X9X3301 == "Y" or 617167X9X3302 == "Y" or 617167X9X3303 == "Y" or 617167X9X3304 == "Y" or 617167X9X3305 == "Y" or 617167X9X3306 == "Y" or 617167X9X3307 == "Y" or 617167X9X3308 == "Y" or 617167X9X3309 == "Y"))}] Here is the question.

        $em = new ExpressionManager();

        foreach(explode("\n",$tests) as $test)
        {
            $tokens = $em->asSplitStringOnExpressions($test);
            print '<b>' . $test . '</b><hr/>';
            print '<code>';
            print implode("<br/>\n",explode("\n",print_r($tokens,TRUE)));
            print '</code><hr/>';
        }
    }

    /**
     * Unit test the Tokenizer - Tokenize and generate a HTML-compatible print-out of a comprehensive set of test cases
     */

    static function UnitTestTokenizer()
    {
        // Comprehensive test cases for tokenizing
        $tests = <<<EOD
        String:  "what about regular expressions, like for SSN (^\d{3}-\d{2}-\d{4}) or US phone# ((?:\(\d{3}\)\s*\d{3}-\d{4})"
        String:  "Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"
        String:  "can single quoted strings" . 'contain nested \'quoted sections\'?';
        Parens:  upcase('hello');
        Numbers:  42 72.35 -15 +37 42A .5 0.7
        And_Or: (this and that or the other);  Sandles, sorting; (a && b || c)
        Words:  hi there, my name is C3PO!
        UnaryOps: ++a, --b !b
        BinaryOps:  (a + b * c / d)
        Comparators:  > >= < <= == != gt ge lt le eq ne (target large gents built agile less equal)
        Assign:  = += -= *= /=
        SGQA:  1X6X12 1X6X12ber1 1X6X12ber1_lab1 3583X84X249
        Errors: Apt # 10C; (2 > 0) ? 'hi' : 'there'; array[30]; >>> <<< /* this is not a comment */ // neither is this
        Words:  q5pointChoice q5pointChoice.bogus q5pointChoice.code q5pointChoice.mandatory q5pointChoice.NAOK q5pointChoice.qid q5pointChoice.question q5pointChoice.relevance q5pointChoice.shown q5pointChoice.type
EOD;

        $em = new ExpressionManager();

        foreach(explode("\n",$tests) as $test)
        {
            $tokens = $em->amTokenize($test);
            print '<b>' . $test . '</b><hr/>';
            print '<code>';
            print implode("<br/>\n",explode("\n",print_r($tokens,TRUE)));
            print '</code><hr/>';
        }
    }

    /**
     * Show a table of allowable Expression Manager functions
     * @return string
     */

    static function ShowAllowableFunctions()
    {
        $em = new ExpressionManager();
        $output = "<h3>Functions Available within Expression Manager</h3>\n";
        $output .= "<table border='1'><tr><th>Function</th><th>Meaning</th><th>Syntax</th><th>Reference</th></tr>\n";
        foreach ($em->amValidFunctions as $name => $func) {
            $output .= "<tr><td>" . $name . "</td><td>" . $func[2] . "</td><td>" . $func[3] . "</td><td><a href='" . $func[4] . "'>" . $func[4] . "</a>&nbsp;</td></tr>\n";
        }
        $output .= "</table>\n";
        return $output;
    }

    /**
     * Unit test the Evaluator, allowing for passing in of extra functions, variables, and tests
     */

    static function UnitTestEvaluator()
    {
        // Some test cases for Evaluator
        $vars = array(
'one' => array('codeValue'=>1, 'jsName'=>'java_one', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>4),
'two' => array('codeValue'=>2, 'jsName'=>'java_two', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>4),
'three' => array('codeValue'=>3, 'jsName'=>'java_three', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>4),
'four' => array('codeValue'=>4, 'jsName'=>'java_four', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>1),
'five' => array('codeValue'=>5, 'jsName'=>'java_five', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>1),
'six' => array('codeValue'=>6, 'jsName'=>'java_six', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>1),
'seven' => array('codeValue'=>7, 'jsName'=>'java_seven', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>5),
'eight' => array('codeValue'=>8, 'jsName'=>'java_eight', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>5),
'nine' => array('codeValue'=>9, 'jsName'=>'java_nine', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>5),
'ten' => array('codeValue'=>10, 'jsName'=>'java_ten', 'readWrite'=>'Y', 'groupSeq'=>1,'questionSeq'=>1),
'half' => array('codeValue'=>.5, 'jsName'=>'java_half', 'readWrite'=>'Y', 'groupSeq'=>1,'questionSeq'=>1),
'hi' => array('codeValue'=>'there', 'jsName'=>'java_hi', 'readWrite'=>'Y', 'groupSeq'=>1,'questionSeq'=>1),
'hello' => array('codeValue'=>"Tom", 'jsName'=>'java_hello', 'readWrite'=>'Y', 'groupSeq'=>1,'questionSeq'=>1),
'a' => array('codeValue'=>0, 'jsName'=>'java_a', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>2),
'b' => array('codeValue'=>0, 'jsName'=>'java_b', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>2),
'c' => array('codeValue'=>0, 'jsName'=>'java_c', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>2),
'd' => array('codeValue'=>0, 'jsName'=>'java_d', 'readWrite'=>'Y', 'groupSeq'=>2,'questionSeq'=>2),
'eleven' => array('codeValue'=>11, 'jsName'=>'java_eleven', 'readWrite'=>'Y', 'groupSeq'=>1,'questionSeq'=>1),
'twelve' => array('codeValue'=>12, 'jsName'=>'java_twelve', 'readWrite'=>'Y', 'groupSeq'=>1,'questionSeq'=>1),
// Constants
'ASSESSMENT_HEADING' => array('codeValue'=>'"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"', 'jsName'=>'', 'readWrite'=>'N'),
'QID' => array('codeValue'=>'value for {QID}', 'jsName'=>'', 'readWrite'=>'N'),
'QUESTIONHELP' => array('codeValue'=>'"can single quoted strings" . \'contain nested \'quoted sections\'?', 'jsName'=>'', 'readWrite'=>'N'),
'QUESTION_HELP' => array('codeValue'=>'Can strings have embedded <tags> like <html>, or even unbalanced "quotes or entities without terminal semicolons like &amp and  &lt?', 'jsName'=>'', 'readWrite'=>'N'),
'NUMBEROFQUESTIONS' => array('codeValue'=>'value for {NUMBEROFQUESTIONS}', 'jsName'=>'', 'readWrite'=>'N'),
'THEREAREXQUESTIONS' => array('codeValue'=>'value for {THEREAREXQUESTIONS}', 'jsName'=>'', 'readWrite'=>'N'),
'TOKEN:FIRSTNAME' => array('codeValue' => 'value for {TOKEN:FIRSTNAME}', 'jsName' => '', 'readWrite' => 'N'),
'WELCOME' => array('codeValue'=>'value for {WELCOME}', 'jsName'=>'', 'readWrite'=>'N'),
// also include SGQA values and read-only variable attributes
'12X34X56'  => array('codeValue'=>5, 'jsName'=>'', 'readWrite'=>'N', 'groupSeq'=>1,'questionSeq'=>1),
'12X3X5lab1_ber'    => array('codeValue'=>10, 'jsName'=>'', 'readWrite'=>'N', 'groupSeq'=>1,'questionSeq'=>1),
'q5pointChoice'    => array('codeValue'=>3, 'jsName'=>'java_q5pointChoice', 'readWrite'=>'N','displayValue'=>'Father', 'relevance'=>1, 'type'=>'5', 'question'=>'(question for q5pointChoice)', 'qid'=>13,'groupSeq'=>2,'questionSeq'=>13),
'qArrayNumbers_ls1_min'    => array('codeValue'=> 7, 'jsName'=>'java_qArrayNumbers_ls1_min', 'readWrite'=>'N','displayValue'=> 'I love LimeSurvey', 'relevance'=>1, 'type'=>'A', 'question'=>'(question for qArrayNumbers)', 'qid'=>6,'groupSeq'=>2,'questionSeq'=>6),
'12X3X5lab1_ber#1'  => array('codeValue'=> 15, 'jsName'=>'', 'readWrite'=>'N', 'groupSeq'=>1,'questionSeq'=>1),
'zero' => array('codeValue'=>0, 'jsName'=>'java_zero', 'groupSeq'=>0,'questionSeq'=>0),
'empty' => array('codeValue'=>'', 'jsName'=>'java_empty', 'groupSeq'=>0,'questionSeq'=>0),
        );

        // Syntax for $tests is
        // expectedResult~expression
        // if the expected result is an error, use NULL for the expected result
        $tests  = <<<EOD
0~zero
~empty
1~is_empty(empty)
1~five > zero
1~five > empty
1~empty < 16
1~zero == empty
3~q5pointChoice.code
5~q5pointChoice.type
(question for q5pointChoice)~q5pointChoice.question
1~q5pointChoice.relevance
4~q5pointChoice.NAOK + 1
NULL~q5pointChoice.bogus
13~q5pointChoice.qid
7~qArrayNumbers_ls1_min.code
6~max(five,(one + (two * four)- three))
6~max((one + (two * four)- three))
212~5 + max(1,(2+3),(4 + (5 + 6)),((7 + 8) + 9),((10 + 11), 12),(13 + (14 * 15) - 16))
29~five + max(one, (two + three), (four + (five + six)),((seven + eight) + nine),((ten + eleven), twelve),(one + (two * three) - four))
1024~max(one,(two*three),pow(four,five),six)
1~(one * (two + (three - four) + five) / six)
2~max(one,two)
5~max(one,two,three,four,five)
1~min(five,four,one,two,three)
0~(a=rand())-a
3~floor(pi())
2.4~(one  * two) + (three * four) / (five * six)
1~sin(0.5 * pi())
50~12X34X56 * 12X3X5lab1_ber
3~a=three
3~c=a
12~c*=four
15~c+=a
5~c/=a
-1~c-=six
27~pow(3,3)
24~one * two * three * four
-4~five - four - three - two
0~two * three - two - two - two
4~two * three - two
1~pi() == pi() * 2 - pi()
1~sin(pi()/2)
1~sin(pi()/2) == sin(.5 * pi())
105~5 + 1, 7 * 15
7~7
15~10 + 5
24~12 * 2
10~13 - 3
3.5~14 / 4
5~3 + 1 * 2
1~one
there~hi
6.25~one * two - three / four + five
1~one + hi
1~two > one
1~two gt one
1~three >= two
1~three ge  two
0~four < three
0~four lt three
0~four <= three
0~four le three
0~four == three
0~four eq three
1~four != three
0~four ne four
0~one * hi
5~abs(-five)
0~acos(cos(pi()))-pi()
0~floor(asin(sin(pi())))
10~ceil(9.1)
9~floor(9.9)
15~sum(one,two,three,four,five)
5~count(one,two,three,four,five)
0~a='hello',b='',c=0
hello~a
0~c
2~count(a,b,c)
5~intval(5.7)
1~is_float(pi())
0~is_float(5)
1~is_numeric(five)
0~is_numeric(hi)
1~is_string(hi)
0~one && 0
0~two and 0
1~five && 6
1~seven && eight
1~one or 0
1~one || 0
1~(one and 0) || (two and three)
NULL~hi(there);
NULL~(one * two + (three - four)
NULL~(one * two + (three - four)))
NULL~++a
NULL~--b
value for {QID}~QID
"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"~ASSESSMENT_HEADING
"can single quoted strings" . 'contain nested 'quoted sections'?~QUESTIONHELP
Can strings have embedded <tags> like <html>, or even unbalanced "quotes or entities without terminal semicolons like &amp and  &lt?~QUESTION_HELP
value for {TOKEN:FIRSTNAME}~TOKEN:FIRSTNAME
value for {THEREAREXQUESTIONS}~THEREAREXQUESTIONS
15~12X3X5lab1_ber#1
NULL~*
NULL~three +
NULL~four * / seven
NULL~(five - three
NULL~five + three)
NULL~seven + = four
NULL~if(seven,three,four))
NULL~if(seven)
NULL~if(seven,three)
NULL~if(seven,three,four,five)
NULL~if(seven,three,)
NULL~>
NULL~five > > three
NULL~seven > = four
NULL~seven >=
NULL~three &&
NULL~three ||
NULL~three +
NULL~three >=
NULL~three +=
NULL~three !
0~!three
NULL~three *
NULL~five ! three
8~five + + three
2~five + - three
NULL~(5 + 7) = 8
NULL~&& four
NULL~min(
NULL~max three, four, five)
NULL~three four
NULL~max(three,four,five) six
NULL~WELCOME='Good morning'
NULL~TOKEN:FIRSTNAME='Tom'
NULL~NUMBEROFQUESTIONS+=3
NULL~NUMBEROFQUESTIONS*=4
NULL~NUMBEROFQUESTIONS/=5
NULL~NUMBEROFQUESTIONS-=6
NULL~'Tom'='tired'
NULL~max()
1|2|3|4|5~implode('|',one,two,three,four,five)
0, 1, 3, 5~list(0,one,'',three,'',five)
5~strlen(hi)
I love LimeSurvey~str_replace('like','love','I like LimeSurvey')
2~strpos('I like LimeSurvey','like')
<span id="d" style="border-style: solid; border-width: 2px; border-color: green">Hi there!</span>~d='<span id="d" style="border-style: solid; border-width: 2px; border-color: green">Hi there!</span>'
Hi there!~c=strip_tags(d)
Hi there!~c
+,-,*,/,!,,,and,&&,or,||,gt,>,lt,<,ge,>=,le,<=,eq,==,ne,!=~implode(',','+','-','*','/','!',',','and','&&','or','||','gt','>','lt','<','ge','>=','le','<=','eq','==','ne','!=')
HI THERE!~strtoupper(c)
hi there!~strtolower(c)
1~three == three
1~three == 3
1~c == 'Hi there!'
1~c == "Hi there!"
1~strpos(c,'there')>1
1~regexMatch('/there/',c)
1~regexMatch('/^.*there.*$/',c)
0~regexMatch('/joe/',c)
1~regexMatch('/(?:dog|cat)food/','catfood stinks')
1~regexMatch('/(?:dog|cat)food/','catfood stinks')
1~regexMatch('/[0-9]{3}-[0-9]{2}-[0-9]{4}/','123-45-6789')
1~regexMatch('/\d{3}-\d{2}-\d{4}/','123-45-6789')
1~regexMatch('/(?:\(\d{3}\))\s*\d{3}-\d{4}/','(212) 555-1212')
11~eleven
144~twelve * twelve
4~if(5 > 7,2,4)
there~if((one > two),'hi','there')
64~if((one < two),pow(2,6),pow(6,2))
1, 2, 3, 4, 5~list(one,two,three,min(four,five,six),max(three,four,five))
11, 12~list(eleven,twelve)
1~is_empty('0')
1~is_empty('')
0~is_empty(1)
1~is_empty(one==two)
0~if('',1,0)
1~if(' ',1,0)
0~!is_empty(one==two)
1~!is_empty(1)
1~is_bool(0)
0~is_bool(1)
&quot;Can strings contain embedded \&quot;quoted passages\&quot; (and parentheses + other characters?)?&quot;~a=htmlspecialchars(ASSESSMENT_HEADING)
&quot;can single quoted strings&quot; . &#039;contain nested &#039;quoted sections&#039;?~b=htmlspecialchars(QUESTIONHELP)
Can strings have embedded &lt;tags&gt; like &lt;html&gt;, or even unbalanced &quot;quotes or entities without terminal semicolons like &amp;amp and  &amp;lt?~c=htmlspecialchars(QUESTION_HELP)
1~c==htmlspecialchars(htmlspecialchars_decode(c))
1~b==htmlspecialchars(htmlspecialchars_decode(b))
1~a==htmlspecialchars(htmlspecialchars_decode(a))
&quot;Can strings contain embedded \\&quot;quoted passages\\&quot; (and parentheses + other characters?)?&quot;~addslashes(a)
&quot;can single quoted strings&quot; . &#039;contain nested &#039;quoted sections&#039;?~addslashes(b)
Can strings have embedded &lt;tags&gt; like &lt;html&gt;, or even unbalanced &quot;quotes or entities without terminal semicolons like &amp;amp and  &amp;lt?~addslashes(c)
"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"~html_entity_decode(a)
"can single quoted strings" . &#039;contain nested &#039;quoted sections&#039;?~html_entity_decode(b)
Can strings have embedded <tags> like <html>, or even unbalanced "quotes or entities without terminal semicolons like &amp and  &lt?~html_entity_decode(c)
&quot;Can strings contain embedded \&quot;quoted passages\&quot; (and parentheses + other characters?)?&quot;~htmlentities(a)
&quot;can single quoted strings&quot; . &#039;contain nested &#039;quoted sections&#039;?~htmlentities(b)
Can strings have embedded &lt;tags&gt; like &lt;html&gt;, or even unbalanced &quot;quotes or entities without terminal semicolons like &amp;amp and &amp;lt?~htmlentities(c)
"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"~htmlspecialchars_decode(a)
"can single quoted strings" . 'contain nested 'quoted sections'?~htmlspecialchars_decode(b)
Can strings have embedded like , or even unbalanced "quotes or entities without terminal semicolons like & and <?~htmlspecialchars_decode(c)
"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"~htmlspecialchars(a)
"can single quoted strings" . 'contain nested 'quoted sections'?~htmlspecialchars(b)
Can strings have embedded <tags> like <html>, or even unbalanced "quotes or entities without terminal semicolons like &amp and &lt?~htmlspecialchars(c)
I was trimmed   ~ltrim('     I was trimmed   ')
     I was trimmed~rtrim('     I was trimmed   ')
I was trimmed~trim('     I was trimmed   ')
1,234,567~number_format(1234567)
Hi There You~ucwords('hi there you')
1~checkdate(1,29,1967)
0~checkdate(2,29,1967)
1144191723~mktime(1,2,3,4,5,6)
April 5, 2006, 1:02 am~date('F j, Y, g:i a',mktime(1,2,3,4,5,6))
NULL~time()
NULL~date('F j, Y, g:i a',time())
EOD;

        $em = new ExpressionManager();
        $em->RegisterVarnamesUsingMerge($vars);

        // manually set relevance status
        $_SESSION['relevanceStatus'] = array();
        foreach ($vars as $var) {
            if (isset($var['questionSeq'])) {
                $_SESSION['relevanceStatus'][$var['questionSeq']] = 1;
            }
        }

        $allJsVarnamesUsed = array();
        $body = '';
        $body .= '<table border="1"><tr><th>Expression</th><th>PHP Result</th><th>Expected</th><th>JavaScript Result</th><th>VarNames</th><th>JavaScript Eqn</th></tr>';
        $i=0;
        $javaScript = array();
        foreach(explode("\n",$tests)as $test)
        {
            ++$i;
            $values = explode("~",$test);
            $expectedResult = array_shift($values);
            $expr = implode("~",$values);
            $resultStatus = 'ok';
            $em->groupSeq=2;
            $em->questionSeq=3;
            $status = $em->Evaluate($expr);
            if ($status)
            {
                $allJsVarnamesUsed = array_merge($allJsVarnamesUsed,$em->GetJsVarsUsed());
            }
            $result = $em->GetResult();
            $valToShow = $result;   // htmlspecialchars($result,ENT_QUOTES,'UTF-8',false);
            $expectedToShow = $expectedResult; // htmlspecialchars($expectedResult,ENT_QUOTES,'UTF-8',false);
            $body .= "<tr>";
            $body .= "<td>" . $em->GetPrettyPrintString() . "</td>\n";
            if (is_null($result)) {
                $valToShow = "NULL";
            }
            if ($valToShow != $expectedToShow)
            {
                $resultStatus = 'error';
            }
            $body .= "<td class='" . $resultStatus . "'>" . $valToShow . "</td>\n";
            $body .= '<td>' . $expectedToShow . "</td>\n";
            $javaScript[] = $em->GetJavascriptTestforExpression($expectedToShow, $i);
            $body .= "<td id='test_" . $i . "'>&nbsp;</td>\n";
            $varsUsed = $em->GetVarsUsed();
            if (is_array($varsUsed) and count($varsUsed) > 0) {
                $varDesc = array();
                foreach ($varsUsed as $v) {
                    $varDesc[] = $v;
                }
                $body .= '<td>' . implode(',<br/>', $varDesc) . "</td>\n";
            }
            else {
                $body .= "<td>&nbsp;</td>\n";
            }
            $jsEqn = $em->GetJavaScriptEquivalentOfExpression();
            if ($jsEqn == '')
            {
                $body .= "<td>&nbsp;</td>\n";
            }
            else
            {
                $body .= '<td>' . $jsEqn . "</td>\n";
            }
            $body .= '</tr>';
        }
        $body .= '</table>';
        $body .= "<script type='text/javascript'>\n";
        $body .= "<!--\n";
        $body .= "var LEMgid=2;\n";
        $body .= "var LEMallOnOnePage=false;\n";
        $body .= "function recompute() {\n";
        $body .= implode("\n",$javaScript);
        $body .= "}\n//-->\n</script>\n";

        $allJsVarnamesUsed = array_unique($allJsVarnamesUsed);
        asort($allJsVarnamesUsed);
        $pre = '';
        $pre .= "<h3>Change some Relevance values to 0 to see how it affects computations</h3>\n";
        $pre .= '<table border="1"><tr><th>#</th><th>JsVarname</th><th>Starting Value</th><th>Relevance</th></tr>';
        $i=0;
        $LEMvarNameAttr=array();
        $LEMalias2varName=array();
        foreach ($allJsVarnamesUsed as $jsVarName)
        {
            ++$i;
            $pre .= "<tr><td>" .  $i . "</td><td>" . $jsVarName;
            foreach($em->amVars as $k => $v) {
                if ($v['jsName'] == $jsVarName)
                {
                    $value = $v['codeValue'];
                }
            }
            $pre .= "</td><td>" . $value . "</td><td><input type='text' id='relevance" . $i . "' value='1' onchange='recompute()'/>\n";
            $pre .= "<input type='hidden' id='" . $jsVarName . "' name='" . $jsVarName . "' value='" . $value . "'/>\n";
            $pre .= "</td></tr>\n";
            $LEMalias2varName[] = "'" . substr($jsVarName,5) . "':'" . $jsVarName . "'";
            $LEMalias2varName[] = "'" . $jsVarName . "':'" . $jsVarName . "'";
            $attrInfo = "'" . $jsVarName .  "': {'jsName':'" . $jsVarName . "'";

            // populate this from $em->amVars - cheat, knowing that the jsVaraName will be java_xxxx
            $varInfo = $em->amVars[substr($jsVarName,5)];
            foreach ($varInfo as $k=>$v) {
                if ($k == 'codeValue') {
                    continue;   // will access it from hidden node
                }
               if ($k == 'displayValue') {
                    $k = 'shown';
                    $v = htmlspecialchars(preg_replace("/[[:space:]]/",' ',$v),ENT_QUOTES);
                }
                if ($k == 'jsName') {
                    continue;   // since already set
                }
                $attrInfo .= ", '" . $k . "':'" . $v . "'";

            }
            $attrInfo .= ",'qid':" . $i . "}";
            $LEMvarNameAttr[] = $attrInfo;
        }
        $pre .= "</table>\n";

        $pre .= "<script type='text/javascript'>\n";
        $pre .= "<!--\n";
        $pre .= "var LEMalias2varName= {". implode(",\n", $LEMalias2varName) ."};\n";
        $pre .= "var LEMvarNameAttr= {" . implode(",\n", $LEMvarNameAttr) . "};\n";
        $pre .= "//-->\n</script>\n";

        print $pre;
        print $body;
    }

    function gT($string)
    {
        // ultimately should call i8n functiouns
        return $string;
    }
}

/**
 * Used by usort() to order Error tokens by their position within the string
 * @param <type> $a
 * @param <type> $b
 * @return <type>
 */
function cmpErrorTokens($a, $b)
{
    if (is_null($a[1])) {
        if (is_null($b[1])) {
            return 0;
        }
        return 1;
    }
    if (is_null($b[1])) {
        return -1;
    }
    if ($a[1][1] == $b[1][1]) {
        return 0;
    }
    return ($a[1][1] < $b[1][1]) ? -1 : 1;
}

/**
 * Count the number of answered questions (non-empty)
 * @param <type> $args
 * @return int
 */
function exprmgr_count($args)
{
    $j=0;    // keep track of how many non-null values seen
    foreach ($args as $arg)
    {
        if ($arg != '') {
            ++$j;
        }
    }
    return $j;
}

/**
 * If $test is true, return $ok, else return $error
 * @param <type> $test
 * @param <type> $ok
 * @param <type> $error
 * @return <type>
 */
function exprmgr_if($test,$ok,$error)
{
    if ($test)
    {
        return $ok;
    }
    else
    {
        return $error;
    }
}

/**
 * Join together $args[0-N] with ', '
 * @param <type> $args
 * @return <type>
 */
function exprmgr_list($args)
{
    $result="";
    $j=1;    // keep track of how many non-null values seen
    foreach ($args as $arg)
    {
        if ($arg != '') {
            if ($j > 1) {
                $result .= ', ' . $arg;
            }
            else {
                $result .= $arg;
            }
            ++$j;
        }
    }
    return $result;
}

/**
 * Join together $args[1-N] with $arg[0]
 * @param <type> $args
 * @return <type>
 */
function exprmgr_implode($args)
{
    if (count($args) <= 1)
    {
        return "";
    }
    $joiner = array_shift($args);
    return implode($joiner,$args);
}

/**
 * Return true if the variable is NULL or blank.
 * @param <type> $arg
 * @return <type>
 */
function exprmgr_empty($arg)
{
    return empty($arg);
}

/**
 * Compute the Sample Standard Deviation of a set of numbers ($args[0-N])
 * @param <type> $args
 * @return <type>
 */
function exprmgr_stddev($args)
{
    $vals = array();
    foreach ($args as $arg)
    {
        if (is_numeric($arg)) {
            $vals[] = $arg;
        }
    }
    $count = count($vals);
    if ($count <= 1) {
        return 0;   // what should default value be?
    }
    $sum = 0;
    foreach ($vals as $val) {
        $sum += $val;
    }
    $mean = $sum / $count;

    $sumsqmeans = 0;
    foreach ($vals as $val)
    {
        $sumsqmeans += ($val - $mean) * ($val - $mean);
    }
    $stddev = sqrt($sumsqmeans / ($count-1));
    return $stddev;
}

/**
 * Javascript equivalent does not cope well with ENT_QUOTES and related PHP constants, so set default to ENT_QUOTES
 * @param <type> $string
 * @return <type>
 */
function expr_mgr_htmlspecialchars($string)
{
    return htmlspecialchars($string,ENT_QUOTES);
}

/**
 * Javascript equivalent does not cope well with ENT_QUOTES and related PHP constants, so set default to ENT_QUOTES
 * @param <type> $string
 * @return <type>
 */
function expr_mgr_htmlspecialchars_decode($string)
{
    return htmlspecialchars_decode($string,ENT_QUOTES);
}

/**
 * Return true of $input matches the regular expression $pattern
 * @param <type> $pattern
 * @param <type> $input
 * @return <type>
 */
function exprmgr_regexMatch($pattern, $input)
{
    try {
        $result = preg_match($pattern, $input);
    } catch (Exception $e) {
        $result = false;
        // How should errors be logged?
        print 'Invalid PERL Regular Expression: ' . htmlspecialchars($pattern);
    }
    return $result;
}

?>
