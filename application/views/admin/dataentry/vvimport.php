<?php
    if ($tableExists) {
    ?>
    <div class='header ui-widget-header'><?php $clang->eT("Import a VV survey file"); ?></div>
    <?php echo CHtml::form(array('admin/dataentry/sa/vvimport/surveyid/'.$surveyid), 'post', array('enctype'=>'multipart/form-data', 'id'=>'vvexport'));?>
        <ul>
            <li>
                <label for='csv_vv_file'><?php $clang->eT("File:"); ?></label>
                <?php echo CHtml::fileField('csv_vv_file'); ?>
            </li>
            <li>
                <label for='noid'><?php $clang->eT("Exclude record IDs?"); ?></label>
                <?php echo CHtml::checkBox('noid',true,array('value'=>"noid",'onChange' => 'javascript:form.insertmethod.disabled=this.checked')) ?>
                <!-- <input type='checkbox' id='noid' name='noid' value='noid' checked=checked onchange='form.insertmethod.disabled=this.checked;' /> -->
            </li>
            <li>
                <label for='insertmethod'><?php $clang->eT("When an imported record matches an existing record ID:"); ?></label>
                <?php  echo CHtml::dropDownList('insertmethod', 'ignore', array(
                        'skip' => $clang->gT("Report and skip the new record."), 
                        'renumber' => $clang->gT("Renumber the new record."),
                        'replace' => $clang->gT("Replace the existing record."),
                        'replaceanswers' => $clang->gT("Replace answers in file in the existing record."),
                        ),array('disabled'=>'disabled')); ?>
            </li>
            <li>
                <label for='notfinalized'><?php $clang->eT("Import as not finalized answers?"); ?></label>
                <?php echo CHtml::checkBox('notfinalized',false,array('value'=>"notfinalized")); ?>
            </li>
            <li>
                <label for='vvcharset'><?php $clang->eT("Character set of the file:"); ?></label>
                <?php  echo CHtml::dropDownList('vvcharset',false,$aEncodings,array('empty' => $clang->gT('Automatic (UTF-8)'))); ?>
            </li>
            <li>
                <label for='dontdeletefirstline' title='<?php $clang->eT("With real vv file : questions code are in second line"); ?>'><?php $clang->eT("First line contains the code of questions:"); ?></label>
                <?php echo CHtml::checkBox('dontdeletefirstline',false,array('value'=>"dontdeletefirstline")); ?>
            </li>
            <li>
                <label for='forceimport' title='<?php $clang->eT("Try to import even if question codes don't match"); ?>'><?php $clang->eT("Force import:"); ?></label>
                <?php echo CHtml::checkBox('forceimport',false,array('value'=>"forceimport")); ?>
            </li>
        </ul>
        <p>
            <input type='submit' value='<?php $clang->eT("Import"); ?>' />
            <input type='hidden' name='action' value='vvimport' />
            <input type='hidden' name='subaction' value='upload' />
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
        </p>
    </form>
    <br />

    <?php } else { ?>
    <br />
    <div class='messagebox'>
        <div class='header'><?php $clang->eT("Import a VV response data file"); ?></div>
        <div class='warningheader'><?php $clang->eT("Cannot import the VVExport file."); ?></div>
        <?php $clang->eT("This survey is not active. You must activate the survey before attempting to import a VVexport file."); ?>
        <br /> <br />
        [<a href='<?php echo $this->createUrl('admin/survey/sa/view/'.$surveyid); ?>'><?php $clang->eT("Return to survey administration"); ?></a>]
    </div>
    <?php } ?>
