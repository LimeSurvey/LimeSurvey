<?php

?>

<div id="copySurvey_modal" class="modal fade" role="dialog" aria-modal="true" aria-labelledby="copySurveyModalTitle">
    <div class="modal-dialog import-modal">
        <!-- Modal content-->
        <div class="modal-content">
            <?php
            //modal header
            App()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Copy survey'), 'modalTitleId' => 'copySurveyModalTitle']
            );
            ?>
            <?php echo CHtml::form(
                ['surveyAdministration/copy'],
            ) ?>
            <div class="modal-body" id="modal-body-copy-survey">
                <div class="row mb-3">
                    <p class="form-label"><?= gT("Select the options for copying your survey."); ?></p>
                </div>
                    <div class="row mb-3">
                        <!-- Source survey -->
                        <label class="form-label" for='surveyIdToCopy'><?= gT("Source survey:"); ?> </label>
                        <select id='surveyIdToCopy' name='surveyIdToCopy' class="form-select w-100"></select>
                    </div>
                    <div class="mb-3">
                        <!-- New survey title -->
                        <label  class="form-label" for='copysurveytitle'><?php eT("New survey title:"); ?> </label>
                        <input type='text' id='copysurveytitle' name='copysurveytitle' value='' class="form-control" aria-describedby="copysurveytitleHelp"/>
                        <p class="form-control-static" id="copysurveytitleHelp">
                            <span class='reg12'><?php echo  gT('Optional - Leave this field empty to assign a new title with "... - Copy" automatically.'); ?> </span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <button
                            type="button"
                            class="btn btn-link p-0 reg12 fw-bold text-decoration-none"
                            data-bs-toggle="collapse"
                            data-bs-target="#copySurveyAdvanced"
                            aria-expanded="false"
                            aria-controls="copySurveyAdvanced"
                        >
                            <?php eT("Advanced options"); ?><span class="ri-arrow-down-s-line" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="collapse" id="copySurveyAdvanced">
                        <div class="mb-3">
                            <!-- New survey ID -->
                            <label class=" form-label" for='copysurveyid'><?php eT("New survey ID:"); ?> </label>
                            <input type='number' step="1" min="1" max="999999" id='copysurveyid' size='82' name='copysurveyid' value='' class="form-control" aria-describedby="copysurveyidHelp" />
                            <p class="form-control-static" id="copysurveyidHelp">
                                <span class='reg12'><?=  gT("Optional - Leave this field empty to assign a new ID automatically."); ?> </span>
                            </p>
                        </div>

                        <fieldset>
                            <legend  class="form-label" ><?php eT("Select the elements to include:"); ?> </legend>
                            <!-- Convert resource links -->
                            <div class="mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-clear-select-all"
                                        data-text-clear="<?php eT('Clear all'); ?>"
                                        data-text-select="<?php eT('Select all'); ?>"
                                        data-icon-clear="ri-close-circle-line"
                                        data-icon-select="ri-check-line">
                                    <i class="ri-close-circle-line"></i>
                                    <?php eT("Clear all"); ?>
                                </button>
                            </div>
                            <div>
                                <input id="copyResourcesAndLinks" name="copyResourcesAndLinks" type="checkbox" value="1" checked>
                                <label class=" form-label reg16" for='copyResourcesAndLinks'><?php eT("Survey resource files and adapt links"); ?> </label>
                            </div>

                            <!-- Exclude quotas -->
                            <div >
                                <input id="copySurveyQuotas" name="copySurveyQuotas" type="checkbox" value="1" checked>
                                <label class=" form-label reg16" for='copySurveyQuotas'><?php eT("Survey quotas"); ?> </label>
                            </div>

                            <!-- Exclude survey permissions -->
                            <div>
                                <input id="copySurveyPermissions" name="copySurveyPermissions" type="checkbox" value="1" checked>
                                <label class=" form-label reg16" for='copySurveyPermissions'><?php eT("Survey permissions"); ?> </label>
                            </div>

                            <!-- include answers -->
                            <div>
                                <input id="copyAnswerOptions" name="copyAnswerOptions" type="checkbox" value="1" checked>
                                <label class=" form-label reg16" for='copyAnswerOptions'><?php eT("Answer options from the original survey"); ?> </label>
                            </div>

                            <!-- Reset conditions/relevance -->
                            <div>
                                <input id="copySurveyConditions" name="copySurveyConditions" type="checkbox" value="1" checked>
                                <label class=" form-label reg16" for='copySurveyConditions'><?php eT("Survey conditions"); ?> </label>
                            </div>

                            <!-- Reset start/end date/time -->
                            <div>
                                <input id="copyStartEndDate" name="copyStartEndDate" type="checkbox" value="1" checked>
                                <label class=" form-label reg16" for='copyStartEndDate'><?php eT("Start/end date/time"); ?> </label>
                            </div>

                            <div>
                                <input id="resetResponseStartId" name="resetResponseStartId" type="checkbox" value="1" checked>
                                <label class=" form-label reg16" for='resetResponseStartId'><?php eT("Reset response start ID"); ?> </label>
                            </div>
                        </fieldset>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                <input type='submit' id="copy-submit" class="btn btn-info" style="min-width:25%" value='<?php eT("Copy survey"); ?>' />
            </div>
            </form>
        </div>
    </div>
</div>

