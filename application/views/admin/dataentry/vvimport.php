<?php
    if ($tableExists) {
    ?>
    <div class='header ui-widget-header'><?php $clang->eT("Import a VV survey file"); ?></div>
    <?php echo CHtml::form(array('admin/dataentry/sa/vvimport/surveyid/'.$surveyid), 'post', array('enctype'=>'multipart/form-data', 'id'=>'vvexport'));?>
        <ul>
            <li>
                <label for='the_file'><?php $clang->eT("File:"); ?></label>
                <input type='file' id='the_file' name='the_file' />
            </li>
            <li>
                <label for='noid'><?php $clang->eT("Exclude record IDs?"); ?></label>
                <input type='checkbox' id='noid' name='noid' value='noid' checked=checked onchange='form.insertmethod.disabled=this.checked;' />
            </li>
            <li>
                <label for='insertmethod'><?php $clang->eT("When an imported record matches an existing record ID:"); ?></label>
                <select id='insertmethod' name='insert' disabled='disabled'>
                    <option value='ignore' selected='selected'><?php $clang->eT("Report and skip the new record."); ?></option>
                    <option value='renumber'><?php $clang->eT("Renumber the new record."); ?></option>
                    <option value='replace'><?php $clang->eT("Replace the existing record."); ?></option>
                </select>
            </li>
            <li>
                <label for='finalized'><?php $clang->eT("Import as not finalized answers?"); ?></label>
                <input type='checkbox' id='finalized' name='finalized' value='notfinalized' />
            </li>
            <li>
                <label for='vvcharset'><?php $clang->eT("Character set of the file:"); ?></label>
                <select id='vvcharset' name='vvcharset'>
                    <?php echo $charsetsout; ?>
                </select>
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
