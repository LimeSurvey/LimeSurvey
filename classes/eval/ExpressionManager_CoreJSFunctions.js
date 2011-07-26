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

function ExprMgr_list()
{
    // takes variable number of arguments
    var result="";
    for (i=0;i<arguments.length;++i) {
        var arg = arguments[i];
        if (i > 0) {
            result += ", " + arg;
        }
        else {
            result += arg;
        }
    }
    return result;
}

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