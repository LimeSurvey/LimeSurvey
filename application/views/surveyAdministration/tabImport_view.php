<?php

/**
 * Import survey
 */
?>
<!--<div class="ls-flex-row wrap align-content-center align-items-center">-->
<div class="ls-flex-row">
    <div class="grow-10 ls-space padding left-10 right-10">
        <div class="">
            <!-- tab import survey -->
            <!-- import form -->
            <?php echo CHtml::form(array('surveyAdministration/copy'), 'post', array('id' => 'importsurvey', 'name' => 'importsurvey', 'class' => '', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return window.LS.validatefilename(this,"' . gT('Please select a file to import!', 'js') . '");')); ?>
            <div class="row">

                <!-- Select file -->
                <div class="mb-3 col-4">
                    <label class='form-label ' for='the_file'>
                        <?php printf(gT("Select survey structure file (*.lss, *.txt) or survey archive (*.lsa) (maximum file size: %01.2f MB)"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                    </label>
                    <div>
                        <input id='the_file' name="the_file" class="form-control" type="file" accept='.lss,.lsa,.tsv,.txt' onchange="$('#import-submit').attr('disabled', false).attr('data-bs-toggle', false);" required />
                    </div>
                </div>

                <!-- Convert resource links and INSERTANS fields? -->
                <div class='mb-3'>
                    <input id="yttranslinksfields" name="translinksfields" type="hidden" value="0">
                    <input id="translinksfields" name="translinksfields" type="checkbox" value="1" checked>
                    <label class='form-label ' for='translinksfields'><?php eT("Convert resource links and expression fields?"); ?> </label>
                </div>

                <!-- Submit -->
                <div class="mt-3 mb-3 col-4">
                    <div>
                        <input type='submit' id="import-submit" class="btn btn-primary w-auto" value='<?php eT("Import survey"); ?>' />
                    </div>
                </div>

                <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="' . $surveyid . '" />'; ?>
                <input type='hidden' name='action' value='importsurvey' />
            </div>
            </form>
        </div>
    </div>
</div>

<div id='pleaseselectfile-popup' class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT("No file selected"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?php eT("Please select a file to import!"); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php eT("Close"); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
