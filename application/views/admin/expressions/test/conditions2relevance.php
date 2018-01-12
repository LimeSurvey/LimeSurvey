<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('expressionsConditions2Relevance');
?>

<?php
$data = LimeExpressionManager::UnitTestConvertConditionsToRelevance();
echo count($data) . " question(s) in your database contain conditions.  Below is the mapping of question ID number to generated relevance equation<br/>";
echo "<pre>";
print_r($data);
echo "</pre>";
?>
