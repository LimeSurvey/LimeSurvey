<div id='tab-<?php echo $type;?>'>
<?php echo PrepareEditorScript(); ?>

<div class='translate'>
<input type='button' class='auto-trans' value='<?php echo $clang->gT("Auto Translate");?>' id='auto-trans-tab-<?php echo $type;?>' />
<img src='<?php echo $this->config->item("imageurl");?>/ajax-loader.gif' style='display: none' class='ajax-loader' alt='<?php echo $clang->gT("Loading...");?>' />
<?php echo translate::displayTranslateFieldsHeader($baselangdesc, $tolangdesc); ?>