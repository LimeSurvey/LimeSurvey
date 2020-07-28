/*
 * This file is part of LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Core JavaScript functions needed by ExpressionManager
 * @author Thomas M. White (TMSWhite)
 * @author Denis Chenu (Shnoulle)
 *
 * Portion from php.js licensed under the MIT licenses.
 * Copyright (c) 2013 Kevin van Zonneveld (http://kvz.io)
 * and Contributors (http://phpjs.org/authors)
 */

/**
 * Default event to trigger on answer part
 * Launch function according to anser-item type
 * @todo : checkconditions/fixnum_checkconditions in this function
 **/
/* text/number item */
$(document).on("keyup change",".answer-item textarea:not([onkeyup]),.answer-item :text:not([onkeyup])",function(event){
    if($(this).data("number"))// data-type ?
    {
        fixnum_checkconditions($(this).val(), $(this).attr('name'), 'text', 'keyup', $(this).data("integer"))
    }
    else
    {
        checkconditions($(this).val(), $(this).attr('name'), 'text', 'keyup')
    }
});
/* select/dropdown item */
$(document).on("change",".select-item select:not([onchange]),.dropdown-item select:not([onchange])",function(event){
    checkconditions($(this).val(), $(this).attr('name'), 'select-one', 'change')
});
/* radio/button item */
$(document).on("change",".radio-item :radio:not([onclick]), .button-item :radio:not([onclick])",function(event){
    checkconditions($(this).val(), $(this).attr('name'), 'radio', 'click')
});
/* checkbox item */
$(document).on("change",".checkbox-item :checkbox:not([onclick]),.button-item :checkbox:not([onclick])",function(event){
    checkconditions($(this).val(), $(this).attr('name'), 'checkbox', 'click')
});

/**
 * For number
 */
var pad = function(num,places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}
/**
 * All EM function (see em_core_helper.php)
 */
function LEMcount()
{
    // takes variable number of arguments - returns count of those arguments that are not null/empty
    var result=0;
    for (i=0;i<arguments.length;++i) {
        var arg = arguments[i];
        if (arg !== '') {
            ++result;
        }
    }
    return result;
}

function LEMunique()
{
    var uniqs = new Array();
    for (i=0;i<arguments.length;++i) {
        var arg = arguments[i];
        if (trim(arg)=='')
            continue;
        if (typeof uniqs[arg] !== 'undefined')
            return false;
        uniqs[arg] = 1;
    }
    return true;
}

function LEMcountif()
{
    // takes variable number of arguments - returns count of those arguments that match the first parameter
    var result=0;
    var match=arguments[0];
    for (i=1;i<arguments.length;++i) {
        var arg = arguments[i];
        if (arg == match) {
            ++result;
        }
    }
    return result;
}

function LEMcountifop()
{
    // takes variable number of arguments - returns count of answered questions which meet the criteria (arg op value)
    var result=0;
    var op=arguments[0];
    var value=arguments[1];
    if (op == 'RX') {
        var reg = new RegExp(value.substr(1,value.length-2));
    }
    for (i=2;i<arguments.length;++i) {
        var arg = arguments[i];
        switch(op)
        {
            case '==': case 'eq': if (arg == value) { ++result; } break;
            case '>=': case 'ge': if (arg >= value) { ++result; } break;
            case '>':  case 'gt': if (arg > value) { ++result; } break;
            case '<=': case 'le': if (arg <= value) { ++result; } break;
            case '<':  case 'lt': if (arg < value) { ++result; } break;
            case '!=': case 'ne': if (arg != value) { ++result; } break;
            case 'RX': {
                try {
                    if (reg.test(arg)) {
                        ++result;
                    }
                }
                catch (err) {
                    return false;
                }
            }
        }
    }
    return result;
}

function LEMsumifop()
{
    // takes variable number of arguments - returns sum of answered questions which meet the criteria (arg op value)
    var result=0;
    var op=arguments[0];
    var value=arguments[1];
    if (op == 'RX') {
        var reg = new RegExp(value.substr(1,value.length-2));
    }
    for (i=2;i<arguments.length;++i) {
        var arg = arguments[i] || 0;
        if (LEMis_numeric(arg)){
            try{
                arg = new Decimal(arg);
            } catch(e){
                arg = new Decimal(arg.toString().replace(/,/,'.'));
            }
        }
        else if(arg === true){
            arg=1;
        }

        switch(op) {
            case '==': case 'eq': if (arg == value) { result += arg; } break;
            case '>=': case 'ge': if (arg >= value) { result += arg; } break;
            case '>':  case 'gt': if (arg > value) { result += arg; } break;
            case '<=': case 'le': if (arg <= value) { result += arg; } break;
            case '<':  case 'lt': if (arg < value) { result += arg; } break;
            case '!=': case 'ne': if (arg != value) { result += arg; } break;
            case 'RX': {
                try {
                    if (reg.test(arg)) {
                        result += arg;
                    }
                }
                catch (err) {
                    return false;
                }
            }
        }
    }
    return result;
}

function LEMpi()
{
    return Math.PI;
}

function LEMsum()
{
    // takes variable number of arguments, returns their sum
    var result= new Decimal(0);
    for (i=0;i<arguments.length;++i) {
        var arg = arguments[i] || 0;
        if (LEMis_numeric(arg)){
            try{
                arg = new Decimal(arg);
            } catch(e){
                arg = new Decimal(arg.toString().replace(/,/,'.'));
            }
            //create decimal checks!
            result = result.add(arg);
        } else if(arg === true){
            result = result.add(1);
        }
    }
    return result.toString();
}

function LEMintval(a)
{
    if (isNaN(a)) {
        return NaN;
    }
    return Math.floor(+a);
}

function LEMis_null(a)
{
    return (a == null);
}

function LEMis_float(a)
{
    if (!LEMis_numeric(a))
    {
        return false;
    }
    var num = new Number(a);
    // should this only return true if there is a non-zero decimal part to the number?
    return (Math.floor(num) != num);
}

/**
 * Test if mixed_var is in integer value (no matter the real type)
 */
function LEMis_int(mixed_var)
{
    try {
        var iCheckValue = new Decimal(mixed_var)
    }
    catch (err) {
        return false;
    }
    return iCheckValue.isInteger();
}
/**
 * Test if mixed_var is a PHP numeric value
 * From: http://phpjs.org/functions/is_numeric/
 */
function LEMis_numeric(mixed_var)
{
    var isNumericRegex = new RegExp(/^(-)?\d*(,|\.)?\d*$/);
    return ( ( ( typeof mixed_var === 'string' && isNumericRegex.test(mixed_var)) || typeof mixed_var === 'number') && mixed_var !== '' && !isNaN(mixed_var));
    // var whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    // return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -1)) && mixed_var !== '' && !isNaN(mixed_var);
}

function LEMis_string(a)
{
    return isNaN(a);
}

/**
 * Find the closest matching numerical input values in a list an replace it by the
 * corresponding value within another list
 *
 * @author Johannes Weberhofer, 2013
 *
 * @param numeric fValueToReplace
 * @param numeric iStrict - 1 for exact matches only otherwise interpolation the
 * 		  closest value should be returned
 * @param string sTranslateFromList - comma seperated list of values to translate from
 * @param string sTranslateToList - comma seperated list of values to translate to
 * @return numeric
 */
function LEMconvert_value( fValueToReplace, iStrict, sTranslateFromList, sTranslateToList)
{
    if ( isNaN(fValueToReplace) || (iStrict==null) || (sTranslateFromList==null) || (sTranslateToList==null) )
    {
        return null;
    }
    aFromValues = sTranslateFromList.split(",");
    aToValues = sTranslateToList.split(",");
    if ( (aFromValues.length > 0)  && (aFromValues.length == aToValues.length) )
    {
        fMinimumDiff = null;
        iNearestIndex = 0;
        for ( i = 0; i < aFromValues.length; i++) {
            if ( isNaN(aFromValues[i]) ) {
                // break processing when non-numeric variables are about to be processed
                return null;
            }
            fCurrentDiff = Math.abs(aFromValues[i] - fValueToReplace);
            if (fCurrentDiff === 0) {
                return aToValues[i];
            } else if (i === 0) {
                fMinimumDiff = fCurrentDiff;
            } else if ( fMinimumDiff > fCurrentDiff ) {
                fMinimumDiff = fCurrentDiff;
                iNearestIndex = i;
            }
        }
        if ( iStrict != 1 ) {
            return aToValues[iNearestIndex];
        }
    }
    return null;
}

/**
 * return float value of mixedVar (near like PHP floatval)
 * @param mixed mixedVar
 * @return float
 * @see: https://locutus.io/php/floatval/
 * original by: Michael White (https://getsprink.com)
 *      note 1: The native parseFloat() method of JavaScript returns NaN
 *      note 1: when it encounters a string before an int or float value.
 *   example 1: floatval('150.03_page-section')
 *   returns 1: 150.03
 *   example 2: floatval('page: 3')
 *   returns 2: 0
 *   example 3: floatval('-50 + 8')
 *   returns 3: -50
 */
function LEMfloatval(mixedVar)
{
    return (parseFloat(mixedVar) || 0)
}
/**
 * If $test is true, return $iftrue, else return $iffalse
 * @param mixed testDone
 * @param mixed ifTrue
 * @param mixed ifFalse
 * @return mixed
 */
function LEMif(testDone,ifTrue,ifFalse)
{
    if(testDone && testDone !== '0') { // so consistent with PHP
        return ifTrue;
    }
    if (typeof ifFalse == "undefined") { // Pre ES6 (else, just add ifFalse = '' in function call
        return '';
    }
    return ifFalse;
}

/**
 *  Returns comma separated list of non-null values
 */

function LEMlist()
{
    // takes variable number of arguments
    var result="";
    var joiner = ', ';
    j=1;    // keep track of how many non-null values seen
    for (i=0;i<arguments.length;++i) {
        var arg = arguments[i];
        if (arg !== '') {
            if (j > 1) {
                result += joiner + arg;
            }
            else {
                result += arg;
            }
            ++j;
        }
    }
    return result;
}

/**
 * Implementation of listifop( cmpAttr, op, value, retAttr, glue, sgqa1, ..., sgqaN )
 * Returns a list of retAttr from sgqa1...sgqaN which pass the critiera (cmpAttr op value)
 */
function LEMlistifop()
{
    // takes variable number of arguments
    var result = "";
    var cmpAttr = arguments[0];
    var op = arguments[1];
    var value = arguments[2];
    var retAttr = arguments[3];
    var glue = arguments[4];

    var validAttributes = "/code|gid|grelevance|gseq|jsName|mandatory|NAOK|qid|qseq|question|readWrite|relevanceStatus|relevance|rowdivid|sgqa|shown|type|valueNAOK|value/";

    if ( ! cmpAttr.match( validAttributes ) ) {
        return cmpAttr +" not recognized ?!";
    }
    if ( ! retAttr.match( validAttributes ) ) {
        return retAttr +" not recognized ?!";
    }

    if ( op == 'RX' ) {
        var reg = new RegExp( value.substr( 1, value.length-2 ) );
    }

    for ( i=5; i < arguments.length; ++i ) {
        var sgqa = arguments[i];
        var cmpVal = LEMval( sgqa +"."+ cmpAttr ); // Equal to LimeExpressionManager::GetVarAttribute($sgqa,$cmpAttr,null,-1,-1) ?
        var match = false;

        switch(op)
        {
            case '==': case 'eq': match = (cmpVal == value); break;
            case '>=': case 'ge': match = (cmpVal >= value); break;
            case '>':  case 'gt': match = (cmpVal > value);  break;
            case '<=': case 'le': match = (cmpVal <= value); break;
            case '<':  case 'lt': match = (cmpVal < value);  break;
            case '!=': case 'ne': match = (cmpVal != value); break;
            case 'RX': try { match = reg.test( cmpVal ); }
            catch (err) { return "Invalid RegEx"; } break;
        }

        if ( match ) {
            retVal = LEMval( sgqa +"."+ retAttr ); // Equal to LimeExpressionManager::GetVarAttribute($sgqa,$retAttr,null,-1,-1) ?
            if ( result != "" ) {
                result += glue;
            }
            result += retVal;
        }
    }

    return result;
}

/**
 *  Returns Natural logarithm of a number
 */

function LEMlog()
{
    // takes variable number of arguments
    if (arguments.length < 1) {
        return NaN;
    }
    var base=Math.exp(1);
    if(arguments.length>1){
        base = arguments[1];
        if (isNaN(base)) { return NaN;}
        if (base<=0 ) { return NaN;}
        base=Math.abs(parseFloat(arguments[1]));
    }
    if(base==Math.exp(1)){// Not needed
        return Math.log(arguments[0]);
    }else{
        return Math.log(arguments[0])/Math.log(base);
    }
}

/**
 * max like php in LimeSurvey, start by https://github.com/kvz/locutus/blob/master/src/php/math/max.js
 * @see https://bugs.limesurvey.org/view.php?id=14337
 * Review for ExpressionManager
 **/
function LEMmax () {
  // original at: http://locutus.io/php/max/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //  revised by: Denis Chenu for LimeSurvey specific
  //      note 1: Long code cause we're aiming for maximum PHP compatibility
  //   example 1: max(1, 3, 5, 6, 7,'')
  //   returns 1: 7
  //   example 2: max(1, 'hello','')
  //   returns 2: 'hello'
  //   example 3: max('hello', 1,'')
  //   returns 3: 'hello'
  //   example 4: max('2hello', 1,'')
  //   returns 4: '2hello'
  //   example 5: max('1hello', 2,'')
  //   returns 5: 2
  //   example 6: max(-1, -2,'')
  //   returns 6: -1

  var ar
  var retVal
  var i = 0
  var n = 0
  var argv = arguments
  var argc = argv.length

  var _compare = function (current, next) {
    if(next === '') {
      return -1;
    }
    if(current === '') {
      return 1;
    }
    if (current === next) {
      return 0
    }
    if (isNaN(next) && !isNaN(current)) {
      return (next.toString() > current.toString() ? 1 : -1)
    }
    if (isNaN(current) && !isNaN(next)) {
      return (next.toString() > current.toString() ? 1 : -1)
    }

    return (next > current ? 1 : -1)
  }

  if (argc === 0) {
    return '';
  }
  if (argc === 1) {
    return argv[0];
  }

  ar = argv
  retVal = ar[0]
  for (i = 1, n = ar.length; i < n; ++i) {
    if (_compare(retVal, ar[i]) === 1) {
      retVal = ar[i]
    }
  }

  return retVal
}
/**
 * min like php in LimeSurvey : https://github.com/kvz/locutus/blob/master/src/php/math/min.js
 * @see https://bugs.limesurvey.org/view.php?id=14337
 * Review for ExpressionManager 
 **/
