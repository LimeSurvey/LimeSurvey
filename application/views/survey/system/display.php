<?php
/**
 * @var string sTemplate : the template to be used
 * @var array aData : the global var for templatereplace
 * @var array aReplacementData  : the array of replacement for templatereplace
 * @var startsurvey boolean start surevy (mean add survey.pstpl page)
 **/
sendCacheHeaders(); // Send the header
doHeader(); // Doctype <html><head> + meta : use global : maybe better to do it here

    $oTemplate = Template::model()->getInstance($sTemplate);
    // Maybe can add language changer here
    echo templatereplace(file_get_contents($oTemplate->viewPath."startpage.pstpl"),$aData,$aReplacementData);
    if(!empty($startsurvey)){
        echo templatereplace(file_get_contents($oTemplate->viewPath."survey.pstpl"),$aData,$aReplacementData);
    }
    echo $content;
    echo templatereplace(file_get_contents($oTemplate->viewPath."endpage.pstpl"),$aData,$aReplacementData);
doFooter();
