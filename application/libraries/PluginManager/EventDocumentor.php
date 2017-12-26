<?php 
/**
 * This file will scan all files in the project and output an array with event names.
 */
$basePath = dirname(dirname(dirname(__FILE__)));

$i = new RecursiveDirectoryIterator($basePath);
$i2 = new RecursiveIteratorIterator($i);
$events = array();


foreach ($i2 as $file) {
    /* @var $file SplFileInfo */
    
    
    if (substr($file->getFileName(), -3, 3) == 'php') {
        scanFile($file->getPathname());
    }
}

$events = array_unique($events);
sort($events);
print_r($events);

function scanFile($fileName)
{
    global $events;
    $contents = file_get_contents($fileName);
    
    $regex = '/(.*)new[[:space:]]+PluginEvent[[:space:]]*\([[:space:]]*[\'"]+(.*)[\'"]+/';
    
    $count = preg_match_all($regex, $contents, $matches);
    if ($count > 0) {
        $events = array_merge($events, $matches[2]);
    }
}
