<?php
/**
 * Put functions that you want visibile within Expression Manager in this file
 *
 * @author Thomas M. White
 */

// Each allowed function is a mapping from local name to external name + number of arguments
// Functions can have -1 (meaning unlimited), or a list of serveral allowable #s of arguments.
$exprmgr_functions = array(
    'if'            => array('exprmgr_if','LEMif','Excel-style if(test,result_if_true,result_if_false)',3),
    'list'          => array('exprmgr_list','LEMlist','Return comma-separated list of values',-1),
    'is_empty'         => array('exprmgr_empty','LEMempty','Determine whether a variable is considered to be empty',1),
    'stddev'        => array('exprmgr_stddev','LEMstddev','Calculate the  Sample Standard  Deviation for the list of numbers',-1),
);

// Extra static variables for unit tests
$exprmgr_extraVars = array(
    'eleven' => array('codeValue'=>11, 'jsName'=>'java_eleven', 'readWrite'=>'Y', 'isOnCurrentPage'=>'N'),
    'twelve' => array('codeValue'=>12, 'jsName'=>'java_twelve', 'readWrite'=>'Y', 'isOnCurrentPage'=>'N'),
);

// Unit tests of any added functions
$exprmgr_extraTests = <<<EOD
11~eleven
144~twelve * twelve
4~if(5 > 7,2,4)
there~if((one > two),'hi','there')
64~if((one < two),pow(2,6),pow(6,2))
1, 2, 3, 4, 5~list(one,two,three,min(four,five,six),max(three,four,five))
11, 12~list(eleven,twelve)
1~is_empty(0)
1~is_empty('')
0~is_empty(1)
1~is_empty(a==b)
0~if('',1,0)
1~if(' ',1,0)
0~!is_empty(a==b)
1~!is_empty(1)
EOD;

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

function exprmgr_implode($args)
{
    if (count($args) <= 1)
    {
        return "";
    }
    $joiner = array_shift($args);
    return implode($joiner,$args);
}

function exprmgr_empty($arg)
{
    return empty($arg);
}

/*
 * Compute the Sample Standard Deviation of a set of numbers
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

?>
