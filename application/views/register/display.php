<?php
    echo templatereplace(file_get_contents("{$sTemplate}/startpage.pstpl"),array(),$aData);
    echo templatereplace(file_get_contents("{$sTemplate}/survey.pstpl"),array(),$aData);
    echo $content;
    echo templatereplace(file_get_contents("{$sTemplate}/endpage.pstpl"),array(),$aData);
?>
