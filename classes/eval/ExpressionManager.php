<?php
/**
 * Description of ExpressionManager
 * (1) Does safe evaluation of PHP expressions.  Only registered Functions, Variables, and NamedConstants are allowed.
 *   (a) Functions include any math, string processing, conditional, formatting, etc. functions
 *   (b) Variables are typically the question name (question.title) - they can be read/write
 *   (c) NamedConstants are any LimeReplacementField or Token, including all INSERTANS:SGQA codes - these are all read-only
 * (2) This class can replace LimeSurvey's current process of resolving strings that contain LimeReplacementFields
 *   (a) String is split by expressions (by curly braces, but safely supporting strings and escaped curly braces)
 *   (b) Expressions (things surrounded by curly braces) are evaluated - thereby doing LimeReplacementField substitution and/or more complex calculations
 *   (c) Non-expressions are left intact
 *   (d) The array of stringParts are re-joined to create the desired final string.
 *
 * At present, all variables are read-only, but this could be extended to support creation  of temporary variables and/or read-write access to registered variables
 *
 * @author Thomas M. White
 */


class ExpressionManager {
    // These three variables are effectively static once constructed
    private $sExpressionRegex;
    private $asTokenType;
    private $sTokenizerRegex;
    private $asCategorizeTokensRegex;
    private $amValidFunctions; // names and # params of valid functions
    private $amVars;    // names and values of valid variables
    private $amNamedConstants;   // names and values of valid named constants

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
    private $namedConstantsUsed;  // list of named constants used in the equation

    // These  variables are only used by sProcessStringContainingExpressions
    private $allVarsUsed;   // full list of variables used within the string, even if contains multiple expressions
    private $allNamedConstantsUsed;  // full list of named constants used in the string, even if  contains multiple expresions

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
        $regex_sgqa = '[0-9]+X[0-9]+X[0-9]+[A-Z0-9_]*\#?[12]?';
        $regex_word = '[A-Z][A-Z0-9_]*:?[A-Z0-9_]*\.?[A-Z0-9_]*\.?[A-Z0-9_]*\.?[A-Z0-9_]*';
        $regex_number = '[0-9]+\.?[0-9]*|\.[0-9]+';
        $regex_andor = '\band\b|\bor\b|&&|\|\|';

        $this->sExpressionRegex = '#((?<!\\\\)' . '{' . '(?!\s*\n\|\s*\r\|\s*\r\n|\s+)' .
                '(' . $regex_dq_string . '|' . $regex_sq_string . '|.*?)*' .
                '(?<!\\\\)(?<!\n|\r|\r\n|\s)' . '}' . ')#';


