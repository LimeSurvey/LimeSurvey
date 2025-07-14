<?php

?>

<div id="importSurvey_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">

            <?php
            //modal header
            App()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Import survey')]
            );
            ?>

            <div class="modal-body" id="modal-body-import-survey">
                <?php
                echo CHtml::form(array('surveyAdministration/import'),
                    'post', array(
                        'id' => 'importsurvey',
                        'name' => 'importsurvey',
                        'class' => '',
                        'enctype' => 'multipart/form-data',
                        'onsubmit' => 'return window.LS.validatefilename(this,"' . gT('Please select a file to import!', 'js') . '");'
                    )
                ); ?>
                <div class="row">

                    <!-- Select file -->
                    <div class="mb-3">
                        <label class='form-label ' for='the_file'>
                            <?php printf(gT("Select survey structure file (*.lss, *.txt) or survey archive (*.lsa) (maximum file size: %01.2f MB)"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                        </label>
                        <div>
                            <input id='the_file' name="the_file" class="form-control" type="file" accept='.lss,.lsa,.tsv,.txt' onchange="$('#import-submit').attr('disabled', false).attr('data-bs-toggle', false);" required />
                        </div>
                    </div>

                    <div class='mb-3'>
                        <input id="yttranslinksfields" name="translinksfields" type="hidden" value="0">
                        <input id="translinksfields" name="translinksfields" type="checkbox" value="1" checked>
                        <label class='form-label ' for='translinksfields'><?php eT("Convert resource links and expression fields?"); ?> </label>
                    </div>

                    <!-- Submit -->
                    <div class="mt-3 mb-3 col-4">
                        <div>
                            <input type='submit' id="import-submit" class="btn btn-primary col-6" value='<?php eT("Import survey"); ?>' />
                        </div>
                    </div>
                </div>
                <?php CHtml::endForm();?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>

                <button id="saveactivateBtn" type="button" class="btn btn-info" >
                    <?php eT("Import survey"); ?>
                </button>
            </div>

        </div>
    </div>
</div>

