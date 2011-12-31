<?php
$data = LimeExpressionManager::RevertUpgradeConditionsToRelevance();
if (is_null($data)) {
    echo "No conditions found in database.  Nothing to revert.";
}
else {
    echo "Found and removed relevance for " . $data . " question(s)";
}
?>