function LEMmin () {
  // original at: http://locutus.io/php/max/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //  revised by: Denis Chenu for LimeSurvey specific
  //      note 1: Long code cause we're aiming for maximum PHP compatibility
  //   example 1: min(1, 3, 5, 6, 7)
  //   returns 1: 1
  //   example 2: max(1, 'hello')
  //   returns 2: 1
  //   example 3: max('hello', 1)
  //   returns 3: 1
  //   example 4: max('2hello', 1)
  //   returns 4: 1
  //   example 5: max('1hello', 2)
  //   returns 5: '1hello'
  //   example 6: min(-1, -2)
  //   returns 6: -2
  //   example 7: min(-1, '')
  //   returns 7: ''

  var ar
  var retVal
  var i = 0
  var n = 0
  var argv = arguments
  var argc = argv.length

  var _compare = function (current, next) {
    if(next === '') {
      return -1;
    }
    if(current === '') {
      return 1;
    }
    if (current === next) {
      return 0;
    }
    if (isNaN(next) && !isNaN(current)) {
      return 1;
    }
    if (isNaN(current) && !isNaN(next)) {
      return -1
    }

    return (next > current ? 1 : -1)
  }

  if (argc === 0) {
    return '';
  }
  if (argc === 1) {
    return argv[0];
  }

  ar = argv
  retVal = ar[0]

  for (i = 1, n = ar.length; i < n; ++i) {
    if (_compare(retVal, ar[i]) === -1) {
      retVal = ar[i]
    }
  }

  return retVal
}

 /**
 * Returns concatenates list
 */
function LEMjoin()
{
    var result="";
    for (i=0;i<arguments.length;++i) {
        result += arguments[i];
    }
    return result;
}

/**
 * Returns concatenates list with first argument as the separator
 */
function LEMimplode()
{
    // takes variable number of arguments
    var result="";
    if (arguments.length <= 1) {
        return "";
    }
    var joiner = arguments[0];
    for (i=1;i<arguments.length;++i) {
        var arg = arguments[i];
        if (i > 1) {
            result += joiner + arg;
        }
        else {
            result += arg;
        }
    }
    return result;
}

/*
 * Returns true if within matches the pattern.  Pattern must start and end with the '/' character
 */
function LEMregexMatch(sRegExp,within)
{
    try {
        var flags = sRegExp.replace(/.*\/([gimy]*)$/, '$1');
        var pattern = sRegExp.replace(new RegExp('^/(.*?)/'+flags+'$'), '$1').trim();
        var reg = new RegExp(pattern, flags); // Note that the /u flag crashes IE11
        var decodedValue = html_entity_decode(within);
        return reg.test(decodedValue);
    }
    catch (err) {
        return false;
    }
}

function LEMstrlen(a)
{
    var  str = new String(a);
    return str.length;
}

function LEMstr_replace (search, replace, subject) {
  // Copied From: http://phpjs.org/functions
  var i = 0,
    j = 0,
    temp = '',
    repl = '',
    sl = 0,
    fl = 0,
    f = [].concat(search),
    r = [].concat(replace),
    s = subject,
    ra = Object.prototype.toString.call(r) === '[object Array]',
    sa = Object.prototype.toString.call(s) === '[object Array]';
    s = [].concat(s);
  for (i = 0, sl = s.length; i < sl; i++) {
    if (s[i] === '') {
      continue;
    }
    for (j = 0, fl = f.length; j < fl; j++) {
      temp = s[i] + '';
      repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
      s[i] = (temp).split(f[j]).join(repl);
    }
  }
  return sa ? s : s[0];
}



function LEMstrpos(haystack,needle,offset)
{
    var haystack = haystack + '';
    var needle = needle + '';
    var index = 0;
    if ((index = haystack.indexOf(needle, offset)) !== -1) {
        return index;
    }
    return false;
}

function LEMempty(v)
{
    if (v === "" || v === false) {
        return true;
    }
    return false;
}

function LEMbool(v)
{
    bool = new Boolean(v);
    if (v.valueOf() && v != 'false') {
        return true;    // fix for JavaScript native processing that considers the value "false" to be true
    }
    return false;
}

/**
 * Match even if different types - especially true vs. 1, and false vs. 0 or blank
 */
function LEMeq(a,b)
{
    if ((a==="true" && b==="1") || (a==="1" && b==="true")) {
        return true;
    }
    if ((a==="false" && (b==="0" || b==="")) || ((a==="0" || a==="") && b ==="false")) {
        return true;
    }
    return a==b;
}

/**
 * Return the value for data element jsName, treating it as blank if its question is irrelevant on the current page.
 * Also convert the string 'false' to '' to cope with a JavaScript type casting issue
 */
function LEMval(alias)
{
    // first find out whether it is using a suffix
    var str = new String(alias);
    var varName = alias;
    var suffix = 'code';    // the default
    var value = "";
    if (str == '') return '';
    newval = str;
    if (LEMradix === ',') {
        newval = str.split(',').join('.');
    }
    var checkNumericRegex = new RegExp(/^[-0-9,\.]*$/);
    if(checkNumericRegex.test(newval)){
        try{
            newval = new Decimal(newval);
        } catch(e){
            if(e)
                newval = new Decimal(newval.toString().replace(/,/,'.'));
        }
    }
    if (newval == parseFloat(newval)) {
        if (newval.length > 0 && newval[0]==0) {
            return newval;   // so keep 0 prefixes on numbers
        }
        return +newval;
    }

    if (str.match(/^INSERTANS:/)) {
        suffix = 'shown';
        varName = varName.substr(10);
    }
    else if (str.match(/\.(code|gid|grelevance|gseq|jsName|mandatory|NAOK|qid|qseq|question|readWrite|relevanceStatus|relevance|rowdivid|sgqa|shown|type|valueNAOK|value)$/)) {
        varName = str.replace(/\.(code|gid|grelevance|gseq|jsName|mandatory|NAOK|qid|qseq|question|readWrite|relevanceStatus|relevance|rowdivid|sgqa|shown|type|valueNAOK|value)$/,'')
        suffix = str.replace(/^(.+)\./,'');
    }

    jsName = LEMalias2varName[varName];
    attr = LEMvarNameAttr[jsName];
    if ((suffix.match(/^code|NAOK|shown|valueNAOK|value$/)) && attr.qid!='') {
        if (!LEMval(varName + '.relevanceStatus')) {
            return '';
        }
    }
    var whichJsName; // correct name whether on- or off-page
    var onSamePage; // Tag if value is on same page or not (
    if (LEMmode=='survey' || (LEMmode=='group' && attr.gseq == LEMgseq) || (LEMmode=='question' && attr.qid == LEMqid))
    {
        whichJsName = (typeof attr.jsName_on === 'undefined') ? attr.jsName : attr.jsName_on;
        onSamePage=true;
    }
    else
    {
        whichJsName = attr.jsName;
        onSamePage=false;
    }

    if (whichJsName === null || typeof document.getElementById(whichJsName) === 'undefined' || document.getElementById(whichJsName) === null) {
        an_error = true;    // this line is here to make debugging easier
        return '';
    }

    // values should always be stored encoded with htmlspecialchars()
    switch (suffix) {
        case 'relevanceStatus': {
            grel = qrel = sgqarel = 1;
            if (!(typeof attr.gseq === 'undefined') && !(document.getElementById('relevanceG' + attr.gseq) === null)) {
                if(typeof attr.type === 'undefined' || attr.type!="*")// Equation question don't test visibility of group ( child of bug #08315).
                    grel = parseInt(document.getElementById('relevanceG' + attr.gseq).value);
            }
            if (!(typeof attr.qid === 'undefined') && !(document.getElementById('relevance' + attr.qid) === null)) {
                qrel = parseInt(document.getElementById('relevance' + attr.qid).value);
            }
            if (!(typeof attr.rowdivid === 'undefined') && !(document.getElementById('relevance' + attr.rowdivid) === null)) {
                sgqarel = parseInt(document.getElementById('relevance' + attr.rowdivid).value);
            }
            return (grel && qrel && sgqarel);
        }
        case 'shown': {
            value = htmlspecialchars_decode(document.getElementById(whichJsName).value);
            var shown="";
            switch(attr.type)
            {
                case 'G': //GENDER drop-down list
                case 'Y': //YES/NO radio-buttons
                case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
                    shown = (typeof attr.answers[value] === 'undefined') ? '' : attr.answers[value];
                    break;
                case '!': //List - dropdown
                case 'L': //LIST drop-down/radio-button list
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                case 'H': //ARRAY (Flexible) - Column Format
                case 'F': //ARRAY (Flexible) - Row Format
                case 'R': //RANKING STYLE
                    if (attr.type == 'O' && varName.match(/comment$/)) {
                        answer = htmlentities(value);
                    }
                    else if ((attr.type == 'L' || attr.type == '!') && varName.match(/_other$/)) {
                        answer = htmlentities(value);
                    }
                    else {
                        which_ans = '0~' + value;
                        if (attr.answers === undefined || typeof attr.answers[which_ans] === 'undefined') {
                            answer = value;
                        }
                        else {
                            answerParts = attr.answers[which_ans].split('|');
                            answerParts.shift();    // remove the first element
                            answer = answerParts.join('|');
                        }
                    }
                    shown = answer;
                    break;
                case '1': //Array (Flexible Labels) dual scale  // need scale
                    prefix = (attr.jsName.match(/_1$/)) ? '1' : '0';
                    which_ans = prefix + '~' + value;
                    if (attr.answers === undefined || typeof attr.answers[which_ans] === 'undefined') {
                        answer = '';
                    }
                    else {
                        answerParts = attr.answers[which_ans].split('|');
                        answerParts.shift();    // remove the first element
                        answer = answerParts.join('|');
                    }
                    shown = answer;
                    break;
                case 'N': //NUMERICAL QUESTION TYPE
                case 'K': //MULTIPLE NUMERICAL QUESTION
                case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case '5': //5 POINT CHOICE radio-buttons
                case 'Q': //MULTIPLE SHORT TEXT
                case ';': //ARRAY (Multi Flexi) Text
                case 'S': //SHORT FREE TEXT
                case 'T': //LONG FREE TEXT
                case 'U': //HUGE FREE TEXT
                case 'D': //DATE
                case '*': //Equation
                case 'I': //Language Question
                case '|': //File Upload
                case 'X': //BOILERPLATE QUESTION
                        shown = htmlentities(value);
                    break;
                case 'M': //Multiple choice checkbox
                case 'P': //Multiple choice with comments checkbox + text
                    if (typeof attr.question === 'undefined' || value == '') {
                        shown = '';
                    }
                    else {
                        if (attr.type == 'P' && varName.match(/comment$/)) {
                            try {
                                var numtest = new Decimal(value);
                                shown = parseFloat(numtest.valueOf());
                            }
                            catch(e) {
                                shown = htmlentities(value);
                            }
                        }
                        else {
                            shown = htmlspecialchars_decode(attr.question);
                        }
                    }
                    break;
            }
            return shown;
        }
        case 'gid':
            return attr.gid;
        case 'grelevance':
            return attr.grelevance;
        case 'mandatory':
            return attr.mandatory;
        case 'qid':
            return attr.qid;
        case 'question':
            return htmlspecialchars_decode(attr.question);
        case 'readWrite':
            return attr.readWrite;
        case 'relevance':
            return htmlspecialchars_decode(attr.relevance);
        case 'sgqa':
            return attr.sgqa;
        case 'type':
            return attr.type;
        case 'gseq':
            return attr.gseq;
        case 'qseq':
            return attr.qseq;
        case 'jsName':
            return whichJsName;
        case 'code':
        case 'NAOK':
        case 'value':
        case 'valueNAOK':
        {
            value = htmlspecialchars_decode(document.getElementById(whichJsName).value);
            if (value === '') {
                return '';
            }
            // Always htmlentities user entered values, see #13928
            switch(attr.type)
            {
                case '!': //List - dropdown
                case 'L': //LIST drop-down/radio-button list
                case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                case 'H': //ARRAY (Flexible) - Column Format
                case 'F': //ARRAY (Flexible) - Row Format
                case 'R': //RANKING STYLE
                    if (attr.type == 'O' && varName.match(/comment$/)) {
                        value = htmlentities(value);
                    }
                    else if ((attr.type == 'L' || attr.type == '!') && varName.match(/_other$/)) {
                        value = htmlentities(value);
                    }
                    break;
                case 'N': //NUMERICAL QUESTION TYPE
                case 'K': //MULTIPLE NUMERICAL QUESTION
                case 'Q': //MULTIPLE SHORT TEXT
                case ';': //ARRAY (Multi Flexi) Text
                case 'S': //SHORT FREE TEXT
                case 'T': //LONG FREE TEXT
                case 'U': //HUGE FREE TEXT
                case 'D': //DATE
                case '*': //Equation
                case '|': //File Upload (unsure need to be htmlentities ?)
                        value = htmlentities(value);
                    break;
                case 'M': //Multiple choice checkbox
                case 'P': //Multiple choice with comments checkbox + text
                    if (attr.type == 'P' && varName.match(/comment$/)) {
                        value = htmlentities(value);
                    }
                    break;
                case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
                case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
                case ':': //ARRAY (Multi Flexi) 1 to 10
                case '5': //5 POINT CHOICE radio-buttons
                case 'I': //Language Question
                case 'X': //BOILERPLATE QUESTION
                default:
                    // Nothing to update
            }

            if (suffix == 'value' || suffix == 'valueNAOK') {
                // if in assessment mode, this returns the assessment value
                // in non-assessment mode, this is identical to .code
                switch (attr.type) {
                    case '!': //List - dropdown
                    case 'L': //LIST drop-down/radio-button list
                    case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
                    case 'H': //ARRAY (Flexible) - Column Format
                    case 'F': //ARRAY (Flexible) - Row Format
                    case 'R': //RANKING STYLE
                        if (attr.type == 'O' && varName.match(/comment$/)) {
//                            value = value;
                        }
                        else if ((attr.type == 'L' || attr.type == '!') && varName.match(/_other$/)) {
//                            value = value;
                        }
                        else {
                            which_ans = '0~' + value;
                            if (attr.answers === undefined || typeof attr.answers[which_ans] === 'undefined') {
                                value = '';
                            }
                            else {
                                answerParts = attr.answers[which_ans].split('|');
                                value = answerParts[0];
                            }
                        }
                        break;
                    case '1': //Array (Flexible Labels) dual scale  // need scale
                        prefix = (attr.jsName.match(/_1$/)) ? '1' : '0';
                        which_ans = prefix + '~' + value;
                        if (attr.answers === undefined || typeof attr.answers[which_ans] === 'undefined') {
                            value = '';
                        }
                        else {
                            answerParts = attr.answers[which_ans].split('|');
                            value = answerParts[0];
                        }
                        break;
                    case 'N': //NUMERICAL QUESTION TYPE
                    case 'K': //MULTIPLE NUMERICAL QUESTION

                }
            }

            if (typeof attr.onlynum !== 'undefined' && attr.onlynum==1) {
                if(value=="") {
                    return "";
                }

                var checkNumericRegex = new RegExp(/^(-)?[0-9]*(,|\.)[0-9]*$/);
                /* Set as number if regexp is OK AND lenght is > 1 (then not fix [-.,] #14533 and no need to fix single number) */
                if( checkNumericRegex.test(value) && value.length > 1 )
                {
                    var length = value.length;
                    var firstLetterIsNull = value.split("").shift() === '0';
                    // @todo : use . or , according to LEMradix !
                    try{
                        var numtest = new Decimal(value);
                    } catch(e){
                        var numtest = new Decimal(value.toString().replace(/,/,'.'));
                        // Error can still happen maybe but don't catch to know (and fix) it
                    }
                    value = numtest.valueOf();
                    if(value.length < length && firstLetterIsNull){
                        value = str_repeat('0', length).substr(0,(length - value.length))+''+value.toString();
                    }
                    value = Number(value); /* If it's a number : always return a number */
                }
                if(LSvar.bNumRealValue) {
                    return value;
                }
                return Number(value);
            }

            // convert content in date questions to standard format yy-mm-dd to facilitate use in EM (comparisons, min/max etc.)
            else if (attr.type=='D')  {
                // get date format pattern of referenced question
                var sdatetimePattern=$(jsName.replace(/java/g, '#dateformat')).val();
                if (sdatetimePattern == ''){
                    sdatetimePattern=$(jsName.replace(/java/g, '#dateformat')).text();
                }
                // if empty (eg., variable on a previous page), set default format yy-mm-dd HH:MM
                sdatetimePattern = sdatetimePattern == ''? 'YYYY-MM-DD HH:mm': sdatetimePattern;

                if (sdatetimePattern==null) {
                    sdatetimePattern="";
                }
                if (value=='INVALID') {
                    value="";
                }
                else {
                    value= moment(value,sdatetimePattern).format('YYYY-MM-DD HH:mm');
                }
                return value;
            }
            else if (isNaN(value)) {
                if (value==='false') {
                    return '';  // so Boolean operations will treat it as false. In JavaScript, Boolean("false") is true since "false" is not a zero-length string
                }
                return value;
            }
            else if(!isNaN(parseFloat(value)) && isFinite(value))
            {
                var length = value.length;
                var firstLetterIsNull = value.split("").shift() === '0';
                try{
                    var numtest = new Decimal(value);
                } catch(e){
                    var numtest = new Decimal(value.toString().replace(/,/,'.'));
                }
                if(numtest.valueOf().length < length && firstLetterIsNull){
                    value = value.toString(); /* return string as it is */
                } else {
                    value = Number(numtest.valueOf()); /* If it's a number : always return a number */
                }
            }

            return value;
        }
        case 'rowdivid':
            if (typeof attr.rowdivid === 'undefined' || attr.rowdivid == '') {
                return '';
            }
            return attr.rowdivid;
        default:
            return 'Unknown Attribute: ' . suffix;
    }
}


