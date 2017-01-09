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
function doreplacement($file,$data, $oTemplate='')
{
    //Produce sample page from template file
    $aReplacements=isset($data['aReplacements']) ? $data['aReplacements'] : array();
    return (array)templatereplace(file_get_contents($file),$aReplacements,$data, 'Unspecified', false, NULL, array(), false, $oTemplate);
}



function getListOfFiles($wh){
    //Returns an array containing all files in a directory
    if ($handle = opendir($wh)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && !is_dir($file)) {
                if(!isset($files) || !$files) $files="$file";
                else $files="$file\n$files";
            }
        }
        closedir($handle);
    }
    $arr=explode("\n",$files);
    sort($arr);
    return $arr;
}

/**
* Load this editfile
*
* @param string $templatename
* @param string $templatefile
* @param array $templates
* @return string
*/
function filetext($templatename,$templatefile,$templates) {
    $sFileName = gettemplatefilename($templates[$templatename],$templatefile);
    if (file_exists($sFileName))
    {
        return file_get_contents($sFileName);
    }
    else
    {
    }
}

/**
 * @param string $target
 */
function mkdir_p($target){
    //creates a new directory
    //Returns 1 for success
    //        2 for "directory/file by that name exists
    //        0 for other errors
    if(file_exists($target) || is_dir($target))
        return 2;
    if(mkdir($target,0777)){
        return 1;
    }
    if(mkdir_p(substr($target, 0, (strrpos($target, '/')))) == 1){
        if(mkdir_p($target) == 1)
            return 1;
        else
            return 0;
    } else {
        return 0;
    }
}

function templateoptions($optionarray, $selectedvalue) {
    $return='';
    foreach ($optionarray as $arkey=>$arvalue) {
        $return .= "<option value='".HTMLEscape($arkey)."'";
        if ($arkey == $selectedvalue)
        {
            $return .= " selected='selected'";
        }
        $return .= '>'.HTMLEscape($arkey)."</option>\n";
    }
    return $return;
}

function multiarray_search($arrayVet, $campo, $valor){
    while(isset($arrayVet[key($arrayVet)])){
        if($arrayVet[key($arrayVet)][$campo] == $valor){
            return key($arrayVet);
        }
        next($arrayVet);
    }
    return false;
}


function recursive_in_array($needle, $haystack) {
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
    if (isStandardTemplate($templatename) && Yii::app()->getConfig("standard_templates_readonly")==true)
    {
        return false;
    }
    else
    {
        return true;
    }
}

/**
* This is a PCLZip callback function that ensures only files are extracted that have a valid extension
*
* @param mixed $p_event
* @param mixed $p_header
* @return int Return 1 for yes (file can be extracted), 0 for no
*/
function templateExtractFilter($p_event, &$p_header)
{
    $aAllowExtensions=explode(',',Yii::app()->getConfig('allowedtemplateuploads'));
    $aAllowExtensions[]='pstpl';
    $info = pathinfo($p_header['filename']);
    // Deny files with multiple extensions in general
    if (substr_count($info['basename'],'.')!=1) return 0;

    if ($p_header['folder'] || !isset($info['extension']) || in_array($info['extension'],$aAllowExtensions)) {
        return 1;
    }
    else {
        return 0;
    }
}

/**
* Determine the storage path for a file
* TODO: remove all that logic.
*
* @param string $template
* @param string $templatefile
*/
function gettemplatefilename($template, $templatefile) {
    switch (pathinfo($templatefile, PATHINFO_EXTENSION))
    {
        case 'pstpl':
            // Default 2.5 templates
            if (file_exists($template.'/views/'.$templatefile))
            {
                return $template.'/views/'.$templatefile;
            }
            // Default 2.06 templates
            elseif(file_exists($template.'/'.$templatefile))
            {
                return $template.'/'.$templatefile;
            }
            // Something else
            else
            {
                $oEditedTemplate = Template::model()->getTemplateConfiguration($template);
                return $template.'/'.$oEditedTemplate->viewPath.'/'.$templatefile;
            }
            break;
        case 'css':
            return $template.'/'.$templatefile;
            break;
        case 'js':
            return $template.'/'.$templatefile;
            break;
        default:
            return $template.'/'.$templatefile;
            break;
    }
}
