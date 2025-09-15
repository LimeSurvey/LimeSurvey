<?php
/**
 * Copy survey
 */
?>
<div class="ls-flex-row">
    <div class="grow-10 ls-space padding left-10 right-10">
        <!-- copy survey form -->
        <?php echo CHtml::form(array('surveyAdministration/copy'), 'post', array('id'=>'copysurveyform', 'name'=>'copysurveyform', 'class'=>'form30 row')); ?>
            <div class="col-md-4">
                <!-- Select survey -->
                <label for='copysurveylist' class=" form-label"><?php  eT("Select survey to copy:"); ?> </label>
                <select id='copysurveylist' name='copysurveylist' required="required" class="form-select activate-search" aria-describedby="copy-servey-list-required">
                    <?php echo getSurveyList(false); ?>
                </select>
                <p class="form-control-static">
                    <span class='annotation text-danger' id="copy-servey-list-required"><?php echo  gT(" Survey to copy is Required"); ?> </span>
                </p>

                <!-- New survey title -->
                <label for='copysurveyname' class=" form-label"><?php echo  eT("New survey title:"); ?> </label>
                <input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' required="required" class="form-control" />
                <p class="form-control-static">
                    <span class='annotation text-danger'><?php echo  gT(" New survey title is Required"); ?> </span>
                </p>

                <!-- New survey ID -->
                <label class=" form-label" for='copysurveyid'><?php echo  eT("New survey ID:"); ?> </label>
                <input type='number' step="1" min="1" max="999999" id='copysurveyid' size='82' name='copysurveyid' value='' class="form-control" aria-describedby="optional1dsk" />
                <p class="form-control-static">
                    <span class='annotation text-info' id="optional1dsk"><?php echo  gT("Optional"); ?> </span>
                </p>
                            <?= gT("If the new survey ID is already used, a random one will be assigned."); ?> </span>
                <!-- Submit -->
                <div class="mt-3">
                    <input type='submit' class='btn btn-primary w-auto' value='<?php eT("Copy survey"); ?>' />
                    <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="' . $surveyid . '" />'; ?>
                    <input type='hidden' name='action' value='copysurvey' />
                </div>
            </div>

            <div class="col">
                <!-- Convert resource links -->
                <div class="form-check">
                    <input id="ytcopysurveytranslinksfields" name="copysurveytranslinksfields" type="hidden" value="0" >
                    <input id="copysurveytranslinksfields" name="copysurveytranslinksfields" type="checkbox" value="1" checked>
                    <label class=" form-label" for='copysurveytranslinksfields'><?php echo  eT("Copy survey resource files and adapt links"); ?> </label>
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
        </form>
    </div>
</div>

<script>
    $(document).on('ready pjax:scriptcomplete', function(){
        $('#copysurveyform').on('submit',  function(event){
            // Disable both buttons. Normally there's no need to re-enable them. The 'save-form-button' may already be disabled by it's onclick event.
            $('#copysurveyform').find('input[type="submit"]').prop('disabled', true);
            $('#save-form-button').addClass('disabled').attr('onclick', 'return false;');
        });
    });

</script>
