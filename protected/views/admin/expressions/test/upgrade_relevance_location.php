<?php
$data = LimeExpressionManager::UpgradeRelevanceAttributeToQuestion();
echo count($data) . " question(s) in your database contain relevance.  Below is the mapping of question ID number to generated relevance equation<br/>";
echo "<pre>";
print_r($data);
echo "</pre>";
?>