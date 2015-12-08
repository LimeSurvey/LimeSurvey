<?php
/**
 * Import survey
 */
?>
<!-- tab import survey -->
    <!-- import form -->
    <?php echo CHtml::form(array('admin/survey/sa/copy'), 'post', array('id'=>'importsurvey', 'name'=>'importsurvey', 'class'=>'form30', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return validatefilename(this,"'. gT('Please select a file to import!', 'js').'");')); ?>
        <ul class="list-unstyled col-lg-12">

            <!-- Select file -->
            <li>
                <label for='the_file'><?php  eT("Select survey structure file (*.lss, *.csv, *.txt) or survey archive (*.lsa):");  ?> </label>
                <input id='the_file' name="the_file" type="file" />
            </li>

            <!-- Convert resource links and INSERTANS fields? -->
            <li>
                <label for='translinksfields'><?php  eT("Convert resource links and INSERTANS fields?"); ?> </label>
                <input id='translinksfields' name="translinksfields" type="checkbox" checked='checked'/>
            </li>

            <!-- Submit -->
            <li>
                <input type='submit' class="btn btn-default" value='<?php  eT("Import survey"); ?>' />
            </li>

            <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="'.$surveyid.'" />'; ?>
            <input type='hidden' name='action' value='importsurvey' />
        </ul>
    </form>
