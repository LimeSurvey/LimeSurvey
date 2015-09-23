<?php
$data = LimeExpressionManager::UnitTestConvertConditionsToRelevance();
echo count($data) . " question(s) in your database contain conditions.  Below is the mapping of question ID number to generated relevance equation<br/>";
echo "<pre>";
print_r($data);
echo "</pre>";
?>
