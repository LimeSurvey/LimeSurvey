<?php

/** This file is part of KCFinder project
  *
  *      @desc Join all JavaScript files in current directory
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

chdir(".."); // For compatibality
chdir("..");
require "lib/helper_httpCache.php";
require "lib/helper_dir.php";
$files = dir::content("js/browser", array(
    'types' => "file",
    'pattern' => '/^.*\.js$/'
));

foreach ($files as $file) {
    $fmtime = filemtime($file);
    if (!isset($mtime) || ($fmtime > $mtime))
        $mtime = $fmtime;
}

httpCache::checkMTime($mtime);
header("Content-Type: text/javascript");
foreach ($files as $file)
    require $file;

?>