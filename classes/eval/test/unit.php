<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<<<<<<< HEAD
<?php
if (!((isset($subaction) && $subaction == 'unit'))) {die("Cannot run this script directly");}
?>
=======
>>>>>>> refs/heads/dev_tms
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css">
            <!--
.error {
    background-color: #ff0000;
}
.ok {
    background-color: #00ff00
}
            -->
        </style>
        <title>ExpressionManager:  Unit Test Core Evaluator</title>
    </head>
    <body onload="recompute()">
<<<<<<< HEAD
<script type="text/javascript" src="../classes/eval/em_javascript.js"></script>
        <?php
//            include_once('../ExpressionManager.php');
=======
<script type="text/javascript" src="../em_javascript.js"></script>
        <?php
            include_once('../ExpressionManager.php');
>>>>>>> refs/heads/dev_tms
            ExpressionManager::UnitTestEvaluator();
        ?>
    </body>
</html>
