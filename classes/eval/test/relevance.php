<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<<<<<<< HEAD
<?php
if (!((isset($subaction) && $subaction == 'relevance'))) {die("Cannot run this script directly");}
?>
=======
>>>>>>> refs/heads/dev_tms
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ExpressionManager:  Unit Test Relevance</title>
<<<<<<< HEAD
        <script type="text/javascript" src="../scripts/jquery/jquery.js"></script>
        <script type="text/javascript" src="../classes/eval/em_javascript.js"></script>
        <script type="text/javascript" src="../scripts/survey_runtime.js"></script>
    </head>
    <body id="limesurvey">
        <?php
//            include_once('../LimeExpressionManager.php');
=======
        <script type="text/javascript" src="../../../scripts/jquery/jquery.js"></script>
        <script type="text/javascript" src="../em_javascript.js"></script>
    </head>
    <body onload="ExprMgr_process_relevance_and_tailoring('');">
        <?php
            include_once('../LimeExpressionManager.php');
>>>>>>> refs/heads/dev_tms
            LimeExpressionManager::UnitTestRelevance();
        ?>
    </body>
</html>
