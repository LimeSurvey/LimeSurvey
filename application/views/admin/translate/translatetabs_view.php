<div id='tab-<?php echo $type;?>'>
<?php
Yii::app()->loadHelper('admin/htmleditor');
echo PrepareEditorScript(true, Yii::app()->getController());
?>

<div class='translate'>
<input type='button' class='auto-trans' value='<?php echo $clang->gT("Auto Translate");?>' id='auto-trans-tab-<?php echo $type;?>' />
<img src='<?php echo Yii::app()->getConfig("imageurl");?>/ajax-loader.gif' style='display: none' class='ajax-loader' alt='<?php echo $clang->gT("Loading...");?>' />
<?php echo $translateTabs; ?>