/** Display number with comma as radix separator, if needed
 */
function LEMfixnum(value)
{
    if (LEMradix===',') {
        var newval = String(value);
        if (!isNaN(parseFloat(newval)) && isFinite(newval)) {
            newval= newval.split('.').join(',');
            return newval;
        }
        return value;   // unchanged
    }
    return value;
}

/*
 * Remove HTML and PHP tags from string
 */
function LEMstrip_tags(htmlString)
{
   var tmp = document.createElement("DIV");
   tmp.innerHTML = htmlString;
   return tmp.textContent||tmp.innerText;
}

/**
 * Compute sample standard deviation of the input variables
 */
function LEMstddev()
{
    vals = new Array();
    j = 0;
    for (i=0;i<arguments.length;++i) {
        if (LEMis_numeric(arguments[i])) {
            vals[j++] = arguments[i];
        }
    }
    count = vals.length;
    if (count <= 1) {
        return 0;   // what should default value be?
    }
    sum = 0;
    for (i=0;i<vals.length;++i) {
        sum += vals[i];
    }
    mean = sum / count;

    sumsqmeans = 0;
    for (i=0;i<vals.length;++i) {
        sumsqmeans += (vals[i] - mean) * (vals[i] - mean);
    }
    stddev = Math.sqrt(sumsqmeans / (count-1));
    return stddev;
}

function LEMstrtoupper(s)
{
    return s.toUpperCase();
}

function LEMstrtolower(s)
{
    return s.toLowerCase();
}

/*
 * return true if any of the arguments are not relevant
 */
function LEManyNA()
{
    for (i=0;i<arguments.length;++i) {
        var arg = arguments[i];
        if (arg.match(/\.NAOK$/)) {
            continue;
        }
        if (typeof LEMalias2varName[arg] === 'undefined') {
            continue;   // default is OK (e.g. for questions with dot notation suffix
        }
        jsName = LEMalias2varName[arg];
        if (typeof LEMvarNameAttr[jsName] === 'undefined') {
            continue;   // default is OK (e.g. for questions with dot notation suffix)
        }
        attr = LEMvarNameAttr[jsName];
        if (!LEMval(attr.sgqa + '.relevanceStatus')) {
            return true;
        }
    }
    return false;
}

/* Set the tabIndex for all potentially visible form elements, and capture the TAB and SHIFT-TAB keys so can
 * control navigation when elements appear and disappear.
 */
function  LEMsetTabIndexes()
{
    if (typeof tabIndexesSet == 'undefined') {
        $(document).on('keydown',"#limesurvey :input[type!=hidden][id!=runonce]",function(event){
            var keyCode = event.keyCode || event.which;
            if (keyCode == 9) {
                // see bug #08590 : lauch checkcondition only for text input. Not needed for radio or checkbox
                // Not sure it's really needed actually, it's a blur event for text and change for select
                // Can use $(this)[0].type
                if($(this).attr('type')=="text")
                {
                    $(this).triggerHandler("keyup");
                }
                if($(this).is('select'))
                {
                    $(this).triggerHandler("change");
                }
                $(this).focus();
                return true;
            }
        });
        tabIndexesSet = true;
    }
}

/** Function to set color coding for Other option in multiple choice questions so that it gets filled in if selected
 */
function LEMflagMandOther(sgqa,checked)
{
    if (checked) {
        if ($.trim($('#java'+sgqa).val()) == '') {
            $('#answer'+sgqa).addClass('em_sq_validation error').removeClass('good');
        }
        else {
            $('#answer'+sgqa).addClass('em_sq_validation good').removeClass('error');
        }
    }
    else {
        $('#answer'+sgqa).addClass('em_sq_validation good').removeClass('error');
    }
}

