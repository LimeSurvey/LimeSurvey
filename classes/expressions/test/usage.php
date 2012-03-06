<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
if (!((isset($subaction) && $subaction == 'usage'))) {die("Cannot run this script directly");}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ExpressionManager::Running Translation Log</title>
    </head>
    <body>
        <table border='1'>
            <?php
                include_once('../LimeExpressionManager.php');
                echo LimeExpressionManager::ShowPageTailorings();
            ?>
        </table>
    </body>
</html>
