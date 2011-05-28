<?php

/** This file is part of KCFinder project
  *
  *      @desc Base CSS definitions
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

require "core/autoload.php";
$mtime = @filemtime(__FILE__);
if ($mtime) httpCache::checkMTime($mtime);
$browser = new browser();
$config = $browser->config;
ob_start();

?>
html, body {
    overflow: hidden;
}

body, form, th, td {
    margin: 0;
    padding: 0;
}

a {
    cursor:pointer;
}

* {
    font-family: Tahoma, Verdana, Arial, sans-serif;
    font-size: 11px;
}

table {
    border-collapse: collapse;
}

#all {
    vvisibility: hidden;
}

#left {
    float: left;
    display: block;
    width: 25%;
}

#right {
    float: left;
    display: block;
    width: 75%;
}

#settings {
    display: none;
    padding: 0;
    float: left;
    width: 100%;
}

#settings > div {
    float: left;
}

#folders {
    padding: 5px;
    overflow: auto;
}

#toolbar {
    padding: 5px;
}

#files {
    padding: 5px;
    overflow: auto;
}

#status {
    padding: 5px;
    float: left;
    overflow: hidden;
}

#fileinfo {
    float: left;
}

#clipboard div {
    width: 16px;
    height: 16px;
}

.folders {
    margin-left: 16px;
}

div.file {
    overflow-x: hidden;
    width: <?php echo $config['thumbWidth'] ?>px;
    float: left;
    text-align: center;
    cursor: default;
    white-space: nowrap;
}

div.file .thumb {
    width: <?php echo $config['thumbWidth'] ?>px;
    height: <?php echo $config['thumbHeight'] ?>px;
    background: no-repeat center center;
}

#files table {
    width: 100%;
}

tr.file {
    cursor: default;
}

tr.file > td {
    white-space: nowrap;
}

tr.file > td.name {
    background-repeat: no-repeat;
    background-position: left center;
    padding-left: 20px;
    width: 100%;
}

tr.file > td.time,
tr.file > td.size {
    text-align: right;
}

#toolbar {
    cursor: default;
    white-space: nowrap;
}

#toolbar a {
    padding-left: 20px;
    text-decoration: none;
    background: no-repeat left center;
}

#toolbar a:hover, a[href="#upload"].uploadHover {
    color: #000;
}

#upload {
    position: absolute;
    overflow: hidden;
    opacity: 0;
    filter: alpha(opacity:0);
}

#upload input {
    cursor: pointer;
}

#uploadResponse {
    display: none;
}

span.brace {
    padding-left: 11px;
    cursor: default;
}

span.brace.opened, span.brace.closed {
    cursor: pointer;
}

#shadow {
    position: absolute;
    top: 0;
    left: 0;
    display: none;
    background: #fff;
    z-index: 100;
    opacity: 0.5;
    filter: alpha(opacity:50);
}

#dialog, #clipboard {
    position: absolute;
    display: none;
    z-index: 101;
    cursor: default;
}

#clipboard {
    z-index: 99;
}

#loading {
    display: none;
    float: right;
}

.menu {
    background: #888;
    white-space: nowrap;
}

.menu a {
    display: block;
}

.menu .list {
    max-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    white-space: nowrap;
}

.file .access, .file .hasThumb {
    display: none;
}

#dialog img {
    cursor: pointer;
}

#resizer {
    position: absolute;
    z-index: 98;
    top: 0;
    background: #000;
    opacity: 0;
    filter: alpha(opacity:0);
}
<?php

header("Content-Type: text/css");
echo text::compressCSS(ob_get_clean());