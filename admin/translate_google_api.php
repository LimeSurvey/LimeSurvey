<?php
/*
 * translate_google_api.php
 * Creates a JSON interface for the auto-translate feature
 */
include_once("login_check.php");
header('Content-type: application/json');
include_once('classes/gtranslate/GTranslate.php');
$sBaselang   = $_REQUEST['baselang'];
$sTolang     = $_REQUEST['tolang'];
$sToconvert  = $_REQUEST['text'];

$aSearch     = array('zh-Hans','zh-Hant-HK','zh-Hant-TW',
                     'nl-informal','de-informal','it-formal','pt-BR','es-MX','nb','nn');
$aReplace    = array('zh-CN','zh-TW','zh-TW','nl','de','it','pt','es','no','no');

$sTolang  = str_replace($aSearch,$aReplace,$sTolang);

try {

    $objGt         = new Gtranslate;
    // Gtranslate requires you to run function named XXLANG_to_XXLANG
    $sProcedure       = $sBaselang."_to_".$sTolang;

    $parts = LimeExpressionManager::SplitStringOnExpressions($sToconvert);

    $sparts = array();
    foreach($parts as $part)
    {
        if ($part[2]=='EXPRESSION')
        {
            $sparts[] = $part[0];
        }
        else
        {
            $convertedPart = $objGt->$sProcedure($part[0]);
            $convertedPart  = str_replace("<br>","\r\n",$convertedPart);
            $convertedPart  = html_entity_decode(stripcslashes($convertedPart));
            $sparts[] = $convertedPart;
        }
    }
    $sConverted = implode(' ', $sparts);

    $aOutput = array(
        'error'     =>  false,
        'baselang'  =>  $sBaselang,
        'tolang'    =>  $sTolang,
        'converted' =>  $sConverted
    );

}   catch (GTranslateException $ge){

    // Get the error message and build the ouput array
    $sError  = $ge->getMessage();
    $aOutput = array(
        'error'     =>  true,
        'baselang'  =>  $sBaselang,
        'tolang'    =>  $sTolang,
        'error'     =>  $sError
    );

}

$ajaxoutput = ls_json_encode($aOutput). "\n";
