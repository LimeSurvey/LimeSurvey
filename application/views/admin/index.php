<?php
$this->_getAdminHeader(Yii::app()->session['metaHeader']);
$this->_showadminmenu();
$this->_getAdminFooter("http://manual.limesurvey.org", $clang->gT("LimeSurvey online manual"));
?>