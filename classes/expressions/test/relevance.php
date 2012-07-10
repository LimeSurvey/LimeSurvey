<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
    if (!((isset($subaction) && $subaction == 'relevance'))) {die("Cannot run this script directly");}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ExpressionManager:  Unit Test Relevance</title>
        <script type="text/javascript" src="../scripts/jquery/jquery.js"></script>
        <script type="text/javascript" src="../scripts/em_javascript.js"></script>
        <script type="text/javascript" src="../scripts/survey_runtime.js"></script>
    </head>
    <body id="limesurvey">
        <?php
            //            include_once('../LimeExpressionManager.php');
            LimeExpressionManager::UnitTestRelevance();
        ?>
    </body>
</html>