        // asTokenRegex and t_tokey_type must be kept in sync  (same number and order)
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
            'STRING',
            'STRING',
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
        $this->amValidFunctions = array(
            'abs'			=>array('abs','Math.abs','Absolute value',1),
            'acos'			=>array('acos','Math.acos','Arc cosine',1),
            'acosh'			=>array('acosh','NA','Inverse hyperbolic cosine',1),
            'asin'			=>array('asin','Math.asin','Arc sine',1),
            'asinh'			=>array('asinh','NA','Inverse hyperbolic sine',1),
            'atan2'			=>array('atan2','Math.atan2','Arc tangent of two variables',2),
            'atan'			=>array('atan','Math.atan','Arc tangent',1),
            'atanh'			=>array('atanh','NA','Inverse hyperbolic tangent',1),
            'base_convert'	=>array('base_convert','NA','Convert a number between arbitrary bases',3),
            'bindec'		=>array('bindec','NA','Binary to decimal',1),
            'ceil'			=>array('ceil','Math.ceil','Round fractions up',1),
            'cos'			=>array('cos','Math.cos','Cosine',1),
            'cosh'			=>array('cosh','NA','Hyperbolic cosine',1),
            'decbin'		=>array('decbin','NA','Decimal to binary',1),
            'dechex'		=>array('dechex','NA','Decimal to hexadecimal',1),
            'decoct'		=>array('decoct','NA','Decimal to octal',1),
            'deg2rad'		=>array('deg2rad','NA','Converts the number in degrees to the radian equivalent',1),
            'exp'			=>array('exp','Math.exp','Calculates the exponent of e',1),
            'expm1'			=>array('expm1','NA','Returns exp(number) - 1, computed in a way that is accurate even when the value of number is close to zero',1),
            'floor'			=>array('floor','Math.floor','Round fractions down',1),
            'fmod'			=>array('fmod','NA','Returns the floating point remainder (modulo) of the division of the arguments',2),
            'getrandmax'	=>array('getrandmax','NA','Show largest possible random value',0),
            'hexdec'		=>array('hexdec','NA','Hexadecimal to decimal',1),
            'hypot'			=>array('hypot','NA','Calculate the length of the hypotenuse of a right-angle triangle',2),
            'is_finite'		=>array('is_finite','NA','Finds whether a value is a legal finite number',1),
            'is_infinite'	=>array('is_infinite','NA','Finds whether a value is infinite',1),
            'is_nan'		=>array('is_nan','NA','Finds whether a value is not a number',1),
            'lcg_value'		=>array('lcg_value','NA','Combined linear congruential generator',0),
            'log10'			=>array('log10','NA','Base-10 logarithm',1),
            'log1p'			=>array('log1p','NA','Returns log(1 + number), computed in a way that is accurate even when the value of number is close to zero',1),
            'log'			=>array('log','Math.log','Natural logarithm',1,2),
            'max'			=>array('max','Math.max','Find highest value',-1),
            'min'			=>array('min','Math.min','Find lowest value',-1),
            'mt_getrandmax'	=>array('mt_getrandmax','NA','Show largest possible random value',0),
            'mt_rand'		=>array('mt_rand','NA','Generate a better random value',0,2),
            'mt_srand'		=>array('mt_srand','NA','Seed the better random number generator',0,1),
            'octdec'		=>array('octdec','NA','Octal to decimal',1),
            'pi'			=>array('pi','NA','Get value of pi',0),
            'pow'			=>array('pow','Math.pow','Exponential expression',2),
            'rad2deg'		=>array('rad2deg','NA','Converts the radian number to the equivalent number in degrees',1),
            'rand'			=>array('rand','Math.random','Generate a random integer',0,2),
            'round'			=>array('round','Math.round','Rounds a float',1,2,3),
            'sin'			=>array('sin','Math.sin','Sine',1),
            'sinh'			=>array('sinh','NA','Hyperbolic sine',1),
            'sqrt'			=>array('sqrt','Math.sqrt','Square root',1),
            'srand'			=>array('srand','NA','Seed the random number generator',0,1),
            'sum'           =>array('array_sum','NA','Calculate the sum of values in an array',-1),
            'tan'			=>array('tan','Math.tan','Tangent',1),
            'tanh'			=>array('tanh','NA','Hyperbolic tangent',1),

            'empty'			=>array('empty','NA','Determine whether a variable is empty',1),
            'intval'		=>array('intval','NA','Get the integer value of a variable',1,2),
            'is_bool'		=>array('is_bool','NA','Finds out whether a variable is a boolean',1),
            'is_float'		=>array('is_float','NA','Finds whether the type of a variable is float',1),
            'is_int'		=>array('is_int','NA','Find whether the type of a variable is integer',1),
            'is_null'		=>array('is_null','NA','Finds whether a variable is NULL',1),
            'is_numeric'	=>array('is_numeric','NA','Finds whether a variable is a number or a numeric string',1),
            'is_scalar'		=>array('is_scalar','NA','Finds whether a variable is a scalar',1),
            'is_string'		=>array('is_string','NA','Find whether the type of a variable is string',1),

            'addcslashes'	=>array('addcslashes','NA','Quote string with slashes in a C style',2),
            'addslashes'	=>array('addslashes','NA','Quote string with slashes',1),
            'bin2hex'		=>array('bin2hex','NA','Convert binary data into hexadecimal representation',1),
            'chr'			=>array('chr','NA','Return a specific character',1),
            'chunk_split'	=>array('chunk_split','NA','Split a string into smaller chunks',1,2,3),
            'convert_uudecode'			=>array('convert_uudecode','NA','Decode a uuencoded string',1),
            'convert_uuencode'			=>array('convert_uuencode','NA','Uuencode a string',1),
            'count_chars'	=>array('count_chars','NA','Return information about characters used in a string',1,2),
            'crc32'			=>array('crc32','NA','Calculates the crc32 polynomial of a string',1),
            'crypt'			=>array('crypt','NA','One-way string hashing',1,2),
            'hebrev'		=>array('hebrev','NA','Convert logical Hebrew text to visual text',1,2),
            'hebrevc'		=>array('hebrevc','NA','Convert logical Hebrew text to visual text with newline conversion',1,2),
            'html_entity_decode'        =>array('html_entity_decode','NA','Convert all HTML entities to their applicable characters',1,2,3),
            'htmlentities'	=>array('htmlentities','NA','Convert all applicable characters to HTML entities',1,2,3),
            'htmlspecialchars_decode'	=>array('htmlspecialchars_decode','NA','Convert special HTML entities back to characters',1,2),
            'htmlspecialchars'			=>array('htmlspecialchars','NA','Convert special characters to HTML entities',1,2,3,4),
            'implode'		=>array('implode','NA','Join array elements with a string',-1),
            'lcfirst'		=>array('lcfirst','NA','Make a string\'s first character lowercase',1),
            'levenshtein'	=>array('levenshtein','NA','Calculate Levenshtein distance between two strings',2,5),
            'ltrim'			=>array('ltrim','NA','Strip whitespace (or other characters) from the beginning of a string',1,2),
            'md5'			=>array('md5','NA','Calculate the md5 hash of a string',1),
            'metaphone'		=>array('metaphone','NA','Calculate the metaphone key of a string',1,2),
            'money_format'	=>array('money_format','NA','Formats a number as a currency string',1,2),
            'nl2br'			=>array('nl2br','NA','Inserts HTML line breaks before all newlines in a string',1,2),
            'number_format'	=>array('number_format','NA','Format a number with grouped thousands',1,2,4),
            'ord'			=>array('ord','NA','Return ASCII value of character',1),
            'quoted_printable_decode'			=>array('quoted_printable_decode','NA','Convert a quoted-printable string to an 8 bit string',1),
            'quoted_printable_encode'			=>array('quoted_printable_encode','NA','Convert a 8 bit string to a quoted-printable string',1),
            'quotemeta'		=>array('quotemeta','NA','Quote meta characters',1),
            'rtrim'			=>array('rtrim','NA','Strip whitespace (or other characters) from the end of a string',1,2),
            'sha1'			=>array('sha1','NA','Calculate the sha1 hash of a string',1),
            'similar_text'	=>array('similar_text','NA','Calculate the similarity between two strings',1,2),
            'soundex'		=>array('soundex','NA','Calculate the soundex key of a string',1),
            'sprintf'		=>array('sprintf','NA','Return a formatted string',-1),
            'str_ireplace'  =>array('str_ireplace','NA','Case-insensitive version of str_replace',3),
            'str_pad'		=>array('str_pad','NA','Pad a string to a certain length with another string',2,3,4),
            'str_repeat'	=>array('str_repeat','NA','Repeat a string',2),
            'str_replace'	=>array('str_replace','NA','Replace all occurrences of the search string with the replacement string',3),
            'str_rot13'		=>array('str_rot13','NA','Perform the rot13 transform on a string',1),
            'str_shuffle'	=>array('str_shuffle','NA','Randomly shuffles a string',1),
            'str_word_count'	=>array('str_word_count','NA','Return information about words used in a string',1),
            'strcasecmp'	=>array('strcasecmp','NA','Binary safe case-insensitive string comparison',2),
            'strcmp'		=>array('strcmp','NA','Binary safe string comparison',2),
            'strcoll'		=>array('strcoll','NA','Locale based string comparison',2),
            'strcspn'		=>array('strcspn','NA','Find length of initial segment not matching mask',2,3,4),
            'strip_tags'	=>array('strip_tags','NA','Strip HTML and PHP tags from a string',1,2),
            'stripcslashes'	=>array('stripcslashes','NA','Un-quote string quoted with addcslashes',1),
            'stripos'		=>array('stripos','NA','Find position of first occurrence of a case-insensitive string',2,3),
            'stripslashes'	=>array('stripslashes','NA','Un-quotes a quoted string',1),
            'stristr'		=>array('stristr','NA','Case-insensitive strstr',2,3),
            'strlen'		=>array('strlen','NA','Get string length',1),
            'strnatcasecmp'	=>array('strnatcasecmp','NA','Case insensitive string comparisons using a "natural order" algorithm',2),
            'strnatcmp'		=>array('strnatcmp','NA','String comparisons using a "natural order" algorithm',2),
            'strncasecmp'	=>array('strncasecmp','NA','Binary safe case-insensitive string comparison of the first n characters',3),
            'strncmp'		=>array('strncmp','NA','Binary safe string comparison of the first n characters',3),
            'strpbrk'		=>array('strpbrk','NA','Search a string for any of a set of characters',2),
            'strpos'		=>array('strpos','NA','Find position of first occurrence of a string',2,3),
            'strrchr'		=>array('strrchr','NA','Find the last occurrence of a character in a string',2),
            'strrev'		=>array('strrev','NA','Reverse a string',1),
            'strripos'		=>array('strripos','NA','Find position of last occurrence of a case-insensitive string in a string',2,3),
            'strrpos'		=>array('strrpos','NA','Find the position of the last occurrence of a substring in a string',2,3),
            'strspn'        =>array('strspn','NA','Finds the length of the initial segment of a string consisting entirely of characters contained within a given mask.',2,3,4),
            'strstr'		=>array('strstr','NA','Find first occurrence of a string',2,3),
            'strtolower'	=>array('strtolower','toLowerCase','Make a string lowercase',1),
            'strtoupper'	=>array('strtoupper','toUpperCase','Make a string uppercase',1),
            'strtr'			=>array('strtr','NA','Translate characters or replace substrings',3),
            'substr_compare'=>array('substr_compare','NA','Binary safe comparison of two strings from an offset, up to length characters',3,4,5),
            'substr_count'	=>array('substr_count','NA','Count the number of substring occurrences',2,3,4),
            'substr_replace'=>array('substr_replace','NA','Replace text within a portion of a string',3,4),
            'substr'		=>array('substr','NA','Return part of a string',2,3),
            'ucfirst'		=>array('ucfirst','NA','Make a string\'s first character uppercase',1),
            'ucwords'		=>array('ucwords','NA','Uppercase the first character of each word in a string',1),

            'stddev'        =>array('stats_standard_deviation','NA','Returns the standard deviation',-1),

            // Locally declared functions
            'if'            => array('exprmgr_if','NA','Excel-style if(test,result_if_true,result_if_false)',3),
            'list'          => array('exprmgr_list','NA','Return comma-separated list of values',-1),
        );

