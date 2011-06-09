<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
        <title></title>
    </head>
    <body>
        <?php
            include 'ExpressionManager.php';
            include 'ExpressionManagerFunctions.php';
            ExpressionManager::UnitTestEvaluator($exprmgr_functions,$exprmgr_extraVars,$exprmgr_extraTests);
        ?>
    </body>
</html>
