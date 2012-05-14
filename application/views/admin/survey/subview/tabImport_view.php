<div id='import'>
    <form enctype='multipart/form-data' class='form30' id='importsurvey' name='importsurvey' action='<?php echo $this->createUrl('admin/survey/copy'); ?>' method='post' onsubmit='return validatefilename(this,"<?php $clang->eT('Please select a file to import!', 'js'); ?> ");'>
        <ul>
            <li><label for='the_file'><?php $clang->eT("Select survey structure file (*.lss, *.csv, *.xls) or survey archive (*.zip):");  ?> </label>
                <input id='the_file' name="the_file" type="file" /></li>
            <li>&nbsp;</li>
            <li><label for='translinksfields'><?php $clang->eT("Convert resource links and INSERTANS fields?"); ?> </label>
                <input id='translinksfields' name="translinksfields" type="checkbox" checked='checked'/></li></ul>
        <p><input type='submit' value='<?php $clang->eT("Import survey"); ?>' />
            <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="'.$surveyid.'" />'; ?>
            <input type='hidden' name='action' value='importsurvey' /></p></form>
</div>
