<div id='resources'>
    <form class='form30' id='browsesurveyresources' name='browsesurveyresources'
          action='<?php echo $this->createUrl("admin/kcfinder/index/load/browse"); ?>' method='get' target="_blank">
        <ul style='list-style-type:none; text-align:center'>
            <li>
                <label>&nbsp;</label>
                <?php echo CHtml::dropDownList('type', 'files', array('files' => $clang->gT('Files'), 'flash' => $clang->gT('Flash'), 'images' => $clang->gT('Images'))); ?>
                <input type='submit' value="<?php $clang->eT("Browse Uploaded Resources") ?>" />
            </li>
            <li>
                <label>&nbsp;</label>
                <input type='button'<?php echo $disabledIfNoResources; ?>
                       onclick='window.open("<?php echo $this->createUrl("admin/export/resources/export/survey/surveyid/$surveyid"); ?>", "_blank")'
                       value="<?php $clang->eT("Export Resources As ZIP Archive") ?>"  />
            </li>
        </ul>
    </form>
    <form enctype='multipart/form-data'  class='form30' id='importsurveyresources' name='importsurveyresources' action='<?php echo $this->createUrl('admin/survey/importsurveyresources/'); ?>' method='post' onsubmit='return validatefilename(this,"<?php $clang->eT('Please select a file to import!', 'js'); ?>");'>
        <input type='hidden' name='surveyid' value='<?php echo $surveyid; ?>' />
        <input type='hidden' name='action' value='importsurveyresources' />
        <ul style='list-style-type:none; text-align:center'>
            <li><label for='the_file'><?php $clang->eT("Select ZIP File:"); ?></label>
                <input id='the_file' name='the_file' type='file' /></li>
            <li><label>&nbsp;</label>
                <input type='button' value='<?php $clang->eT("Import Resources ZIP Archive"); ?>' <?php echo $ZIPimportAction; ?> /></li>
        </ul>
    </form>
</div>