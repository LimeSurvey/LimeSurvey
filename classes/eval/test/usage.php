<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<<<<<<< HEAD
<?php
if (!((isset($subaction) && $subaction == 'usage'))) {die("Cannot run this script directly");}
?>
=======
>>>>>>> refs/heads/dev_tms
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
