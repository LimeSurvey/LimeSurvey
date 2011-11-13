<?php
$this->_getAdminHeader(Yii::app()->session['metaHeader']);
$this->_showadminmenu();
$this->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
?>