<?php

/** This file is part of KCFinder project
  *
  *      @desc Load language labels in JavaScript
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

require "core/autoload.php";

if (function_exists('set_magic_quotes_runtime'))
    @set_magic_quotes_runtime(false);

$input = new input();
if (!isset($input->get['lng']) || ($input->get['lng'] == 'en')) die;
$file = "lang/" . $input->get['lng'] . ".php";
$files = glob("lang/*.php");
if (!in_array($file, $files)) die;
$mtime = @filemtime($file);
if ($mtime) httpCache::checkMTime($mtime);
require $file;
header("Content-Type: text/javascript; charset={$lang['_charset']}");
foreach ($lang as $english => $native)
    if (substr($english, 0, 1) != "_")
        echo "browser.labels['" . text::jsValue($english) . "']=\"" . text::jsValue($native) . "\";";

?>
