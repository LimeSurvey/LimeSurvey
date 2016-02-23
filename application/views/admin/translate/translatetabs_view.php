<div id='tab-<?php echo $type;?>' class='tab-pane fade in <?php if($activeTab){echo "active";}?>'>
<?php
Yii::app()->loadHelper('admin/htmleditor');
echo PrepareEditorScript(true, Yii::app()->getController());
?>

<div class='translate'>
<?php if(App()->getConfig('googletranslateapikey')){ ?>
    <input type='button' class='auto-trans' value='<?php eT("Auto Translate");?>' id='auto-trans-tab-<?php echo $type;?>' />
    <img src='<?php echo Yii::app()->getConfig("adminimageurl");?>/ajax-loader.gif' style='display: none' class='ajax-loader' alt='<?php eT("Loading...");?>' />
<?php } ?>
<?php echo $translateTabs; ?>
