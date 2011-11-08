<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LEM:  Upgrade Relevance Location</title>
    </head>
    <body>
        <?php
            require_once("../../../config-defaults.php");
            require_once("../../../config.php");
            require_once("../../../common.php");
            require_once('../LimeExpressionManager.php');
            $data = LimeExpressionManager::UpgradeRelevanceAttributeToQuestion();
            echo count($data) . " question(s) in your database contain relevance.  Below is the mapping of question ID number to generated relevance equation<br/>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        ?>
    </body>
</html>