/* The following functions are courtesy of phpjs.org */
/*
 * More info at: http://phpjs.org
 *
 * This is version: 3.26
 * php.js is copyright 2011 Kevin van Zonneveld.
 *
 * Portions copyright Brett Zamir (http://brett-zamir.me), Kevin van Zonneveld
 * (http://kevin.vanzonneveld.net), Onno Marsman, Theriault, Michael White
 * (http://getsprink.com), Waldo Malqui Silva, Paulo Freitas, Jack, Jonas
 * Raoni Soares Silva (http://www.jsfromhell.com), Philip Peterson, Legaev
 * Andrey, Ates Goral (http://magnetiq.com), Alex, Ratheous, Martijn Wieringa,
 * Rafal Kukawski (http://blog.kukawski.pl), lmeyrick
 * (https://sourceforge.net/projects/bcmath-js/), Nate, Philippe Baumann,
 * Enrique Gonzalez, Webtoolkit.info (http://www.webtoolkit.info/), Carlos R.
 * L. Rodrigues (http://www.jsfromhell.com), Ash Searle
 * (http://hexmen.com/blog/), Jani Hartikainen, travc, Ole Vrijenhoek,
 * Erkekjetter, Michael Grier, Rafal Kukawski (http://kukawski.pl), Johnny
 * Mast (http://www.phpvrouwen.nl), T.Wild, d3x,
 * http://stackoverflow.com/questions/57803/how-to-convert-decimal-to-hex-in-javascript,
 * Rafal Kukawski (http://blog.kukawski.pl/), stag019, pilus, WebDevHobo
 * (http://webdevhobo.blogspot.com/), marrtins, GeekFG
 * (http://geekfg.blogspot.com), Andrea Giammarchi
 * (http://webreflection.blogspot.com), Arpad Ray (mailto:arpad@php.net),
 * gorthaur, Paul Smith, Tim de Koning (http://www.kingsquare.nl), Joris, Oleg
 * Eremeev, Steve Hilder, majak, gettimeofday, KELAN, Josh Fraser
 * (http://onlineaspect.com/2007/06/08/auto-detect-a-time-zone-with-javascript/),
 * Marc Palau, Kevin van Zonneveld (http://kevin.vanzonneveld.net/), Martin
 * (http://www.erlenwiese.de/), Breaking Par Consulting Inc
 * (http://www.breakingpar.com/bkp/home.nsf/0/87256B280015193F87256CFB006C45F7),
 * Chris, Mirek Slugen, saulius, Alfonso Jimenez
 * (http://www.alfonsojimenez.com), Diplom@t (http://difane.com/), felix,
 * Mailfaker (http://www.weedem.fr/), Tyler Akins (http://rumkin.com), Caio
 * Ariede (http://caioariede.com), Robin, Kankrelune
 * (http://www.webfaktory.info/), Karol Kowalski, Imgen Tata
 * (http://www.myipdf.com/), mdsjack (http://www.mdsjack.bo.it), Dreamer,
 * Felix Geisendoerfer (http://www.debuggable.com/felix), Lars Fischer, AJ,
 * David, Aman Gupta, Michael White, Public Domain
 * (http://www.json.org/json2.js), Steven Levithan
 * (http://blog.stevenlevithan.com), Sakimori, Pellentesque Malesuada,
 * Thunder.m, Dj (http://phpjs.org/functions/htmlentities:425#comment_134018),
 * Steve Clay, David James, Francois, class_exists, nobbler, T. Wild, Itsacon
 * (http://www.itsacon.net/), date, Ole Vrijenhoek (http://www.nervous.nl/),
 * Fox, Raphael (Ao RUDLER), Marco, noname, Mateusz "loonquawl" Zalega, Frank
 * Forte, Arno, ger, mktime, john (http://www.jd-tech.net), Nick Kolosov
 * (http://sammy.ru), marc andreu, Scott Cariss, Douglas Crockford
 * (http://javascript.crockford.com), madipta, Slawomir Kaniecki,
 * ReverseSyntax, Nathan, Alex Wilson, kenneth, Bayron Guevara, Adam Wallner
 * (http://web2.bitbaro.hu/), paulo kuong, jmweb, Lincoln Ramsay, djmix,
 * Pyerre, Jon Hohle, Thiago Mata (http://thiagomata.blog.com), lmeyrick
 * (https://sourceforge.net/projects/bcmath-js/this.), Linuxworld, duncan,
 * Gilbert, Sanjoy Roy, Shingo, sankai, Oskar Larsson Högfeldt
 * (http://oskar-lh.name/), Denny Wardhana, 0m3r, Everlasto, Subhasis Deb,
 * josh, jd, Pier Paolo Ramon (http://www.mastersoup.com/), P, merabi, Soren
 * Hansen, Eugene Bulkin (http://doubleaw.com/), Der Simon
 * (http://innerdom.sourceforge.net/), echo is bad, Ozh, XoraX
 * (http://www.xorax.info), EdorFaus, JB, J A R, Marc Jansen, Francesco, LH,
 * Stoyan Kyosev (http://www.svest.org/), nord_ua, omid
 * (http://phpjs.org/functions/380:380#comment_137122), Brad Touesnard, MeEtc
 * (http://yass.meetcweb.com), Peter-Paul Koch
 * (http://www.quirksmode.org/js/beat.html), Olivier Louvignes
 * (http://mg-crea.com/), T0bsn, Tim Wiel, Bryan Elliott, Jalal Berrami,
 * Martin, JT, David Randall, Thomas Beaucourt (http://www.webapp.fr), taith,
 * vlado houba, Pierre-Luc Paour, Kristof Coomans (SCK-CEN Belgian Nucleair
 * Research Centre), Martin Pool, Kirk Strobeck, Rick Waldron, Brant Messenger
 * (http://www.brantmessenger.com/), Devan Penner-Woelk, Saulo Vallory, Wagner
 * B. Soares, Artur Tchernychev, Valentina De Rosa, Jason Wong
 * (http://carrot.org/), Christoph, Daniel Esteban, strftime, Mick@el, rezna,
 * Simon Willison (http://simonwillison.net), Anton Ongson, Gabriel Paderni,
 * Marco van Oort, penutbutterjelly, Philipp Lenssen, Bjorn Roesbeke
 * (http://www.bjornroesbeke.be/), Bug?, Eric Nagel, Tomasz Wesolowski,
 * Evertjan Garretsen, Bobby Drake, Blues (http://tech.bluesmoon.info/), Luke
 * Godfrey, Pul, uestla, Alan C, Ulrich, Rafal Kukawski, Yves Sucaet,
 * sowberry, Norman "zEh" Fuchs, hitwork, Zahlii, johnrembo, Nick Callen,
 * Steven Levithan (stevenlevithan.com), ejsanders, Scott Baker, Brian Tafoya
 * (http://www.premasolutions.com/), Philippe Jausions
 * (http://pear.php.net/user/jausions), Aidan Lister
 * (http://aidanlister.com/), Rob, e-mike, HKM, ChaosNo1, metjay, strcasecmp,
 * strcmp, Taras Bogach, jpfle, Alexander Ermolaev
 * (http://snippets.dzone.com/user/AlexanderErmolaev), DxGx, kilops, Orlando,
 * dptr1988, Le Torbi, James (http://www.james-bell.co.uk/), Pedro Tainha
 * (http://www.pedrotainha.com), James, Arnout Kazemier
 * (http://www.3rd-Eden.com), Chris McMacken, gabriel paderni, Yannoo,
 * FGFEmperor, baris ozdil, Tod Gentille, Greg Frazier, jakes, 3D-GRAF, Allan
 * Jensen (http://www.winternet.no), Howard Yeend, Benjamin Lupton, davook,
 * daniel airton wermann (http://wermann.com.br), Atli Þór, Maximusya, Ryan
 * W Tenney (http://ryan.10e.us), Alexander M Beedie, fearphage
 * (http://http/my.opera.com/fearphage/), Nathan Sepulveda, Victor, Matteo,
 * Billy, stensi, Cord, Manish, T.J. Leahy, Riddler
 * (http://www.frontierwebdev.com/), Rafal Kukawski, FremyCompany, Matt
 * Bradley, Tim de Koning, Luis Salazar (http://www.freaky-media.com/), Diogo
 * Resende, Rival, Andrej Pavlovic, Garagoth, Le Torbi
 * (http://www.letorbi.de/), Dino, Josep Sanz (http://www.ws3.es/), rem,
 * Russell Walker (http://www.nbill.co.uk/), Jamie Beck
 * (http://www.terabit.ca/), setcookie, Michael, YUI Library:
 * http://developer.yahoo.com/yui/docs/YAHOO.util.DateLocale.html, Blues at
 * http://hacks.bluesmoon.info/strftime/strftime.js, Ben
 * (http://benblume.co.uk/), DtTvB
 * (http://dt.in.th/2008-09-16.string-length-in-bytes.html), Andreas, William,
 * meo, incidence, Cagri Ekin, Amirouche, Amir Habibi
 * (http://www.residence-mixte.com/), Luke Smith (http://lucassmith.name),
 * Kheang Hok Chin (http://www.distantia.ca/), Jay Klehr, Lorenzo Pisani,
 * Tony, Yen-Wei Liu, Greenseed, mk.keck, Leslie Hoare, dude, booeyOH, Ben
 * Bryan
 *
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL KEVIN VAN ZONNEVELD BE LIABLE FOR ANY CLAIM, DAMAGES
 * OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

function is_bool (mixed_var) {
    // Returns true if variable is a boolean
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/is_bool
    // +   original by: Onno Marsman
    // *     example 1: is_bool(false);
    // *     returns 1: true
    // *     example 2: is_bool(0);
    // *     returns 2: false
    return (typeof mixed_var === 'boolean');
}

function addslashes (str) {
    // Escapes single quote, double quotes and backslash characters in a string with backslashes
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/addslashes
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Ates Goral (http://magnetiq.com)
    // +   improved by: marrtins
    // +   improved by: Nate
    // +   improved by: Onno Marsman
    // +   input by: Denny Wardhana
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Oskar Larsson Högfeldt (http://oskar-lh.name/)
    // *     example 1: addslashes("kevin's birthday");
    // *     returns 1: 'kevin\'s birthday'
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

function html_entity_decode (string, quote_style) {
    // Convert all HTML entities to their applicable characters
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/html_entity_decode
    // +   original by: john (http://www.jd-tech.net)
    // +      input by: ger
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: marc andreu
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Ratheous
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Nick Kolosov (http://sammy.ru)
    // +   bugfixed by: Fox
    // -    depends on: get_html_translation_table
    // *     example 1: html_entity_decode('Kevin &amp; van Zonneveld');
    // *     returns 1: 'Kevin & van Zonneveld'
    // *     example 2: html_entity_decode('&amp;lt;');
    // *     returns 2: '&lt;'
    var hash_map = {},
        symbol = '',
        tmp_str = '',
        entity = '';
    tmp_str = string.toString();

    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }

    // fix &amp; problem
    // http://phpjs.org/functions/get_html_translation_table:416#comment_97660
    delete(hash_map['&']);
    hash_map['&'] = '&amp;';

    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(entity).join(symbol);
    }
    tmp_str = tmp_str.split('&#039;').join("'");

    return tmp_str;
}

function htmlentities (string, quote_style, charset, double_encode) {
    // Convert all applicable characters to HTML entities
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/htmlentities
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: nobbler
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +   improved by: Rafał Kukawski (http://blog.kukawski.pl)
    // -    depends on: get_html_translation_table
    // *     example 1: htmlentities('Kevin & van Zonneveld');
    // *     returns 1: 'Kevin &amp; van Zonneveld'
    // *     example 2: htmlentities("foo'bar","ENT_QUOTES");
    // *     returns 2: 'foo&#039;bar'
    var hash_map = {},
        symbol = '',
        entity = '',
        self = this;
    string += '';
    double_encode = !!double_encode || double_encode == null;

    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }
    hash_map["'"] = '&#039;';

    if (double_encode) {
        for (symbol in hash_map) {
            entity = hash_map[symbol];
            string = string.split(symbol).join(entity);
        }
    } else {
        string = string.replace(/([\s\S]*?)(&(?:#\d+|#x[\da-f]+|[a-z][\da-z]*);|$)/g, function (ignore, text, entity) {
            return self.htmlentities(text, quote_style, charset) + entity;
        });
    }

    return string;
}

function get_html_translation_table (table, quote_style) {
    // Returns the internal translation table used by htmlspecialchars and htmlentities
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/get_html_translation_table
    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // +   bugfixed by: Alex
    // +   bugfixed by: Marco
    // +   bugfixed by: madipta
    // +   improved by: KELAN
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Frank Forte
    // +   bugfixed by: T.Wild
    // +      input by: Ratheous
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js, meaning the constants are not
    // %          note: real constants, but strings instead. Integers are also supported if someone
    // %          note: chooses to create the constants themselves.
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
    var entities = {},
        hash_map = {},
        decimal = 0,
        symbol = '';
    var constMappingTable = {},
        constMappingQuoteStyle = {};
    var useTable = {},
        useQuoteStyle = {};

    // Translate arguments
    constMappingTable[0] = 'HTML_SPECIALCHARS';
    constMappingTable[1] = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';

    useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
    useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';

    if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
        throw new Error("Table: " + useTable + ' not supported');
        // return false;
    }

    entities['38'] = '&amp;';
    if (useTable === 'HTML_ENTITIES') {
        entities['160'] = '&nbsp;';
        entities['161'] = '&iexcl;';
        entities['162'] = '&cent;';
        entities['163'] = '&pound;';
        entities['164'] = '&curren;';
        entities['165'] = '&yen;';
        entities['166'] = '&brvbar;';
        entities['167'] = '&sect;';
        entities['168'] = '&uml;';
        entities['169'] = '&copy;';
        entities['170'] = '&ordf;';
        entities['171'] = '&laquo;';
        entities['172'] = '&not;';
        entities['173'] = '&shy;';
        entities['174'] = '&reg;';
        entities['175'] = '&macr;';
        entities['176'] = '&deg;';
        entities['177'] = '&plusmn;';
        entities['178'] = '&sup2;';
        entities['179'] = '&sup3;';
        entities['180'] = '&acute;';
        entities['181'] = '&micro;';
        entities['182'] = '&para;';
        entities['183'] = '&middot;';
        entities['184'] = '&cedil;';
        entities['185'] = '&sup1;';
        entities['186'] = '&ordm;';
        entities['187'] = '&raquo;';
        entities['188'] = '&frac14;';
        entities['189'] = '&frac12;';
        entities['190'] = '&frac34;';
        entities['191'] = '&iquest;';
        entities['192'] = '&Agrave;';
        entities['193'] = '&Aacute;';
        entities['194'] = '&Acirc;';
        entities['195'] = '&Atilde;';
        entities['196'] = '&Auml;';
        entities['197'] = '&Aring;';
        entities['198'] = '&AElig;';
        entities['199'] = '&Ccedil;';
        entities['200'] = '&Egrave;';
        entities['201'] = '&Eacute;';
        entities['202'] = '&Ecirc;';
        entities['203'] = '&Euml;';
        entities['204'] = '&Igrave;';
        entities['205'] = '&Iacute;';
        entities['206'] = '&Icirc;';
        entities['207'] = '&Iuml;';
        entities['208'] = '&ETH;';
        entities['209'] = '&Ntilde;';
        entities['210'] = '&Ograve;';
        entities['211'] = '&Oacute;';
        entities['212'] = '&Ocirc;';
        entities['213'] = '&Otilde;';
        entities['214'] = '&Ouml;';
        entities['215'] = '&times;';
        entities['216'] = '&Oslash;';
        entities['217'] = '&Ugrave;';
        entities['218'] = '&Uacute;';
        entities['219'] = '&Ucirc;';
        entities['220'] = '&Uuml;';
        entities['221'] = '&Yacute;';
        entities['222'] = '&THORN;';
        entities['223'] = '&szlig;';
        entities['224'] = '&agrave;';
        entities['225'] = '&aacute;';
        entities['226'] = '&acirc;';
        entities['227'] = '&atilde;';
        entities['228'] = '&auml;';
        entities['229'] = '&aring;';
        entities['230'] = '&aelig;';
        entities['231'] = '&ccedil;';
        entities['232'] = '&egrave;';
        entities['233'] = '&eacute;';
        entities['234'] = '&ecirc;';
        entities['235'] = '&euml;';
        entities['236'] = '&igrave;';
        entities['237'] = '&iacute;';
        entities['238'] = '&icirc;';
        entities['239'] = '&iuml;';
        entities['240'] = '&eth;';
        entities['241'] = '&ntilde;';
        entities['242'] = '&ograve;';
        entities['243'] = '&oacute;';
        entities['244'] = '&ocirc;';
        entities['245'] = '&otilde;';
        entities['246'] = '&ouml;';
        entities['247'] = '&divide;';
        entities['248'] = '&oslash;';
        entities['249'] = '&ugrave;';
        entities['250'] = '&uacute;';
        entities['251'] = '&ucirc;';
        entities['252'] = '&uuml;';
        entities['253'] = '&yacute;';
        entities['254'] = '&thorn;';
        entities['255'] = '&yuml;';
    }

    if (useQuoteStyle !== 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
    if (useQuoteStyle === 'ENT_QUOTES') {
        entities['39'] = '&#39;';
    }
    entities['60'] = '&lt;';
    entities['62'] = '&gt;';


    // ascii decimals to real symbols
    for (decimal in entities) {
        symbol = String.fromCharCode(decimal);
        hash_map[symbol] = entities[decimal];
    }

    return hash_map;
}

function htmlspecialchars (string, quote_style, charset, double_encode) {
    // Convert special characters to HTML entities
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/htmlspecialchars
    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Nathan
    // +   bugfixed by: Arno
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +      input by: felix
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: charset argument not supported
    // *     example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
    // *     returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
    // *     example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);
    // *     returns 2: 'ab"c&#039;d'
    // *     example 3: htmlspecialchars("my "&entity;" is still here", null, null, false);
    // *     returns 3: 'my &quot;&entity;&quot; is still here'
    var optTemp = 0,
        i = 0,
        noquotes = false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 3;    // default for PHP is 2 - using 3 as default for LimeSurvey
    }
    string = string.toString();
    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');

    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
    }

    return string;
}

function htmlspecialchars_decode (string, quote_style) {
    // Convert special HTML entities back to characters
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/htmlspecialchars_decode
    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Mateusz "loonquawl" Zalega
    // +      input by: ReverseSyntax
    // +      input by: Slawomir Kaniecki
    // +      input by: Scott Cariss
    // +      input by: Francois
    // +   bugfixed by: Onno Marsman
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
    // *     returns 1: '<p>this -> &quot;</p>'
    // *     example 2: htmlspecialchars_decode("&amp;quot;");
    // *     returns 2: '&quot;'
    var optTemp = 0,
        i = 0,
        noquotes = false;
    if (typeof quote_style === 'undefined') {
        quote_style = 3;    // default for PHP is 2 - using 3 as default for LimeSurvey
    }
    string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            } else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
        // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
    }
    if (!noquotes) {
        string = string.replace(/&quot;/g, '"');
    }
    // Put this in last place to avoid escape being double-decoded
    string = string.replace(/&amp;/g, '&');

    return string;
}

function ltrim (str, charlist) {
    // Strips whitespace from the beginning of a string
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/ltrim
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Erkekjetter
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // *     example 1: ltrim('    Kevin van Zonneveld    ');
    // *     returns 1: 'Kevin van Zonneveld    '
    charlist = !charlist ? ' \\s\u00A0' : (charlist + '').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
    var re = new RegExp('^[' + charlist + ']+', 'g');
    return (str + '').replace(re, '');
}

function nl2br(str, is_xhtml) {
  //  discuss at: http://phpjs.org/functions/nl2br/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Philip Peterson
  // improved by: Onno Marsman
  // improved by: Atli Þór
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Maximusya
  // bugfixed by: Onno Marsman
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Brett Zamir (http://brett-zamir.me)
  //   example 1: nl2br('Kevin\nvan\nZonneveld');
  //   returns 1: 'Kevin<br />\nvan<br />\nZonneveld'
  //   example 2: nl2br("\nOne\nTwo\n\nThree\n", false);
  //   returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
  //   example 3: nl2br("\nOne\nTwo\n\nThree\n", true);
  //   returns 3: '<br />\nOne<br />\nTwo<br />\n<br />\nThree<br />\n'

  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

  return (str + '')
    .replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function number_format (number, decimals, dec_point, thousands_sep) {
    // http://kevin.vanzonneveld.net
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
    // +   improved by: davook
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Jay Klehr
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Amir Habibi (http://www.residence-mixte.com/)
    // +     bugfix by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +      input by: Amirouche
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
    // *    example 13: number_format('1 000,50', 2, '.', ' ');
    // *    returns 13: '100 050.00'
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function quoted_printable_decode (str) {
    // Convert a quoted-printable string to an 8 bit string
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/quoted_printable_decode
    // +   original by: Ole Vrijenhoek
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +   reimplemented by: Theriault
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Theriault
    // *     example 1: quoted_printable_decode('a=3Db=3Dc');
    // *     returns 1: 'a=b=c'
    // *     example 2: quoted_printable_decode('abc  =20\r\n123  =20\r\n');
    // *     returns 2: 'abc   \r\n123   \r\n'
    // *     example 3: quoted_printable_decode('012345678901234567890123456789012345678901234567890123456789012345678901234=\r\n56789');
    // *     returns 3: '01234567890123456789012345678901234567890123456789012345678901234567890123456789'
    // *    example 4: quoted_printable_decode("Lorem ipsum dolor sit amet=23, consectetur adipisicing elit");
    // *    returns 4: Lorem ipsum dolor sit amet#, consectetur adipisicing elit
    // Removes softline breaks
    var RFC2045Decode1 = /=\r\n/gm,
        // Decodes all equal signs followed by two hex digits
        RFC2045Decode2IN = /=([0-9A-F]{2})/gim,
        // the RFC states against decoding lower case encodings, but following apparent PHP behavior
        // RFC2045Decode2IN = /=([0-9A-F]{2})/gm,
        RFC2045Decode2OUT = function (sMatch, sHex) {
            return String.fromCharCode(parseInt(sHex, 16));
        };
    return str.replace(RFC2045Decode1, '').replace(RFC2045Decode2IN, RFC2045Decode2OUT);
}

function quoted_printable_encode (str) {
    // +   original by: Theriault
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // *     example 1: quoted_printable_encode('a=b=c');
    // *     returns 1: 'a=3Db=3Dc'
    // *     example 2: quoted_printable_encode('abc   \r\n123   \r\n');
    // *     returns 2: 'abc  =20\r\n123  =20\r\n'
    // *     example 3: quoted_printable_encode('0123456789012345678901234567890123456789012345678901234567890123456789012345');
    // *     returns 3: '012345678901234567890123456789012345678901234567890123456789012345678901234=\r\n5'
    // RFC 2045: 6.7.2: Octets with decimal values of 33 through 60 (bang to less-than) inclusive, and 62 through 126 (greater-than to tilde), inclusive, MAY be represented as the US-ASCII characters
    // PHP does not encode any of the above; as does this function.
    // RFC 2045: 6.7.3: Octets with values of 9 and 32 MAY be represented as US-ASCII TAB (HT) and SPACE characters, respectively, but MUST NOT be so represented at the end of an encoded line
    // PHP does not encode spaces (octet 32) except before a CRLF sequence as stated above. PHP always encodes tabs (octet 9). This function replicates PHP.
    // RFC 2045: 6.7.4: A line break in a text body, represented as a CRLF sequence in the text canonical form, must be represented by a (RFC 822) line break
    // PHP does not encode a CRLF sequence, as does this function.
    // RFC 2045: 6.7.5: The Quoted-Printable encoding REQUIRES that encoded lines be no more than 76 characters long. If longer lines are to be encoded with the Quoted-Printable encoding, "soft" line breaks must be used.
    // PHP breaks lines greater than 76 characters; as does this function.
    var hexChars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'],
        RFC2045Encode1IN = / \r\n|\r\n|[^!-<>-~ ]/gm,
        RFC2045Encode1OUT = function (sMatch) {
            // Encode space before CRLF sequence to prevent spaces from being stripped
            // Keep hard line breaks intact; CRLF sequences
            if (sMatch.length > 1) {
                return sMatch.replace(' ', '=20');
            }
            // Encode matching character
            var chr = sMatch.charCodeAt(0);
            return '=' + hexChars[((chr >>> 4) & 15)] + hexChars[(chr & 15)];
        },
        // Split lines to 75 characters; the reason it's 75 and not 76 is because softline breaks are preceeded by an equal sign; which would be the 76th character.
        // However, if the last line/string was exactly 76 characters, then a softline would not be needed. PHP currently softbreaks anyway; so this function replicates PHP.
        RFC2045Encode2IN = /.{1,72}(?!\r\n)[^=]{0,3}/g,
        RFC2045Encode2OUT = function (sMatch) {
            if (sMatch.substr(sMatch.length - 2) === '\r\n') {
                return sMatch;
            }
            return sMatch + '=\r\n';
        };
    str = str.replace(RFC2045Encode1IN, RFC2045Encode1OUT).replace(RFC2045Encode2IN, RFC2045Encode2OUT);
    // Strip last softline break
    return str.substr(0, str.length - 3);
}

function quotemeta (str) {
    // Quotes meta characters
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/quotemeta
    // +   original by: Paulo Freitas
    // *     example 1: quotemeta(". + * ? ^ ( $ )");
    // *     returns 1: '\. \+ \* \? \^ \( \$ \)'
    return (str + '').replace(/([\.\\\+\*\?\[\^\]\$\(\)])/g, '\\$1');
}

function round (value, precision, mode) {
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +    revised by: Onno Marsman
    // +      input by: Greenseed
    // +    revised by: T.Wild
    // +      input by: meo
    // +      input by: William
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Josep Sanz (http://www.ws3.es/)
    // +    revised by: Rafał Kukawski (http://blog.kukawski.pl/)
    // %        note 1: Great work. Ideas for improvement:
    // %        note 1:  - code more compliant with developer guidelines
    // %        note 1:  - for implementing PHP constant arguments look at
    // %        note 1:  the pathinfo() function, it offers the greatest
    // %        note 1:  flexibility & compatibility possible
    // *     example 1: round(1241757, -3);
    // *     returns 1: 1242000
    // *     example 2: round(3.6);
    // *     returns 2: 4
    // *     example 3: round(2.835, 2);
    // *     returns 3: 2.84
    // *     example 4: round(1.1749999999999, 2);
    // *     returns 4: 1.17
    // *     example 5: round(58551.799999999996, 2);
    // *     returns 5: 58551.8
    var m, f, isHalf, sgn; // helper variables
    precision |= 0; // making sure precision is integer
    m = Math.pow(10, precision);
    value *= m;
    sgn = (value > 0) | -(value < 0); // sign of the number
    isHalf = value % 1 === 0.5 * sgn;
    f = Math.floor(value);

    if (isHalf) {
        switch (mode) {
        case 'PHP_ROUND_HALF_DOWN':
            value = f + (sgn < 0); // rounds .5 toward zero
            break;
        case 'PHP_ROUND_HALF_EVEN':
            value = f + (f % 2 * sgn); // rouds .5 towards the next even integer
            break;
        case 'PHP_ROUND_HALF_ODD':
            value = f + !(f % 2); // rounds .5 towards the next odd integer
            break;
        default:
            value = f + (sgn > 0); // rounds .5 away from zero
        }
    }

    return (isHalf ? value : Math.round(value)) / m;
}

function rtrim (str, charlist) {
    // Removes trailing whitespace
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/rtrim
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Erkekjetter
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   input by: rem
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: rtrim('    Kevin van Zonneveld    ');
    // *     returns 1: '    Kevin van Zonneveld'
    charlist = !charlist ? ' \\s\u00A0' : (charlist + '').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\\$1');
    var re = new RegExp('[' + charlist + ']+$', 'g');
    return (str + '').replace(re, '');
}

function sprintf () {
    // Return a formatted string
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/sprintf
    // +   original by: Ash Searle (http://hexmen.com/blog/)
    // + namespaced by: Michael White (http://getsprink.com)
    // +    tweaked by: Jack
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Paulo Freitas
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: sprintf("%01.2f", 123.1);
    // *     returns 1: 123.10
    // *     example 2: sprintf("[%10s]", 'monkey');
    // *     returns 2: '[    monkey]'
    // *     example 3: sprintf("[%'#10s]", 'monkey');
    // *     returns 3: '[####monkey]'
    var regex = /%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuidfegEG])/g;
    var a = arguments,
        i = 0,
        format = a[i++];

    // pad()
    var pad = function (str, len, chr, leftJustify) {
        if (!chr) {
            chr = ' ';
        }
        var padding = (str.length >= len) ? '' : Array(1 + len - str.length >>> 0).join(chr);
        return leftJustify ? str + padding : padding + str;
    };

    // justify()
    var justify = function (value, prefix, leftJustify, minWidth, zeroPad, customPadChar) {
        var diff = minWidth - value.length;
        if (diff > 0) {
            if (leftJustify || !zeroPad) {
                value = pad(value, minWidth, customPadChar, leftJustify);
            } else {
                value = value.slice(0, prefix.length) + pad('', diff, '0', true) + value.slice(prefix.length);
            }
        }
        return value;
    };

    // formatBaseX()
    var formatBaseX = function (value, base, prefix, leftJustify, minWidth, precision, zeroPad) {
        // Note: casts negative numbers to positive ones
        var number = value >>> 0;
        prefix = prefix && number && {
            '2': '0b',
            '8': '0',
            '16': '0x'
        }[base] || '';
        value = prefix + pad(number.toString(base), precision || 0, '0', false);
        return justify(value, prefix, leftJustify, minWidth, zeroPad);
    };

    // formatString()
    var formatString = function (value, leftJustify, minWidth, precision, zeroPad, customPadChar) {
        if (precision != null) {
            value = value.slice(0, precision);
        }
        return justify(value, '', leftJustify, minWidth, zeroPad, customPadChar);
    };

    // doFormat()
    var doFormat = function (substring, valueIndex, flags, minWidth, _, precision, type) {
        var number;
        var prefix;
        var method;
        var textTransform;
        var value;

        if (substring == '%%') {
            return '%';
        }

        // parse flags
        var leftJustify = false,
            positivePrefix = '',
            zeroPad = false,
            prefixBaseX = false,
            customPadChar = ' ';
        var flagsl = flags.length;
        for (var j = 0; flags && j < flagsl; j++) {
            switch (flags.charAt(j)) {
            case ' ':
                positivePrefix = ' ';
                break;
            case '+':
                positivePrefix = '+';
                break;
            case '-':
                leftJustify = true;
                break;
            case "'":
                customPadChar = flags.charAt(j + 1);
                break;
            case '0':
                zeroPad = true;
                break;
            case '#':
                prefixBaseX = true;
                break;
            }
        }

        // parameters may be null, undefined, empty-string or real valued
        // we want to ignore null, undefined and empty-string values
        if (!minWidth) {
            minWidth = 0;
        } else if (minWidth == '*') {
            minWidth = +a[i++];
        } else if (minWidth.charAt(0) == '*') {
            minWidth = +a[minWidth.slice(1, -1)];
        } else {
            minWidth = +minWidth;
        }

        // Note: undocumented perl feature:
        if (minWidth < 0) {
            minWidth = -minWidth;
            leftJustify = true;
        }

        if (!isFinite(minWidth)) {
            throw new Error('sprintf: (minimum-)width must be finite');
        }

        if (!precision) {
            precision = 'fFeE'.indexOf(type) > -1 ? 6 : (type == 'd') ? 0 : undefined;
        } else if (precision == '*') {
            precision = +a[i++];
        } else if (precision.charAt(0) == '*') {
            precision = +a[precision.slice(1, -1)];
        } else {
            precision = +precision;
        }

        // grab value using valueIndex if required?
        value = valueIndex ? a[valueIndex.slice(0, -1)] : a[i++];

        switch (type) {
        case 's':
            return formatString(String(value), leftJustify, minWidth, precision, zeroPad, customPadChar);
        case 'c':
            return formatString(String.fromCharCode(+value), leftJustify, minWidth, precision, zeroPad);
        case 'b':
            return formatBaseX(value, 2, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
        case 'o':
            return formatBaseX(value, 8, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
        case 'x':
            return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
        case 'X':
            return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad).toUpperCase();
        case 'u':
            return formatBaseX(value, 10, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
        case 'i':
        case 'd':
            number = (+value) | 0;
            prefix = number < 0 ? '-' : positivePrefix;
            value = prefix + pad(String(Math.abs(number)), precision, '0', false);
            return justify(value, prefix, leftJustify, minWidth, zeroPad);
        case 'e':
        case 'E':
        case 'f':
        case 'F':
        case 'g':
        case 'G':
            number = +value;
            prefix = number < 0 ? '-' : positivePrefix;
            method = ['toExponential', 'toFixed', 'toPrecision']['efg'.indexOf(type.toLowerCase())];
            textTransform = ['toString', 'toUpperCase']['eEfFgG'.indexOf(type) % 2];
            value = prefix + Math.abs(number)[method](precision);
            return justify(value, prefix, leftJustify, minWidth, zeroPad)[textTransform]();
        default:
            return substring;
        }
    };

    return format.replace(regex, doFormat);
}

function str_pad (input, pad_length, pad_string, pad_type) {
    // Returns input string padded on the left or right to specified length with pad_string
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/str_pad
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // + namespaced by: Michael White (http://getsprink.com)
    // +      input by: Marco van Oort
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: str_pad('Kevin van Zonneveld', 30, '-=', 'STR_PAD_LEFT');
    // *     returns 1: '-=-=-=-=-=-Kevin van Zonneveld'
    // *     example 2: str_pad('Kevin van Zonneveld', 30, '-', 'STR_PAD_BOTH');
    // *     returns 2: '------Kevin van Zonneveld-----'
    var half = '',
        pad_to_go;

    var str_pad_repeater = function (s, len) {
        var collect = '',
            i;

        while (collect.length < len) {
            collect += s;
        }
        collect = collect.substr(0, len);

        return collect;
    };

    input += '';
    pad_string = pad_string !== undefined ? pad_string : ' ';

    if (pad_type != 'STR_PAD_LEFT' && pad_type != 'STR_PAD_RIGHT' && pad_type != 'STR_PAD_BOTH') {
        pad_type = 'STR_PAD_RIGHT';
    }
    if ((pad_to_go = pad_length - input.length) > 0) {
        if (pad_type == 'STR_PAD_LEFT') {
            input = str_pad_repeater(pad_string, pad_to_go) + input;
        } else if (pad_type == 'STR_PAD_RIGHT') {
            input = input + str_pad_repeater(pad_string, pad_to_go);
        } else if (pad_type == 'STR_PAD_BOTH') {
            half = str_pad_repeater(pad_string, Math.ceil(pad_to_go / 2));
            input = half + input + half;
            input = input.substr(0, pad_length);
        }
    }

    return input;
}

function str_repeat (input, multiplier) {
    // Returns the input string repeat mult times
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/str_repeat
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // *     example 1: str_repeat('-=', 10);
    // *     returns 1: '-=-=-=-=-=-=-=-=-=-='
    return new Array(multiplier + 1).join(input);
}

function strcasecmp (f_string1, f_string2) {
    // Binary safe case-insensitive string comparison
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/strcasecmp
    // +     original by: Martijn Wieringa
    // +     bugfixed by: Onno Marsman
    // *         example 1: strcasecmp('Hello', 'hello');
    // *         returns 1: 0
    var string1 = (f_string1 + '').toLowerCase();
    var string2 = (f_string2 + '').toLowerCase();

    if (string1 > string2) {
        return 1;
    } else if (string1 == string2) {
        return 0;
    }

    return -1;
}

function strcmp (str1, str2) {
    // Binary safe string comparison
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/strcmp
    // +   original by: Waldo Malqui Silva
    // +      input by: Steve Hilder
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: gorthaur
    // *     example 1: strcmp( 'waldo', 'owald' );
    // *     returns 1: 1
    // *     example 2: strcmp( 'owald', 'waldo' );
    // *     returns 2: -1
    return ((str1 == str2) ? 0 : ((str1 > str2) ? 1 : -1));
}

function strip_tags (input, allowed) {
    // Strips HTML and PHP tags from a string
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/strip_tags
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Luke Godfrey
    // +      input by: Pul
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +      input by: Alex
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Marc Palau
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Eric Nagel
    // +      input by: Bobby Drake
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Tomasz Wesolowski
    // +      input by: Evertjan Garretsen
    // +    revised by: Rafał Kukawski (http://blog.kukawski.pl/)
    // *     example 1: strip_tags('<p>Kevin</p> <b>van</b> <i>Zonneveld</i>', '<i><b>');
    // *     returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
    // *     example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
    // *     returns 2: '<p>Kevin van Zonneveld</p>'
    // *     example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
    // *     returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
    // *     example 4: strip_tags('1 < 5 5 > 1');
    // *     returns 4: '1 < 5 5 > 1'
    // *     example 5: strip_tags('1 <br/> 1');
    // *     returns 5: '1  1'
    // *     example 6: strip_tags('1 <br/> 1', '<br>');
    // *     returns 6: '1  1'
    // *     example 7: strip_tags('1 <br/> 1', '<br><br/>');
    // *     returns 7: '1 <br/> 1'
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}

function stripslashes (str) {
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Ates Goral (http://magnetiq.com)
    // +      fixed by: Mick@el
    // +   improved by: marrtins
    // +   bugfixed by: Onno Marsman
    // +   improved by: rezna
    // +   input by: Rick Waldron
    // +   reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +   input by: Brant Messenger (http://www.brantmessenger.com/)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: stripslashes('Kevin\'s code');
    // *     returns 1: "Kevin's code"
    // *     example 2: stripslashes('Kevin\\\'s code');
    // *     returns 2: "Kevin\'s code"
    return (str + '').replace(/\\(.?)/g, function (s, n1) {
        switch (n1) {
        case '\\':
            return '\\';
        case '0':
            return '\u0000';
        case '':
            return '';
        default:
            return n1;
        }
    });
}

function stripos (f_haystack, f_needle, f_offset) {
    // Finds position of first occurrence of a string within another, case insensitive
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/stripos
    // +     original by: Martijn Wieringa
    // +      revised by: Onno Marsman
    // *         example 1: stripos('ABC', 'a');
    // *         returns 1: 0
    var haystack = (f_haystack + '').toLowerCase();
    var needle = (f_needle + '').toLowerCase();
    var index = 0;

    if ((index = haystack.indexOf(needle, f_offset)) !== -1) {
        return index;
    }
    return false;
}

function stristr (haystack, needle, bool) {
    // Finds first occurrence of a string within another, case insensitive
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/stristr
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfxied by: Onno Marsman
    // *     example 1: stristr('Kevin van Zonneveld', 'Van');
    // *     returns 1: 'van Zonneveld'
    // *     example 2: stristr('Kevin van Zonneveld', 'VAN', true);
    // *     returns 2: 'Kevin '
    var pos = 0;

    haystack += '';
    pos = haystack.toLowerCase().indexOf((needle + '').toLowerCase());
    if (pos == -1) {
        return false;
    } else {
        if (bool) {
            return haystack.substr(0, pos);
        } else {
            return haystack.slice(pos);
        }
    }
}

function strrev (string) {
    // Reverse a string
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/strrev
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   reimplemented by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: strrev('Kevin van Zonneveld');
    // *     returns 1: 'dlevennoZ nav niveK'
    // *     example 2: strrev('a\u0301haB') === 'Baha\u0301'; // combining
    // *     returns 2: true
    // *     example 3: strrev('A\uD87E\uDC04Z') === 'Z\uD87E\uDC04A'; // surrogates
    // *     returns 2: true
    string = string + '';

    // Performance will be enhanced with the next two lines of code commented
    //      out if you don't care about combining characters
    // Keep Unicode combining characters together with the character preceding
    //      them and which they are modifying (as in PHP 6)
    // See http://unicode.org/reports/tr44/#Property_Table (Me+Mn)
    // We also add the low surrogate range at the beginning here so it will be
    //      maintained with its preceding high surrogate
    var grapheme_extend = /(.)([\uDC00-\uDFFF\u0300-\u036F\u0483-\u0489\u0591-\u05BD\u05BF\u05C1\u05C2\u05C4\u05C5\u05C7\u0610-\u061A\u064B-\u065E\u0670\u06D6-\u06DC\u06DE-\u06E4\u06E7\u06E8\u06EA-\u06ED\u0711\u0730-\u074A\u07A6-\u07B0\u07EB-\u07F3\u0901-\u0903\u093C\u093E-\u094D\u0951-\u0954\u0962\u0963\u0981-\u0983\u09BC\u09BE-\u09C4\u09C7\u09C8\u09CB-\u09CD\u09D7\u09E2\u09E3\u0A01-\u0A03\u0A3C\u0A3E-\u0A42\u0A47\u0A48\u0A4B-\u0A4D\u0A51\u0A70\u0A71\u0A75\u0A81-\u0A83\u0ABC\u0ABE-\u0AC5\u0AC7-\u0AC9\u0ACB-\u0ACD\u0AE2\u0AE3\u0B01-\u0B03\u0B3C\u0B3E-\u0B44\u0B47\u0B48\u0B4B-\u0B4D\u0B56\u0B57\u0B62\u0B63\u0B82\u0BBE-\u0BC2\u0BC6-\u0BC8\u0BCA-\u0BCD\u0BD7\u0C01-\u0C03\u0C3E-\u0C44\u0C46-\u0C48\u0C4A-\u0C4D\u0C55\u0C56\u0C62\u0C63\u0C82\u0C83\u0CBC\u0CBE-\u0CC4\u0CC6-\u0CC8\u0CCA-\u0CCD\u0CD5\u0CD6\u0CE2\u0CE3\u0D02\u0D03\u0D3E-\u0D44\u0D46-\u0D48\u0D4A-\u0D4D\u0D57\u0D62\u0D63\u0D82\u0D83\u0DCA\u0DCF-\u0DD4\u0DD6\u0DD8-\u0DDF\u0DF2\u0DF3\u0E31\u0E34-\u0E3A\u0E47-\u0E4E\u0EB1\u0EB4-\u0EB9\u0EBB\u0EBC\u0EC8-\u0ECD\u0F18\u0F19\u0F35\u0F37\u0F39\u0F3E\u0F3F\u0F71-\u0F84\u0F86\u0F87\u0F90-\u0F97\u0F99-\u0FBC\u0FC6\u102B-\u103E\u1056-\u1059\u105E-\u1060\u1062-\u1064\u1067-\u106D\u1071-\u1074\u1082-\u108D\u108F\u135F\u1712-\u1714\u1732-\u1734\u1752\u1753\u1772\u1773\u17B6-\u17D3\u17DD\u180B-\u180D\u18A9\u1920-\u192B\u1930-\u193B\u19B0-\u19C0\u19C8\u19C9\u1A17-\u1A1B\u1B00-\u1B04\u1B34-\u1B44\u1B6B-\u1B73\u1B80-\u1B82\u1BA1-\u1BAA\u1C24-\u1C37\u1DC0-\u1DE6\u1DFE\u1DFF\u20D0-\u20F0\u2DE0-\u2DFF\u302A-\u302F\u3099\u309A\uA66F-\uA672\uA67C\uA67D\uA802\uA806\uA80B\uA823-\uA827\uA880\uA881\uA8B4-\uA8C4\uA926-\uA92D\uA947-\uA953\uAA29-\uAA36\uAA43\uAA4C\uAA4D\uFB1E\uFE00-\uFE0F\uFE20-\uFE26]+)/g;
    string = string.replace(grapheme_extend, '$2$1'); // Temporarily reverse
    return string.split('').reverse().join('');
}

function strstr (haystack, needle, bool) {
    // Finds first occurrence of a string within another
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/strstr
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: strstr('Kevin van Zonneveld', 'van');
    // *     returns 1: 'van Zonneveld'
    // *     example 2: strstr('Kevin van Zonneveld', 'van', true);
    // *     returns 2: 'Kevin '
    // *     example 3: strstr('name@example.com', '@');
    // *     returns 3: '@example.com'
    // *     example 4: strstr('name@example.com', '@', true);
    // *     returns 4: 'name'
    var pos = 0;

    haystack += '';
    pos = haystack.indexOf(needle);
    if (pos == -1) {
        return false;
    } else {
        if (bool) {
            return haystack.substr(0, pos);
        } else {
            return haystack.slice(pos);
        }
    }
}

function strtotime (text, now) {
 //  discuss at: http://locutus.io/php/strtotime/
  // original by: Caio Ariede (http://caioariede.com)
  // improved by: Kevin van Zonneveld (http://kvz.io)
  // improved by: Caio Ariede (http://caioariede.com)
  // improved by: A. Matías Quezada (http://amatiasq.com)
  // improved by: preuter
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Mirko Faber
  //    input by: David
  // bugfixed by: Wagner B. Soares
  // bugfixed by: Artur Tchernychev
  // bugfixed by: Stephan Bösch-Plepelits (http://github.com/plepe)
  //      note 1: Examples all have a fixed timestamp to prevent
  //      note 1: tests to fail because of variable time(zones)
  //   example 1: strtotime('+1 day', 1129633200)
  //   returns 1: 1129719600
  //   example 2: strtotime('+1 week 2 days 4 hours 2 seconds', 1129633200)
  //   returns 2: 1130425202
  //   example 3: strtotime('last month', 1129633200)
  //   returns 3: 1127041200
  //   example 4: strtotime('2009-05-04 08:30:00 GMT')
  //   returns 4: 1241425800
  //   example 5: strtotime('2009-05-04 08:30:00+00')
  //   returns 5: 1241425800
  //   example 6: strtotime('2009-05-04 08:30:00+02:00')
  //   returns 6: 1241418600
  //   example 7: strtotime('2009-05-04T08:30:00Z')
  //   returns 7: 1241425800

  var parsed
  var match
  var today
  var year
  var date
  var days
  var ranges
  var len
  var times
  var regex
  var i
  var fail = false

  if (!text) {
    return fail
  }

  // Unecessary spaces
  text = text.replace(/^\s+|\s+$/g, '')
    .replace(/\s{2,}/g, ' ')
    .replace(/[\t\r\n]/g, '')
    .toLowerCase()

  // in contrast to php, js Date.parse function interprets:
  // dates given as yyyy-mm-dd as in timezone: UTC,
  // dates with "." or "-" as MDY instead of DMY
  // dates with two-digit years differently
  // etc...etc...
  // ...therefore we manually parse lots of common date formats
  var pattern = new RegExp([
    '^(\\d{1,4})',
    '([\\-\\.\\/:])',
    '(\\d{1,2})',
    '([\\-\\.\\/:])',
    '(\\d{1,4})',
    '(?:\\s(\\d{1,2}):(\\d{2})?:?(\\d{2})?)?',
    '(?:\\s([A-Z]+)?)?$'
  ].join(''))
  match = text.match(pattern)

  if (match && match[2] === match[4]) {
    if (match[1] > 1901) {
      switch (match[2]) {
        case '-':
          // YYYY-M-D
          if (match[3] > 12 || match[5] > 31) {
            return fail
          }

          return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000
        case '.':
          // YYYY.M.D is not parsed by strtotime()
          return fail
        case '/':
          // YYYY/M/D
          if (match[3] > 12 || match[5] > 31) {
            return fail
          }

          return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000
      }
    } else if (match[5] > 1901) {
      switch (match[2]) {
        case '-':
          // D-M-YYYY
          if (match[3] > 12 || match[1] > 31) {
            return fail
          }

          return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000
        case '.':
          // D.M.YYYY
          if (match[3] > 12 || match[1] > 31) {
            return fail
          }

          return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000
        case '/':
          // M/D/YYYY
          if (match[1] > 12 || match[3] > 31) {
            return fail
          }

          return new Date(match[5], parseInt(match[1], 10) - 1, match[3],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000
      }
    } else {
      switch (match[2]) {
        case '-':
          // YY-M-D
          if (match[3] > 12 || match[5] > 31 || (match[1] < 70 && match[1] > 38)) {
            return fail
          }

          year = match[1] >= 0 && match[1] <= 38 ? +match[1] + 2000 : match[1]
          return new Date(year, parseInt(match[3], 10) - 1, match[5],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000
        case '.':
          // D.M.YY or H.MM.SS
          if (match[5] >= 70) {
            // D.M.YY
            if (match[3] > 12 || match[1] > 31) {
              return fail
            }

            return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000
          }
          if (match[5] < 60 && !match[6]) {
            // H.MM.SS
            if (match[1] > 23 || match[3] > 59) {
              return fail
            }

            today = new Date()
            return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
            match[1] || 0, match[3] || 0, match[5] || 0, match[9] || 0) / 1000
          }

          // invalid format, cannot be parsed
          return fail
        case '/':
          // M/D/YY
          if (match[1] > 12 || match[3] > 31 || (match[5] < 70 && match[5] > 38)) {
            return fail
          }

          year = match[5] >= 0 && match[5] <= 38 ? +match[5] + 2000 : match[5]
          return new Date(year, parseInt(match[1], 10) - 1, match[3],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000
        case ':':
          // HH:MM:SS
          if (match[1] > 23 || match[3] > 59 || match[5] > 59) {
            return fail
          }

          today = new Date()
          return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
          match[1] || 0, match[3] || 0, match[5] || 0) / 1000
      }
    }
  }

  // other formats and "now" should be parsed by Date.parse()
  if (text === 'now') {
    return now === null || isNaN(now)
      ? new Date().getTime() / 1000 | 0
      : now | 0
  }
  if (!isNaN(parsed = Date.parse(text))) {
    return parsed / 1000 | 0
  }
  // Browsers !== Chrome have problems parsing ISO 8601 date strings, as they do
  // not accept lower case characters, space, or shortened time zones.
  // Therefore, fix these problems and try again.
  // Examples:
  //   2015-04-15 20:33:59+02
  //   2015-04-15 20:33:59z
  //   2015-04-15t20:33:59+02:00
  pattern = new RegExp([
    '^([0-9]{4}-[0-9]{2}-[0-9]{2})',
    '[ t]',
    '([0-9]{2}:[0-9]{2}:[0-9]{2}(\\.[0-9]+)?)',
    '([\\+-][0-9]{2}(:[0-9]{2})?|z)'
  ].join(''))
  match = text.match(pattern)
  if (match) {
    // @todo: time zone information
    if (match[4] === 'z') {
      match[4] = 'Z'
    } else if (match[4].match(/^([\+-][0-9]{2})$/)) {
      match[4] = match[4] + ':00'
    }

    if (!isNaN(parsed = Date.parse(match[1] + 'T' + match[2] + match[4]))) {
      return parsed / 1000 | 0
    }
  }

  date = now ? new Date(now * 1000) : new Date()
  days = {
    'sun': 0,
    'mon': 1,
    'tue': 2,
    'wed': 3,
    'thu': 4,
    'fri': 5,
    'sat': 6
  }
  ranges = {
    'yea': 'FullYear',
    'mon': 'Month',
    'day': 'Date',
    'hou': 'Hours',
    'min': 'Minutes',
    'sec': 'Seconds'
  }

  function lastNext (type, range, modifier) {
    var diff
    var day = days[range]

    if (typeof day !== 'undefined') {
      diff = day - date.getDay()

      if (diff === 0) {
        diff = 7 * modifier
      } else if (diff > 0 && type === 'last') {
        diff -= 7
      } else if (diff < 0 && type === 'next') {
        diff += 7
      }

      date.setDate(date.getDate() + diff)
    }
  }

  function process (val) {
    // @todo: Reconcile this with regex using \s, taking into account
    // browser issues with split and regexes
    var splt = val.split(' ')
    var type = splt[0]
    var range = splt[1].substring(0, 3)
    var typeIsNumber = /\d+/.test(type)
    var ago = splt[2] === 'ago'
    var num = (type === 'last' ? -1 : 1) * (ago ? -1 : 1)

    if (typeIsNumber) {
      num *= parseInt(type, 10)
    }

    if (ranges.hasOwnProperty(range) && !splt[1].match(/^mon(day|\.)?$/i)) {
      return date['set' + ranges[range]](date['get' + ranges[range]]() + num)
    }

    if (range === 'wee') {
      return date.setDate(date.getDate() + (num * 7))
    }

    if (type === 'next' || type === 'last') {
      lastNext(type, range, num)
    } else if (!typeIsNumber) {
      return false
    }

    return true
  }

  times = '(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec' +
    '|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?' +
    '|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)'
  regex = '([+-]?\\d+\\s' + times + '|' + '(last|next)\\s' + times + ')(\\sago)?'

  match = text.match(new RegExp(regex, 'gi'))
  if (!match) {
    return fail
  }

  for (i = 0, len = match.length; i < len; i++) {
    if (!process(match[i])) {
      return fail
    }
  }

  return (date.getTime() / 1000)
}

function substr (str, start, len) {
    // Returns part of a string
    //
    // version: 909.322
    // discuss at: http://phpjs.org/functions/substr
    // +     original by: Martijn Wieringa
    // +     bugfixed by: T.Wild
    // +      tweaked by: Onno Marsman
    // +      revised by: Theriault
    // +      improved by: Brett Zamir (http://brett-zamir.me)
    // %    note 1: Handles rare Unicode characters if 'unicode.semantics' ini (PHP6) is set to 'on'
    // *       example 1: substr('abcdef', 0, -1);
    // *       returns 1: 'abcde'
    // *       example 2: substr(2, 0, -6);
    // *       returns 2: false
    // *       example 3: ini_set('unicode.semantics',  'on');
    // *       example 3: substr('a\uD801\uDC00', 0, -1);
    // *       returns 3: 'a'
    // *       example 4: ini_set('unicode.semantics',  'on');
    // *       example 4: substr('a\uD801\uDC00', 0, 2);
    // *       returns 4: 'a\uD801\uDC00'
    // *       example 5: ini_set('unicode.semantics',  'on');
    // *       example 5: substr('a\uD801\uDC00', -1, 1);
    // *       returns 5: '\uD801\uDC00'
    // *       example 6: ini_set('unicode.semantics',  'on');
    // *       example 6: substr('a\uD801\uDC00z\uD801\uDC00', -3, 2);
    // *       returns 6: '\uD801\uDC00z'
    // *       example 7: ini_set('unicode.semantics',  'on');
    // *       example 7: substr('a\uD801\uDC00z\uD801\uDC00', -3, -1)
    // *       returns 7: '\uD801\uDC00z'
    // Add: (?) Use unicode.runtime_encoding (e.g., with string wrapped in "binary" or "Binary" class) to
    // allow access of binary (see file_get_contents()) by: charCodeAt(x) & 0xFF (see https://developer.mozilla.org/En/Using_XMLHttpRequest ) or require conversion first?
    var i = 0,
        allBMP = true,
        es = 0,
        el = 0,
        se = 0,
        ret = '';
    str += '';
    var end = str.length;

    // BEGIN REDUNDANT
    this.php_js = this.php_js || {};
    this.php_js.ini = this.php_js.ini || {};
    // END REDUNDANT
    switch ((this.php_js.ini['unicode.semantics'] && this.php_js.ini['unicode.semantics'].local_value.toLowerCase())) {
    case 'on':
        // Full-blown Unicode including non-Basic-Multilingual-Plane characters
        // strlen()
        for (i = 0; i < str.length; i++) {
            if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                allBMP = false;
                break;
            }
        }

        if (!allBMP) {
            if (start < 0) {
                for (i = end - 1, es = (start += end); i >= es; i--) {
                    if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                        start--;
                        es--;
                    }
                }
            } else {
                var surrogatePairs = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
                while ((surrogatePairs.exec(str)) != null) {
                    var li = surrogatePairs.lastIndex;
                    if (li - 2 < start) {
                        start++;
                    } else {
                        break;
                    }
                }
            }

            if (start >= end || start < 0) {
                return false;
            }
            if (len < 0) {
                for (i = end - 1, el = (end += len); i >= el; i--) {
                    if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                        end--;
                        el--;
                    }
                }
                if (start > end) {
                    return false;
                }
                return str.slice(start, end);
            } else {
                se = start + len;
                for (i = start; i < se; i++) {
                    ret += str.charAt(i);
                    if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                        se++; // Go one further, since one of the "characters" is part of a surrogate pair
                    }
                }
                return ret;
            }
            break;
        }
        // Fall-through
    case 'off':
        // assumes there are no non-BMP characters;
        //    if there may be such characters, then it is best to turn it on (critical in true XHTML/XML)
    default:
        if (start < 0) {
            start += end;
        }
        end = typeof len === 'undefined' ? end : (len < 0 ? len + end : len + start);
        // PHP returns false if start does not fall within the string.
        // PHP returns false if the calculated end comes before the calculated start.
        // PHP returns an empty string if start and end are the same.
        // Otherwise, PHP returns the portion of the string from start to end.
        return start >= str.length || start < 0 || start > end ? !1 : str.slice(start, end);
    }
    return undefined; // Please Netbeans
}

function trim (str, charlist) {
// Strips whitespace from the beginning and end of a string
//
// version: 1107.2516
// discuss at: http://phpjs.org/functions/trim
// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   improved by: mdsjack (http://www.mdsjack.bo.it)
// +   improved by: Alexander Ermolaev (http://snippets.dzone.com/user/AlexanderErmolaev)
// +      input by: Erkekjetter
// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +      input by: DxGx
// +   improved by: Steven Levithan (http://blog.stevenlevithan.com)
// +    tweaked by: Jack
// +   bugfixed by: Onno Marsman
// *     example 1: trim('    Kevin van Zonneveld    ');
// *     returns 1: 'Kevin van Zonneveld'
// *     example 2: trim('Hello World', 'Hdle');
// *     returns 2: 'o Wor'
// *     example 3: trim(16, 1);
// *     returns 3: 6
var whitespace, l = 0,
i = 0;
str += '';

if (!charlist) {
// default list
whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
} else {
// preg_quote custom list
charlist += '';
whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
}

l = str.length;
for (i = 0; i < l; i++) {
if (whitespace.indexOf(str.charAt(i)) === -1) {
str = str.substring(i);
break;
}
}

l = str.length;
for (i = l - 1; i >= 0; i--) {
if (whitespace.indexOf(str.charAt(i)) === -1) {
str = str.substring(0, i + 1);
break;
}
}

return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}

function ucwords (str) {
    // Uppercase the first character of every word in a string
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/ucwords
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Waldo Malqui Silva
    // +   bugfixed by: Onno Marsman
    // +   improved by: Robin
    // +      input by: James (http://www.james-bell.co.uk/)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: ucwords('kevin van  zonneveld');
    // *     returns 1: 'Kevin Van  Zonneveld'
    // *     example 2: ucwords('HELLO WORLD');
    // *     returns 2: 'HELLO WORLD'
    return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
}

function checkdate (m, d, y) {
    // Returns true(1) if it is a valid date in gregorian calendar
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/checkdate
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Pyerre
    // +   improved by: Theriault
    // *     example 1: checkdate(12, 31, 2000);
    // *     returns 1: true
    // *     example 2: checkdate(2, 29, 2001);
    // *     returns 2: false
    // *     example 3: checkdate(3, 31, 2008);
    // *     returns 3: true
    // *     example 4: checkdate(1, 390, 2000);
    // *     returns 4: false
    return m > 0 && m < 13 && y > 0 && y < 32768 && d > 0 && d <= (new Date(y, m, 0)).getDate();
}

function date (format, timestamp) {
    // http://kevin.vanzonneveld.net
    // +   original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
    // +      parts by: Peter-Paul Koch (http://www.quirksmode.org/js/beat.html)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: MeEtc (http://yass.meetcweb.com)
    // +   improved by: Brad Touesnard
    // +   improved by: Tim Wiel
    // +   improved by: Bryan Elliott
    //
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: David Randall
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +  derived from: gettimeofday
    // +      input by: majak
    // +   bugfixed by: majak
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Alex
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Thomas Beaucourt (http://www.webapp.fr)
    // +   improved by: JT
    // +   improved by: Theriault
    // +   improved by: Rafał Kukawski (http://blog.kukawski.pl)
    // +      input by: Martin
    // +      input by: Alex Wilson
    // %        note 1: Uses global: php_js to store the default timezone
    // %        note 2: Although the function potentially allows timezone info (see notes), it currently does not set
    // %        note 2: per a timezone specified by date_default_timezone_set(). Implementers might use
    // %        note 2: this.php_js.currentTimezoneOffset and this.php_js.currentTimezoneDST set by that function
    // %        note 2: in order to adjust the dates in this function (or our other date functions!) accordingly
    // *     example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400);
    // *     returns 1: '09:09:40 m is month'
    // *     example 2: date('F j, Y, g:i a', 1062462400);
    // *     returns 2: 'September 2, 2003, 2:26 am'
    // *     example 3: date('Y W o', 1062462400);
    // *     returns 3: '2003 36 2003'
    // *     example 4: x = date('Y m d', (new Date()).getTime()/1000);
    // *     example 4: (x+'').length == 10 // 2009 01 09
    // *     returns 4: true
    // *     example 5: date('W', 1104534000);
    // *     returns 5: '53'
    // *     example 6: date('B t', 1104534000);
    // *     returns 6: '999 31'
    // *     example 7: date('W U', 1293750000.82); // 2010-12-31
    // *     returns 7: '52 1293750000'
    // *     example 8: date('W', 1293836400); // 2011-01-01
    // *     returns 8: '52'
    // *     example 9: date('W Y-m-d', 1293974054); // 2011-01-02
    // *     returns 9: '52 2011-01-02'
    var that = this,
        jsdate, f, formatChr = /\\?([a-z])/gi,
        formatChrCb,
        // Keep this here (works, but for code commented-out
        // below for file size reasons)
        //, tal= [],
        _pad = function (n, c) {
            if ((n = n + '').length < c) {
                return new Array((++c) - n.length).join('0') + n;
            }
            return n;
        },
        txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    formatChrCb = function (t, s) {
        return f[t] ? f[t]() : s;
    };
    f = {
        // Day
        d: function () { // Day of month w/leading 0; 01..31
            return _pad(f.j(), 2);
        },
        D: function () { // Shorthand day name; Mon...Sun
            return f.l().slice(0, 3);
        },
        j: function () { // Day of month; 1..31
            return jsdate.getDate();
        },
        l: function () { // Full day name; Monday...Sunday
            return txt_words[f.w()] + 'day';
        },
        N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
            return f.w() || 7;
        },
        S: function () { // Ordinal suffix for day of month; st, nd, rd, th
            var j = f.j();
            return j > 4 || j < 21 ? 'th' : {1: 'st', 2: 'nd', 3: 'rd'}[j % 10] || 'th';
        },
        w: function () { // Day of week; 0[Sun]..6[Sat]
            return jsdate.getDay();
        },
        z: function () { // Day of year; 0..365
            var a = new Date(f.Y(), f.n() - 1, f.j()),
                b = new Date(f.Y(), 0, 1);
            return Math.round((a - b) / 864e5) + 1;
        },

        // Week
        W: function () { // ISO-8601 week number
            var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3),
                b = new Date(a.getFullYear(), 0, 4);
            return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
        },

        // Month
        F: function () { // Full month name; January...December
            return txt_words[6 + f.n()];
        },
        m: function () { // Month w/leading 0; 01...12
            return _pad(f.n(), 2);
        },
        M: function () { // Shorthand month name; Jan...Dec
            return f.F().slice(0, 3);
        },
        n: function () { // Month; 1...12
            return jsdate.getMonth() + 1;
        },
        t: function () { // Days in month; 28...31
            return (new Date(f.Y(), f.n(), 0)).getDate();
        },

        // Year
        L: function () { // Is leap year?; 0 or 1
            return new Date(f.Y(), 1, 29).getMonth() === 1 | 0;
        },
        o: function () { // ISO-8601 year
            var n = f.n(),
                W = f.W(),
                Y = f.Y();
            return Y + (n === 12 && W < 9 ? -1 : n === 1 && W > 9);
        },
        Y: function () { // Full year; e.g. 1980...2010
            return jsdate.getFullYear();
        },
        y: function () { // Last two digits of year; 00...99
            return (f.Y() + "").slice(-2);
        },

        // Time
        a: function () { // am or pm
            return jsdate.getHours() > 11 ? "pm" : "am";
        },
        A: function () { // AM or PM
            return f.a().toUpperCase();
        },
        B: function () { // Swatch Internet time; 000..999
            var H = jsdate.getUTCHours() * 36e2,
                // Hours
                i = jsdate.getUTCMinutes() * 60,
                // Minutes
                s = jsdate.getUTCSeconds(); // Seconds
            return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
        },
        g: function () { // 12-Hours; 1..12
            return f.G() % 12 || 12;
        },
        G: function () { // 24-Hours; 0..23
            return jsdate.getHours();
        },
        h: function () { // 12-Hours w/leading 0; 01..12
            return _pad(f.g(), 2);
        },
        H: function () { // 24-Hours w/leading 0; 00..23
            return _pad(f.G(), 2);
        },
        i: function () { // Minutes w/leading 0; 00..59
            return _pad(jsdate.getMinutes(), 2);
        },
        s: function () { // Seconds w/leading 0; 00..59
            return _pad(jsdate.getSeconds(), 2);
        },
        u: function () { // Microseconds; 000000-999000
            return _pad(jsdate.getMilliseconds() * 1000, 6);
        },

        // Timezone
        e: function () { // Timezone identifier; e.g. Atlantic/Azores, ...
            // The following works, but requires inclusion of the very large
            // timezone_abbreviations_list() function.
/*              return this.date_default_timezone_get();
*/
            throw 'Not supported (see source code of date() for timezone on how to add support)';
        },
        I: function () { // DST observed?; 0 or 1
            // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
            // If they are not equal, then DST is observed.
            var a = new Date(f.Y(), 0),
                // Jan 1
                c = Date.UTC(f.Y(), 0),
                // Jan 1 UTC
                b = new Date(f.Y(), 6),
                // Jul 1
                d = Date.UTC(f.Y(), 6); // Jul 1 UTC
            return 0 + ((a - c) !== (b - d));
        },
        O: function () { // Difference to GMT in hour format; e.g. +0200
            var a = jsdate.getTimezoneOffset();
            return (a > 0 ? "-" : "+") + _pad(Math.abs(a / 60 * 100), 4);
        },
        P: function () { // Difference to GMT w/colon; e.g. +02:00
            var O = f.O();
            return (O.substr(0, 3) + ":" + O.substr(3, 2));
        },
        T: function () { // Timezone abbreviation; e.g. EST, MDT, ...
            // The following works, but requires inclusion of the very
            // large timezone_abbreviations_list() function.
/*              var abbr = '', i = 0, os = 0, default = 0;
            if (!tal.length) {
                tal = that.timezone_abbreviations_list();
            }
            if (that.php_js && that.php_js.default_timezone) {
                default = that.php_js.default_timezone;
                for (abbr in tal) {
                    for (i=0; i < tal[abbr].length; i++) {
                        if (tal[abbr][i].timezone_id === default) {
                            return abbr.toUpperCase();
                        }
                    }
                }
            }
            for (abbr in tal) {
                for (i = 0; i < tal[abbr].length; i++) {
                    os = -jsdate.getTimezoneOffset() * 60;
                    if (tal[abbr][i].offset === os) {
                        return abbr.toUpperCase();
                    }
                }
            }
*/
            return 'UTC';
        },
        Z: function () { // Timezone offset in seconds (-43200...50400)
            return -jsdate.getTimezoneOffset() * 60;
        },

        // Full Date/Time
        c: function () { // ISO-8601 date.
            return 'Y-m-d\\Th:i:sP'.replace(formatChr, formatChrCb);
        },
        r: function () { // RFC 2822
            return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
        },
        U: function () { // Seconds since UNIX epoch
            return jsdate.getTime() / 1000 | 0;
        }
    };
    this.date = function (format, timestamp) {
        that = this;
        jsdate = ((typeof timestamp === 'undefined') ? new Date() : // Not provided
        (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
        new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
        );
        return format.replace(formatChr, formatChrCb);
    };
    return this.date(format, timestamp);
}

