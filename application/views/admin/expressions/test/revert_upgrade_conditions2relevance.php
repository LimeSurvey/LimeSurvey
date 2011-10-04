<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LimeExpressionManager:  Revert Upgrade Conditions to Relevance</title>
    </head>
    <body>
        <?php
            $data = LimeExpressionManager::RevertUpgradeConditionsToRelevance();
            if (is_null($data)) {
                echo "No conditions found in database.  Nothing to revert.";
            }
            else {
                echo "Found and removed relevance attributes for " . $data . " question(s)";
            }
        ?>
    </body>
</html>
