<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
if (!((isset($subaction) && $subaction == 'conditions2relevance'))) {die("Cannot run this script directly");}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LimeExpressionManager:  Preview Conditions to Relevance</title>
    </head>
    <body>
        <?php
//            require_once("../../../config-defaults.php");
//            require_once("../../../config.php");
//            require_once("../../../common.php");
//            include_once('../LimeExpressionManager.php');
            $data = LimeExpressionManager::UnitTestConvertConditionsToRelevance();
            echo count($data) . " question(s) in your database contain conditions.  Below is the mapping of question ID number to generated relevance equation<br/>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        ?>
    </body>
</html>
