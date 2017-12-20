<?php 
header('Content-type: application/json');
//echo $data;
$this->layout=false;

echo CJavaScript::jsonEncode($data); 
Yii::app()->end();
die();
?>
