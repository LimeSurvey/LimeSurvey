<?php 
header('Content-type: application/json');
//echo $data;
$this->layout=false;

echo html_entity_decode(CJavaScript::jsonEncode($data)); 
Yii::app()->end();
die();
?>
