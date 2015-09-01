<?php
    echo \ls\helpers\Replacements::templatereplace(file_get_contents("{$sTemplate}/startpage.pstpl"), array(), $aData);
    echo \ls\helpers\Replacements::templatereplace(file_get_contents("{$sTemplate}/survey.pstpl"), array(), $aData);
    echo $content;
    echo \ls\helpers\Replacements::templatereplace(file_get_contents("{$sTemplate}/endpage.pstpl"), array(), $aData);
?>
