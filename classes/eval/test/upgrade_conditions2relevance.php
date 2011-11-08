<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LimeExpressionManager:  Upgrade Conditions to Relevance</title>
    </head>
    <body>
        <?php
            require_once("../../../config-defaults.php");
            require_once("../../../config.php");
            require_once("../../../common.php");
            include_once('../LimeExpressionManager.php');
            $data = LimeExpressionManager::UpgradeConditionsToRelevance();
            if (is_null($data)) {
                echo "No conditions found in database";
            }
            else {
                echo "Found and converted conditions for " . count($data) . " question(s)<br/>";
                echo "<pre>";
                print_r($data);
                echo "</pre>";
            }
        ?>
    </body>
</html>
