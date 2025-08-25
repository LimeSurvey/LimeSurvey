<?php

?>

<div id="importSurvey_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <?php
            echo CHtml::form(array('surveyAdministration/import'),
                'post', array(
                    'id' => 'importsurvey',
                    'name' => 'importsurvey',
                    'class' => '',
                    'enctype' => 'multipart/form-data',
                    'data-error-file-required' => gT('No file selected'),
                )
            ); ?>
            <?php
            //modal header
            App()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Import survey')]
            );
            ?>
            <div class="modal-body" id="modal-body-import-survey">
                <div class="row">
                    <div class="mb-3">
                        <label class='form-label ' >
                            <?php printf(gT(
                                "Select survey structure file (*.lss, *.txt) or survey archive (*.lsa) (maximum file size: %01.2f MB)"),
                                getMaximumFileUploadSize() / 1024 / 1024
                            ); ?>
                        </label>
                        <div class="upload-container">
                            <label><b><?php eT("Select or drop a file here"); ?></b></label>
                            <label for="fileUpload" class="upload-label" id="drop_zone">
                                <div class="upload-text" id="file-upload-text">
                                    <span class="ri-upload-line">&nbsp;</span> </br>
                                    <?php et('Drop file here'); ?>
                                </div>
                                <input
                                    type="file"
                                    id="fileUpload"
                                    name="the_file"
                                    class="form-control upload-input"
                                    accept='.lss,.lsa,.tsv,.txt'
                                    onchange="$('#import-submit').attr('disabled', false).attr('data-bs-toggle', false);"
                                />
                            </label>
                        </div>
                    </div>
                    <div class='mb-3'>
                        <input id="yttranslinksfields" name="translinksfields" type="hidden" value="0">
                        <input id="translinksfields" name="translinksfields" type="checkbox" value="1" checked>
                        <label
                            class='form-label '
                            for='translinksfields'>
                            <?php eT("Convert resource links and expression fields?"); ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                <input type='submit' id="import-submit" class="btn btn-info col-3" value='<?php eT("Import survey"); ?>' />
            </div>
            </form>
        </div>
    </div>
</div>
