<?php
    if ($tableExists) {
    ?>
    <div class='header ui-widget-header'><?php eT("Import a VV survey file"); ?></div>
    <?php echo CHtml::form(array('admin/dataentry/sa/vvimport/surveyid/'.$surveyid), 'post', array('enctype'=>'multipart/form-data', 'id'=>'vvexport'));?>
        <ul>
            <li>
                <label for='csv_vv_file'><?php eT("File:"); ?></label>
                <?php echo CHtml::fileField('csv_vv_file'); ?>
            </li>
            <li>
                <label for='noid'><?php eT("Exclude record IDs?"); ?></label>
                <?php echo CHtml::checkBox('noid',true,array('value'=>"noid",'onChange' => 'javascript:form.insertmethod.disabled=this.checked')) ?>
                <!-- <input type='checkbox' id='noid' name='noid' value='noid' checked=checked onchange='form.insertmethod.disabled=this.checked;' /> -->
            </li>
            <li>
                <label for='insertmethod'><?php eT("When an imported record matches an existing record ID:"); ?></label>
                <?php  echo CHtml::dropDownList('insertmethod', 'ignore', array(
                        'skip' => gT("Report and skip the new record."), 
                        'renumber' => gT("Renumber the new record."),
                        'replace' => gT("Replace the existing record."),
                        'replaceanswers' => gT("Replace answers in file in the existing record."),
                        ),array('disabled'=>'disabled')); ?>
            </li>
            <li>
                <label for='notfinalized'><?php eT("Import as not finalized answers?"); ?></label>
                <?php echo CHtml::checkBox('notfinalized',false,array('value'=>"notfinalized")); ?>
            </li>
            <li>
                <label for='vvcharset'><?php eT("Character set of the file:"); ?></label>
                <?php  echo CHtml::dropDownList('vvcharset',false,$aEncodings,array('empty' => gT('Automatic (UTF-8)'))); ?>
            </li>
            <li>
                <label for='dontdeletefirstline' title='<?php eT("With real vv file : questions code are in second line"); ?>'><?php eT("First line contains the code of questions:"); ?></label>
                <?php echo CHtml::checkBox('dontdeletefirstline',false,array('value'=>"dontdeletefirstline")); ?>
            </li>
            <li>
                <label for='forceimport' title='<?php eT("Try to import even if question codes don't match"); ?>'><?php eT("Force import:"); ?></label>
                <?php echo CHtml::checkBox('forceimport',false,array('value'=>"forceimport")); ?>
            </li>
        </ul>
        <p>
            <input type='submit' value='<?php eT("Import"); ?>' />
            <input type='hidden' name='action' value='vvimport' />
            <input type='hidden' name='subaction' value='upload' />
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
        </p>
    </form>
    <br />

    <?php } else { ?>
    <br />
    <div class='messagebox'>
        <div class='header'><?php eT("Import a VV response data file"); ?></div>
        <div class='warningheader'><?php eT("Cannot import the VVExport file."); ?></div>
        <?php eT("This survey is not active. You must activate the survey before attempting to import a VVexport file."); ?>
        <br /> <br />
        [<a href='<?php echo $this->createUrl('admin/survey/sa/view/'.$surveyid); ?>'><?php eT("Return to survey administration"); ?></a>]
    </div>
    <?php } ?>
