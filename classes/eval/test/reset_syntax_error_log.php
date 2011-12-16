<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LimeExpressionManager:  Reset Log of Syntax Errors</title>
    </head>
    <body>
        <?php
            require_once("../../../config-defaults.php");
            require_once("../../../config.php");
            require_once("../../../common.php");
            include_once('../LimeExpressionManager.php');
            LimeExpressionManager::ResetSyntaxErrorLog();
        ?>
        <p>The log of syntax errors has been reset (truncated).</p>
    </body>
</html>
