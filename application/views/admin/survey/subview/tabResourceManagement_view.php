<div id='resources'>
    <?php echo CHtml::form(Yii::app()->getConfig('publicurl') . "third_party/kcfinder/browse.php?language=".sTranslateLangCode2CK($clang->getlangcode()), 'get', array('id'=>'browsesurveyresources', 'name'=>'browsesurveyresources', 'target'=>'_blank', 'class'=>'form30')); ?>
        <ul>
            <li>
                <label>&nbsp;</label>
                <?php echo CHtml::dropDownList('type', 'files', array('files' => $clang->gT('Files'), 'flash' => $clang->gT('Flash'), 'images' => $clang->gT('Images'))); ?>
                <input type='submit' value="<?php $clang->eT("Browse Uploaded Resources") ?>" />
            </li>
            <li>
                <label>&nbsp;</label>
                <input type='button'<?php echo $disabledIfNoResources; ?>
                       onclick='window.open("<?php echo $this->createUrl("admin/export/sa/resources/export/survey/surveyid/$surveyid"); ?>", "_blank")'
                       value="<?php $clang->eT("Export Resources As ZIP Archive") ?>"  />
            </li>
        </ul>
    </form>
    <?php echo CHtml::form(array('admin/survey/sa/importsurveyresources'), 'post', array('id'=>'importsurveyresources', 'name'=>'importsurveyresources', 'class'=>'form30', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return validatefilename(this,"'.$clang->gT('Please select a file to import!', 'js').'");')); ?>
        <input type='hidden' name='surveyid' value='<?php echo $surveyid; ?>' />
        <input type='hidden' name='action' value='importsurveyresources' />
        <ul>
            <li><label for='the_file'><?php $clang->eT("Select ZIP File:"); ?></label>
                <input id='the_file' name='the_file' type='file' /></li>
            <li><label>&nbsp;</label>
                <input type='button' value='<?php $clang->eT("Import Resources ZIP Archive"); ?>' <?php echo $ZIPimportAction; ?> /></li>
        </ul>
    </form>
</div>
