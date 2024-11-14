<?php

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
*/


/**
 * @param string $file
 */
function doreplacement($file, $data, $oTemplate = '')
{
    //Produce sample page from template file
    $aReplacements = $data['aReplacements'] ?? array();
    return (array) templatereplace(file_get_contents($file), $aReplacements, $data, 'Unspecified', false, null, array(), false, $oTemplate);
}



function getListOfFiles($wh)
{
    //Returns an array containing all files in a directory
    if ($handle = opendir($wh)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && !is_dir($file)) {
                if (!isset($files) || !$files) {
                    $files = "$file";
                } else {
                    $files = "$file\n$files";
                }
            }
        }
        closedir($handle);
    }
    $arr = explode("\n", (string) $files);
    sort($arr);
    return $arr;
}


/**
 * @param string $target
 */
function mkdir_p($target)
{
    //creates a new directory
    //Returns 1 for success
    //        2 for "directory/file by that name exists
    //        0 for other errors
    if (file_exists($target) || is_dir($target)) {
            return 2;
    }
    if (mkdir($target, 0777)) {
        return 1;
    }
    if (mkdir_p(substr($target, 0, (strrpos($target, '/')))) == 1) {
        if (mkdir_p($target) == 1) {
                    return 1;
        } else {
                    return 0;
        }
    } else {
        return 0;
    }
}

function themeoptions($optionarray, $selectedvalue)
{
    $return = '';
    foreach ($optionarray as $arkey => $arvalue) {
        $return .= "<option value='" . HTMLEscape($arkey) . "'";
        if ($arkey == $selectedvalue) {
            $return .= " selected='selected'";
        }
        $return .= '>' . HTMLEscape($arkey) . "</option>\n";
    }
    return $return;
}

function multiarray_search($arrayVet, $campo, $valor)
{
    while (isset($arrayVet[key($arrayVet)])) {
        if ($arrayVet[key($arrayVet)][$campo] == $valor) {
            return key($arrayVet);
        }
        next($arrayVet);
    }
    return false;
}


function recursive_in_array($needle, $haystack)
{
    foreach ($haystack as $stalk) {
        if ($needle == $stalk || (is_array($stalk) && recursive_in_array($needle, $stalk))) {
            return true;
        }
    }
    return false;
}

/**
* This function checks if a certain template may be by modified, copied, deleted according to the settings in config.php
* @param mixed $templatename
*/
function is_template_editable($templatename)
{
    if (Template::isStandardTemplate($templatename) && Yii::app()->getConfig("standard_themes_readonly") == true) {
        return false;
    } else {
        return true;
    }
}

/**
* This is a callback function that ensures only files are extracted that have a valid extension
*
* @param mixed $p_header
* @return int Return 1 for yes (file can be extracted), 0 for no
*/
function templateExtractFilter(&$p_header)
{
    $aAllowExtensions = explode(',', Yii::app()->getConfig('allowedthemeuploads') . ',' . Yii::app()->getConfig('allowedthemeimageformats'));
    $aAllowExtensions[] = 'twig';
    $info = pathinfo((string) $p_header['filename']);

    if ($p_header['folder'] || !isset($info['extension']) || in_array(strtolower($info['extension']), $aAllowExtensions)) {
        return 1;
    } else {
        return 0;
    }
}
