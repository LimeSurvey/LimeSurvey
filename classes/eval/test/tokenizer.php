<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
if (!((isset($subaction) && $subaction == 'tokenizer'))) {die("Cannot run this script directly");}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ExpressionManager:  Unit Test Tokenizer</title>
    </head>
    <body>
        <?php
//            include_once('../ExpressionManager.php');
            ExpressionManager::UnitTestTokenizer();
        ?>
    </body>
</html>
