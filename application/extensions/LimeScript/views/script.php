<?php
$json = json_encode($data, JSON_FORCE_OBJECT);
echo "LS.data = $json";
App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.LimeScript.assets'). '/script.js'));
?>