function gmdate (format, timestamp) {
    // Format a GMT date/time
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/gmdate
    // +   original by: Brett Zamir (http://brett-zamir.me)
    // +   input by: Alex
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // -    depends on: date
    // *     example 1: gmdate('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400); // Return will depend on your timezone
    // *     returns 1: '07:09:40 m is month'
    var dt = typeof timestamp === 'undefined' ? new Date() : // Not provided
                        typeof timestamp === 'object' ? new Date(timestamp) : // Javascript Date()
                        new Date(timestamp * 1000); // UNIX timestamp (auto-convert to int)
    timestamp = Date.parse(dt.toUTCString().slice(0, -4)) / 1000;
    return this.date(format, timestamp);
}

function idate (format, timestamp) {
    // Format a local time/date as integer
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/idate
    // +   original by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Alex
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +  derived from: date
    // +  derived from: gettimeofday
    // *     example 1: idate('y');
    // *     returns 1: 9
    if (format === undefined) {
        throw 'idate() expects at least 1 parameter, 0 given';
    }
    if (!format.length || format.length > 1) {
        throw 'idate format is one char';
    }

    // Fix: Need to allow date_default_timezone_set() (check for this.php_js.default_timezone and use)
    var date = ((typeof timestamp === 'undefined') ? new Date() : // Not provided
    (timestamp instanceof Date) ? new Date(timestamp) : // Javascript Date()
    new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
    ),
        a;

    switch (format) {
    case 'B':
        return Math.floor(((date.getUTCHours() * 36e2) + (date.getUTCMinutes() * 60) + date.getUTCSeconds() + 36e2) / 86.4) % 1e3;
    case 'd':
        return date.getDate();
    case 'h':
        return date.getHours() % 12 || 12;
    case 'H':
        return date.getHours();
    case 'i':
        return date.getMinutes();
    case 'I':
        // capital 'i'
        // Logic derived from getimeofday().
        // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
        // If they are not equal, then DST is observed.
        a = date.getFullYear();
        return 0 + (((new Date(a, 0)) - Date.UTC(a, 0)) !== ((new Date(a, 6)) - Date.UTC(a, 6)));
    case 'L':
        a = date.getFullYear();
        return (!(a & 3) && (a % 1e2 || !(a % 4e2))) ? 1 : 0;
    case 'm':
        return date.getMonth() + 1;
    case 's':
        return date.getSeconds();
    case 't':
        return (new Date(date.getFullYear(), date.getMonth() + 1, 0)).getDate();
    case 'U':
        return Math.round(date.getTime() / 1000);
    case 'w':
        return date.getDay();
    case 'W':
        a = new Date(date.getFullYear(), date.getMonth(), date.getDate() - (date.getDay() || 7) + 3);
        return 1 + Math.round((a - (new Date(a.getFullYear(), 0, 4))) / 864e5 / 7);
    case 'y':
        return parseInt((date.getFullYear() + '').slice(2), 10); // This function returns an integer, unlike date()
    case 'Y':
        return date.getFullYear();
    case 'z':
        return Math.floor((date - new Date(date.getFullYear(), 0, 1)) / 864e5);
    case 'Z':
        return -date.getTimezoneOffset() * 60;
    default:
        throw 'Unrecognized date format token';
    }
}

