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
                            <span class='text-info'><?php echo  gT("Optional - Leave this field empty to assign a new ID automatically"); ?> </span>
                        </p>
                    </div>

                    <div class="row">
                        <label class=" form-label" ><?php echo  eT("Select the elements to include:"); ?> </label>
                        <!-- Convert resource links -->
                        <div class="form-check">
                            <input id="ytcopysurveytranslinksfields" name="copysurveytranslinksfields" type="hidden" value="0" >
                            <input id="copysurveytranslinksfields" name="copysurveytranslinksfields" type="checkbox" value="1" checked>
                            <label class=" form-label" for='copysurveytranslinksfields'><?php echo  eT("Survey resource files and adapt links"); ?> </label>
                        </div>

                        <!-- Exclude quotas -->
                        <div class="form-check">
                            <input id="ytcopysurveyexcludequotas" name="copysurveyexcludequotas" type="hidden" value="0" checked>
                            <input id="copysurveyexcludequotas" name="copysurveyexcludequotas" type="checkbox" value="1">
                            <label class=" form-label" for='copysurveyexcludequotas'><?php echo  eT("Exclude quotas"); ?> </label>
                        </div>

                        <!-- Exclude survey permissions -->
                        <div class="form-check">
                            <input id="ytcopysurveyexcludepermissions" name="copysurveyexcludepermissions" type="hidden" value="0" checked>
                            <input id="copysurveyexcludepermissions" name="copysurveyexcludepermissions" type="checkbox" value="1">
                            <label class=" form-label" for='copysurveyexcludepermissions'><?php echo  eT("Exclude survey permissions"); ?> </label>
                        </div>

                        <!-- Exclude answers -->
                        <div class="form-check">
                            <input id="ytcopysurveyexcludeanswers" name="copysurveyexcludeanswers" type="hidden" value="0" checked>
                            <input id="copysurveyexcludeanswers" name="copysurveyexcludeanswers" type="checkbox" value="1">
                            <label class=" form-label" for='copysurveyexcludeanswers'><?php echo  eT("Exclude answers"); ?> </label>
                        </div>

                        <!-- Reset conditions/relevance -->
                        <div class="form-check">
                            <input id="ytcopysurveyresetconditions" name="copysurveyresetconditions" type="hidden" value="0" checked>
                            <input id="copysurveyresetconditions" name="copysurveyresetconditions" type="checkbox" value="1">
                            <label class=" form-label" for='copysurveyresetconditions'><?php echo  eT("Reset conditions"); ?> </label>
                        </div>

                        <!-- Reset start/end date/time -->
                        <div class="form-check">
                            <input id="ytcopysurveyresetstartenddate" name="copysurveyresetstartenddate" type="hidden" value="0" checked>
                            <input id="copysurveyresetstartenddate" name="copysurveyresetstartenddate" type="checkbox" value="1">
                            <label class=" form-label" for='copysurveyresetstartenddate'><?php echo  eT("Reset start/end date/time"); ?> </label>
                        </div>

                        <div class="form-check">
                            <input id="ytcopysurveyresetresponsestartid" name="copysurveyresetresponsestartid" type="hidden" value="0" checked>
                            <input id="copysurveyresetresponsestartid" name="copysurveyresetresponsestartid" type="checkbox" value="1">
                            <label class=" form-label" for='copysurveyresetresponsestartid'><?php echo  eT("Reset response start ID"); ?> </label>
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

