<?php
Yii::app()->clientScript->registerScript('csrfToken',"csrfToken ='".Yii::app()->request->csrfToken."';",CClientScript::POS_HEAD);
?>