function mktime () {
    // Get UNIX timestamp for a date
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/mktime
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: baris ozdil
    // +      input by: gabriel paderni
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: FGFEmperor
    // +      input by: Yannoo
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: jakes
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Marc Palau
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: 3D-GRAF
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Chris
    // +    revised by: Theriault
    // %        note 1: The return values of the following examples are
    // %        note 1: received only if your system's timezone is UTC.
    // *     example 1: mktime(14, 10, 2, 2, 1, 2008);
    // *     returns 1: 1201875002
    // *     example 2: mktime(0, 0, 0, 0, 1, 2008);
    // *     returns 2: 1196467200
    // *     example 3: make = mktime();
    // *     example 3: td = new Date();
    // *     example 3: real = Math.floor(td.getTime() / 1000);
    // *     example 3: diff = (real - make);
    // *     results 3: diff < 5
    // *     example 4: mktime(0, 0, 0, 13, 1, 1997)
    // *     returns 4: 883612800
    // *     example 5: mktime(0, 0, 0, 1, 1, 1998)
    // *     returns 5: 883612800
    // *     example 6: mktime(0, 0, 0, 1, 1, 98)
    // *     returns 6: 883612800
    // *     example 7: mktime(23, 59, 59, 13, 0, 2010)
    // *     returns 7: 1293839999
    // *     example 8: mktime(0, 0, -1, 1, 1, 1970)
    // *     returns 8: -1
    var d = new Date(),
        r = arguments,
        i = 0,
        e = ['Hours', 'Minutes', 'Seconds', 'Month', 'Date', 'FullYear'];

    for (i = 0; i < e.length; i++) {
        if (typeof r[i] === 'undefined') {
            r[i] = d['get' + e[i]]();
            r[i] += (i === 3); // +1 to fix JS months.
        } else {
            r[i] = parseInt(r[i], 10);
            if (isNaN(r[i])) {
                return false;
            }
        }
    }

    // Map years 0-69 to 2000-2069 and years 70-100 to 1970-2000.
    r[5] += (r[5] >= 0 ? (r[5] <= 69 ? 2e3 : (r[5] <= 100 ? 1900 : 0)) : 0);

    // Set year, month (-1 to fix JS months), and date.
    // !This must come before the call to setHours!
    d.setFullYear(r[5], r[3] - 1, r[4]);

    // Set hours, minutes, and seconds.
    d.setHours(r[0], r[1], r[2]);

    // Divide milliseconds by 1000 to return seconds and drop decimal.
    // Add 1 second if negative or it'll be off from PHP by 1 second.
    return (d.getTime() / 1e3 >> 0) - (d.getTime() < 0);
}

