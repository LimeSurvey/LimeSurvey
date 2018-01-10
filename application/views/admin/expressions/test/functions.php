<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('expressionsFunctions');

?>

<?php
echo ExpressionManager::ShowAllowableFunctions();
?>