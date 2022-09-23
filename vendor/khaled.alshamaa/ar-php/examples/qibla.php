<?php
error_reporting(E_STRICT);

if (isset($_GET['d'])) { 
    $degree = $_GET['d']; 
} else { 
    $degree = 0; 
}

header("Content-type: image/svg+xml");

$str = file_get_contents('./images/compass.svg');
$arrow = '<polyline points="200,272,216,300,232,272,216,100"  transform="rotate('.$degree.',216,272)" style="fill:red"/>';
$str = str_replace('</svg>', $arrow.'</svg>', $str);

echo $str; 
?>