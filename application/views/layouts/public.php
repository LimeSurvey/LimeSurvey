<?php
/**
 * @var string sTemplate : the template to be used
 * @var array aData : the global var for templatereplace
 * @var array aReplacementData  : the array of replacement for templatereplace
 * @var startsurvey boolean start surevy (mean add survey.pstpl page)
 **/
/* send the header : @see common_helper sendCacheHeaders */
//~ sendCacheHeaders(); // Send the header
if (!headers_sent())
{
    if (Yii::app()->getConfig('x_frame_options','allow')=='sameorigin')
    {
        header('X-Frame-Options: SAMEORIGIN');
    }
    header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');  // this line lets IE7 run LimeSurvey in an iframe
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header('Content-Type: text/html; charset=utf-8');
}
?><!DOCTYPE html>
<?php
    Yii::app()->loadHelper('surveytranslator');
    $lang=App()->getLanguage();
    $langDir= (getLanguageRTL(App()->getLanguage())) ? "rtl": "ltr";
?><html lang="<?php echo $lang ?>" class="no-js <?php echo $lang ?> dir-<?php  echo $langDir  ?>" <?php echo ($langDir=='rtl' ? 'dir="rtl"':''); ?>>
<head>
<?php
    $oTemplate = Template::model()->getInstance($this->sTemplate);
    if($oTemplate->cssFramework == 'bootstrap')
    {
        Yii::app()->getClientScript()->registerMetaTag('width=device-width, initial-scale=1.0', 'viewport');
        App()->bootstrap->registerAllScripts();
    }
    Yii::app()->clientScript->registerPackage('survey-template');
    // Maybe can add language changer here
    /* Add head by template + start body (if template start body here ....) */
    echo templatereplace(file_get_contents($oTemplate->pstplPath."startpage.pstpl"),$this->aReplacementData,$this->aGlobalData);
    if(!empty($this->bStartSurvey)){
        echo templatereplace(file_get_contents($oTemplate->pstplPath."survey.pstpl"),$this->aReplacementData,$this->aGlobalData);
    }
    echo $content;
    echo templatereplace(file_get_contents($oTemplate->pstplPath."endpage.pstpl"),$this->aReplacementData,$this->aGlobalData);
?>
</body>
</html>
