<?php
/**
 * Put functions that you want visibile within Expression Manager in this file
 *
 * @author Thomas M. White
 */

// Each allowed function is a mapping from local name to external name + number of arguments
// Functions can have -1 (meaning unlimited), or a list of serveral allowable #s of arguments.
$exprmgr_functions = array(
    'if'            => array('exprmgr_if','NA','Excel-style if(test,result_if_true,result_if_false)',3),
    'list'          => array('exprmgr_list','NA','Return comma-separated list of values',-1),
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
    return implode(", ",$args);
}



?>
