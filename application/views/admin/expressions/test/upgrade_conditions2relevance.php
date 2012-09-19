<?php
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