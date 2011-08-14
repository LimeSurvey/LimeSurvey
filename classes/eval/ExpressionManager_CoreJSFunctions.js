/* Core JavaScript functions needed by ExpressionManager */

function LEMpi()
{
    return Math.PI;
}

function LEMsum()
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
    if (isNaN(a))
    {
        return false;
    }
    var num = new Number(a);
    // should this only return true if there is a non-zero decimal part to the number?
    return (Math.floor(num) != num);
}

function LEMis_int(a)
{
    if (isNaN(a))
    {
        return false;
    }
    var num = new Number(a);
    // should this only return true if there is a non-zero decimal part to the number?
    return (Math.floor(num) == num);
}

function LEMis_numeric(a)
{
    return !(isNaN(a));
}

function LEMis_string(a)
{
    return isNaN(a);
}

function LEMif(a,b,c)
{
    return (!!a) ? b : c;
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

function LEMstrlen(a)
{
    var  str = new String(a);
    return str.length;
}

function LEMstr_replace(needle, replace, haystack)
{
    var str = new String(haystack);
    return str.replace(needle, replace);
}

function LEMstrpos(haystack,needle)
{
    var str = new String(haystack);
    return str.search(needle);
}

function LEMempty(v)
{
    if (v == "" || v == 0 || v == "0" || v == "false" || v == "NULL" || v == false) {
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
 * Return the value for data element jsName, treating it as blank if its question is irrelevant on the current page.
 * Also convert the string 'false' to '' to cope with a JavaScript type casting issue
 */
function LEMvalue(jsName,relevanceNum)
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

/**
 * Return the value for data element jsName, treating it as blank if its question is irrelevant on the current page.
 * Also convert the string 'false' to '' to cope with a JavaScript type casting issue
 */
function LEMval(alias)
{
    jsName = LEMalias2varName[alias].jsName;
    attr = LEMvarNameAttr[jsName];
    if (attr.qid!='') {
        if (document.getElementById('relevance' + attr.qid).value!=='1'){
            return '';
        }
    }
    value = document.getElementById(attr.jsName).value;
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

/* 
 * Remove HTML and PHP tags from string
 */
function LEMstrip_tags(htmlString)
{
   var tmp = document.createElement("DIV");
   tmp.innerHTML = htmlString;
   return tmp.textContent||tmp.innerText;
}

function LEMround()
{
    if (arguments.length==1) {
        return Math.round(arguments[0]);
    }
    if (arguments.length==2) {
        num = new  Number(arguments[0]);
        return num.toFixed(arguments[1]);
    }
    return 0;
}

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
        jsName = LEMalias2varName[arg].jsName;
        attr = LEMvarNameAttr[jsName];
        if (attr.qid!='') {
            if (document.getElementById('relevance' + attr.qid).value!=='1'){
                return true;    // means that the question is not relevant
            }
        }
    }
    return false;
}