        $this->amVars = array();
        $this->amNamedConstants = array();

    }

    /**
     * Add an error to the error log
     *
     * @param <type> $errMsg
     * @param <type> $token
     */
    private function AddError($errMsg, $token)
    {
        $this->errs[] = array($errMsg, $token);
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
        $this->namedConstantsUsed = array();

        if ($this->HasSyntaxErrors()) {
            return false;
        }
        else if ($this->EvaluateExpressions())
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
            case 'STRING':
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
                        $result = array($this->amVars[$token[0]]['codeValue'],$token[1],'NUMBER');
                        $this->StackPush($result);
                        return true;
                    }
                    else if ($this->isValidNamedConstant($token[0]))
                    {
                        $this->namedConstantsUsed[] = $token[0];
                        $result = array($this->amNamedConstants[$token[0]],$token[1],'NUMBER');
                        $this->StackPush($result);
                        return true;
                    }
                    else
                    {
                        $this->AddError("Undefined variable or named constant", $token);
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
            if ($this->isValidVariable($token1[0]) and $token2[2] == 'ASSIGN')
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
            else if ($this->isValidNamedConstant($token1[0]) and $token2[2] == 'ASSIGN')
            {
                $this->AddError('The value of named constants cannot be changed', $token1);
                return false;
            }
            else if ($token2[2] == 'ASSIGN')
            {
                $this->AddError('Only variables can be assigned values', $token1);
                return false;
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
            else if ($token[2] == 'COMMA')
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

    /**
     * Returns array of all JavaScript-equivalent variable names used when parsing a string via sProcessStringContainingExpressions
     * @return <type>
     */
    public function GetAllJsVarsUsed()
    {
        $names = array_unique($this->allVarsUsed);
        if (is_null($names)) {
            return null;
        }
        $jsNames = array();
        foreach ($names as $name)
        {
            if (isset($this->amVars[$name]['jsName']))
            {
                $jsNames[] = $this->amVars[$name]['jsName'];
            }
        }
        return array_unique($jsNames);
    }

    /**
     * Returns array of all named constants used when parsing a string via sProcessStringContainingExpressions
     * @return <type>
     */
    
    public function GetAllNamedConstantsUsed()
    {
        return array_unique($this->allNamedConstantsUsed);
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
     * Return an array of human-readable errors (message, offending token, offset of offending token within equation)
     * @return array
     */
    public function GetReadableErrors()
    {
        // Try to color code the equation
        // Surround whole message with a span, so can add ToolTip of generic error messages
        // Surround each identifiable error with a span so can color code it and attach its own ToolTip (e.g. unknown function or variable name)
        if (count($this->errs) == 0) {
            return '';
        }
        usort($this->errs,"cmpErrorTokens");    // sort errors in order of occurence in string
        $parts = array();
        $curpos = 0;
        $generalErrs = array();
        foreach ($this->errs as $err)
        {
            $token = $err[1];
            if (is_null($token)) {
                if (strlen($err[0]) > 0) {
                    $generalErrs[] = $err[0];
                }
                continue;
            }
            $pos = $token[1];
            if ($token[2] == 'STRING')
            {
                $tok = "'" . $token[0] . '"';
            }
            else
            {
                $tok =$token[0];
            }
            if (!is_numeric($pos)) {
                if (strlen($err[0]) > 0) {
                    $generalErrs[] = $err[0];
                }
                continue;
            }
            $parts[]= array(
                'OkString' => substr($this->expr,$curpos,($pos-$curpos)),
                'BadString' => $tok,
                'msg' => $err[0]
                );
            $curpos = $pos + strlen($tok);
        }
        if ($curpos < strlen($this->expr)) {
            $parts[] = array(
                'OkString' => substr($this->expr, $curpos, strlen($this->expr) - $curpos),
                'BadString' => '',
                'msg' => ''
            );
        }
        $msg = '';
        $errSpecificStyle= "style='border-style: solid; border-width: 2px; border-color: red;'";
        $errGeneralStyle = "style='background-color: yellow;'";
        foreach ($parts as $part)
        {
            $msg .= $part['OkString'];
            if (isset($part['BadString']) && strlen($part['BadString']) > 0)
            {
                if (strlen($part['msg']) > 0) {
                    $msg .= "<span title='" . $part['msg'] . "' " . $errSpecificStyle . ">" . $part['BadString'] . "</span>";
                }
                else {
                    $msg .= "<span " . $errSpecificStyle . ">" . $part['BadString'] . "</span>";
                }
            }
        }
        $extraErrs = implode("; ", $generalErrs);
        $msg = "<span title='" . $extraErrs . "' " . $errGeneralStyle . ">" . $msg . "</span>";
        return $msg;
    }

    public function GetJsVarsUsed()
    {
        $names = array_unique($this->varsUsed);
        if (is_null($names)) {
            return null;
        }
        $jsNames = array();
        foreach ($names as $name)
        {
            if (isset($this->amVars[$name]['jsName']))
            {
                $jsNames[] = $this->amVars[$name]['jsName'];
            }
        }
        return array_unique($jsNames);
    }
    
    /**
     * Return array of list of named constants used in the equation
     * @return <type> 
     */

    public function GetNamedConstantsUsed()
    {
        return array_unique($this->namedConstantsUsed);
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
                        if (!($this->isValidVariable($token[0]) or $this->isValidNamedConstant($token[0])))
                        {
                            $this->AddError("Undefined variable or named constant", $token);
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
     * Return true if the named constant name is registered
     * @param <type> $name
     * @return boolean
     */
    private function isValidNamedConstant($name)
    {
        return array_key_exists($name,$this->amNamedConstants);
    }

    /**
     * Return true if the variable name is registered
     * @param <type> $name
     * @return boolean
     */
    private function isValidVariable($name)
    {
        return array_key_exists($name,$this->amVars);
    }
    
    /**
     * Process an expression and return its boolean value
     * @param <type> $expr
     * @return <type>
     */
    public function ProcessBooleanExpression($expr)
    {
        $status = $this->Evaluate($expr);
        if (!$status) {
            return true;    // if there are errors in the expression, show it instead of hiding it?
        }
        $result = $this->GetResult();
        if (is_null($result)) {
            return true;    // if there are errors in the expression, show it instead of hiding it?
        }
        return (boolean) $result;
    }
    
    /**
     * Process a full string, containing multiple expressions delimited by {}, return a consolidated string
     * @param <type> $src 
     */

    public function sProcessStringContainingExpressions($src)
    {
        // tokenize string by the {} pattern, properly dealing with strings in quotations, and escaped curly brace values
        $stringParts = $this->asSplitStringOnExpressions($src);

        $resolvedParts = array();
        $this->allVarsUsed = array();
        $this->allNamedConstantsUsed = array();

        foreach ($stringParts as $stringPart)
        {
            if ($stringPart[2] == 'STRING') {
                $resolvedParts[] =  $stringPart[0];
            }
            else {
                if ($this->Evaluate(substr($stringPart[0],1,-1)))
                {
                    $resolvedParts[] = $this->GetResult();
                    $this->allVarsUsed = array_merge($this->allVarsUsed,$this->GetVarsUsed());
                    $this->allNamedConstantsUsed = array_merge($this->allNamedConstantsUsed, $this->GetNamedConstantsUsed());
                }
                else 
                {
                    // show original and errors in-line
                    $resolvedParts[] = $this->GetReadableErrors();
                }
            }
        }
        $result = implode('',$this->flatten_array($resolvedParts));
        return $result;    // recurse in case there are nested ones, avoiding infinite loops?
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

        if (function_exists($funcName)) {
            $numArgsAllowed = array_slice($func, 3);
            $argsPassed = is_array($params) ? count($params) : 0;

            // for unlimited #  parameters
            try
            {
                if (in_array(-1, $numArgsAllowed)) {
                    if ($argsPassed == 0)
                    {
                        $this->AddError("Function must have at least one argument", $funcNameToken);
                        return false;
                    }
                    $result = $funcName($params);

                // Call  function with the params passed
                } elseif (in_array($argsPassed, $numArgsAllowed)) {

                    switch ($argsPassed) {
                    case 0:
                        $result = $funcName();
                        break;
                    case 1:
                        $result = $funcName($params[0]);
                        break;
                    case 2:
                        $result = $funcName($params[0], $params[1]);
                        break;
                    case 3:
                        $result = $funcName($params[0], $params[1], $params[2]);
                        break;
                    case 4:
                        $result = $funcName($params[0], $params[1], $params[2], $params[3]);
                        break;
                    default:
                        $this->AddError("Unsupported number of arguments: " . $argsPassed, $funNameToken);
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
     * Add list of allowable NamedConstant names within the equation
     * $varnames is an array of key to value mappings like this:
     * 'myvar' => value
     * where value is optional (e.g. can be blank), and can be any scalar type (e.g. string, number, but not array)
     * the system will use the values as  fast lookup when doing calculations, but if it needs to set values, it will call
     * the interface function to set the values by name
     *
     * @param array $varnames
     */
    public function RegisterNamedConstantsUsingMerge(array $varnames) {
        $this->amNamedConstants = array_merge($this->amNamedConstants, $varnames);
    }

    public function RegisterNamedConstantsUsingReplace(array $varnames) {
        $this->amNamedConstants = array_merge(array(), $varnames);
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
                $token[2] = 'STRING';
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
                case 'STRING':
                    $this->stack[] = array(1,$token[1],'STRING');
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
                        if ($this->asTokenType[$i] == 'STRING')
                        {
                            // remove outside quotes
                            $unquotedToken = stripslashes(substr($token,1,-1));
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
"this is a string that contains {something in curly braces)"
This example has escaped curly braces like \{this is not an equation\}
Should the parser check for unmatched { opening curly braces?
What about for unmatched } closing curly braces?
{name}, you said that you are {age} years old, and that you have {numKids} {if((numKids==1),'child','children')} and {numPets} {if((numPets==1),'pet','pets')} running around the house. So, you have {numKids + numPets} wild {if((numKids + numPets ==1),'beast','beasts')} to chase around every day.
Since you have more {if((INSERT61764X1X3 > INSERT61764X1X4),'children','pets')} than you do {if((INSERT61764X1X3 > INSERT61764X1X4),'pets','children')}, do you feel that the {if((INSERT61764X1X3 > INSERT61764X1X4),'pets','children')} are at a disadvantage?
EOD;

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
     * Unit test the Evaluator, allowing for passing in of extra functions, variables, and tests
     * @param array $extraFunctions
     * @param array $extraVars
     * @param <type> $extraTests
     */
    
    static function UnitTestEvaluator(array $extraFunctions=array(), array $extraVars=array(), $extraTests='1~1')
    {
        // Some test cases for Evaluator
        $vars = array(
'one' => array('codeValue'=>1, 'jsName'=>'java_one'),
'two' => array('codeValue'=>2, 'jsName'=>'java_two'),
'three' => array('codeValue'=>3, 'jsName'=>'java_three'),
'four' => array('codeValue'=>4, 'jsName'=>'java_four'),
'five' => array('codeValue'=>5, 'jsName'=>'java_five'),
'six' => array('codeValue'=>6, 'jsName'=>'java_six'),
'seven' => array('codeValue'=>7, 'jsName'=>'java_seven'),
'eight' => array('codeValue'=>8, 'jsName'=>'java_eight'),
'nine' => array('codeValue'=>9, 'jsName'=>'java_nine'),
'ten' => array('codeValue'=>10, 'jsName'=>'java_ten'),
'eleven' => array('codeValue'=>11, 'jsName'=>'java_eleven'),
'twelve' => array('codeValue'=>12, 'jsName'=>'java_twelve'),
'half' => array('codeValue'=>.5, 'jsName'=>'java_half'),
'hi' => array('codeValue'=>'there', 'jsName'=>'java_hi'),
'hello' => array('codeValue'=>"Tom", 'jsName'=>'java_hello'),
'a' => array('codeValue'=>0, 'jsName'=>'java_a'),
'b' => array('codeValue'=>0, 'jsName'=>'java_b'),
'c' => array('codeValue'=>0, 'jsName'=>'java_c'),
'd' => array('codeValue'=>0, 'jsName'=>'java_d'),
        );

        $namedConstant = array(
            'ADMINEMAIL'					=>'value for {ADMINEMAIL}',
            'ADMINNAME'						=>'value for {ADMINNAME}',
            'AID'							=>'value for {AID}',
            'ANSWERSCLEARED'				=>'value for {ANSWERSCLEARED}',
            'ANSWER'						=>'value for {ANSWER}',
            'ASSESSMENTS'					=>'value for {ASSESSMENTS}',
            'ASSESSMENT_CURRENT_TOTAL'		=>'value for {ASSESSMENT_CURRENT_TOTAL}',
            'ASSESSMENT_HEADING'			=>'value for {ASSESSMENT_HEADING}',
            'CHECKJAVASCRIPT'				=>'value for {CHECKJAVASCRIPT}',
            'CLEARALL'						=>'value for {CLEARALL}',
            'CLOSEWINDOW'					=>'value for {CLOSEWINDOW}',
            'COMPLETED'						=>'value for {COMPLETED}',
            'DATESTAMP'						=>'value for {DATESTAMP}',
            'EMAILCOUNT'					=>'value for {EMAILCOUNT}',
            'EMAIL'							=>'value for {EMAIL}',
            'EXPIRY'						=>'value for {EXPIRY}',
            'FIRSTNAME'						=>'value for {FIRSTNAME}',
            'GID'							=>'value for {GID}',
            'GROUPDESCRIPTION'				=>'value for {GROUPDESCRIPTION}',
            'GROUPNAME'						=>'value for {GROUPNAME}',
            'INSERTANS:123X45X67'			=>'value for {INSERTANS:123X45X67}',
            'INSERTANS:123X45X67ber'		=>'value for {INSERTANS:123X45X67ber}',
            'INSERTANS:123X45X67ber_01a'	=>'value for {INSERTANS:123X45X67ber_01a}',
            'LANGUAGECHANGER'				=>'value for {LANGUAGECHANGER}',
            'LANGUAGE'						=>'value for {LANGUAGE}',
            'LANG'							=>'value for {LANG}',
            'LASTNAME'						=>'value for {LASTNAME}',
            'LOADERROR'						=>'value for {LOADERROR}',
            'LOADFORM'						=>'value for {LOADFORM}',
            'LOADHEADING'					=>'value for {LOADHEADING}',
            'LOADMESSAGE'					=>'value for {LOADMESSAGE}',
            'NAME'							=>'value for {NAME}',
            'NAVIGATOR'						=>'value for {NAVIGATOR}',
            'NOSURVEYID'					=>'value for {NOSURVEYID}',
            'NOTEMPTY'						=>'value for {NOTEMPTY}',
            'NULL'							=>'value for {NULL}',
            'NUMBEROFQUESTIONS'				=>'value for {NUMBEROFQUESTIONS}',
            'OPTOUTURL'						=>'value for {OPTOUTURL}',
            'PASSTHRULABEL'					=>'value for {PASSTHRULABEL}',
            'PASSTHRUVALUE'					=>'value for {PASSTHRUVALUE}',
            'PERCENTCOMPLETE'				=>'value for {PERCENTCOMPLETE}',
            'PERC'							=>'value for {PERC}',
            'PRIVACYMESSAGE'				=>'value for {PRIVACYMESSAGE}',
            'PRIVACY'						=>'value for {PRIVACY}',
            'QID'							=>'value for {QID}',
            'QUESTIONHELPPLAINTEXT'			=>'value for {QUESTIONHELPPLAINTEXT}',
            'QUESTIONHELP'					=>'value for {QUESTIONHELP}',
            'QUESTION_CLASS'				=>'value for {QUESTION_CLASS}',
            'QUESTION_CODE'					=>'value for {QUESTION_CODE}',
            'QUESTION_ESSENTIALS'			=>'value for {QUESTION_ESSENTIALS}',
            'QUESTION_FILE_VALID_MESSAGE'	=>'value for {QUESTION_FILE_VALID_MESSAGE}',
            'QUESTION_HELP'					=>'value for {QUESTION_HELP}',
            'QUESTION_INPUT_ERROR_CLASS'	=>'value for {QUESTION_INPUT_ERROR_CLASS}',
            'QUESTION_MANDATORY'			=>'value for {QUESTION_MANDATORY}',
            'QUESTION_MAN_CLASS'			=>'value for {QUESTION_MAN_CLASS}',
            'QUESTION_MAN_MESSAGE'			=>'value for {QUESTION_MAN_MESSAGE}',
            'QUESTION_NUMBER'				=>'value for {QUESTION_NUMBER}',
            'QUESTION_TEXT'					=>'value for {QUESTION_TEXT}',
            'QUESTION_VALID_MESSAGE'		=>'value for {QUESTION_VALID_MESSAGE}',
            'QUESTION'						=>'value for {QUESTION}',
            'REGISTERERROR'					=>'value for {REGISTERERROR}',
            'REGISTERFORM'					=>'value for {REGISTERFORM}',
            'REGISTERMESSAGE1'				=>'value for {REGISTERMESSAGE1}',
            'REGISTERMESSAGE2'				=>'value for {REGISTERMESSAGE2}',
            'RESTART'						=>'value for {RESTART}',
            'RETURNTOSURVEY'				=>'value for {RETURNTOSURVEY}',
            'SAVEALERT'						=>'value for {SAVEALERT}',
            'SAVEDID'						=>'value for {SAVEDID}',
            'SAVEERROR'						=>'value for {SAVEERROR}',
            'SAVEFORM'						=>'value for {SAVEFORM}',
            'SAVEHEADING'					=>'value for {SAVEHEADING}',
            'SAVEMESSAGE'					=>'value for {SAVEMESSAGE}',
            'SAVE'							=>'value for {SAVE}',
            'SGQ'							=>'value for {SGQ}',
            'SID'							=>'value for {SID}',
            'SITENAME'						=>'value for {SITENAME}',
            'SUBMITBUTTON'					=>'value for {SUBMITBUTTON}',
            'SUBMITCOMPLETE'				=>'value for {SUBMITCOMPLETE}',
            'SUBMITREVIEW'					=>'value for {SUBMITREVIEW}',
            'SURVEYCONTACT'					=>'value for {SURVEYCONTACT}',
            'SURVEYDESCRIPTION'				=>'value for {SURVEYDESCRIPTION}',
            'SURVEYFORMAT'					=>'value for {SURVEYFORMAT}',
            'SURVEYLANGAGE'					=>'value for {SURVEYLANGAGE}',
            'SURVEYLISTHEADING'				=>'value for {SURVEYLISTHEADING}',
            'SURVEYLIST'					=>'value for {SURVEYLIST}',
            'SURVEYNAME'					=>'value for {SURVEYNAME}',
            'SURVEYURL'						=>'value for {SURVEYURL}',
            'TEMPLATECSS'					=>'value for {TEMPLATECSS}',
            'TEMPLATEURL'					=>'value for {TEMPLATEURL}',
            'TEXT'							=>'value for {TEXT}',
            'THEREAREXQUESTIONS'			=>'value for {THEREAREXQUESTIONS}',
            'TIME'							=>'value for {TIME}',
            'TOKEN:EMAIL'					=>'value for {TOKEN:EMAIL}',
            'TOKEN:FIRSTNAME'				=>'value for {TOKEN:FIRSTNAME}',
            'TOKEN:LASTNAME'				=>'value for {TOKEN:LASTNAME}',
            'TOKEN:XXX'						=>'value for {TOKEN:XXX}',
            'TOKENCOUNT'					=>'value for {TOKENCOUNT}',
            'TOKEN_COUNTER'					=>'value for {TOKEN_COUNTER}',
            'TOKEN'							=>'value for {TOKEN}',
            'URL'							=>'value for {URL}',
            'WELCOME'						=>'value for {WELCOME}',
            // also include SGQA values and read-only variable attributes
            '12X34X56'  =>5,
            '12X3X5lab1_ber'    =>10,
            'q5pointChoice.code'    =>5,
            'q5pointChoice.value'   => 'Father',
            'qArrayNumbers.ls1.min.code'    => 7,
            'qArrayNumbers.ls1.min.value' => 'I love LimeSurvey',
            '12X3X5lab1_ber#2'  => 15,
        );

        // Syntax for $tests is~
        // expectedResult~expression
        // if the expected result is an error, use NULL for the expected result
        $tests  = <<<EOD
50~12X34X56 * 12X3X5lab1_ber
3~a=three
3~c=a
12~c*=four
15~c+=a
5~c/=a
-1~c-=six
2~max(one,two)
5~max(one,two,three,four,five)
1024~max(one,(two*three),pow(four,five),six)
1~min(one,two,three,four,five)
27~pow(3,3)
5~hypot(three,four)
0~0
24~one * two * three * four
-4~five - four - three - two
0~two * three - two - two - two
4~two * three - two
3~floor(pi())
1~pi() == pi() * 2 - pi()
1~sin(pi()/2)
1~sin(0.5 * pi())
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
0~acos(pi()/2)
0~asin(pi()/2)
10~ceil(9.1)
9~floor(9.9)
32767~getrandmax()
0~(a=rand())-a
1~ceil((rand()+1) / getrandmax())
15~sum(one,two,three,four,five)
5~intval(5.7)
1~is_float(pi())
0~is_float(5)
1~is_numeric(five)
0~is_numeric(hi)
1~is_string(hi)
2.4~(one  * two) + (three * four) / (five * six)
1~(one * (two + (three - four) + five) / six)
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
11~eleven
144~twelve * twelve
4~if(5 > 7,2,4)
there~if((one > two),'hi','there')
64~if((one < two),pow(2,6),pow(6,2))
1, 2, 3, 4, 5~list(one,two,three,min(four,five,six),max(three,four,five))
11, 12~list(eleven,twelve)
value for {INSERTANS:123X45X67}~INSERTANS:123X45X67
value for {QID}~QID
value for {ASSESSMENT_HEADING}~ASSESSMENT_HEADING
value for {TOKEN:FIRSTNAME}~TOKEN:FIRSTNAME
value for {THEREAREXQUESTIONS}~THEREAREXQUESTIONS
5~q5pointChoice.code
Father~q5pointChoice.value
7~qArrayNumbers.ls1.min.code
I love LimeSurvey~qArrayNumbers.ls1.min.value
15~12X3X5lab1_ber#2
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
EOD;
        
        $em = new ExpressionManager();
        $em->RegisterVarnamesUsingMerge($vars);
        $em->RegisterNamedConstantsUsingMerge($namedConstant);

        if (is_array($extraVars) and count($extraVars) > 0)
        {
            $em->RegisterVarnamesUsingMerge($extraVars);
        }
        if (is_array($extraFunctions) and count($extraFunctions) > 0)
        {
            $em->RegisterFunctions($extraFunctions);
        }
        if (is_string($extraTests))
        {
            $tests .= "\n" . $extraTests;
        }

        print '<table border="1"><tr><th>Expression</th><th>Result</th><th>Expected</th><th>VarNames Used</th><th>Javascript VarNames</th><th>Named Constants Used</th><th>Errors</th></tr>';
        foreach(explode("\n",$tests)as $test)
        {
            $values = explode("~",$test);
            $expectedResult = array_shift($values);
            $expr = implode("~",$values);
            $resultStatus = 'ok';
            print '<tr><td>' . $expr . "</td>\n";
            $status = $em->Evaluate($expr);
            $result = $em->GetResult();
            $valToShow = $result;
            if (is_null($result)) {
                $valToShow = "NULL";
            }
            print '<td>' . $valToShow . "</td>\n";
            if ($valToShow != $expectedResult)
            {
                $resultStatus = 'error';
            }
            print "<td class='" . $resultStatus . "'>" . $expectedResult . "</td>\n";
            $varsUsed = $em->GetVarsUsed();
            if (is_array($varsUsed) and count($varsUsed) > 0) {
                print '<td>' . implode(', ', $varsUsed) . "</td>\n";
            }
            else {
                print "<td>&nbsp;</td>\n";
            }
            $jsVarsUsed = $em->GetJsVarsUsed();
            if (is_array($jsVarsUsed) and count($jsVarsUsed) > 0) {
                print '<td>' . implode(', ', $jsVarsUsed) . "</td>\n";
            }
            else {
                print "<td>&nbsp;</td>\n";
            }
            $namedConstantsUsed = $em->GetNamedConstantsUsed();
            if (is_array($namedConstantsUsed) and count($namedConstantsUsed) > 0) {
                print '<td>' . implode(', ', $namedConstantsUsed) . "</td>\n";
            }
            else {
                print "<td>&nbsp;</td>\n";
            }
            $errString = $em->GetReadableErrors();
            if (strlen($errString) > 0) {
                print "<td>" . $errString . "</td>\n";
            }
            else {
                print "<td>&nbsp;</td>\n";
            }
            print '</tr>';
        }
        print '</table>';
    }
}

/*
 * Extra Functions can  go here.
 * TODO  Find good way to inlcude these extra functions externally.
 * Tried via ExpressionManagerFunctions, but they weren't properly included
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

function exprmgr_list($args)
{
    return implode(", ",$args);
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

?>
