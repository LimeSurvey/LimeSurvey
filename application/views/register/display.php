<?php
    global $oTemplate;
    echo templatereplace(file_get_contents($oTemplate->viewPath."startpage.pstpl"),array(),$aData);
    echo templatereplace(file_get_contents($oTemplate->viewPath."survey.pstpl"),array(),$aData);
    echo $content;
    echo templatereplace(file_get_contents($oTemplate->viewPath."endpage.pstpl"),array(),$aData);
?>
