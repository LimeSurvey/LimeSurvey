/* Core JavaScript functions needed by ExpressionManager */

function ExprMgr_pi()
{
    return Math.PI;
}

function ExprMgr_sum()
{
    // takes variable number of arguments
    var result=0;
    for (i=0;i<arguments.length;++i) {
        var arg = arguments[i];
        if (!isNaN(arg)) {
            result += (+arg);
        }
    }
    return result;
}

function ExprMgr_intval(a)
{
    if (isNaN(a)) {
        return NaN;
    }
    return Math.floor(+a);
}

function ExprMgr_is_null(a)
{
    return (a == null);
}

function ExprMgr_is_float(a)
{
    if (isNaN(a))
    {
        return false;
    }
    var num = new Number(a);
    // should this only return true if there is a non-zero decimal part to the number?
    return (Math.floor(num) != num);
}

function ExprMgr_is_int(a)
{
    if (isNaN(a))
    {
        return false;
    }
    var num = new Number(a);
    // should this only return true if there is a non-zero decimal part to the number?
    return (Math.floor(num) == num);
}

function ExprMgr_is_numeric(a)
{
    return !(isNaN(a));
}

function ExprMgr_is_string(a)
{
    return isNaN(a);
}

function ExprMgr_if(a,b,c)
{
    return (!!a) ? b : c;
}

/**
 *  Returns comma separated list of non-null values
 */

function ExprMgr_list()
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
 * Returns concatenates list with first argument as the separator
 */
function ExprMgr_implode()
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

function ExprMgr_strlen(a)
{
    var  str = new String(a);
    return str.length;
}

function ExprMgr_str_replace(needle, replace, haystack)
{
    var str = new String(haystack);
    return str.replace(needle, replace);
}

function ExprMgr_strpos(haystack,needle)
{
    var str = new String(haystack);
    return str.search(needle);
}

function ExprMgr_empty(v)
{
    if (v == "" || v == 0 || v == "0" || v == "false" || v == "NULL" || v == false) {
        return true;
    }
    return false;
}

function ExprMgr_bool(v)
{
    bool = new Boolean(v);
    if (v.valueOf() && v != 'false') {
        return true;    // fix for JavaScript native processing that considers the value "false" to be true
    }
    return false;
}

/**
 * Return the value for data element jsName, treating it as blank if its question is irrelevant on the current page.
 * Also convert the string 'false' to '' to cope with a JavaScript type casting issue
 */
function ExprMgr_value(jsName,relevanceNum)
{
    if (relevanceNum!='') {
        if (document.getElementById(relevanceNum).value!=='1') {
            return '';  // means that the question is not relevant
        }
    }
    value = document.getElementById(jsName).value;
    if (isNaN(value)) {
        if (value==='false') {
            return '';  // so Boolean operations will treat it as false. In JavaScript, Boolean("false") is true since "false" is not a zero-length string
        }
        return value;
    }
    else {
        return +value;  // convert it to numeric return type
    }
}