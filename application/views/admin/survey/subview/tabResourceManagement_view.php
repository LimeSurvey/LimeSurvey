<?php
	$yii = Yii::app();
	$controller = $yii->getController();
?>
<div id='resources'>
            <form enctype='multipart/form-data'  class='form30' id='importsurveyresources' name='importsurveyresources' action='<?php echo $yii->homeUrl.('/admin/survey/sa/importsurveyresources/'); ?>' method='post' onsubmit='return validatefilename(this,"<?php $clang->eT('Please select a file to import!','js'); ?>");'>
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            <input type='hidden' name='action' value='importsurveyresources' />
            <ul>
            <li><label>&nbsp;</label>
            <input type='button' onclick='window.open("<?php echo $yii->getConfig('sCKEditorURL'); ?>/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php", "_blank")' value="<?php $clang->eT("Browse Uploaded Resources"); ?>" <?php echo $disabledIfNoResources; ?> /></li>
            <li><label>&nbsp;</label>
            <input type='button' onclick='window.open("<?php echo $yii->homeUrl.("/admin/export/sa/resources/exportsurvresources/surveyid/$surveyid/"); ?>", "_blank")' value="<?php $clang->eT("Export Resources As ZIP Archive"); ?>" <?php echo $disabledIfNoResources; ?> /></li>
            <li><label for='the_file'><?php $clang->eT("Select ZIP File:"); ?></label>
            <input id='the_file' name='the_file' type='file' size='50' /></li>
            <li><label>&nbsp;</label>
            <input type='button' value='<?php $clang->eT("Import Resources ZIP Archive"); ?>' <?php echo $ZIPimportAction; ?> /></li>
            </ul></form>

        
</div>