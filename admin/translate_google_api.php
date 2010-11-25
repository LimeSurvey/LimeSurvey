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

    // Replace {TEXT} with <TEXT>. Text within <> act as a placeholder and are
    // not translated by Google Translate
    $sToNewconvert  = preg_replace("/\{(\w+)\}/", "<$1>",$sToconvert);
    if ($sToNewconvert == $sToconvert)
        $bDoNotConvertBack = true;
    $sToconvert = $sToNewconvert;
    $sConverted  = $objGt->$sProcedure($sToconvert);
    $sConverted  = str_replace("<br>","\r\n",$sConverted);
    if (!$bDoNotConvertBack)
        $sConverted  = preg_replace("/\<(\w+)\>/", '{$1}',$sConverted);
    $sConverted  = html_entity_decode(stripcslashes($sConverted));

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

$ajaxoutput = json_encode($aOutput). "\n";
