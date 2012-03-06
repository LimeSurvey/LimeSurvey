<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
if (!((isset($subaction) && $subaction == 'data'))) {die("Cannot run this script directly");}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ExpressionManager::Current Data</title>
    </head>
    <body>
        <?php
            include_once('../LimeExpressionManager.php');
            echo LimeExpressionManager::ShowLogicFile();
        ?>
    </body>
</html>
