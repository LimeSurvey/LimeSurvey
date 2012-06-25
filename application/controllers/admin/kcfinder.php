<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * 	$Id$
 */

class kcfinder extends Survey_Common_Action
{

    function index($load = false)
    {
        Yii::app()->session['KCFINDER'] = array();

        $sAllowedExtensions = implode(' ', array_map('trim', explode(',', Yii::app()->getConfig('allowedresourcesuploads'))));
        $_SESSION['KCFINDER']['types'] = array(
            'files' => $sAllowedExtensions,
            'flash' => $sAllowedExtensions,
            'images' => $sAllowedExtensions
        );

        if (Yii::app()->getConfig('demoMode') === false &&
                isset(Yii::app()->session['loginID']) &&
                isset(Yii::app()->session['FileManagerContext']))
        {
            // disable upload at survey creation time
            // because we don't know the sid yet
            if (preg_match('/^(create|edit):(question|group|answer)/', Yii::app()->session['FileManagerContext']) != 0 ||
                    preg_match('/^edit:survey/', Yii::app()->session['FileManagerContext']) != 0 ||
                    preg_match('/^edit:assessments/', Yii::app()->session['FileManagerContext']) != 0 ||
                    preg_match('/^edit:emailsettings/', Yii::app()->session['FileManagerContext']) != 0)
            {
                $contextarray = explode(':', Yii::app()->session['FileManagerContext'], 3);
                $surveyid = $contextarray[2];

                if (hasSurveyPermission($surveyid, 'surveycontent', 'update'))
                {
                    $_SESSION['KCFINDER']['disabled'] = false;
                    if (preg_match('/^edit:emailsettings/',$_SESSION['FileManagerContext']) != 0)
                    {
                        $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->getRequest()->getHostInfo($schema).Yii::app()->getConfig('uploadurl')."/surveys/{$surveyid}/";
                    }
                    else
                    {
                        $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->getConfig('uploadurl')."/surveys/{$surveyid}/";
                    }
                    $_SESSION['KCFINDER']['uploadDir'] = Yii::app()->getConfig('uploaddir') .DIRECTORY_SEPARATOR.'surveys'.DIRECTORY_SEPARATOR.$surveyid.DIRECTORY_SEPARATOR;
                }
            }
            elseif (preg_match('/^edit:label/', Yii::app()->session['FileManagerContext']) != 0)
            {
                $contextarray = explode(':', Yii::app()->session['FileManagerContext'], 3);
                $labelid = $contextarray[2];
                // check if the user has label management right and labelid defined
                if (Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1 && isset($labelid) && $labelid != '')
                {
                    $_SESSION['KCFINDER']['disabled'] = false;
                    $_SESSION['KCFINDER']['uploadURL'] = Yii::app()->getConfig('uploadurl')."/labels/{$labelid}/";
                    $_SESSION['KCFINDER']['uploadDir'] = Yii::app()->getConfig('uploaddir') .DIRECTORY_SEPARATOR.'labels'.DIRECTORY_SEPARATOR.$labelid.DIRECTORY_SEPARATOR;
                }
            }
        }

        Yii::registerAutoloader(array($this, 'kcfinder_autoload'));
        if (!empty($load) && file_exists(Yii::app()->getConfig('generalscripts')."admin/kcfinder/" . $load . EXT))
        {
            chdir(Yii::app()->getConfig('generalscripts')."admin/kcfinder/");
            require_once(Yii::app()->getConfig('generalscripts')."admin/kcfinder/" . $load . EXT);
        }
    }

    function kcfinder_autoload($class)
    {
        if ($class == "uploader")
            require Yii::app()->getConfig('generalscripts')."kcfinder/core/uploader.php";
        elseif ($class == "browser")
            require Yii::app()->getConfig('generalscripts')."admin/kcfinder/core/browser.php";
        elseif (file_exists(Yii::app()->getConfig('generalscripts')."admin/kcfinder/core/types/$class.php"))
            require Yii::app()->getConfig('generalscripts')."admin/kcfinder/core/types/$class.php";
        elseif (file_exists(Yii::app()->getConfig('generalscripts')."admin/kcfinder/lib/class_$class.php"))
            require Yii::app()->getConfig('generalscripts')."admin/kcfinder/lib/class_$class.php";
        elseif (file_exists(Yii::app()->getConfig('generalscripts')."admin/kcfinder/lib/helper_$class.php"))
            require Yii::app()->getConfig('generalscripts')."admin/kcfinder/lib/helper_$class.php";
    }

    function css()
    {
        $this->index();

        $mtime = @filemtime(__FILE__);
        if ($mtime)
            httpCache::checkMTime($mtime);
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
        visibility: hidden;
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
    }

    function js_localise()
    {
        $this->index();

        if (function_exists('set_magic_quotes_runtime'))
            @set_magic_quotes_runtime(false);

        $input = new input();
        if (!isset($input->get['lng']) || ($input->get['lng'] == 'en'))
            die;
        $file = Yii::app()->getConfig('generalscripts')."admin/kcfinder/lang/" . $input->get['lng'] . ".php";
        $files = glob(Yii::app()->getConfig('generalscripts')."admin/kcfinder/lang/*.php");
        if (!in_array($file, $files))
            die;
        $mtime = @filemtime($file);
        if ($mtime)
            httpCache::checkMTime($mtime);
        require $file;
        header("Content-Type: text/javascript; charset={$lang['_charset']}");
        foreach ($lang as $english => $native)
            if (substr($english, 0, 1) != "_")
                echo "browser.labels['" . text::jsValue($english) . "']=\"" . text::jsValue($native) . "\";";
    }

}