function rand (min, max) {
    // http://kevin.vanzonneveld.net
    // +   original by: Leslie Hoare
    // +   bugfixed by: Onno Marsman
    // %          note 1: See the commented out code below for a version which will work with our experimental (though probably unnecessary) srand() function)
    // *     example 1: rand(1, 1);
    // *     returns 1: 1
    var argc = arguments.length;
    if (argc === 0) {
        min = 0;
        max = 2147483647;
    } else if (argc === 1) {
        throw new Error('Warning: rand() expects exactly 2 parameters, 1 given');
    }
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function time () {
    // Return current UNIX timestamp
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/time
    // +   original by: GeekFG (http://geekfg.blogspot.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: metjay
    // +   improved by: HKM
    // *     example 1: timeStamp = time();
    // *     results 1: timeStamp > 1000000000 && timeStamp < 2000000000
    return Math.floor(new Date().getTime() / 1000);
}

/**
 * Updates the repeated headings in a dynamic table.
 * @param {string} questionId
 * @param {number} rep        - Repetition
 */
function updateHeadings(questionId, rep)
{
    var tab = $('#' + questionId).find('table.question');
    tab.find('.repeat').remove();
    var header = tab.find('thead>tr');
    var trs = tab.find('tr:visible');
    trs.each(function(i, tr)
    {
        // add heading but not for the first and the last rows
        if(i != 0 && i % rep == 0 && i != trs.length-1)
        {
            header.clone().addClass('repeat').addClass('headings').insertAfter(tr);
        }
    });
}

/**
 * Updates the colors in a dynamic table.
 * No use of jQuery in this function due to speed reasons.
 * Get all "#questionId table.question tr:visible" and reset
 * class array1/array2.
 * @param {string} questionId
 */
function updateColors(questionId)
{
    var tab = $('#' + questionId).find('table.question');
    var trs = tab.find('tr:visible');
    trs.each(function(i, tr)
    {
        // fix line colors
        $(tr).removeClass('array1').removeClass('array2').addClass('array' + (1 + i % 2));
    });
}
