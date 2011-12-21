<?php
    echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
    echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
?>
<div id="wrapper"><p id="optoutmessage">
<?php
    echo $html;
?>
</p></div>
<?php
    echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
?>