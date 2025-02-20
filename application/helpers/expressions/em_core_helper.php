<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
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
 * Description of ExpressionManager
 * (1) Does safe evaluation of PHP expressions.  Only registered Functions, and known Variables are allowed.
 *   (a) Functions include any math, string processing, conditional, formatting, etc. functions
 * (2) This class replaces LimeSurvey's <= 1.91+  process of resolving strings that contain LimeReplacementFields
 *   (a) String is split by expressions (by curly braces, but safely supporting strings and escaped curly braces)
 *   (b) Expressions (things surrounded by curly braces) are evaluated - thereby doing LimeReplacementField substitution and/or more complex calculations
 *   (c) Non-expressions are left intact
 *   (d) The array of stringParts are re-joined to create the desired final string.
 * (3) The core of ExpressionScript Engine is a Recursive Descent Parser (RDP), based off of one build via JavaCC by TMSWhite in 1999.
 *   (a) Functions that start with RDP_ should not be touched unless you really understand compiler design.
 *
 * @author LimeSurvey Team (limesurvey.org)
 * @author Thomas M. White (TMSWhite)
 */

class ExpressionManager
{
    // These are the allowable variable suffixes for variables - each represents an attribute of a variable that can be updated on same page
    private $aRDP_regexpVariableAttribute = array(
        'code',
        'NAOK',
        'relevanceStatus',
        'shown',
        'valueNAOK',
        'value',
    );
    /* var string[] allowable static suffixes for variables - each represents an attribute of a variable that can not be updated on same page
     * @see LimeExpressionManager->knownVars definition
     */
    private $aRDP_regexpStaticAttribute = array(
        'qid',
        'gid',
        'question',
        'sgqa',
        'type',
        'relevance',
        'grelevance',
        'qseq',
        'gseq',
        'jsName',
        'jsName_on',
        'mandatory',
        'rowdivid',
    );
    // These three variables are effectively static once constructed
    private $RDP_ExpressionRegex;
    private $RDP_TokenType;
    private $RDP_TokenizerRegex;
    private $RDP_CategorizeTokensRegex;
    private $RDP_ValidFunctions; // names and # params of valid functions

    // Thes variables are used while  processing the equation
    private $RDP_expr; // the source expression
    private $RDP_tokens; // the list of generated tokens
    private $RDP_count; // total number of $RDP_tokens
    private $RDP_pos; // position within the $token array while processing equation
    /** @var array[] information about current errors : array with string, $token (EM internal array). Reset in RDP_Evaluate (and only in RDP_Evaluate) */
    private $RDP_errs;
    /** @var array[] information about current warnings : array with string, $token (EM internal array) and optional link Reset in RDP_Evaluate or manually */
    private $RDP_warnings = array();
    private $RDP_onlyparse;
    private $RDP_stack; // stack of intermediate results
    private $RDP_result; // final result of evaluating the expression;
    private $RDP_evalStatus; // true if $RDP_result is a valid result, and  there are no serious errors
    private $varsUsed; // list of variables referenced in the equation
    public $resetErrorsAndWarningsOnEachPart = true;

    // These  variables are only used by sProcessStringContainingExpressions
    private $allVarsUsed; // full list of variables used within the string, even if contains multiple expressions
    private $prettyPrintSource; // HTML formatted output of running sProcessStringContainingExpressions
    private $substitutionNum; // Keeps track of number of substitions performed XXX

    /**
     * @var array
     */
    private $substitutionInfo; // array of JavaScripts to managing dynamic substitution
    private $jsExpression; // caches computation of JavaScript equivalent for an Expression

    private $questionSeq; // sequence order of question - so can detect if try to use variable before it is set
    private $groupSeq; // sequence order of groups - so can detect if try to use variable before it is set
    private $surveyMode = 'group';

    // The following are only needed to enable click on variable names within pretty print and open new window to edit them
    private $sid = null; // the survey ID
    private $hyperlinkSyntaxHighlighting = true; // TODO - change this back to false
    private $sgqaNaming = false;

