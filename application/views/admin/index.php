<?php
$this->getAdminHeader(Yii::app()->session['metaHeader']);
$this->_showadminmenu();
$this->getAdminFooter("http://manual.limesurvey.org", gT("LimeSurvey online manual"));
?>
