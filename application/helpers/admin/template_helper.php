<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id: activate_functions.php 10193 2011-06-05 12:20:37Z c_schmitz $
 *	Files Purpose: holds functions to activate a survey and precheck the consistency of the survey
 */


function doreplacement($file,$data) { //Produce sample page from template file
   /**
   $output=array();
   foreach(file($file) as $op) {
       $output[]=templatereplace($op);
   }
   return $output;
   */
   return (array)templatereplace($file,array(),$data);
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

function textarea_encode($html_code)
{
   $from = array('<', '>');
   $to = array('&lt;', '&gt;');
   $html_code = str_replace($from, $to, $html_code);
   return $html_code;
}

                               //Load this editfile
function filetext($templatename,$templatefile,$templates) {
    $sFileName=$templates[$templatename].'/'.$templatefile;
    if (file_exists($sFileName))
    {
        return file_get_contents($sFileName);
}
    else
    {
        return '';
    }
}

function makegraph($currentstep, $total)
{
    $CI = &get_instance();
    $CI->load->helper('surveytranslator');
	$clang = $CI->limesurvey_lang;
    $size = intval(($currentstep-1)/$total*100);

    $graph = '<script type="text/javascript">
                $(function() {
                $("#progressbar").progressbar({
                value: '.$size.'
                });
                });';
    if (getLanguageRTL($clang->langcode))
    {
       $graph.='$(document).ready(function() {
                $("div.ui-progressbar-value").removeClass("ui-corner-left");
                $("div.ui-progressbar-value").addClass("ui-corner-right");
                });';
    }
    $graph.='</script>

            <div id="progress-wrapper">
            <span class="hide">'.sprintf($clang->gT('You have completed %s%% of this survey'),$size).'</span>
            <div id="progress-pre">';
    if (getLanguageRTL($clang->langcode))
    {
       $graph.='100%';
    }
    else
    {
       $graph.='0%';
    }

    $graph.='</div>
            <div id="progressbar"></div>
            <div id="progress-post">';
    if (getLanguageRTL($clang->langcode))
    {
       $graph.='0%';
    }
    else
    {
       $graph.='100%';
    }
    $graph.='</div>
            </div>';

    if ($size == 0) // Progress bar looks dumb if 0
    {
        $graph.='
        <script type="text/javascript">
        $(document).ready(function() {
        $("div.ui-progressbar-value").hide();
        });
        </script>';
    }

    return $graph;
}

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

function makeoptions($array, $value, $text, $selectedvalue) {
    $return='';
       foreach ($array as $ar) {
           $return .= "<option value='".html_escape($ar[$value])."'";
           if (html_escape($ar[$value]) == $selectedvalue) {
               $return .= " selected='selected'";
           }
    $return .= '>'.$ar[$text]."</option>\n";
    }
    return $return;
}

function templateoptions($optionarray, $selectedvalue) {
    $return='';
    foreach ($optionarray as $arkey=>$arvalue) {
        $return .= "<option value='".html_escape($arkey)."'";
        if ($arkey == $selectedvalue) {
            $return .= " selected='selected'";
        }
        $return .= '>'.html_escape($arkey)."</option>\n";
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
    $CI = &get_instance();

    if (isStandardTemplate($templatename) && $CI->config->item("standard_templates_readonly")==true)
   {
       return false;
   }
   else
   {
       return true;
   }
}
