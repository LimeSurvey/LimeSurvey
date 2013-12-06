<?php
// Needed for own post function in ajax : can be remove when move to jquery
Yii::app()->clientScript->registerScript('csrfToken',"csrfToken ='".Yii::app()->request->csrfToken."';",CClientScript::POS_HEAD);
// Add crsf token to whole ajax request using jquery
Yii::app()->clientScript->registerScript('csrfTokenByAjaxSetup',"$.ajaxSetup({data: {YII_CSRF_TOKEN: csrfToken}});",CClientScript::POS_HEAD);
?>
