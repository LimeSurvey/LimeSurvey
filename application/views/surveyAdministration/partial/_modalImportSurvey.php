<?php

?>

<div id="importSurvey_modal" class="modal fade" role="dialog" aria-modal="true" aria-labelledby="importSurveyModalTitle importSurveyModalTitle-dialogsr">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <?php
            echo CHtml::form(
                array('surveyAdministration/import'),
                'post',
                array(
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
                ['modalTitle' => gT('Import survey'), 'modalTitleId' => 'importSurveyModalTitle']
            );
            ?>
            <div class="modal-body" id="modal-body-import-survey">
                <p class="note"><?php echo sprintf(gT('Fields with %s are required.'), '<span class="required">*</span>'); ?></p>
                <div class="row">
                    <div class="mb-3">
                        <label class='form-label ' id="fileUpload-instructions">
                            <?php if (isFileUploadSizeLimited()) {
                                printf(
                                    gT(
                                        "Select survey structure file (*.lss, *.txt) or survey archive (*.lsa) (maximum file size: %01.2f MB)"
                                    ),
                                    getMaximumFileUploadSize() / 1024 / 1024
                                );
                            } else {
                                eT("Select survey structure file (*.lss, *.txt) or survey archive (*.lsa)");
                            } ?>
                        </label>
                        <div class="upload-container">
                            <label id="fileUpload-label" for="fileUpload"><b><?php eT("Select or drop a file here"); ?> <span class="required" aria-hidden="true">*</span></b></label>
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
                                    aria-labelledby="fileUpload-label file-upload-text"
                                    aria-required="true"
                                    aria-describedby="fileUpload-instructions fileUpload-error"
                                    onchange="$('#import-submit').attr('disabled', false).attr('data-bs-toggle', false);"
                                />
                            </label>
                            <?php // Filled when the form is submitted without a file: The alert role announces it ?>
                            <div id="fileUpload-error" class="text-danger mt-1" role="alert"></div>
                        </div>
                    </div>
                    <div class='mb-3'>
                        <label class='form-label' for='surveysgroup'><?php eT("Survey group:"); ?></label>
                        <?php $this->widget('yiiwheels.widgets.select2.WhSelect2', [
                            'asDropDownList' => true,
                            'htmlOptions' => [],
                            'data' => [
                                'default'     => gT("Import on default survey group"),
                                'from_survey' => gT("Keep the survey group from the imported file"),
                            ],
                            'value' => 'default',
                            'name' => 'surveysgroup',
                            'pluginOptions' => ['minimumResultsForSearch' => -1]
                        ]); ?>
                        <div class="alert alert-warning mt-2 d-none" id="survey_group_import_warning">
                            <?php eT("Survey group will be matched by name. Please note that survey group permissions will be inherited by the imported survey."); ?>
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
                <input type='submit' id="import-submit" class="btn btn-info" value='<?php eT("Import survey"); ?>' />
            </div>
            </form>
        </div>
    </div>
</div>

<?php
App()->getClientScript()->registerScript('ImportSurveyModal', "
$('#importsurvey').on('submit', function(e) {
    // Use a small timeout to allow client-side validation to run.
    // If validation fails, the form submission is cancelled before the spinner is shown.
    setTimeout(function() {
        if (!e.isDefaultPrevented()) {
            $('#ls-loading').show();
            // Set the text of the status region again, so the screen reader announces it
            var loadingText = $('#ls-loading .visually-hidden');
            // Keep the original text: A second submit must not read the temporarily emptied text
            var text = loadingText.data('loadingText');
            if (text === undefined) {
                text = loadingText.text();
                loadingText.data('loadingText', text);
            }
            loadingText.text('');
            setTimeout(function () { loadingText.text(text); }, 100);
        }
    }, 100);
});
", LSYii_ClientScript::POS_END);
?>
