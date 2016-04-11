<?php
/**
 * ressources panel tab
 */
?>

<!-- ressources panel -->
<div id='resources' class="tab-pane fade in">
    <?php echo CHtml::form(Yii::app()->getConfig('publicurl') . "third_party/kcfinder/browse.php?language=".sTranslateLangCode2CK( App()->language), 'get', array('id'=>'browsesurveyresources', 'name'=>'browsesurveyresources', 'target'=>'_blank', 'class'=>'form30')); ?>
        <ul class="list-unstyled">
            <li>
                <label>&nbsp;</label>
                <?php echo CHtml::dropDownList('type', 'files', array('files' =>  gT('Files','unescaped'), 'flash' =>  gT('Flash','unescaped'), 'images' =>  gT('Images','unescaped'))); ?>
                <input type='submit' class="btn btn-default" value="<?php  eT("Browse Uploaded Resources") ?>" />
            </li>
            <li>
                <label>&nbsp;</label>
                <input type='button'<?php echo $disabledIfNoResources; ?>
                        class="btn btn-default"
                       onclick='window.open("<?php echo $this->createUrl("admin/export/sa/resources/export/survey/surveyid/$surveyid"); ?>", "_blank")'
                       value="<?php  eT("Export Resources As ZIP Archive") ?>"  />
            </li>
        </ul>
    </form>

    <?php echo CHtml::form(array('admin/survey/sa/importsurveyresources'), 'post', array('id'=>'importsurveyresources', 'name'=>'importsurveyresources', 'class'=>'form30', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return validatefilename(this,"'. gT('Please select a file to import!', 'js').'");')); ?>
        <input type='hidden' name='surveyid' value='<?php echo $surveyid; ?>' />
        <input type='hidden' name='action' value='importsurveyresources' />
        <ul class="list-unstyled">
            <li>
                <label for='the_file'><?php  eT("Select ZIP File:"); ?></label>
                <input id='the_file' name='the_file' type='file' />
            </li>
            <li>
                <label>&nbsp;</label>
                <input type='button' class="btn btn-default" value='<?php  eT("Import Resources ZIP Archive"); ?>' <?php echo $ZIPimportAction; ?> />
            </li>
        </ul>
    </form>
</div>
