<?php

?>

<div id="copySurvey_modal" class="modal fade" role="dialog">
    <div class="modal-dialog import-modal">
        <!-- Modal content-->
        <div class="modal-content">
            <?php
            //modal header
            App()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Copy survey')]
            );
            ?>
            <?php echo CHtml::form(
                ['surveyAdministration/copy'],
            ) ?>
            <div class="modal-body" id="modal-body-copy-survey">

                    <!-- surveyIdToCopy -->
                    <input type='hidden' id='surveyIdToCopy' name='surveyIdToCopy' value='' class="form-control" />
                    <div class="row">
                        <!-- New survey ID -->
                        <label class=" form-label" for='copysurveyid'><?php echo  eT("New survey ID:"); ?> </label>
                        <input type='number' step="1" min="1" max="999999" id='copysurveyid' size='82' name='copysurveyid' value='' class="form-control" />
                        <p class="form-control-static">
                            <span class='reg12'><?php echo  gT("Optional - Leave this field empty to assign a new ID automatically"); ?> </span>
                        </p>
                    </div>

                    <div class="row">
                        <label class=" form-label semibold12" ><?php echo  eT("Select the elements to include:"); ?> </label>
                        <!-- Convert resource links -->
                        <div class="form-check">
                            <input name="copyResourcesAndLinks" type="checkbox" value="1" checked>
                            <label class=" form-label reg16" for='copyResourcesAndLinks'><?php echo  eT("Survey resource files and adapt links"); ?> </label>
                        </div>

                        <!-- Exclude quotas -->
                        <div class="form-check">
                            <input name="copySurveyQuotas" type="checkbox" value="1">
                            <label class=" form-label reg16" for='copySurveyQuotas'><?php echo  eT("Survey quotas"); ?> </label>
                        </div>

                        <!-- Exclude survey permissions -->
                        <div class="form-check">
                            <input name="copySurveyPermissions" type="checkbox" value="1">
                            <label class=" form-label reg16" for='copySurveyPermissions'><?php echo  eT("Survey permissions"); ?> </label>
                        </div>

                        <!-- include answers -->
                        <div class="form-check">
                            <input name="copyAnswerOptions" type="checkbox" value="1" checked>
                            <label class=" form-label reg16" for='copyAnswerOptions'><?php echo  eT("Answer options from the original survey"); ?> </label>
                        </div>

                        <!-- Reset conditions/relevance -->
                        <div class="form-check">
                            <input name="copySurveyConditions" type="checkbox" value="1">
                            <label class=" form-label reg16" for='copySurveyConditions'><?php echo  eT("Survey conditions"); ?> </label>
                        </div>

                        <!-- Reset start/end date/time -->
                        <div class="form-check">
                            <input name="resetStartEndDate" type="checkbox" value="1">
                            <label class=" form-label reg16" for='resetStartEndDate'><?php echo  eT("Start/end date/time"); ?> </label>
                        </div>

                        <div class="form-check">
                            <input name="resetResponseStartId" type="checkbox" value="1">
                            <label class=" form-label reg16" for='resetResponseStartId'><?php echo  eT("Reset response start ID"); ?> </label>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                <input type='submit' id="copy-submit" class="btn btn-info col-3" value='<?php eT("Copy survey"); ?>' />

                <!-- Submit -->
            </div>
            </form>
        </div>
    </div>
</div>

