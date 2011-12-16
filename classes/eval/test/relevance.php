<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ExpressionManager:  Unit Test Relevance</title>
        <script type="text/javascript" src="../../../scripts/jquery/jquery.js"></script>
        <script type="text/javascript" src="../em_javascript.js"></script>
    </head>
    <body onload="ExprMgr_process_relevance_and_tailoring('');">
        <?php
            include_once('../LimeExpressionManager.php');
            LimeExpressionManager::UnitTestRelevance();
        ?>
    </body>
</html>