    function __construct()
    {
        /* EM core string must be in adminlang : keep the actual for resetting at end. See bug #12208 */
        /**
         * @var string|null $baseLang set the previous language if need to be set
         */
        $baseLang = null;
        if (Yii::app() instanceof CWebApplication && Yii::app()->session['adminlang']) {
            $baseLang = Yii::app()->getLanguage();
            Yii::app()->setLanguage(Yii::app()->session['adminlang']);
        }
        // List of token-matching regular expressions
        // Note, this is effectively a Lexer using Regular Expressions.  Don't change this unless you understand compiler design.
        $RDP_regex_dq_string = '(?<!\\\\)".*?(?<!\\\\)"';
        $RDP_regex_sq_string = '(?<!\\\\)\'.*?(?<!\\\\)\'';
        $RDP_regex_whitespace = '\s+';
        $RDP_regex_lparen = '\(';
        $RDP_regex_rparen = '\)';
        $RDP_regex_comma = ',';
        $RDP_regex_not = '!';
        $RDP_regex_inc_dec = '\+\+|--';
        $RDP_regex_binary = '[+*/-]';
        $RDP_regex_compare = '<=|<|>=|>|==|!=|\ble\b|\blt\b|\bge\b|\bgt\b|\beq\b|\bne\b';
        $RDP_regex_assign = '='; // '=|\+=|-=|\*=|/=';
        $RDP_regex_sgqa = '(?:INSERTANS:)?[0-9]+X[0-9]+X[0-9]+[A-Z0-9_]*\#?[01]?(?:\.(?:[a-zA-Z0-9_]*))?';
        $RDP_regex_word = '(?:TOKEN:)?(?:[A-Z][A-Z0-9_]*)?(?:\.(?:[A-Z][A-Z0-9_]*))*(?:\.(?:[a-zA-Z0-9_]*))?';
        $RDP_regex_number = '[0-9]+\.?[0-9]*|\.[0-9]+';
        $RDP_regex_andor = '\band\b|\bor\b|&&|\|\|';
        $RDP_regex_lcb = '{';
        $RDP_regex_rcb = '}';
        $RDP_regex_sq = '\'';
        $RDP_regex_dq = '"';
        $RDP_regex_bs = '\\\\';

        $RDP_StringSplitRegex = array(
            $RDP_regex_lcb,
            $RDP_regex_rcb,
            $RDP_regex_sq,
            $RDP_regex_dq,
            $RDP_regex_bs,
        );

        // RDP_ExpressionRegex is the regular expression that splits apart strings that contain curly braces in order to find expressions
        $this->RDP_ExpressionRegex = '#(' . implode('|', $RDP_StringSplitRegex) . ')#i';

        // asTokenRegex and RDP_TokenType must be kept in sync  (same number and order)
        $RDP_TokenRegex = array(
            $RDP_regex_dq_string,
            $RDP_regex_sq_string,
            $RDP_regex_whitespace,
            $RDP_regex_lparen,
            $RDP_regex_rparen,
            $RDP_regex_comma,
            $RDP_regex_andor,
            $RDP_regex_compare,
            $RDP_regex_sgqa,
            $RDP_regex_word,
            $RDP_regex_number,
            $RDP_regex_not,
            $RDP_regex_inc_dec,
            $RDP_regex_assign,
            $RDP_regex_binary,
            );

        $this->RDP_TokenType = array(
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

        // $RDP_TokenizerRegex - a single regex used to split and equation into tokens
        $this->RDP_TokenizerRegex = '#(' . implode('|', $RDP_TokenRegex) . ')#i';

        // $RDP_CategorizeTokensRegex - an array of patterns so can categorize the type of token found - would be nice if could get this from preg_split
        // Adding ability to capture 'OTHER' type, which indicates an error - unsupported syntax element
        $this->RDP_CategorizeTokensRegex = preg_replace("#^(.*)$#", "#^$1$#i", $RDP_TokenRegex);
        $this->RDP_CategorizeTokensRegex[] = '/.+/';
        $this->RDP_TokenType[] = 'OTHER';
        // Each allowed function is a mapping from local name to external name + number of arguments
        // Functions can have a list of serveral allowable #s of arguments.
        // If the value is -1, the function must have a least one argument but can have an unlimited number of them
        // -2 means that at least one argument is required.  -3 means at least two arguments are required, etc.
        $this->RDP_ValidFunctions = array(
            'abs' => array('exprmgr_abs', 'Decimal.asNum.abs', gT('Absolute value'), 'number abs(number)', 'http://php.net/abs', 1),
            'acos' => array('acos', 'Decimal.asNum.acos', gT('Arc cosine'), 'number acos(number)', 'http://php.net/acos', 1),
            'addslashes' => array('addslashes', gT('addslashes'), 'Quote string with slashes', 'string addslashes(string)', 'http://php.net/addslashes', 1),
            'asin' => array('asin', 'Decimal.asNum.asin', gT('Arc sine'), 'number asin(number)', 'http://php.net/asin', 1),
            'atan' => array('atan', 'Decimal.asNum.atan', gT('Arc tangent'), 'number atan(number)', 'http://php.net/atan', 1),
            'atan2' => array('atan2', 'Decimal.asNum.atan2', gT('Arc tangent of two variables'), 'number atan2(number, number)', 'http://php.net/atan2', 2),
            'ceil' => array('ceil', 'Decimal.asNum.ceil', gT('Round fractions up'), 'number ceil(number)', 'http://php.net/ceil', 1),
            'checkdate' => array('exprmgr_checkdate', 'checkdate', gT('Returns true(1) if it is a valid date in gregorian calendar'), 'bool checkdate(month,day,year)', 'http://php.net/checkdate', 3),
            'cos' => array('cos', 'Decimal.asNum.cos', gT('Cosine'), 'number cos(number)', 'http://php.net/cos', 1),
            'count' => array('exprmgr_count', 'LEMcount', gT('Count the number of answered questions in the list'), 'number count(arg1, arg2, ... argN)', '', -1),
            'countif' => array('exprmgr_countif', 'LEMcountif', gT('Count the number of answered questions in the list equal the first argument'), 'number countif(matches, arg1, arg2, ... argN)', '', -2),
            'countifop' => array('exprmgr_countifop', 'LEMcountifop', gT('Count the number of answered questions in the list which pass the critiera (arg op value)'), 'number countifop(op, value, arg1, arg2, ... argN)', '', -3),
            'date' => array('exprmgr_date', 'date', gT('Format a local date/time'), 'string date(format [, timestamp=time()])', 'http://php.net/date', 1, 2),
            'exp' => array('exp', 'Decimal.asNum.exp', gT('Calculates the exponent of e'), 'number exp(number)', 'http://php.net/exp', 1),
            'fixnum' => array('exprmgr_fixnum', 'LEMfixnum', gT('Display numbers with comma as decimal separator, if needed'), 'string fixnum(number)', '', 1),
            'floatval' => array('floatval', 'LEMfloatval', gT('Get float value of a variable'), 'number floatval(number)', 'http://php.net/floatval', 1),
            'floor' => array('floor', 'Decimal.asNum.floor', gT('Round fractions down'), 'number floor(number)', 'http://php.net/floor', 1),
            'gmdate' => array('gmdate', 'gmdate', gT('Format a GMT date/time'), 'string gmdate(format [, timestamp=time()])', 'http://php.net/gmdate', 1, 2),
            'html_entity_decode' => array('html_entity_decode', 'html_entity_decode', gT('Convert all HTML entities to their applicable characters (always uses ENT_QUOTES and UTF-8)'), 'string html_entity_decode(string)', 'http://php.net/html-entity-decode', 1),
            'htmlentities' => array('htmlentities', 'htmlentities', gT('Convert all applicable characters to HTML entities (always uses ENT_QUOTES and UTF-8)'), 'string htmlentities(string)', 'http://php.net/htmlentities', 1),
            'htmlspecialchars' => array('expr_mgr_htmlspecialchars', 'htmlspecialchars', gT('Convert special characters to HTML entities (always uses ENT_QUOTES and UTF-8)'), 'string htmlspecialchars(string)', 'http://php.net/htmlspecialchars', 1),
            'htmlspecialchars_decode' => array('expr_mgr_htmlspecialchars_decode', 'htmlspecialchars_decode', gT('Convert special HTML entities back to characters (always uses ENT_QUOTES and UTF-8)'), 'string htmlspecialchars_decode(string)', 'http://php.net/htmlspecialchars-decode', 1),
            'idate' => array('idate', 'idate', gT('Format a local time/date as integer'), 'string idate(string [, timestamp=time()])', 'http://php.net/idate', 1, 2),
            'if' => array('exprmgr_if', 'LEMif', gT('Conditional processing'), 'if(test,result_if_true[,result_if_false = \'\'])', '', 2, 3),
            'implode' => array('exprmgr_implode', 'LEMimplode', gT('Join array elements with a string'), 'string implode(glue,arg1,arg2,...,argN)', 'http://php.net/implode', -2),
            'intval' => array('intval', 'LEMintval', gT('Get the integer value of a variable'), 'int intval(number [, base=10])', 'http://php.net/intval', 1, 2),
            'is_empty' => array('exprmgr_empty', 'LEMempty', gT('Determine whether a variable is considered to be empty'), 'bool is_empty(var)', 'http://php.net/empty', 1),
            'is_float' => array('is_float', 'LEMis_float', gT('Finds whether the type of a variable is float'), 'bool is_float(var)', 'http://php.net/is-float', 1),
            'is_int' => array('exprmgr_int', 'LEMis_int', gT('Check if the content of a variable is a valid integer value'), 'bool is_int(var)', 'http://php.net/is-int', 1),
            'is_nan' => array('is_nan', 'isNaN', gT('Finds whether a value is not a number'), 'bool is_nan(var)', 'http://php.net/is-nan', 1),
            'is_null' => array('is_null', 'LEMis_null', gT('Finds whether a variable is NULL'), 'bool is_null(var)', 'http://php.net/is-null', 1),
            'is_numeric' => array('is_numeric', 'LEMis_numeric', gT('Finds whether a variable is a number or a numeric string'), 'bool is_numeric(var)', 'http://php.net/is-numeric', 1),
            'is_string' => array('is_string', 'LEMis_string', gT('Find whether the type of a variable is string'), 'bool is_string(var)', 'http://php.net/is-string', 1),
            'join' => array('exprmgr_join', 'LEMjoin', gT('Join strings, return joined string.This function is an alias of implode("",argN)'), 'string join(arg1,arg2,...,argN)', '', -1),
            'list' => array('exprmgr_list', 'LEMlist', gT('Return comma-separated list of values'), 'string list(arg1, arg2, ... argN)', '', -2),
            'listifop' => array('exprmgr_listifop', 'LEMlistifop', gT('Return a list of retAttr from sgqa1...sgqaN which pass the criteria (cmpAttr op value)'), 'string listifop(cmpAttr, op, value, retAttr, glue, sgqa1, sgqa2,...,sgqaN)', '', -6),
            'log' => array('exprmgr_log', 'LEMlog', gT('The logarithm of number to base, if given, or the natural logarithm. '), 'number log(number,base=e)', 'http://php.net/log', -2),
            'ltrim' => array('ltrim', 'ltrim', gT('Strip whitespace (or other characters) from the beginning of a string'), 'string ltrim(string [, charlist])', 'http://php.net/ltrim', 1, 2),
            'max' => array('max', 'LEMmax', gT('Find highest value'), 'number|string max(arg1, arg2, ... argN)', 'http://php.net/max', -2),
            'min' => array('min', 'LEMmin', gT('Find lowest value'), 'number|string min(arg1, arg2, ... argN)', 'http://php.net/min', -2),
            'mktime' => array('exprmgr_mktime', 'mktime', gT('Get UNIX timestamp for a date (each of the 6 arguments are optional)'), 'number mktime([hour [, minute [, second [, month [, day [, year ]]]]]])', 'http://php.net/mktime', 0, 1, 2, 3, 4, 5, 6),
            'nl2br' => array('nl2br', 'nl2br', gT('Inserts HTML line breaks before all newlines in a string'), 'string nl2br(string)', 'http://php.net/nl2br', 1, 1),
            'number_format' => array('number_format', 'number_format', gT('Format a number with grouped thousands'), 'string number_format(number)', 'http://php.net/number-format', 1),
            'pi' => array('pi', 'LEMpi', gT('Get value of pi'), 'number pi()', '', 0),
            'pow' => array('pow', 'Decimal.asNum.pow', gT('Exponential expression'), 'number pow(base, exp)', 'http://php.net/pow', 2),
            'quoted_printable_decode' => array('quoted_printable_decode', 'quoted_printable_decode', gT('Convert a quoted-printable string to an 8 bit string'), 'string quoted_printable_decode(string)', 'http://php.net/quoted-printable-decode', 1),
            'quoted_printable_encode' => array('quoted_printable_encode', 'quoted_printable_encode', gT('Convert a 8 bit string to a quoted-printable string'), 'string quoted_printable_encode(string)', 'http://php.net/quoted-printable-encode', 1),
            'quotemeta' => array('quotemeta', 'quotemeta', gT('Quote meta characters'), 'string quotemeta(string)', 'http://php.net/quotemeta', 1),
            'rand' => array('rand', 'rand', gT('Generate a random integer'), 'int rand() OR int rand(min, max)', 'http://php.net/rand', 0, 2),
            'regexMatch' => array('exprmgr_regexMatch', 'LEMregexMatch', gT('Compare a string to a regular expression pattern'), 'bool regexMatch(pattern,input)', '', 2),
            'round' => array('round', 'round', gT('Rounds a number to an optional precision'), 'number round(val [, precision])', 'http://php.net/round', 1, 2),
            'rtrim' => array('rtrim', 'rtrim', gT('Strip whitespace (or other characters) from the end of a string'), 'string rtrim(string [, charlist])', 'http://php.net/rtrim', 1, 2),
            'sin' => array('sin', 'Decimal.asNum.sin', gT('Sine'), 'number sin(arg)', 'http://php.net/sin', 1),
            'sprintf' => array('sprintf', 'sprintf', gT('Return a formatted string'), 'string sprintf(format, arg1, arg2, ... argN)', 'http://php.net/sprintf', -2),
            'sqrt' => array('sqrt', 'Decimal.asNum.sqrt', gT('Square root'), 'number sqrt(arg)', 'http://php.net/sqrt', 1),
            'stddev' => array('exprmgr_stddev', 'LEMstddev', gT('Calculate the Sample Standard Deviation for the list of numbers'), 'number stddev(arg1, arg2, ... argN)', '', -2),
            'str_pad' => array('str_pad', 'str_pad', gT('Pad a string to a certain length with another string'), 'string str_pad(input, pad_length [, pad_string])', 'http://php.net/str-pad', 2, 3),
            'str_repeat' => array('str_repeat', 'str_repeat', gT('Repeat a string'), 'string str_repeat(input, multiplier)', 'http://php.net/str-repeat', 2),
            'str_replace' => array('str_replace', 'LEMstr_replace', gT('Replace all occurrences of the search string with the replacement string'), 'string str_replace(search,  replace, subject)', 'http://php.net/str-replace', 3),
            'strcasecmp' => array('strcasecmp', 'strcasecmp', gT('Binary safe case-insensitive string comparison'), 'int strcasecmp(str1, str2)', 'http://php.net/strcasecmp', 2),
            'strcmp' => array('strcmp', 'strcmp', gT('Binary safe string comparison'), 'int strcmp(str1, str2)', 'http://php.net/strcmp', 2),
            'strip_tags' => array('strip_tags', 'strip_tags', gT('Strip HTML and PHP tags from a string'), 'string strip_tags(str, allowable_tags)', 'http://php.net/strip-tags', 1, 2),
            'stripos' => array('exprmgr_stripos', 'stripos', gT('Find position of first occurrence of a case-insensitive string'), 'int stripos(haystack, needle [, offset=0])', 'http://php.net/stripos', 2, 3),
            'stripslashes' => array('stripslashes', 'stripslashes', gT('Un-quotes a quoted string'), 'string stripslashes(string)', 'http://php.net/stripslashes', 1),
            'stristr' => array('exprmgr_stristr', 'stristr', gT('Case-insensitive strstr'), 'string stristr(haystack, needle [, before_needle=false])', 'http://php.net/stristr', 2, 3),
            'strlen' => array('exprmgr_strlen', 'LEMstrlen', gT('Get string length'), 'int strlen(string)', 'http://php.net/strlen', 1),
            'strpos' => array('exprmgr_strpos', 'LEMstrpos', gT('Find position of first occurrence of a string'), 'int strpos(haystack, needle [ offset=0])', 'http://php.net/strpos', 2, 3),
            'strrev' => array('strrev', 'strrev', gT('Reverse a string'), 'string strrev(string)', 'http://php.net/strrev', 1),
            'strstr' => array('exprmgr_strstr', 'strstr', gT('Find first occurrence of a string'), 'string strstr(haystack, needle [, before_needle=false])', 'http://php.net/strstr', 2, 3),
            'strtolower' => array('exprmgr_strtolower', 'LEMstrtolower', gT('Make a string lowercase'), 'string strtolower(string)', 'http://php.net/strtolower', 1),
            'strtotime' => array('strtotime', 'strtotime', gT('Convert a date/time string to unix timestamp'), 'int strtotime(string)', 'http://php.net/manual/de/function.strtotime', 1),
            'strtoupper' => array('exprmgr_strtoupper', 'LEMstrtoupper', gT('Make a string uppercase'), 'string strtoupper(string)', 'http://php.net/strtoupper', 1),
            'substr' => array('exprmgr_substr', 'substr', gT('Return part of a string'), 'string substr(string, start [, length])', 'http://php.net/substr', 2, 3),
            'sum' => array('exprmgr_array_sum', 'LEMsum', gT('Calculate the sum of values in an array'), 'number sum(arg1, arg2, ... argN)', '', -2),
            'sumifop' => array('exprmgr_sumifop', 'LEMsumifop', gT('Sum the values of answered questions in the list which pass the critiera (arg op value)'), 'number sumifop(op, value, arg1, arg2, ... argN)', '', -3),
            'tan' => array('tan', 'Decimal.asNum.tan', gT('Tangent'), 'number tan(arg)', 'http://php.net/tan', 1),
            'convert_value' => array('exprmgr_convert_value', 'LEMconvert_value', gT('Convert a numerical value using a inputTable and outputTable of numerical values'), 'number convert_value(fValue, iStrict, sTranslateFromList, sTranslateToList)', '', 4),
            'time' => array('time', 'time', gT('Return current UNIX timestamp'), 'number time()', 'http://php.net/time', 0),
            'trim' => array('trim', 'trim', gT('Strip whitespace (or other characters) from the beginning and end of a string'), 'string trim(string [, charlist])', 'http://php.net/trim', 1, 2),
            'ucwords' => array('ucwords', 'ucwords', gT('Uppercase the first character of each word in a string'), 'string ucwords(string)', 'http://php.net/ucwords', 1),
            'unique' => array('exprmgr_unique', 'LEMunique', gT('Returns true if all non-empty responses are unique'), 'boolean unique(arg1, ..., argN)', '', -1),
        );
        /* Reset the language */
        if ($baseLang) {
            Yii::app()->setLanguage($baseLang);
        }
    }

    /**
     * Since this class can be get by session, need to add a call the «start» event manually
     * @return void
     */
    public function ExpressionManagerStartEvent()
    {
        if (Yii::app() instanceof CConsoleApplication) {
            return;
        }

        $event = new \LimeSurvey\PluginManager\PluginEvent('ExpressionManagerStart');
        $result = App()->getPluginManager()->dispatchEvent($event);
        $newValidFunctions = $result->get('functions', array());
        $newPackages = $result->get('packages', array()); // package added to expression-extend['depends'] : maybe don't add it in event, but add an helper ?

        $this->RegisterFunctions($newValidFunctions); // No validation : plugin dev can break all easily
        foreach ($newPackages as $name => $definition) {
            $this->addPackageForExpressionManager($name, $definition);
        }
        App()->getClientScript()->registerPackage('expression-extend');
    }

    /**
     * Add a package for expression
     * @param string $name of package
     * @param array $definition @see https://www.yiiframework.com/doc/api/1.1/CClientScript#packages-detail
     * @return void
     */
    public function addPackageForExpressionManager($name, $definition)
    {
        Yii::app()->clientScript->addPackage($name, $definition);
        array_push(Yii::app()->clientScript->packages['expression-extend']['depends'], $name);
    }

    /**
     * Add an error to the error log
     *
     * @param string $errMsg
     * @param array|null $token
     * @return void
     */
    private function RDP_AddError($errMsg, $token)
    {
        $this->RDP_errs[] = array($errMsg, $token);
    }

    /**
     * Add a warning to the error log
     *
     * @param EMWarningInterface $warning
     * @return void
     */
    private function RDP_AddWarning(EMWarningInterface $warning)
    {
        $this->RDP_warnings[] = $warning;
    }

    /**
     * @return array
     */
    public function RDP_GetErrors()
    {
        return $this->RDP_errs;
    }

    /**
     * Get informatin about type mismatch between arguments.
     * @param Token $arg1
     * @param Token $arg2
     * @return boolean[] Like (boolean $bMismatchType, boolean $bBothNumeric, boolean $bBothString)
     */
    private function getMismatchInformation(array $arg1, array $arg2)
    {
        /* When value come from DB : it's set to 1.000000 (DECIMAL) : must be fixed see #11163. Response::model() must fix this . or not ? */
        /* Don't return true always : user can entre non numeric value in a numeric value : we must compare as string then */
        $arg1[0] = ($arg1[2] == "NUMBER" && strpos((string) $arg1[0], ".")) ? rtrim(rtrim((string) $arg1[0], "0"), ".") : $arg1[0];
        $arg2[0] = ($arg2[2] == "NUMBER" && strpos((string) $arg2[0], ".")) ? rtrim(rtrim((string) $arg2[0], "0"), ".") : $arg2[0];

        $bNumericArg1 = $arg1[0] !== "" && (!$arg1[0] || strval(floatval($arg1[0])) == strval($arg1[0]));
        $bNumericArg2 = $arg2[0] !== "" && (!$arg2[0] || strval(floatval($arg2[0])) == strval($arg2[0]));
        $bStringArg1 = !$arg1[0] || !$bNumericArg1;
        $bStringArg2 = !$arg2[0] || !$bNumericArg2;

        $bBothNumeric = ($bNumericArg1 && $bNumericArg2);
        $bBothString = ($bStringArg1 && $bStringArg2);
        $bMismatchType = (!$bBothNumeric && !$bBothString);

        return array($bMismatchType, $bBothNumeric, $bBothString);
    }

    /**
     * RDP_EvaluateBinary() computes binary expressions, such as (a or b), (c * d), popping  the top two entries off the
     * stack and pushing the result back onto the stack.
     *
     * @param array $token
     * @return boolean - false if there is any error, else true
     */
    public function RDP_EvaluateBinary(array $token)
    {
        if (count($this->RDP_stack) < 2) {
            $this->RDP_AddError(self::gT("Unable to evaluate binary operator - fewer than 2 entries on stack"), $token);
            return false;
        }
        $arg2 = $this->RDP_StackPop();
        $arg1 = $this->RDP_StackPop();
        if (is_null($arg1) or is_null($arg2)) {
            $this->RDP_AddError(self::gT("Invalid value(s) on the stack"), $token);
            return false;
        }
        list($bMismatchType, $bBothNumeric, $bBothString) = $this->getMismatchInformation($arg1, $arg2);
        $isForcedString = false;
        /* @var array argument as forced string, arg type is at 2.
         * Question can return NUMBER or WORD : DQ and SQ is string entered by user, STRING is WORD with +""
         */
        $aForceStringArray = array('DQ_STRING', 'SQ_STRING', 'STRING'); //
        if (in_array($arg1[2], $aForceStringArray) || in_array($arg2[2], $aForceStringArray)) {
            $isForcedString = true;
            // Set bBothString if one is forced to be string, only if both can be numeric. Mimic JS and PHP
            if ($bBothNumeric) {
                $bBothNumeric = false;
                $bBothString = true;
                $bMismatchType = false;
                $arg1[0] = strval($arg1[0]);
                $arg2[0] = strval($arg2[0]);
            }
        }

        switch (strtolower((string) $token[0])) {
            case 'or':
            case '||':
                $result = array(($arg1[0] or $arg2[0]), $token[1], 'NUMBER');
                break;
            case 'and':
            case '&&':
                $result = array(($arg1[0] and $arg2[0]), $token[1], 'NUMBER');
                break;
            case '==':
            case 'eq':
                $result = array(($arg1[0] == $arg2[0]), $token[1], 'NUMBER');
                break;
            case '!=':
            case 'ne':
                $result = array(($arg1[0] != $arg2[0]), $token[1], 'NUMBER');
                break;
            case '<':
            case 'lt':
                if ($bMismatchType) {
                    if ($isForcedString) {
                        $this->RDP_AddWarning(new EMWarningInvalidComparison($token));
                    }
                    $result = array(false, $token[1], 'NUMBER');
                } elseif (!$bBothNumeric && $bBothString) {
                    if ($isForcedString) {
                        $this->RDP_AddWarning(new EMWarningInvalidComparison($token));
                    }
                    $result = array(strcmp((string) $arg1[0], (string) $arg2[0]) < 0, $token[1], 'NUMBER');
                } else {
                    $result = array(($arg1[0] < $arg2[0]), $token[1], 'NUMBER');
                }
                break;
            case '<=';
            case 'le':
                if ($bMismatchType) {
                    if ($isForcedString) {
                        $this->RDP_AddWarning(new EMWarningInvalidComparison($token));
                    }
                    $result = array(false, $token[1], 'NUMBER');
                } else {
                    // Need this explicit comparison in order to be in agreement with JavaScript
                    if (($arg1[0] == '0' && $arg2[0] == '') || ($arg1[0] == '' && $arg2[0] == '0')) {
                        $result = array(true, $token[1], 'NUMBER');
                    } elseif (!$bBothNumeric && $bBothString) {
                        if ($isForcedString) {
                            $this->RDP_AddWarning(new EMWarningInvalidComparison($token));
                        }
                        $result = array(strcmp((string) $arg1[0], (string) $arg2[0]) <= 0, $token[1], 'NUMBER');
                    } else {
                        $result = array(($arg1[0] <= $arg2[0]), $token[1], 'NUMBER');
                    }
                }
                break;
            case '>':
            case 'gt':
                if ($bMismatchType) {
                    if ($isForcedString) {
                        $this->RDP_AddWarning(new EMWarningInvalidComparison($token));
                    }
                    $result = array(false, $token[1], 'NUMBER');
                } else {
                    // Need this explicit comparison in order to be in agreement with JavaScript : still needed since we use ==='' ?
                    if (($arg1[0] == '0' && $arg2[0] == '') || ($arg1[0] == '' && $arg2[0] == '0')) {
                        $result = array(false, $token[1], 'NUMBER');
                    } elseif (!$bBothNumeric && $bBothString) {
                        if ($isForcedString) {
                            $this->RDP_AddWarning(new EMWarningInvalidComparison($token));
                        }
                        $result = array(strcmp((string) $arg1[0], (string) $arg2[0]) > 0, $token[1], 'NUMBER');
                    } else {
                        $result = array(($arg1[0] > $arg2[0]), $token[1], 'NUMBER');
                    }
                }
                break;
            case '>=';
            case 'ge':
                if ($bMismatchType) {
                    if ($isForcedString) {
                        $this->RDP_AddWarning(new EMWarningInvalidComparison($token));
                    }
                    $result = array(false, $token[1], 'NUMBER');
                } elseif (!$bBothNumeric && $bBothString) {
                    if ($isForcedString) {
                        $this->RDP_AddWarning(new EMWarningInvalidComparison($token));
                    }
                    $result = array(strcmp((string) $arg1[0], (string) $arg2[0]) >= 0, $token[1], 'NUMBER');
                } else {
                    $result = array(($arg1[0] >= $arg2[0]), $token[1], 'NUMBER');
                }
                break;
            case '+':
                if ($bBothNumeric) {
                    $this->RDP_AddWarning(new EMWarningPlusOperator($token));
                    $result = array(($arg1[0] + $arg2[0]), $token[1], 'NUMBER');
                } else {
                    $this->RDP_AddWarning(new EMWarningPlusOperator($token));
                    $result = array($arg1[0] . $arg2[0], $token[1], 'STRING');
                }
                break;
            case '-':
                if ($bBothNumeric) {
                    $result = array(($arg1[0] - $arg2[0]), $token[1], 'NUMBER');
                } else {
                    $result = array(NAN, $token[1], 'NUMBER');
                }
                break;
            case '*':
                if ($bBothNumeric) {
                    $result = array((doubleVal($arg1[0]) * doubleVal($arg2[0])), $token[1], 'NUMBER');
                } else {
                    $result = array(NAN, $token[1], 'NUMBER');
                }
                break;
            case '/';
                if ($bBothNumeric) {
                    if ($arg2[0] == 0) {
                        $result = array(NAN, $token[1], 'NUMBER');
                    } else {
                        $result = array(($arg1[0] / $arg2[0]), $token[1], 'NUMBER');
                    }
                } else {
                    $result = array(NAN, $token[1], 'NUMBER');
                }
                break;
        }

        $this->RDP_StackPush($result);
        return true;
    }

    /**
     * Processes operations like +a, -b, !c
     * @param array $token
     * @return boolean - true if success, false if any error occurred
     */

    private function RDP_EvaluateUnary(array $token)
    {
        if (count($this->RDP_stack) < 1) {
            $this->RDP_AddError(self::gT("Unable to evaluate unary operator - no entries on stack"), $token);
            return false;
        }
        $arg1 = $this->RDP_StackPop();
        if (is_null($arg1)) {
            $this->RDP_AddError(self::gT("Invalid value(s) on the stack"), $token);
            return false;
        }
        // If argmument is empty, then assume it is 0
        if ($arg1[0] == '') {
             $arg1[0] = 0;
        };
        // TODO:  try to determine datatype?
        switch ($token[0]) {
            case '+':
                $result = array((+$arg1[0]), $token[1], 'NUMBER');
                break;
            case '-':
                $result = array((-$arg1[0]), $token[1], 'NUMBER');
                break;
            case '!';
                $result = array((!$arg1[0]), $token[1], 'NUMBER');
                break;
        }
        $this->RDP_StackPush($result);
        return true;
    }


    /**
     * Main entry function
     * @param string $expr
     * @param boolean $onlyparse - if true, then validate the syntax without computing an answer
     * @param boolean $resetErrorsAndWarnings - if true (default), EM errors and warnings will be cleared before evaluation
     * @return boolean - true if success, false if any error occurred
     */
    public function RDP_Evaluate($expr, $onlyparse = false, $resetErrorsAndWarnings = true)
    {
        $this->RDP_expr = $expr;
        $this->RDP_tokens = $this->RDP_Tokenize($expr);
        $this->RDP_count = count($this->RDP_tokens);
        $this->RDP_pos = -1; // starting position within array (first act will be to increment it)
        if ($resetErrorsAndWarnings) {
            $this->RDP_errs = array();
            $this->RDP_warnings = array();
        }
        $this->RDP_onlyparse = $onlyparse;
        $this->RDP_stack = array();
        $this->RDP_evalStatus = false;
        $this->RDP_result = null;
        $this->varsUsed = array();
        $this->jsExpression = null;

        if ($this->HasSyntaxErrors()) {
            return false;
        } elseif ($this->RDP_EvaluateExpressions()) {
            if ($this->RDP_pos < $this->RDP_count) {
                $this->RDP_AddError(self::gT("Extra tokens found"), $this->RDP_tokens[$this->RDP_pos]);
                return false;
            }
            $this->RDP_result = $this->RDP_StackPop();
            if (is_null($this->RDP_result)) {
                return false;
            }
            if (count($this->RDP_stack) == 0) {
                $this->RDP_evalStatus = true;
                return true;
            } else {
                $this->RDP_AddError(self::gT("Unbalanced equation - values left on stack"), null);
                return false;
            }
        } else {
            $this->RDP_AddError(self::gT("Not a valid expression"), null);
            return false;
        }
    }


    /**
     * Process "a op b" where op in (+,-,concatenate)
     * @return boolean - true if success, false if any error occurred
     */
    private function RDP_EvaluateAdditiveExpression()
    {
        if (!$this->RDP_EvaluateMultiplicativeExpression()) {
            return false;
        }
        while (($this->RDP_pos + 1) < $this->RDP_count) {
            $token = $this->RDP_tokens[++$this->RDP_pos];
            if ($token[2] == 'BINARYOP') {
                switch ($token[0]) {
                    case '+':
                    case '-';
                        if ($this->RDP_EvaluateMultiplicativeExpression()) {
                            if (!$this->RDP_EvaluateBinary($token)) {
                                return false;
                            }
                            // else continue;
                        } else {
                            return false;
                        }
                        break;
                    default:
                        --$this->RDP_pos;
                        return true;
                }
            } else {
                --$this->RDP_pos;
                return true;
            }
        }
        return true;
    }

    /**
     * Process a Constant (number of string), retrieve the value of a known variable, or process a function, returning result on the stack.
     * @return boolean|null - true if success, false if any error occurred
     */

    private function RDP_EvaluateConstantVarOrFunction()
    {
        if ($this->RDP_pos + 1 >= $this->RDP_count) {
                $this->RDP_AddError(self::gT("Poorly terminated expression - expected a constant or variable"), null);
                return false;
        }
        $token = $this->RDP_tokens[++$this->RDP_pos];
        switch ($token[2]) {
            case 'NUMBER':
            case 'DQ_STRING':
            case 'SQ_STRING':
                $this->RDP_StackPush($token);
                return true;
                // NB: No break needed
            case 'WORD':
            case 'SGQA':
                if (($this->RDP_pos + 1) < $this->RDP_count and $this->RDP_tokens[($this->RDP_pos + 1)][2] == 'LP') {
                    return $this->RDP_EvaluateFunction();
                } else {
                    if ($this->RDP_isValidVariable($token[0])) {
                        $this->varsUsed[] = $token[0]; // add this variable to list of those used in this equation
                        if (preg_match("/\.(" . $this->getRegexpStaticValidAttributes() . ")$/", (string) $token[0])) {
                            $relStatus = 1; // static, so always relevant
                        } else {
                            $relStatus = $this->GetVarAttribute($token[0], 'relevanceStatus', 1);
                        }
                        if ($relStatus == 1) {
                            $argtype = ($this->GetVarAttribute($token[0], 'onlynum', 0)) ? "NUMBER" : "WORD";
                            $result = array($this->GetVarAttribute($token[0], null, ''), $token[1], $argtype);
                        } else {
                            $result = array(null, $token[1], 'NUMBER'); // was 0 instead of NULL
                        }
                        $this->RDP_StackPush($result);
                        return true;
                    } else {
                        $this->RDP_AddError(self::gT("Undefined variable"), $token);
                        return false;
                    }
                }
                // NB: No break needed
            case 'COMMA':
                --$this->RDP_pos;
                $this->RDP_AddError("Should never get to this line?", $token);
                return false;
                // NB: No break needed
            default:
                return false;
                // NB: No break needed
        }
    }

    /**
     * Process "a == b", "a eq b", "a != b", "a ne b"
     * @return boolean - true if success, false if any error occurred
     */
    private function RDP_EvaluateEqualityExpression()
    {
        if (!$this->RDP_EvaluateRelationExpression()) {
            return false;
        }
        while (($this->RDP_pos + 1) < $this->RDP_count) {
            $token = $this->RDP_tokens[++$this->RDP_pos];
            switch (strtolower((string) $token[0])) {
                case '==':
                case 'eq':
                case '!=':
                case 'ne':
                    if ($this->RDP_EvaluateRelationExpression()) {
                        if (!$this->RDP_EvaluateBinary($token)) {
                            return false;
                        }
                        // else continue;
                    } else {
                        return false;
                    }
                    break;
                default:
                    --$this->RDP_pos;
                    return true;
            }
        }
        return true;
    }

    /**
     * Process a single expression (e.g. without commas)
     * @return boolean - true if success, false if any error occurred
     */

    private function RDP_EvaluateExpression()
    {
        if ($this->RDP_pos + 2 < $this->RDP_count) {
            $token1 = $this->RDP_tokens[++$this->RDP_pos];
            $token2 = $this->RDP_tokens[++$this->RDP_pos];
            if ($token2[2] == 'ASSIGN') {
                if ($this->RDP_isValidVariable($token1[0])) {
                    $this->varsUsed[] = $token1[0]; // add this variable to list of those used in this equation
                    if ($this->RDP_isWritableVariable($token1[0])) {
                        $evalStatus = $this->RDP_EvaluateLogicalOrExpression();
                        if ($evalStatus) {
                            $result = $this->RDP_StackPop();
                            if (!is_null($result)) {
                                $newResult = $token2;
                                $newResult[2] = 'NUMBER';
                                $newResult[0] = $this->RDP_SetVariableValue($token2[0], $token1[0], $result[0]);
                                $this->RDP_StackPush($newResult);
                            } else {
                                $evalStatus = false;
                            }
                        }
                        $this->RDP_AddWarning(new EMWarningAssignment($token2));
                        return $evalStatus;
                    } else {
                        $this->RDP_AddError(self::gT('The value of this variable can not be changed'), $token1);
                        return false;
                    }
                } else {
                    $this->RDP_AddError(self::gT('Only variables can be assigned values'), $token1);
                    return false;
                }
            } else {
                // not an assignment expression, so try something else
                $this->RDP_pos -= 2;
                return $this->RDP_EvaluateLogicalOrExpression();
            }
        } else {
            return $this->RDP_EvaluateLogicalOrExpression();
        }
    }

    /**
     * Process "expression [, expression]*
     * @return boolean - true if success, false if any error occurred
     */

    private function RDP_EvaluateExpressions()
    {
        $evalStatus = $this->RDP_EvaluateExpression();
        if (!$evalStatus) {
            return false;
        }
        while (++$this->RDP_pos < $this->RDP_count) {
            $token = $this->RDP_tokens[$this->RDP_pos];
            if ($token[2] == 'RP') {
                return true; // presumbably the end of an expression
            } elseif ($token[2] == 'COMMA') {
                if ($this->RDP_EvaluateExpression()) {
                    $secondResult = $this->RDP_StackPop();
                    $firstResult = $this->RDP_StackPop();
                    if (is_null($firstResult)) {
                        return false;
                    }
                    $this->RDP_StackPush($secondResult);
                    $evalStatus = true;
                } else {
                    return false; // an error must have occurred
                }
            } else {
                $this->RDP_AddError(self::gT("Expected expressions separated by commas"), $token);
                $evalStatus = false;
                break;
            }
        }
        while (++$this->RDP_pos < $this->RDP_count) {
            $token = $this->RDP_tokens[$this->RDP_pos];
            $this->RDP_AddError(self::gT("Extra token found after expressions"), $token);
            $evalStatus = false;
        }
        return $evalStatus;
    }

    /**
     * Process a function call
     * @return boolean|null - true if success, false if any error occurred
     */
    private function RDP_EvaluateFunction()
    {
        $funcNameToken = $this->RDP_tokens[$this->RDP_pos]; // note that don't need to increment position for functions
        $funcName = $funcNameToken[0];
        if (!$this->RDP_isValidFunction($funcName)) {
            $this->RDP_AddError(self::gT("Undefined function"), $funcNameToken);
            return false;
        }
        $token2 = $this->RDP_tokens[++$this->RDP_pos];
        if ($token2[2] != 'LP') {
            $this->RDP_AddError(self::gT("Expected left parentheses after function name"), $funcNameToken);
        }
        $params = array(); // will just store array of values, not tokens
        while ($this->RDP_pos + 1 < $this->RDP_count) {
            $token3 = $this->RDP_tokens[$this->RDP_pos + 1];
            if (count($params) > 0) {
                // should have COMMA or RP
                if ($token3[2] == 'COMMA') {
                    ++$this->RDP_pos; // consume the token so can process next clause
                    if ($this->RDP_EvaluateExpression()) {
                        $value = $this->RDP_StackPop();
                        if (is_null($value)) {
                            return false;
                        }
                        $params[] = $value[0];
                        continue;
                    } else {
                        $this->RDP_AddError(self::gT("Extra comma found in function"), $token3);
                        return false;
                    }
                }
            }
            if ($token3[2] == 'RP') {
                ++$this->RDP_pos; // consume the token so can process next clause
                return $this->RDP_RunFunction($funcNameToken, $params);
            } else {
                if ($this->RDP_EvaluateExpression()) {
                    $value = $this->RDP_StackPop();
                    if (is_null($value)) {
                        return false;
                    }
                    $params[] = $value[0];
                    continue;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * Process "a && b" or "a and b"
     * @return boolean - true if success, false if any error occurred
     */

    private function RDP_EvaluateLogicalAndExpression()
    {
        if (!$this->RDP_EvaluateEqualityExpression()) {
            return false;
        }
        while (($this->RDP_pos + 1) < $this->RDP_count) {
            $token = $this->RDP_tokens[++$this->RDP_pos];
            switch (strtolower((string) $token[0])) {
                case '&&':
                case 'and':
                    if ($this->RDP_EvaluateEqualityExpression()) {
                        if (!$this->RDP_EvaluateBinary($token)) {
                            return false;
                        }
                        // else continue
                    } else {
                        return false; // an error must have occurred
                    }
                    break;
                default:
                    --$this->RDP_pos;
                    return true;
            }
        }
        return true;
    }

    /**
     * Process "a || b" or "a or b"
     * @return boolean - true if success, false if any error occurred
     */
    private function RDP_EvaluateLogicalOrExpression()
    {
        if (!$this->RDP_EvaluateLogicalAndExpression()) {
            return false;
        }
        while (($this->RDP_pos + 1) < $this->RDP_count) {
            $token = $this->RDP_tokens[++$this->RDP_pos];
            switch (strtolower((string) $token[0])) {
                case '||':
                case 'or':
                    if ($this->RDP_EvaluateLogicalAndExpression()) {
                        if (!$this->RDP_EvaluateBinary($token)) {
                            return false;
                        }
                        // else  continue
                    } else {
                        // an error must have occurred
                        return false;
                    }
                    break;
                default:
                    // no more expressions being  ORed together, so continue parsing
                    --$this->RDP_pos;
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

    private function RDP_EvaluateMultiplicativeExpression()
    {
        if (!$this->RDP_EvaluateUnaryExpression()) {
            return  false;
        }
        while (($this->RDP_pos + 1) < $this->RDP_count) {
            $token = $this->RDP_tokens[++$this->RDP_pos];
            if ($token[2] == 'BINARYOP') {
                switch ($token[0]) {
                    case '*':
                    case '/';
                        if ($this->RDP_EvaluateUnaryExpression()) {
                            if (!$this->RDP_EvaluateBinary($token)) {
                                return false;
                            }
                            // else  continue
                        } else {
                            // an error must have occurred
                            return false;
                        }
                        break;
                    default:
                        --$this->RDP_pos;
                        return true;
                }
            } else {
                --$this->RDP_pos;
                return true;
            }
        }
        return true;
    }

    /**
     * Process expressions including functions and parenthesized blocks
     * @return boolean|null - true if success, false if any error occurred
     */

    private function RDP_EvaluatePrimaryExpression()
    {
        if (($this->RDP_pos + 1) >= $this->RDP_count) {
            $this->RDP_AddError(self::gT("Poorly terminated expression - expected a constant or variable"), null);
            return false;
        }
        $token = $this->RDP_tokens[++$this->RDP_pos];
        if ($token[2] == 'LP') {
            if (!$this->RDP_EvaluateExpressions()) {
                return false;
            }
            $token = $this->RDP_tokens[$this->RDP_pos];
            if ($token[2] == 'RP') {
                return true;
            } else {
                $this->RDP_AddError(self::gT("Expected right parentheses"), $token);
                return false;
            }
        } else {
            --$this->RDP_pos;
            return $this->RDP_EvaluateConstantVarOrFunction();
        }
    }

    /**
     * Process "a op b" where op in (lt, gt, le, ge, <, >, <=, >=)
     * @return boolean - true if success, false if any error occurred
     */
    private function RDP_EvaluateRelationExpression()
    {
        if (!$this->RDP_EvaluateAdditiveExpression()) {
            return false;
        }
        while (($this->RDP_pos + 1) < $this->RDP_count) {
            $token = $this->RDP_tokens[++$this->RDP_pos];
            switch (strtolower((string) $token[0])) {
                case '<':
                case 'lt':
                case '<=';
                case 'le':
                case '>':
                case 'gt':
                case '>=';
                case 'ge':
                    if ($this->RDP_EvaluateAdditiveExpression()) {
                        if (!$this->RDP_EvaluateBinary($token)) {
                            return false;
                        }
                        // else  continue
                    } else {
                        // an error must have occurred
                        return false;
                    }
                    break;
                default:
                    --$this->RDP_pos;
                    return true;
            }
        }
        return true;
    }

    /**
     * Process "op a" where op in (+,-,!)
     * @return boolean|null - true if success, false if any error occurred
     */

    private function RDP_EvaluateUnaryExpression()
    {
        if (($this->RDP_pos + 1) >= $this->RDP_count) {
            $this->RDP_AddError(self::gT("Poorly terminated expression - expected a constant or variable"), null);
            return false;
        }
        $token = $this->RDP_tokens[++$this->RDP_pos];
        if ($token[2] == 'NOT' || $token[2] == 'BINARYOP') {
            switch ($token[0]) {
                case '+':
                case '-':
                case '!':
                    if (!$this->RDP_EvaluatePrimaryExpression()) {
                        return false;
                    }
                    return $this->RDP_EvaluateUnary($token);
                    // NB: No break needed
                default:
                    --$this->RDP_pos;
                    return $this->RDP_EvaluatePrimaryExpression();
            }
        } else {
            --$this->RDP_pos;
            return $this->RDP_EvaluatePrimaryExpression();
        }
    }

    /**
     * Returns array of all JavaScript-equivalent variable names used when parsing a string via sProcessStringContainingExpressions
     * @return array
     */
    public function GetAllJsVarsUsed()
    {
        if (is_null($this->allVarsUsed)) {
            return array();
        }
        $names = array_unique($this->allVarsUsed);
        if (is_null($names)) {
            return array();
        }
        $jsNames = array();
        foreach ($names as $name) {
            if (preg_match("/\.(" . $this->getRegexpStaticValidAttributes() . ")$/", (string) $name)) {
                continue;
            }
            $val = $this->GetVarAttribute($name, 'jsName', '');
            if ($val != '') {
                $jsNames[] = $val;
            }
        }
        return array_unique($jsNames);
    }

    /**
     * Return the list of all of the JavaScript variables used by the most recent expression - only those that are set on the current page
     * This is used to control static vs dynamic substitution.  If an expression is entirely made up of off-page changes, it can be statically replaced.
     * @return array
     */
    public function GetOnPageJsVarsUsed()
    {
        if (is_null($this->varsUsed)) {
            return array();
        }
        if ($this->surveyMode == 'survey') {
            return $this->GetJsVarsUsed();
        }
        $names = array_unique($this->varsUsed);
        if (is_null($names)) {
            return array();
        }
        $jsNames = array();
        foreach ($names as $name) {
            if (preg_match("/\.(" . $this->getRegexpStaticValidAttributes() . ")$/", (string) $name)) {
                continue;
            }
            $val = $this->GetVarAttribute($name, 'jsName', '');
            switch ($this->surveyMode) {
                case 'group':
                    $gseq = $this->GetVarAttribute($name, 'gseq', '');
                    $onpage = ($gseq == $this->groupSeq);
                    break;
                case 'question':
                    $qseq = $this->GetVarAttribute($name, 'qseq', '');
                    $onpage = ($qseq == $this->questionSeq);
                    break;
                case 'survey':
                    $onpage = true;
                    break;
            }
            if ($val != '' && $onpage) {
                $jsNames[] = $val;
            }
        }
        return array_unique($jsNames);
    }

    /**
     * Return the list of all of the JavaScript variables used by the most recent expression
     * @return array
     */
    public function GetJsVarsUsed()
    {
        if (is_null($this->varsUsed)) {
            return array();
        }
        $names = array_unique($this->varsUsed);
        if (is_null($names)) {
            return array();
        }
        $jsNames = array();
        foreach ($names as $name) {
            if (preg_match("/\.(" . $this->getRegexpStaticValidAttributes() . ")$/", (string) $name)) {
                continue;
            }
            $val = $this->GetVarAttribute($name, 'jsName', '');
            if ($val != '') {
                $jsNames[] = $val;
            }
        }
        return array_unique($jsNames);
    }

    /**
     * @return void
     */
    public function SetJsVarsUsed($vars)
    {
        $this->varsUsed = $vars;
    }

    /**
     * Return the JavaScript variable name for a named variable
     * @param string $name
     * @return string
     */
    public function GetJsVarFor($name)
    {
        return $this->GetVarAttribute($name, 'jsName', '');
    }

    /**
     * Returns array of all variables used when parsing a string via sProcessStringContainingExpressions
     * @return array
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
        return $this->RDP_result[0];
    }

    /**
     * Return an array of errors
     * @return array
     */
    public function GetErrors()
    {
        return $this->RDP_errs;
    }

    /**
     * Converts the most recent expression into a valid JavaScript expression, mapping function and variable names and operators as needed.
     * @return string the JavaScript expresssion
     */
    public function GetJavaScriptEquivalentOfExpression()
    {
        if (!is_null($this->jsExpression)) {
            return $this->jsExpression;
        }
        if ($this->HasErrors()) {
            $this->jsExpression = '';
            return '';
        }
        $tokens = $this->RDP_tokens;
        /* @var string|null used for ASSIGN expression */
        $idToSet = null;
        /* @var string[] the final expression line by line (to be join at end) */
        $stringParts = array();
        $numTokens = count($tokens);

        /* @var integer bracket count for static function management */
        $bracket = 0;
        /* @var string static string to be parsed bedfore send to JS */
        $staticStringToParse = "";
        for ($i = 0; $i < $numTokens; ++$i) {
            $token = $tokens[$i]; // When do these need to be quoted?
            if (!empty($staticStringToParse)) { /* Currently inside a static function */
                switch ($token[2]) {
                    case 'LP':
                        $staticStringToParse .= $token[0];
                        $bracket++;
                        break;
                    case 'RP':
                        $staticStringToParse .= $token[0];
                        $bracket--;
                        break;
                    case 'DQ_STRING':
                        // A string inside double quote : add double quote again
                        $staticStringToParse .= '"' . $token[0] . '"';
                        break;
                    case 'SQ_STRING':
                        // A string inside single quote : add single quote again
                        $staticStringToParse .= "'" . $token[0] . "'";
                        break;
                    default:
                        // This set whole string inside function as a static var : must document clearly.
                        $staticStringToParse .= $token[0];
                }
                if ($bracket == 0) { // Last close bracket : get the static final function and reset
                    //~ $staticString = LimeExpressionManager::ProcessStepString("{".$staticStringToParse."}",array(),3,true);
                    $staticString = $this->sProcessStringContainingExpressions("{" . $staticStringToParse . "}", 0, 3, 1, -1, -1, true); // As static : no gseq,qseq etc …
                    $stringParts[] = "'" . addcslashes($staticString, "'") . "'";
                    $staticStringToParse = "";
                }
            } else {
                switch ($token[2]) {
                    case 'DQ_STRING':
                        $stringParts[] = '"' . addcslashes((string) $token[0], '\"') . '"'; // htmlspecialchars($token[0],ENT_QUOTES,'UTF-8',false) . "'";
                        break;
                    case 'SQ_STRING':
                        $stringParts[] = "'" . addcslashes((string) $token[0], "\'") . "'"; // htmlspecialchars($token[0],ENT_QUOTES,'UTF-8',false) . "'";
                        break;
                    case 'SGQA':
                    case 'WORD':
                        if ($i + 1 < $numTokens && $tokens[$i + 1][2] == 'LP') {
                            // then word is a function name
                            $funcInfo = $this->RDP_ValidFunctions[$token[0]];
                            if ($funcInfo[1] === null) {
                                /* start a static function */
                                $staticStringToParse = $token[0]; // The function name
                                $bracket = 0; // Reset bracket (again)
                            } else {
                                $stringParts[] = $funcInfo[1]; // the PHP function name
                            }
                        } elseif ($i + 1 < $numTokens && $tokens[$i + 1][2] == 'ASSIGN') {
                            $jsName = $this->GetVarAttribute($token[0], 'jsName', '');
                            /* Value is in the page : can not set */
                            if (!empty($jsName)) {
                                $idToSet = $jsName;
                                if ($tokens[$i + 1][0] == '+=') {
                                    // Javascript does concatenation unless both left and right side are numbers, so refactor the equation
                                    $varName = $this->GetVarAttribute($token[0], 'varName', $token[0]);
                                    $stringParts[] = " = LEMval('" . $varName . "') + ";
                                    ++$i;
                                }
                            }
                        } else {
                            if (preg_match("/\.(" . $this->getRegexpStaticValidAttributes() . ")$/", (string) $token[0])) {
                                /* This is a static variables : set as static */
                                $static = $this->sProcessStringContainingExpressions("{" . $token[0] . "}", 0, 1, 1, -1, -1, true);
                                $stringParts[] = "'" . addcslashes($static, "'") . "'";
                            } else {
                                $jsName = $this->GetVarAttribute($token[0], 'jsName', '');
                                $code = $this->GetVarAttribute($token[0], 'code', '');
                                if ($jsName != '') {
                                    $varName = $this->GetVarAttribute($token[0], 'varName', $token[0]);
                                    $stringParts[] = "LEMval('" . $varName . "') ";
                                } else {
                                    $stringParts[] = "'" . addcslashes($code, "'") . "'";
                                }
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
                        switch (strtolower((string) $token[0])) {
                            case 'and':
                                $stringParts[] = ' && ';
                                break;
                            case 'or':
                                $stringParts[] = ' || ';
                                break;
                            case 'lt':
                                $stringParts[] = ' < ';
                                break;
                            case 'le':
                                $stringParts[] = ' <= ';
                                break;
                            case 'gt':
                                $stringParts[] = ' > ';
                                break;
                            case 'ge':
                                $stringParts[] = ' >= ';
                                break;
                            case 'eq':
                            case '==':
                                $stringParts[] = ' == ';
                                break;
                            case 'ne':
                            case '!=':
                                $stringParts[] = ' != ';
                                break;
                            case '=':
                                /* ASSIGN : usage jquery: don't add anything (disable default) */;
                                break;
                            default:
                                $stringParts[] = ' ' . $token[0] . ' ';
                                break;
                        }
                        break;
                }
            }
        }
        // for each variable that does not have a default value, add clause to throw error if any of them are NA
        $nonNAvarsUsed = array();
        foreach ($this->GetVarsUsed() as $var) {
            /* This function wants to see the NAOK suffix (NAOK|valueNAOK|shown)
             * OR static var and Check dynamic var inside static function too
             * see https://bugs.limesurvey.org/view.php?id=18008 for issue about sgqa and question
             * See https://bugs.limesurvey.org/view.php?id=14818 for feature
             */
            if (!preg_match("/^.*\.(NAOK|valueNAOK|shown|relevanceStatus)$/", (string) $var) &&  !preg_match("/^.*\.(" . $this->getRegexpStaticValidAttributes() . ")$/", (string) $var)) {
                if ($this->GetVarAttribute($var, 'jsName', '') != '') {
                    $nonNAvarsUsed[] = $var;
                }
            }
        }
        $mainClause = implode('', $stringParts);
        if ($idToSet) {
            /* If there are an id to set (assign) : set it via jquery */
            $mainClause = "$('#{$idToSet}').val({$mainClause})";
        }
        $varsUsed = implode("', '", $nonNAvarsUsed);
        if ($varsUsed != '') {
            $this->jsExpression = "LEMif(LEManyNA('" . $varsUsed . "'),'',(" . $mainClause . "))";
        } else {
            $this->jsExpression = '(' . $mainClause . ')';
        }
        return $this->jsExpression;
    }

    /**
     * JavaScript Test function - simply writes the result of the current JavaScriptEquivalentFunction to the output buffer.
     * @param string $expected
     * @param integer $num
     * @return string
     */
    public function GetJavascriptTestforExpression($expected, $num)
    {
        // assumes that the hidden variables have already been declared
        $expr = $this->GetJavaScriptEquivalentOfExpression();
        if (is_null($expr) || $expr == '') {
            $expr = "'NULL'";
        }
        $jsmultiline_expr = str_replace("\n", "\\\n", $expr);
        $jsmultiline_expected = str_replace("\n", "\\\n", addslashes($expected));
        $jsParts = array();
        $jsParts[] = "val = " . $jsmultiline_expr . ";\n";
        $jsParts[] = "klass = (LEMeq(addslashes(val),'" . $jsmultiline_expected . "')) ? 'ok' : 'error';\n";
        $jsParts[] = "document.getElementById('test_" . $num . "').innerHTML=(val);\n";
        $jsParts[] = "document.getElementById('test_" . $num . "').className=klass;\n";
        return implode('', $jsParts);
    }

    /**
     * Generate the function needed to dynamically change the value of a <span> section
     * @param integer $questionNum No longer used
     * @param string $elementId - the ID name for the function
     * @param string $eqn No longer used
     * @return string : javascript part
     */
    public function GetJavaScriptFunctionForReplacement($questionNum, $elementId, $eqn)
    {
        $jsParts = array();
        $jsParts[] = "jQuery('#{$elementId}').html(LEMfixnum(\n";
        $jsParts[] = $this->GetJavaScriptEquivalentOfExpression();
        $jsParts[] = "));\n";
        // Add an event after html is updated (see #11937 and really good helper for template manager)
        $jsParts[] = "jQuery('#{$elementId}').trigger('html:updated');\n"; // See http://learn.jquery.com/events/introduction-to-custom-events/#naming-custom-events for colons in name
        return implode('', $jsParts);
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
     * @param string $expr
     */
    public function SetPrettyPrintSource($expr)
    {
        $this->prettyPrintSource = $expr;
    }

    /**
     * Color-codes Expressions (using HTML <span> tags), showing variable types and values.
     * @return string HTML
     */
    public function GetPrettyPrintString()
    {
        //~ Yii::app()->setLanguage(Yii::app()->session['adminlang']);
        // color code the equation, showing not only errors, but also variable attributes
        $errs = $this->RDP_errs;
        $tokens = $this->RDP_tokens;
        $errCount = count($errs);
        $errIndex = 0;
        if ($errCount > 0) {
            usort($errs, "cmpErrorTokens");
        }
        $warnings = $this->RDP_warnings;
        $warningsCount = count($warnings);
        if (!empty($warnings)) {
            usort($warnings, "cmpWarningTokens");
        }
        $stringParts = array();
        $numTokens = count($tokens);
        $bHaveError = false;

        $globalErrs = array(); // Error not related to a token (bracket for example)
        while ($errIndex < $errCount) {
            if (empty($errs[$errIndex][1])) {
                $globalErrs[] = $errs[$errIndex][0];
                $bHaveError = true;
            }
            $errIndex++;
        }

        for ($i = 0; $i < $numTokens; ++$i) {
            $token = $tokens[$i];
            $messages = array();
            $thisTokenHasError = false;
            $errIndex = 0;
            while ($errIndex < $errCount) {
                if ($errs[$errIndex][1] == $token) { // Error related to this token
                    $messages[] = $errs[$errIndex][0];
                    $thisTokenHasError = true;
                }
                $errIndex++;
            }
            $thisTokenHasWarning = false;
            $warningIndex = 0;
            while ($warningIndex < $warningsCount) {
                if ($warnings[$warningIndex]->getToken() == $token) { // Error related to this token
                    $messages[] = $warnings[$warningIndex]->getMessage();
                    $thisTokenHasWarning = true;
                }
                $warningIndex++;
            }
            if ($thisTokenHasError) {
                $stringParts[] = "<span class='em-error' title=' ' >";
                $bHaveError = true;
            } elseif ($thisTokenHasWarning) {
                $stringParts[] = "<span class='em-warning' title=' '>";
            }
            switch ($token[2]) {
                case 'DQ_STRING':
                    /* Check $token[0] forced string */
                    $stringParts[] = CHtml::tag('span', array(
                        'title' => !empty($messages) ? implode('; ', $messages) : null,
                        'class' => 'em-var-string'
                    ), "\"" . $token[0] . "\"");
                    break;
                case 'SQ_STRING':
                    $stringParts[] = CHtml::tag('span', array(
                        'title' => !empty($messages) ? implode('; ', $messages) : null,
                        'class' => 'em-var-string'
                    ), "'" . CHtml::encode($token[0]) . "'");
                    break;
                case 'SGQA':
                case 'WORD':
                    if ($i + 1 < $numTokens && $tokens[$i + 1][2] == 'LP') {
                        // then word is a function name
                        if ($this->RDP_isValidFunction($token[0])) {
                            $funcInfo = $this->RDP_ValidFunctions[$token[0]];
                            $messages[] = $funcInfo[2];
                            $messages[] = $funcInfo[3];
                        }
                        $stringParts[] = "<span title='" . CHtml::encode(implode('; ', $messages)) . "' class='em-function' >";
                        $stringParts[] = $token[0];
                        $stringParts[] = "</span>";
                    } else {
                        if (!$this->RDP_isValidVariable($token[0])) {
                            $class = 'em-var-error';
                            $displayName = $token[0];
                        } else {
                            $jsName = $this->GetVarAttribute($token[0], 'jsName', '');
                            $code = $this->GetVarAttribute($token[0], 'code', '');
                            $question = $this->GetVarAttribute($token[0], 'question', '');
                            $qcode = $this->GetVarAttribute($token[0], 'qcode', '');
                            $questionSeq = $this->GetVarAttribute($token[0], 'qseq', -1);
                            $groupSeq = $this->GetVarAttribute($token[0], 'gseq', -1);
                            $ansList = $this->GetVarAttribute($token[0], 'ansList', '');
                            $gid = $this->GetVarAttribute($token[0], 'gid', -1);
                            $qid = $this->GetVarAttribute($token[0], 'qid', -1);

                            if ($jsName != '') {
                                $descriptor = '[' . $jsName . ']';
                            } else {
                                $descriptor = '';
                            }
                            // Show variable name instead of SGQA code, if available
                            if ($qcode != '') {
                                if (preg_match('/^INSERTANS:/', (string) $token[0])) {
                                    $displayName = $qcode . '.shown';
                                    $descriptor = '[' . $token[0] . ']';
                                } else {
                                    $args = explode('.', (string) $token[0]);
                                    if (count($args) == 2) {
                                        $displayName = $qcode . '.' . $args[1];
                                    } else {
                                        $displayName = $qcode;
                                    }
                                }
                            } else {
                                $displayName = $token[0];
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

                            $messages[] = $descriptor . $question;
                            if ($ansList != '') {
                                $messages[] = $ansList;
                            }
                            if ($code != '') {
                                if ($token[2] == 'SGQA' && preg_match('/^INSERTANS:/', (string) $token[0])) {
                                    $shown = $this->GetVarAttribute($token[0], 'shown', '');
                                    $messages[] = 'value=[' . $code . '] '
                                            . $shown;
                                } else {
                                    $messages[] = 'value=' . $code;
                                }
                            }

                            if ($this->groupSeq == -1 || $groupSeq == -1 || $questionSeq == -1 || $this->questionSeq == -1) {
                                $class = 'em-var-static';
                            } elseif ($groupSeq > $this->groupSeq) {
                                $class = 'em-var-before em-var-diffgroup';
                            } elseif ($groupSeq < $this->groupSeq) {
                                $class = 'em-var-after ';
                            } elseif ($questionSeq > $this->questionSeq) {
                                $class = 'em-var-before em-var-inpage';
                            } else {
                                $class = 'em-var-after em-var-inpage';
                            }
                        }
                        // prevent EM prcessing of messages within span
                        $message = implode('; ', $messages);
                        $message = str_replace(array('{', '}'), array('{ ', ' }'), $message);

                        if ($this->hyperlinkSyntaxHighlighting && isset($gid) && isset($qid) && $qid > 0 && $this->RDP_isValidVariable($token[0])) {
                            $editlink = App()->getController()->createUrl('questionAdministration/view/surveyid/' . $this->sid . '/gid/' . $gid . '/qid/' . $qid);
                            $stringParts[] = "<a title='" . CHtml::encode($message) . "' class='em-var {$class}' href='{$editlink}' >";
                        } else {
                            $stringParts[] = "<span title='" . CHtml::encode($message) . "' class='em-var {$class}' >";
                        }
                        if ($this->sgqaNaming) {
                            $sgqa = substr((string) $jsName, 4);
                            $nameParts = explode('.', (string) $displayName);
                            if (count($nameParts) == 2) {
                                $sgqa .= '.' . $nameParts[1];
                            }
                            $stringParts[] = $sgqa;
                        } else {
                            $stringParts[] = $displayName;
                        }
                        if ($this->hyperlinkSyntaxHighlighting && isset($gid) && isset($qid) && $qid > 0 && $this->RDP_isValidVariable($token[0])) {
                            $stringParts[] = "</a>";
                        } else {
                            $stringParts[] = "</span>";
                        }
                    }
                    break;
                case 'ASSIGN':
                    $stringParts[] = CHtml::tag('span', array(
                        'title' => !empty($messages) ? implode('; ', $messages) : null,
                        'class' => 'em-assign em-warning'
                    ), ' ' . $token[0] . ' ');
                    break;
                case 'COMMA':
                    $stringParts[] = $token[0] . ' ';
                    break;
                case 'LP':
                case 'RP':
                case 'NUMBER':
                    $stringParts[] = $token[0];
                    break;
                case 'COMPARE':
                    $stringParts[] = CHtml::tag('span', array(
                        'title' => !empty($messages) ? implode('; ', $messages) : null,
                        'class' => 'em-compare'
                    ), ' ' . $token[0] . ' ');
                    break;
                default:
                    $stringParts[] = CHtml::tag('span', array(
                        'title' => !empty($messages) ? implode('; ', $messages) : null,
                    ), ' ' . $token[0] . ' ');
                    break;
            }
            if ($thisTokenHasError || $thisTokenHasWarning) {
                $stringParts[] = "</span>";
                ++$errIndex;
            }
        }
        if ($this->sid && Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update') && method_exists(App(), 'getClientScript')) {
            App()->getClientScript()->registerPackage('expressionscript');
        }
        $sClass = 'em-expression';
        $sClass .= ($bHaveError) ? " em-haveerror" : "";
        $title = "";
        if (!empty($globalErrs)) {
            $sClass .= " em-error";
            $title = " title='" . CHtml::encode(implode('; ', $globalErrs)) . "'";
        }
        return "<span class='$sClass' $title >" . implode('', $stringParts) . "</span>";
    }

    /**
     * Get information about the variable, including JavaScript name, read-write status, and whether set on current page.
     * @param string $name
     * @param string|null $attr
     * @param string $default
     * @return string
     */
    private function GetVarAttribute($name, $attr, $default)
    {
        return LimeExpressionManager::GetVarAttribute($name, $attr, $default, $this->groupSeq, $this->questionSeq);
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
        return (count($this->RDP_errs) > 0);
    }

    /**
     * Return array of warnings
     * @return array
     */
    public function GetWarnings()
    {
        return $this->RDP_warnings;
    }

    /**
     * Reset current warnings
     * @see Related issue #15547: Invalid error count on Survey Logic file for subquestion relevance
     * @link https://bugs.limesurvey.org/view.php?id=15547
     * ProcessBooleanExpression didn't reset RDP_errors anb RDP_warnings, need a way to reset for Survey logic checking
     * @return void
     */
    public function ResetWarnings()
    {
        $this->RDP_warnings = array();
    }

    /**
     * Reset current errors
     * @see Related issue #16738: https://bugs.limesurvey.org/view.php?id=16738
     * @link https://bugs.limesurvey.org/view.php?id=16738
     * ProcessBooleanExpression didn't reset RDP_errors anb RDP_warnings, need a way to reset for Survey logic checking
     * @return void
     */
    public function ResetErrors()
    {
        $this->RDP_errs = array();
    }

    /**
     * Reset current errors and current warnings
     * @return void
     */
    public function ResetErrorsAndWarnings()
    {
        $this->ResetErrors();
        $this->ResetWarnings();
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

        for ($i = 0; $i < $this->RDP_count; ++$i) {
            $token = $this->RDP_tokens[$i];
            switch ($token[2]) {
                case 'LP':
                    ++$nesting;
                    break;
                case 'RP':
                    --$nesting;
                    if ($nesting < 0) {
                        $this->RDP_AddError(self::gT("Extra right parentheses detected"), $token);
                    }
                    break;
                case 'WORD':
                case 'SGQA':
                    if ($i + 1 < $this->RDP_count and $this->RDP_tokens[$i + 1][2] == 'LP') {
                        if (!$this->RDP_isValidFunction($token[0])) {
                            $this->RDP_AddError(self::gT("Undefined function"), $token);
                        }
                    } else {
                        if (!($this->RDP_isValidVariable($token[0]))) {
                            $this->RDP_AddError(self::gT("Undefined variable"), $token);
                        }
                    }
                    break;
                case 'OTHER':
                    $this->RDP_AddError(self::gT("Unsupported syntax"), $token);
                    break;
                default:
                    break;
            }
        }
        if ($nesting > 0) {
            $this->RDP_AddError(sprintf(self::gT("Missing %s closing right parentheses"), $nesting), null);
        }
        if ($nesting < 0) {
            $this->RDP_AddError(sprintf(self::gT("Missing %s closing left parentheses"), abs($nesting)), null);
        }
        return (count($this->RDP_errs) > 0);
    }

    /**
     * Return true if the function name is registered
     * @param string $name
     * @return boolean
     */

    private function RDP_isValidFunction($name)
    {
        return array_key_exists($name, $this->RDP_ValidFunctions);
    }

    /**
     * Add extra attributes for var
     * @param string[] $extraAttributes
     * @param boolean $static is a static attribute , unused currently since there are no way to create the EM js system
     * @return void
     */
    public function addRegexpExtraAttributes($extraAttributes, $static = true)
    {
        if (!$static) {
            $this->aRDP_regexpVariableAttribute = array_merge($this->aRDP_regexpVariableAttribute, $extraAttributes);
        } else {
            $this->aRDP_regexpStaticAttribute = array_merge($this->aRDP_regexpStaticAttribute, $extraAttributes);
        }
    }

    public function getRegexpValidAttributes()
    {
        /* Static var or cache it ? Must control when updated */
        return implode("|", array_merge($this->aRDP_regexpVariableAttribute, $this->aRDP_regexpStaticAttribute));
    }

    public function getRegexpStaticValidAttributes()
    {
        /* Static var or cache it ? Must control when updated */
        return implode("|", $this->aRDP_regexpStaticAttribute);
    }

    /**
     * Return true if the variable name is registered
     * @param string $name
     * @return boolean
     */
    private function RDP_isValidVariable($name)
    {
        $varName = preg_replace("/^(?:INSERTANS:)?(.*?)(?:\.(?:" . $this->getRegexpValidAttributes() . "))?$/", "$1", $name);
        return LimeExpressionManager::isValidVariable($varName);
    }

    /**
     * Return true if the variable name is writable
     * @param string $name
     * @return boolean
     */
    private function RDP_isWritableVariable($name)
    {
        return ($this->GetVarAttribute($name, 'readWrite', 'N') == 'Y');
    }

    /**
     * Process an expression and return its boolean value
     * @param string $expr
     * @param int $groupSeq - needed to determine whether using variables before they are declared
     * @param int $questionSeq - needed to determine whether using variables before they are declared
     * @return boolean
     */
    public function ProcessBooleanExpression($expr, $groupSeq = -1, $questionSeq = -1)
    {
        $this->groupSeq = $groupSeq;
        $this->questionSeq = $questionSeq;

        $expr = $this->ExpandThisVar($expr);
        $status = $this->RDP_Evaluate($expr);
        if (!$status) {
            return false; // if there are errors in the expression, hide it?
        }
        $result = $this->GetResult();
        if (is_null($result)) {
            return false; // if there are errors in the expression, hide it?
        }

        foreach ($this->GetVarsUsed() as $var) {
            /* this function wants to see the NAOK suffix : NAOK|valueNAOK|shown|relevanceStatus
             * Static suffix are always OK (no need NAOK)
             */
            if (!preg_match("/^.*\.(NAOK|valueNAOK|shown|relevanceStatus)$/", (string) $var) && ! preg_match("/\.(" . $this->getRegexpStaticValidAttributes() . ")$/", (string) $var)) {
                if (!LimeExpressionManager::GetVarAttribute($var, 'relevanceStatus', false, $groupSeq, $questionSeq)) {
                    return false;
                }
            }
        }
        return (bool) $result;
    }

    /**
     * Start processing a group of substitions - will be incrementally numbered
     */

    public function StartProcessingGroup($sid = null, $rooturl = '', $hyperlinkSyntaxHighlighting = true)
    {
        $this->substitutionNum = 0;
        $this->substitutionInfo = array(); // array of JavaScripts for managing each substitution
        $this->sid = $sid;
        $this->hyperlinkSyntaxHighlighting = $hyperlinkSyntaxHighlighting;
    }

    /**
     * Clear cache of tailoring content.
     * When re-displaying same page, need to avoid generating double the amount of tailoring content.
     */
    public function ClearSubstitutionInfo()
    {
        $this->substitutionNum = 0;
        $this->substitutionInfo = array(); // array of JavaScripts for managing each substitution
    }

    /**
     * Process multiple substitution iterations of a full string, containing multiple expressions delimited by {}, return a consolidated string
     * @param string $src
     * @param int $questionNum
     * @param int $numRecursionLevels - number of levels of recursive substitution to perform
     * @param int $whichPrettyPrintIteration - if recursing, specify which pretty-print iteration is desired
     * @param int $groupSeq - needed to determine whether using variables before they are declared
     * @param int $questionSeq - needed to determine whether using variables before they are declared
     * @param boolean $staticReplacement
     * @return string
     */
    public function sProcessStringContainingExpressions($src, $questionNum = 0, $numRecursionLevels = 1, $whichPrettyPrintIteration = 1, $groupSeq = -1, $questionSeq = -1, $staticReplacement = false)
    {
        // tokenize string by the {} pattern, properly dealing with strings in quotations, and escaped curly brace values
        $this->allVarsUsed = array();
        $this->questionSeq = $questionSeq;
        $this->groupSeq = $groupSeq;
        $result = $src;
        $prettyPrint = '';

        $prettyPrintIterationDone = false;
        for ($i = 1; $i <= $numRecursionLevels; ++$i) {
            // TODO - Since want to use <span> for dynamic substitution, what if there are recursive substititons?
            $prevResult = $result;
            $result = $this->sProcessStringContainingExpressionsHelper($result, $questionNum, $staticReplacement);
            if ($result === $prevResult) {
                // No update during process : can exit of iteration
                if (!$prettyPrintIterationDone) {
                    $prettyPrint = $this->prettyPrintSource;
                }
                // No need errors : already done
                break;
            }
            if ($i == $whichPrettyPrintIteration) {
                $prettyPrint = $this->prettyPrintSource;
                $prettyPrintIterationDone = true;
            }
        }
        $this->prettyPrintSource = $prettyPrint; // ensure that if doing recursive substition, can get original source to pretty print
        $result = str_replace(array('\{', '\}',), array('{', '}'), $result);
        return $result;
    }

    /**
     * Process one substitution iteration of a full string, containing multiple expressions delimited by {}, return a consolidated string
     * @param string $src
     * @param integer $questionNum - used to generate substitution <span>s that indicate to which question they belong
     * @param boolean $staticReplacement
     * @return string
     */
    public function sProcessStringContainingExpressionsHelper($src, $questionNum, $staticReplacement = false)
    {
        // tokenize string by the {} pattern, properly dealing with strings in quotations, and escaped curly brace values
        $stringParts = $this->asSplitStringOnExpressions($src);
        $resolvedParts = array();
        $prettyPrintParts = array();
        $this->ResetErrorsAndWarnings();
        foreach ($stringParts as $stringPart) {
            if ($stringPart[2] == 'STRING') {
                $resolvedParts[] = $stringPart[0];
                $prettyPrintParts[] = $stringPart[0];
            } else {
                ++$this->substitutionNum;
                $expr = $this->ExpandThisVar(substr((string) $stringPart[0], 1, -1));
                if ($this->RDP_Evaluate($expr, false, $this->resetErrorsAndWarningsOnEachPart)) {
                    $resolvedPart = $this->GetResult();
                } else {
                    // show original and errors in-line only if user have the right to update survey content
                    if ($this->sid && Permission::model()->hasSurveyPermission($this->sid, 'surveycontent', 'update')) {
                        $resolvedPart = $this->GetPrettyPrintString();
                    } else {
                        $resolvedPart = '';
                    }
                }
                $onpageJsVarsUsed = $this->GetOnPageJsVarsUsed();
                $jsVarsUsed = $this->GetJsVarsUsed();
                $prettyPrintParts[] = $this->GetPrettyPrintString();
                $this->allVarsUsed = array_merge($this->allVarsUsed, $this->GetVarsUsed());

                if (count($onpageJsVarsUsed) > 0 && !$staticReplacement) {
                    $idName = "LEMtailor_Q_" . $questionNum . "_" . $this->substitutionNum;
                    $resolvedParts[] = "<span id='" . $idName . "'>" . $resolvedPart . "</span>";
                    $this->substitutionInfo[] = array(
                        'questionNum' => $questionNum,
                        'num' => $this->substitutionNum,
                        'id' => $idName,
                        'raw' => $stringPart[0],
                        'result' => $resolvedPart,
                        'vars' => implode('|', $jsVarsUsed),
                        'js' => $this->GetJavaScriptFunctionForReplacement($questionNum, $idName, $expr),
                    );
                } else {
                    $resolvedParts[] = $resolvedPart;
                }
            }
        }
        $result = implode('', $this->flatten_array($resolvedParts));
        $this->prettyPrintSource = implode('', $this->flatten_array($prettyPrintParts));
        return $result; // recurse in case there are nested ones, avoiding infinite loops?
    }

    /**
     * If the equation contains reference to this, expand to comma separated list if needed.
     *
     * @param string $src
     * @return string
     */
    function ExpandThisVar($src)
    {
        /** @var array */
        static $cache = [];
        if (isset($cache[$src])) {
            return $cache[$src];
        }
        /** @var boolean $setInCache set result in static $cache. mantis #14998 */
        $setInCache = true;
        /** @var string */
        $expandedVar = "";
        $tokens = $this->Tokenize($src, 1);
        foreach ($tokens as $token) {
            switch ($token[2]) {
                case 'SGQA':
                case 'WORD':
                    $splitter = '(?:\b(?:self|that))(?:\.(?:[A-Z0-9_]+))*'; // self or that, optionaly followed by dot and alnum
                    if (preg_match("/" . $splitter . "/", (string) $token[0])) {
                        $setInCache = false;
                        $expandedVar .= LimeExpressionManager::GetAllVarNamesForQ($this->questionSeq, $token[0]);
                    } else {
                        $expandedVar .= $token[0];
                    }
                    break;
                case 'DQ_STRING';
                    $expandedVar .= "\"{$token[0]}\"";
                    break;
                case 'SQ_STRING';
                    $expandedVar .= "'{$token[0]}'";
                    break;
                case 'SPACE':
                case 'LP':
                case 'RP':
                case 'COMMA':
                case 'AND_OR':
                case 'COMPARE':
                case 'NUMBER':
                case 'NOT':
                case 'OTHER':
                case 'ASSIGN':
                case 'BINARYOP':
                default:
                    $expandedVar .= $token[0];
            }
        }
        if ($setInCache) {
            $cache[$src] = $expandedVar;
        }
        return $expandedVar;
    }

    /**
     * Get info about all <span> elements needed for dynamic tailoring
     * @return array
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
    private function flatten_array(array $a)
    {
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
     * Some PHP functions require specific data types - those can be cast here.
     * @param array $funcNameToken
     * @param array $params
     * @return boolean|null
     */
    private function RDP_RunFunction($funcNameToken, $params)
    {
        $name = $funcNameToken[0];
        if (!$this->RDP_isValidFunction($name)) {
            return false;
        }

        $func = $this->RDP_ValidFunctions[$name];
        $funcName = $func[0];
        $result = 1; // default value for $this->RDP_onlyparse
        if (is_callable($funcName)) {
            $numArgsAllowed = array_slice($func, 5); // get array of allowable argument counts from end of $func
            $argsPassed = is_array($params) ? count($params) : 0;

            // for unlimited #  parameters (any value less than 0).
            try {
                if ($numArgsAllowed[0] < 0) {
                    $minArgs = abs($numArgsAllowed[0] + 1); // so if value is -2, means that requires at least one argument
                    if ($argsPassed < $minArgs) {
                        $this->RDP_AddError(sprintf(gT("Function must have at least %s argument|Function must have at least %s arguments", $minArgs), $minArgs), $funcNameToken);
                        return false;
                    }
                    if (!$this->RDP_onlyparse) {
                        switch ($funcName) {
                            case 'sprintf':
                                /* function with any number of params */
                                $result = call_user_func_array('sprintf', $params);
                                break;
                            default:
                                /* function with array as param*/
                                $result = call_user_func($funcName, $params);
                                break;
                        }
                    }
                // Call  function with the params passed
                } elseif (in_array($argsPassed, $numArgsAllowed)) {
                    switch ($argsPassed) {
                        case 0:
                            if (!$this->RDP_onlyparse) {
                                $result = call_user_func($funcName);
                            }
                            break;
                        case 1:
                            if (!$this->RDP_onlyparse) {
                                switch ($funcName) {
                                    case 'acos':
                                    case 'asin':
                                    case 'atan':
                                    case 'cos':
                                    case 'exp':
                                    case 'is_nan':
                                    case 'sin':
                                    case 'sqrt':
                                    case 'tan':
                                    case 'ceil':
                                    case 'floor':
                                    case 'round':
                                        if (is_numeric($params[0])) {
                                            $result = $funcName(floatval($params[0]));
                                        } else {
                                            $result = NAN; // NAN in PHP …
                                        }
                                        break;
                                    default:
                                        $result = call_user_func($funcName, $params[0]);
                                        break;
                                }
                            }
                            break;
                        case 2:
                            if (!$this->RDP_onlyparse) {
                                switch ($funcName) {
                                    case 'atan2':
                                    case 'pow':
                                        if (is_numeric($params[0]) && is_numeric($params[1])) {
                                            $result = $funcName(floatval($params[0]), floatval($params[1]));
                                        } else {
                                            $result = false; // Not same than other
                                        }
                                        break;
                                    default:
                                        try {
                                            $result = call_user_func($funcName, $params[0], $params[1]);
                                        } catch (\Throwable $e) {
                                            $this->RDP_AddError($e->getMessage(), $funcNameToken);
                                            return false;
                                        }
                                        break;
                                }
                            }
                            break;
                        case 3:
                            if (!$this->RDP_onlyparse) {
                                $result = call_user_func($funcName, $params[0], $params[1], $params[2]);
                            }
                            break;
                        case 4:
                        case 5:
                        case 6:
                        default:
                            /* We can accept any fixed numbers of params with call_user_func_array */
                            if (!$this->RDP_onlyparse) {
                                $result = call_user_func_array($funcName, $params);
                            }
                            break;
                    }
                } else {
                    $this->RDP_AddError(sprintf(self::gT("Function does not support %s arguments"), $argsPassed) . ' '
                            . sprintf(self::gT("Function supports this many arguments, where -1=unlimited: %s"), implode(',', $numArgsAllowed)), $funcNameToken);
                    return false;
                }
                if (function_exists("geterrors_" . $funcName)) {
                    /* @todo allow adding it for plugin , if it work …*/
                    if ($sError = call_user_func_array("geterrors_" . $funcName, $params)) {
                        $this->RDP_AddError($sError, $funcNameToken);
                        return false;
                    }
                }
            } catch (Exception $e) {
                $this->RDP_AddError($e->getMessage(), $funcNameToken);
                return false;
            }
            $token = array($result, $funcNameToken[1], 'NUMBER');
            $this->RDP_StackPush($token);
            return true;
        }
    }

    /**
     * Add user functions to array of allowable functions within the equation.
     * $functions is an array of key to value mappings like this:
     * See $this->RDP_ValidFunctions for examples of the syntax
     * @param array $functions
     */

    public function RegisterFunctions(array $functions)
    {
        $this->RDP_ValidFunctions = array_merge($this->RDP_ValidFunctions, $functions);
    }

    /**
     * Set the value of a registered variable
     * @param string $op - the operator (=,*=,/=,+=,-=)
     * @param string $name
     * @param string $value
     * @return int
     */
    private function RDP_SetVariableValue($op, $name, $value)
    {
        if ($this->RDP_onlyparse) {
            return 1;
        }
        return LimeExpressionManager::SetVariableValue($op, $name, $value);
    }

    /**
     * Split a source string into STRING vs. EXPRESSION, where the latter is surrounded by unescaped curly braces.
     * This version properly handles nested curly braces and curly braces within strings within curly braces - both of which are needed to better support JavaScript
     * Users still need to add a space or carriage return after opening braces (and ideally before closing braces too) to avoid  having them treated as expressions.
     * @param string $src
     * @return array
     */
    public function asSplitStringOnExpressions($src)
    {
        // Empty string, return an array
        if ($src === "") {
            return array();
        }
        // No replacement to do, preg_split get more time than strpos
        if (strpos($src, "{") === false || $src === "{"  || $src === "}") {
            return array (
                0 => array ($src,0,'STRING')
            );
        };

        // Seems to need split and replacement
        $parts = preg_split($this->RDP_ExpressionRegex, $src, -1, (PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE));

        $count = count($parts);
        $tokens = array();
        $inSQString = false;
        $inDQString = false;
        $curlyDepth = 0;
        $thistoken = array();
        $offset = 0;
        for ($j = 0; $j < $count; ++$j) {
            switch ($parts[$j]) {
                case '{':
                    if ($j < ($count - 1) && preg_match('/\s|\n|\r/', substr($parts[$j + 1], 0, 1))) {
                        // don't count this as an expression if the opening brace is followed by whitespace
                        $thistoken[] = '{';
                        $thistoken[] = $parts[++$j];
                    } elseif ($inDQString || $inSQString) {
                        // just push the curly brace
                        $thistoken[] = '{';
                    } elseif ($curlyDepth > 0) {
                        // a nested curly brace - just push it
                        $thistoken[] = '{';
                        ++$curlyDepth;
                    } else {
                        // then starting an expression - save the out-of-expression string
                        if (count($thistoken) > 0) {
                            $_token = implode('', $thistoken);
                            $tokens[] = array(
                                $_token,
                                $offset,
                                'STRING'
                                );
                            $offset += strlen($_token);
                        }
                        $curlyDepth = 1;
                        $thistoken = array();
                        $thistoken[] = '{';
                    }
                    break;
                case '}':
                    // don't count this as an expression if the closing brace is preceded by whitespace
                    if ($j > 0 && preg_match('/\s|\n|\r/', substr($parts[$j - 1], -1, 1))) {
                        $thistoken[] = '}';
                    } elseif ($curlyDepth == 0) {
                        // just push the token
                        $thistoken[] = '}';
                    } else {
                        if ($inSQString || $inDQString) {
                            // just push the token
                            $thistoken[] = '}';
                        } else {
                            --$curlyDepth;
                            if ($curlyDepth == 0) {
                                // then closing expression
                                $thistoken[] = '}';
                                $_token = implode('', $thistoken);
                                $tokens[] = array(
                                    $_token,
                                    $offset,
                                    'EXPRESSION'
                                    );
                                $offset += strlen($_token);
                                $thistoken = array();
                            } else {
                                // just push the token
                                $thistoken[] = '}';
                            }
                        }
                    }
                    break;
                case '\'':
                    $thistoken[] = '\'';
                    if ($curlyDepth == 0) {
                        // only counts as part of a string if it is already within an expression
                    } else {
                        if ($inDQString) {
                            // then just push the single quote
                        } else {
                            if ($inSQString) {
                                $inSQString = false; // finishing a single-quoted string
                            } else {
                                $inSQString = true; // starting a single-quoted string
                            }
                        }
                    }
                    break;
                case '"':
                    $thistoken[] = '"';
                    if ($curlyDepth == 0) {
                        // only counts as part of a string if it is already within an expression
                    } else {
                        if ($inSQString) {
                            // then just push the double quote
                        } else {
                            if ($inDQString) {
                                $inDQString = false; // finishing a double-quoted string
                            } else {
                                $inDQString = true; // starting a double-quoted string
                            }
                        }
                    }
                    break;
                case '\\':
                    if ($j < ($count - 1)) {
                        $thistoken[] = $parts[$j++];
                        $thistoken[] = $parts[$j];
                    }
                    break;
                default:
                    $thistoken[] = $parts[$j];
                    break;
            }
        }
        if (count($thistoken) > 0) {
            $tokens[] = array(
                implode('', $thistoken),
                $offset,
                'STRING',
            );
        }
        return $tokens;
    }

    /**
     * Specify the survey  mode for this survey.  Options are 'survey', 'group', and 'question'
     * @param string $mode
     */
    public function SetSurveyMode($mode)
    {
        if (preg_match('/^group|question|survey$/', $mode)) {
            $this->surveyMode = $mode;
        }
    }


    /**
     * Pop a value token off of the stack
     * @return token
     */
    public function RDP_StackPop()
    {
        if (count($this->RDP_stack) > 0) {
            return array_pop($this->RDP_stack);
        } else {
            $this->RDP_AddError(self::gT("Tried to pop value off of empty stack"), null);
            return null;
        }
    }

    /**
     * Stack only holds values (number, string), not operators
     * @param array $token
     */
    public function RDP_StackPush(array $token)
    {
        if ($this->RDP_onlyparse) {
            // If only parsing, still want to validate syntax, so use "1" for all variables
            switch ($token[2]) {
                case 'DQ_STRING':
                case 'SQ_STRING':
                    $this->RDP_stack[] = array(1, $token[1], $token[2]);
                    break;
                case 'NUMBER':
                default:
                    $this->RDP_stack[] = array(1, $token[1], 'NUMBER');
                    break;
            }
        } else {
            $this->RDP_stack[] = $token;
        }
    }

    /**
     * Public call of RDP_Tokenize
     *
     * @param string $sSource : the string to tokenize
     * @param bool $bOnEdit : on edition, actually don't remove space
     * @return array
     */
    public function Tokenize($sSource, $bOnEdit)
    {
        return $this->RDP_Tokenize($sSource, $bOnEdit);
    }

    /**
     * Split the source string into tokens, removing whitespace, and categorizing them by type.
     *
     * @param string $sSource : the string to tokenize
     * @param bool $bOnEdit : on edition, actually don't remove space
     * @return array
     */
    private function RDP_Tokenize($sSource, $bOnEdit = false)
    {
        $cacheKey = 'RDP_Tokenize_' . $sSource . json_encode($bOnEdit);
        $value = EmCacheHelper::get($cacheKey);
        if ($value !== false) {
            return $value;
        }

        // $aInitTokens = array of tokens from equation, showing value and offset position.  Will include SPACE.
        if ($bOnEdit) {
                    $aInitTokens = preg_split($this->RDP_TokenizerRegex, $sSource, -1, (PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE));
        } else {
                    $aInitTokens = preg_split($this->RDP_TokenizerRegex, $sSource, -1, (PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE));
        }
        // $aTokens = array of tokens from equation, showing value, offsete position, and type.  Will not contain SPACE if !$bOnEdit, but will contain OTHER
        $aTokens = array();
        // Add token_type to $tokens:  For each token, test each categorization in order - first match will be the best.
        $countInitTokens = count($aInitTokens);
        for ($j = 0; $j < $countInitTokens; ++$j) {
            for ($i = 0; $i < count($this->RDP_CategorizeTokensRegex); ++$i) {
                $sToken = $aInitTokens[$j][0];
                if (preg_match($this->RDP_CategorizeTokensRegex[$i], $sToken)) {
                    if ($this->RDP_TokenType[$i] !== 'SPACE' || $bOnEdit) {
                        $aInitTokens[$j][2] = $this->RDP_TokenType[$i];
                        if ($this->RDP_TokenType[$i] == 'DQ_STRING' || $this->RDP_TokenType[$i] == 'SQ_STRING') {
                            // remove outside quotes
                            $sUnquotedToken = str_replace(array('\"', "\'", "\\\\"), array('"', "'", '\\'), substr($sToken, 1, -1));
                            $aInitTokens[$j][0] = $sUnquotedToken;
                        }
                        $aTokens[] = $aInitTokens[$j]; // get first matching non-SPACE token type and push onto $tokens array
                    }
                    break; // only get first matching token type
                }
            }
        }

        EmCacheHelper::set($cacheKey, $aTokens);
        return $aTokens;
    }

    /**
     * Show a table of allowable ExpressionScript Engine functions
     * @return string
     */
    static function ShowAllowableFunctions()
    {
        $em = new ExpressionManager();
        $output = "<div class='h3'>Functions Available within ExpressionScript Engine</div>\n";
        $output .= "<table border='1' class='table'><tr><th>Function</th><th>Meaning</th><th>Syntax</th><th>Reference</th></tr>\n";
        foreach ($em->RDP_ValidFunctions as $name => $func) {
            $output .= "<thead><tr><th>" . $name . "</th><th>" . $func[2] . "</th><th>" . $func[3] . "</th><th>";

        // 508 fix, don't output empty anchor tags
            if ($func[4]) {
                $output .= "<a href='" . $func[4] . "'>" . $func[4] . "</a>";
            }

            $output .= "&nbsp;</td></tr>\n";
        }
        $output .= "</table>\n";
        return $output;
    }

    /**
     * Show a table of allowable ExpressionScript Engine functions
     * @return string
     */
    static function GetAllowableFunctions()
    {
        $em = new ExpressionManager();
        return $em->RDP_ValidFunctions;
    }

    /**
     * Show a translated string for admin user, always in admin language #12208
     * public for geterrors_exprmgr_regexMatch function only
     * @param string $string to translate
     * @param string $sEscapeMode Valid values are html (this is the default, js and unescaped)
     * @return string : translated string
     */
    public static function gT($string, $sEscapeMode = 'html')
    {
        return gT(
            $string,
            $sEscapeMode,
            Yii::app()->session->get('adminlang', App()->getConfig("defaultlang"))
        );
    }
}

/**
 * Used by usort() to order Error tokens by their position within the string
 * This must be outside of the class in order to work in PHP 5.2
 * @param array $a
 * @param array $b
 * @return int
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
 * @param EMWarningInterface $a
 * @param EMWarningInterface $b
 * @return int
 * @todo Unify errors and warnings with a EMErrorComparableInterface
 */
function cmpWarningTokens(EMWarningInterface $a, EMWarningInterface $b)
{
    $tokenA = $a->getToken();
    $tokenB = $b->getToken();

    if (is_null($tokenA)) {
        if (is_null($tokenB)) {
            return 0;
        }
        return 1;
    }
    if (is_null($tokenB)) {
        return -1;
    }

    if ($tokenA[1] == $tokenB[1]) {
        return 0;
    }
    return ($tokenA[1] < $tokenB[1]) ? -1 : 1;
}

/**
 * Count the number of answered questions (non-empty)
 * @param array $args
 * @return int
 */
function exprmgr_count($args)
{
    $j = 0; // keep track of how many non-null values seen
    foreach ($args as $arg) {
        if ($arg != '') {
            ++$j;
        }
    }
    return $j;
}

/**
 * Count the number of answered questions (non-empty) which match the first argument
 * @param array $args
 * @return int
 */
function exprmgr_countif($args)
{
    $j = 0; // keep track of how many non-null values seen
    $match = array_shift($args);
    foreach ($args as $arg) {
        if ($arg == $match) {
            ++$j;
        }
    }
    return $j;
}

/**
 * Count the number of answered questions (non-empty) which meet the criteria (arg op value)
 * @param array $args
 * @return int
 */
function exprmgr_countifop($args)
{
    $j = 0;
    $op = array_shift($args);
    $value = array_shift($args);
    foreach ($args as $arg) {
        switch ($op) {
            case '==':
            case 'eq':
                if ($arg == $value) {
                    ++$j;
                }
                break;
            case '>=':
            case 'ge':
                if ($arg >= $value) {
                    ++$j;
                }
                break;
            case '>':
            case 'gt':
                if ($arg > $value) {
                    ++$j;
                }
                break;
            case '<=':
            case 'le':
                if ($arg <= $value) {
                    ++$j;
                }
                break;
            case '<':
            case 'lt':
                if ($arg < $value) {
                    ++$j;
                }
                break;
            case '!=':
            case 'ne':
                if ($arg != $value) {
                    ++$j;
                }
                break;
            case 'RX':
                try {
                    if (@preg_match($value, (string) $arg)) {
                        ++$j;
                    }
                } catch (Exception $e) {
                    // Do nothing
                }
                break;
        }
    }
    return $j;
}
/**
 * Find position of first occurrence of unicode string in a unicode string, case insensitive
 * @param string $haystack : checked string
 * @param string $needle : string to find
 * @param $offset : offset
 * @return int|false : position or false if not found
 */
function exprmgr_stripos($haystack, $needle, $offset = 0)
{
    if ($offset > mb_strlen($haystack)) {
            return false;
    }
    return mb_stripos($haystack, $needle, $offset, 'UTF-8');
}
/**
 * Finds first occurrence of a unicode string within another, case-insensitive
 * @param string $haystack : checked string
 * @param string $needle : string to find
 * @param boolean $before_needle : portion to return
 * @return string|false
 */
function exprmgr_stristr($haystack, $needle, $before_needle = false)
{
    return mb_stristr($haystack, $needle, $before_needle, 'UTF-8');
}
/**
 * Get unicode string length
 * @param string $string
 * @return int
 */
function exprmgr_strlen($string)
{
    return mb_strlen($string, 'UTF-8');
}
/**
 * Find position of first occurrence of unicode string in a unicode string
 * @param string $haystack : checked string
 * @param string $needle : string to find
 * @param int $offset : offset
 * @return int|false : position or false if not found
 */
function exprmgr_strpos($haystack, $needle, $offset = 0)
{
    if ($offset > mb_strlen($haystack)) {
            return false;
    }
    return mb_strpos($haystack, $needle, $offset, 'UTF-8');
}
/**
 * Finds first occurrence of a unicode string within another
 * @param string $haystack : checked string
 * @param string $needle : string to find
 * @param boolean $before_needle : portion to return
 * @return string|false
 */
function exprmgr_strstr($haystack, $needle, $before_needle = false)
{
    return mb_strstr($haystack, $needle, $before_needle, 'UTF-8');
}
/**
 * Make an unicode string lowercase
 * @param string $string
 * @return string
 */
function exprmgr_strtolower($string)
{
    return mb_strtolower($string, 'UTF-8');
}
/**
 * Make an unicode string uppercase
 * @param string $string
 * @return string
 */
function exprmgr_strtoupper($string)
{
    return mb_strtoupper($string, 'UTF-8');
}
/**
 * Get part of unicode string
 * @param string $string
 * @param int $start
 * @param int $end
 * @return string
 */
function exprmgr_substr($string, $start, $end = null)
{
    return mb_substr($string, $start, $end, 'UTF-8');
}
/**
 * Sum of values of answered questions which meet the criteria (arg op value)
 * @param array $args
 * @return int
 */
function exprmgr_sumifop($args)
{
    $result = 0;
    $op = array_shift($args);
    $value = array_shift($args);
    foreach ($args as $arg) {
        switch ($op) {
            case '==':
            case 'eq':
                if ($arg == $value) {
                    $result += $arg;
                }
                break;
            case '>=':
            case 'ge':
                if ($arg >= $value) {
                    $result += $arg;
                }
                break;
            case '>':
            case 'gt':
                if ($arg > $value) {
                    $result += $arg;
                }
                break;
            case '<=':
            case 'le':
                if ($arg <= $value) {
                    $result += $arg;
                }
                break;
            case '<':
            case 'lt':
                if ($arg < $value) {
                    $result += $arg;
                }
                break;
            case '!=':
            case 'ne':
                if ($arg != $value) {
                    $result += $arg;
                }
                break;
            case 'RX':
                try {
                    if (@preg_match($value, (string) $arg)) {
                        $result += $arg;
                    }
                } catch (Exception $e) {
                    // Do nothing
                }
                break;
        }
    }
    return $result;
}

/**
 * Validate a Gregorian date
 * @see https://www.php.net/checkdate
 * Check if all params are valid before send it to PHP checkdate to avoid PHP Warning
 *
 * @param mixed $month
 * @param mixed $day
 * @param mixed $year
 * @return boolean
 */
function exprmgr_checkdate($month, $day, $year)
{
    if (
        (!ctype_digit($month) && !is_int($month))
        || (!ctype_digit($day) && !is_int($day))
        || (!ctype_digit($year) && !is_int($year))
    ) {
        return false;
    }
    return checkdate(intval($month), intval($day), intval($year));
}

/**
 * Find the closest matching Numerical input values in a list an replace it by the
 * corresponding value within another list
 *
 * @author Johannes Weberhofer, 2013
 *
 * @param double $fValueToReplace
 * @param integer $iStrict - 1 for exact matches only otherwise interpolation the
 *          closest value should be returned
 * @param string $sTranslateFromList - comma seperated list of numeric values to translate from
 * @param string $sTranslateToList - comma seperated list of numeric values to translate to
 * @return integer|null
 */
function exprmgr_convert_value($fValueToReplace, $iStrict, $sTranslateFromList, $sTranslateToList)
{
    if ((is_numeric($fValueToReplace)) && ($iStrict != null) && ($sTranslateFromList != null) && ($sTranslateToList != null)) {
        $aFromValues = explode(',', $sTranslateFromList);
        $aToValues = explode(',', $sTranslateToList);
        if ((count($aFromValues) > 0) && (count($aFromValues) == count($aToValues))) {
            $fMinimumDiff = null;
            $iNearestIndex = 0;
            for ($i = 0; $i < count($aFromValues); $i++) {
                if (!is_numeric($aFromValues[$i])) {
                    // break processing when non-numeric variables are about to be processed
                    return null;
                }
                $fCurrentDiff = abs($aFromValues[$i] - $fValueToReplace);
                if ($fCurrentDiff === 0) {
                    return $aToValues[$i];
                } elseif ($i === 0) {
                    $fMinimumDiff = $fCurrentDiff;
                } elseif ($fMinimumDiff > $fCurrentDiff) {
                    $fMinimumDiff = $fCurrentDiff;
                    $iNearestIndex = $i;
                }
            }
            if ($iStrict != 1) {
                return $aToValues[$iNearestIndex];
            }
        }
    }
    return null;
}

/**
 * Return format a local time/date
 * Need to test if timestamp is numeric (else E_WARNING with debug>0)
 * @param string $format
 * @param int $timestamp
 * @return string|false
 * @link http://php.net/function.date.php
 */
function exprmgr_date($format, $timestamp = null)
{
    $timestamp = $timestamp ?? time();
    if (!is_numeric($timestamp)) {
        return false;
    }
    return date($format, $timestamp);
}

function exprmgr_abs($num)
{
    if (!is_numeric($num)) {
        return false;
    }

    // Trying to cast either to int or float, depending on the value.
    $num = $num + 0;
    return abs($num);
}

/**
 * Calculate the sum of values in an array
 * @see https://bugs.limesurvey.org/view.php?id=19897
 * @see https://www.php.net/manual/en/function.array-sum.php
 * Like php 8.1 and before : Ignore array or object, cast to float string and cast to int other.
 * @param array $args
 * @return float
 */
function exprmgr_array_sum($args)
{
    $args = array_map(function ($arg) {
        if (is_int($arg) || is_float($arg)) {
            return $arg;
        }
        if (is_string($arg)) {
            return floatval($arg);
        }
        if (is_array($arg) || is_object($arg)) {
            return 0;
        }
        return intval($arg);
    }, $args);
    return array_sum($args);
}

/**
 * If $test is true, return $iftrue, else return $iffalse
 * @param mixed $testDone
 * @param mixed $iftrue
 * @param mixed $iffalse
 * @return mixed
 */
function exprmgr_if($testDone, $iftrue, $iffalse = '')
{
    if ($testDone) {
        return $iftrue;
    }
    return $iffalse;
}

/**
 * Return true if the variable is an integer for LimeSurvey
 * Allow usage of numeric answercode as int
 * Can not use is_int due to SQL DECIMAL system.
 * @param string $arg
 * @return integer
 * @link http://php.net/is_int#82857
 */
function exprmgr_int($arg)
{
    if (strpos($arg, ".")) {
        // DECIMAL from SQL return always .00000000, the remove all 0 and one . , see #09550
        $arg = preg_replace("/\.$/", "", rtrim(strval($arg), "0"));
    }
    // Allow 000 for value
    // Disallow '' (and false) @link https://bugs.limesurvey.org/view.php?id=17950
    return (preg_match("/^-?\d+$/", $arg));
}

/**
 * Join together $args[0-N] with ', '
 * @param array $args
 * @return string
 */
function exprmgr_list($args)
{
    $result = "";
    $j = 1; // keep track of how many non-null values seen
    foreach ($args as $arg) {
        if ($arg != '') {
            if ($j > 1) {
                $result .= ', ' . $arg;
            } else {
                $result .= $arg;
            }
            ++$j;
        }
    }
    return $result;
}

/**
 * Implementation of listifop( $cmpAttr, $op, $value, $retAttr, $glue, $sgqa1, ..., sgqaN )
 * Return a list of retAttr from sgqa1...sgqaN which pass the critiera (cmpAttr op value)
 * @param array $args
 * @return string
 */
function exprmgr_listifop($args)
{
    $result = "";
    $cmpAttr = array_shift($args);
    $op = array_shift($args);
    $value = array_shift($args);
    $retAttr = array_shift($args);
    $glue = array_shift($args);

    $validAttributes = "/" . LimeExpressionManager::getRegexpValidAttributes() . "/";
    if (! preg_match($validAttributes, (string) $cmpAttr)) {
        return $cmpAttr . " not recognized ?!";
    }
    if (! preg_match($validAttributes, (string) $retAttr)) {
        return $retAttr . " not recognized ?!";
    }

    foreach ($args as $sgqa) {
        $cmpVal = LimeExpressionManager::GetVarAttribute($sgqa, $cmpAttr, null, -1, -1);
        $match = false;

        switch ($op) {
            case '==':
            case 'eq':
                $match = ($cmpVal == $value);
                break;
            case '>=':
            case 'ge':
                $match = ($cmpVal >= $value);
                break;
            case '>':
            case 'gt':
                $match = ($cmpVal > $value);
                break;
            case '<=':
            case 'le':
                $match = ($cmpVal <= $value);
                break;
            case '<':
            case 'lt':
                $match = ($cmpVal < $value);
                break;
            case '!=':
            case 'ne':
                $match = ($cmpVal != $value);
                break;
            case 'RX':
                try {
                    $match = preg_match($value, (string) $cmpVal);
                } catch (Exception $ex) {
                    return "Invalid RegEx";
                }
                break;
        }

        if ($match) {
            $retVal = LimeExpressionManager::GetVarAttribute($sgqa, $retAttr, null, -1, -1);
            if ($result != "") {
                $result .= $glue;
            }
            $result .= $retVal;
        }
    }

    return $result;
}

/**
 * return log($arg[0],$arg[1]=e)
 * @param array $args
 * @return float
 */
function exprmgr_log($args)
{
    if (count($args) < 1) {
        return NAN;
    }
    $number = $args[0];
    if (!is_numeric($number)) {
        return NAN;
    }
    $base = $args[1] ?? exp(1);
    if (!is_numeric($base)) {
        return NAN;
    }
    if (floatval($base) <= 0) {
        return NAN;
    }
    return log($number, $base);
}
/**
 * Get Unix timestamp for a date : false if parameters is invalid.
 * Get default value for unset (or null) value
 * E_NOTICE if arguments are not numeric (debug>0), then test it before
 * @param int $hour
 * @param int $minute
 * @param int $second
 * @param int $month
 * @param int $day
 * @param int $year
 * @return int|boolean
 */
function exprmgr_mktime($hour = null, $minute = null, $second = null, $month = null, $day = null, $year = null)
{
    $hour = $hour ?? date("H");
    $minute = $minute ?? date("i");
    $second = $second ?? date("s");
    $month = $month ?? date("n");
    $day = $day ?? date("j");
    $year = $year ?? date("Y");
    $hour = $hour ?? date("H");
    $iInvalidArg = count(array_filter(array($hour, $minute, $second, $month, $day, $year), function ($timeValue) {
        return !is_numeric($timeValue); /* This allow get by string like "01.000" , same than javascript with 2.72.6 and default PHP(5.6) function*/
    }));
    if ($iInvalidArg) {
        return false;
    }
    return mktime($hour, $minute, $second, $month, $day, $year);
}

/**
 * Join together $args[N]
 * @param array $args
 * @return string
 */
function exprmgr_join($args)
{
    return implode("", $args);
}

/**
 * Join together $args[1-N] with $arg[0]
 * @param array $args
 * @return string
 */
function exprmgr_implode($args)
{
    if (count($args) <= 1) {
        return "";
    }
    $joiner = array_shift($args);
    return implode($joiner, $args);
}

/**
 * Return true if the variable is NULL or blank.
 * @param null|string|boolean $arg
 * @return boolean
 */
function exprmgr_empty($arg)
{
    if ($arg === null || $arg === "" || $arg === false) {
        return true;
    }
    return false;
}

/**
 * Compute the Sample Standard Deviation of a set of numbers ($args[0-N])
 * @param array $args
 * @return float
 */
function exprmgr_stddev($args)
{
    $vals = array();
    foreach ($args as $arg) {
        if (is_numeric($arg)) {
            $vals[] = $arg;
        }
    }
    $count = count($vals);
    if ($count <= 1) {
        return 0; // what should default value be?
    }
    $sum = 0;
    foreach ($vals as $val) {
        $sum += $val;
    }
    $mean = $sum / $count;

    $sumsqmeans = 0;
    foreach ($vals as $val) {
        $sumsqmeans += ($val - $mean) * ($val - $mean);
    }
    $stddev = sqrt($sumsqmeans / ($count - 1));
    return $stddev;
}

/**
 * Javascript equivalent does not cope well with ENT_QUOTES and related PHP constants, so set default to ENT_QUOTES
 * @param string $string
 * @return string
 */
function expr_mgr_htmlspecialchars($string)
{
    return htmlspecialchars($string, ENT_QUOTES);
}

/**
 * Javascript equivalent does not cope well with ENT_QUOTES and related PHP constants, so set default to ENT_QUOTES
 * @param string $string
 * @return string
 */
function expr_mgr_htmlspecialchars_decode($string)
{
    return htmlspecialchars_decode($string, ENT_QUOTES);
}

/**
 * Return true if $input matches the regular expression $pattern
 * @param string $pattern
 * @param string $input
 * @return boolean
 */
function exprmgr_regexMatch($pattern, $input)
{
    // Test the regexp pattern agains null : must always return 0, false if error happen
    if (@preg_match($pattern . 'u', '') === false) {
        return false; // invalid : true or false ?
    }
    // 'u' is the regexp modifier for unicode so that non-ASCII string will be validated properly
    return preg_match($pattern . 'u', $input);
}
/**
 * Return error information from pattern of regular expression $pattern
 * @param string $pattern
 * @param string $input
 * @return string|null
 */
function geterrors_exprmgr_regexMatch($pattern, $input)
{
    // @todo : use set_error_handler to get the preg_last_error
    if (@preg_match($pattern . 'u', '') === false) {
        return sprintf(ExpressionManager::gT('Invalid PERL Regular Expression: %s'), htmlspecialchars($pattern));
    }
}

/**
 * Display number with comma as radix separator, if needed
 * @param string $value
 * @return string
 */
function exprmgr_fixnum($value)
{
    if (LimeExpressionManager::usingCommaAsRadix()) {
        $newval = implode(',', explode('.', $value));
        return $newval;
    }
    return $value;
}
/**
 * Returns true if all non-empty values are unique
 * @param array $args
 * @return boolean
 */
function exprmgr_unique($args)
{
    $uniqs = array();
    foreach ($args as $arg) {
        if (trim((string) $arg) == '') {
            continue; // ignore blank answers
        }
        if (isset($uniqs[$arg])) {
            return false;
        }
        $uniqs[$arg] = 1;
    }
    return true;
}
