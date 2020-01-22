<?php

/** This file is part of KCFinder project
  *
  *      @desc This file is included first, before each other
  *   @package KCFinder
  *   @version 3.12
  *    @author Pavel Tzonkov <sunhater@sunhater.com>
  * @copyright 2010-2014 KCFinder Project
  *   @license http://opensource.org/licenses/GPL-3.0 GPLv3
  *   @license http://opensource.org/licenses/LGPL-3.0 LGPLv3
  *      @link http://kcfinder.sunhater.com
  *
  * This file is the place you can put any code (at the end of the file),
  * which will be executed before any other. Suitable for:
  *     1. Set PHP ini settings using ini_set()
  *     2. Custom session save handler with session_set_save_handler()
  *     3. Any custom integration code. If you use any global variables
  *        here, they can be accessed in conf/config.php via $GLOBALS
  *        array. It's recommended to use constants instead.
  */


// PHP VERSION CHECK
if (!preg_match('/^(\d+\.\d+)/', PHP_VERSION, $ver) || ($ver[1] < 5.3))
    die("You are using PHP " . PHP_VERSION . " when KCFinder require at least version 5.3.0! Some systems has an option to change the active PHP version. Please refer to your hosting provider or upgrade your PHP distribution.");


// SAFE MODE CHECK
if (ini_get("safe_mode"))
    die("The \"safe_mode\" PHP ini setting is turned on! You cannot run KCFinder in safe mode.");


// CMS INTEGRATION
// Possible files -> drupal, BolmerCMS
if(isset($_GET['cms']) && (basename($cmsFile) == $cmsFile) && preg_match("/drupal|BolmerCMS/", $_GET['cms'])){
    $cmsFile = basename($_GET['cms']);
    if (is_file("integration/{$cmsFile}.php") )
        require "integration/{$cmsFile}.php";
}


// REGISTER AUTOLOAD FUNCTION
require "core/autoload.php";


// json_encode() IMPLEMENTATION IF JSON EXTENSION IS MISSING
if (!function_exists("json_encode")) {

    function json_encode($data) {

        if (is_array($data)) {
            $ret = array();

            // OBJECT
            if (array_keys($data) !== range(0, count($data) - 1)) {
                foreach ($data as $key => $val)
                    $ret[] = json_encode((string) $key) . ':' . json_encode($val);
                return "{" . implode(",", $ret) . "}";

            // ARRAY
            } else {
                foreach ($data as $val)
                    $ret[] = json_encode($val);
                return "[" . implode(",", $ret) . "]";
            }

        // BOOLEAN OR NULL
        } elseif (is_bool($data) || ($data === null))
            return ($data === null)
                ? "null"
                : ($data ? "true" : "false");

        // FLOAT
        elseif (is_float($data))
            return rtrim(rtrim(number_format($data, 14, ".", ""), "0"), ".");

        // INTEGER
        elseif (is_int($data))
            return $data;

        // STRING
        return '"' .
            str_replace('/', "\\/",
            str_replace("\t", "\\t",
            str_replace("\r", "\\r",
            str_replace("\n", "\\n",
            str_replace('"', "\\\"",
            str_replace("\\", "\\\\",
        $data)))))) . '"';
    }
}


function checkLSSession()
{
    //relative path calculated from the path where kcfinder is running
    $sLimesurveyFolder = realpath( dirname(__FILE__) . "/../../../application");

    // code adapted from /index.php
    if (!defined('APPPATH'))
    {
        define('APPPATH', $sLimesurveyFolder.'/');
    }

    // define BASEPATH in order to access LS config.php
    if (!defined('BASEPATH'))
    {
        define("BASEPATH", realpath($sLimesurveyFolder . "/../framework") . "/");
    }
    require_once BASEPATH . 'yii.php';
    require_once APPPATH . 'core/LSYii_Application.php';
    $config = require_once(APPPATH . 'config/internal.php');
    Yii::$enableIncludePath = false;

    // chdir is required because config['rootdir'] is defined with getcwd()
    $currentDir = getcwd();
    chdir(APPPATH . "..");
    Yii::createApplication('LSYii_Application', $config);
    Yii::app()->session->open();
    chdir($currentDir);
}

checkLSSession();
?>
