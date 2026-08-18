<?php
$summaryLabels = [
    'surveys'             => gT('Surveys'),
    'languages'           => gT('Languages'),
    'groups'              => gT('Question groups'),
    'questions'           => gT('Questions'),
    'question_attributes' => gT('Question attributes'),
    'answers'             => gT('Answers'),
    'subquestions'        => gT('Subquestions'),
    'defaultvalues'       => gT('Default answers'),
    'conditions'          => gT('Conditions'),
    'labelsets'           => gT('Label sets'),
    'assessments'         => gT('Assessments'),
    'quota'               => gT('Quotas'),
    'quotamembers'        => gT('Quota rules'),
    'quotals'             => gT('Quota language settings'),
    'plugin_settings'     => gT('Plugin settings'),
    'themes'              => gT('Themes'),
    'responses'           => gT('Responses'),
];
?>

<div id="importSurvey_modal" class="modal fade import-survey-modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
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
                    'data-summary-labels' => CJavaScript::jsonEncode($summaryLabels),
                    'data-form-title' => gT('Import survey'),
                    'data-summary-title' => gT('Import summary'),
                    'data-success-message' => gT('Survey imported successfully'),
                    'data-error-message' => gT('The survey could not be imported.'),
                    'data-drop-zone-text' => gT('Drop file here'),
                    'data-error-file-type' => gT('Please select an .lss, .lsa, .txt, or .tsv file.'),
                    'data-error-file-size' => gT('The selected file is too large. Maximum file size is 40.00 MB.'),
                    'data-error-file-count' => gT('Please select only one file.'),
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
                <div id="import-survey-form-content">
                    <div class="import-survey-modal__description">
                        <p><?php eT('Select a survey structure file (.lss, .txt, .tsv) or survey archive file (.lsa)'); ?></p>
                        <p><?php eT('Maximum file size 40.00 MB'); ?></p>
                    </div>
                    <div>
                        <div class="upload-container">
                            <label class="form-label" for="fileUpload"><?php eT("Select or drop a file here"); ?></label>
                            <label for="fileUpload" class="upload-label" id="drop_zone">
                                <div class="upload-text" id="file-upload-text">
                                    <span class="ri-upload-line" aria-hidden="true"></span>
                                    <span><?php et('Drop file here'); ?></span>
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
                    <div class="import-survey-modal__group-select">
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
                    <div class="import-survey-modal__checkbox">
                        <input id="yttranslinksfields" name="translinksfields" type="hidden" value="0">
                        <input id="translinksfields" name="translinksfields" type="checkbox" value="1" checked>
                        <label
                            class='form-label '
                            for='translinksfields'>
                            <?php eT("Convert resource links and expression fields?"); ?>
                        </label>
                    </div>
                </div>
                <div id="import-survey-summary" class="d-none import-survey-summary">
                    <p class="import-survey-summary__intro"><?php eT('Import of survey completed.'); ?></p>
                    <div
                        class="import-survey-summary__table"
                        role="table"
                        aria-label="<?php eT('Survey structure import summary'); ?>"
                    >
                        <div id="import-survey-summary-rows" role="rowgroup"></div>
                    </div>
                    <div id="import-survey-summary-warnings" class="alert alert-warning d-none mt-3 mb-0">
                        <strong><?php eT('Warnings'); ?></strong>
                        <ul class="mb-0 mt-1"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="import-survey-form-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                <input type='submit' id="import-submit" class="btn btn-info" value='<?php eT("Import survey"); ?>' disabled />
            </div>
            <div class="modal-footer d-none" id="import-survey-summary-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Close'); ?></button>
                <a id="import-survey-go-to-survey" class="btn btn-info" href="#"><?php eT('Go to survey'); ?></a>
            </div>
            </form>
        </div>
    </div>
</div>
