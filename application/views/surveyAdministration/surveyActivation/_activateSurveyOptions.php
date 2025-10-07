<?php
/** @var $aSurveysettings array */
/** @var  $oSurvey Survey */
/** @var bool $closeAccessMode */

$optionsOnOff = ['Y' => gT('On'), 'N' => gT('Off')];
?>
<div class="row">
    <div class='col-md-12'>
        <h2><?php eT("Please keep in mind:"); ?></h2>
        <h2><?php eT("Once a survey has been activated you can no longer add or delete questions, question groups or subquestions.") ?> </h2>
        <p>
            <?php eT("Editing questions, question groups or subquestions is still possible. The following settings cannot be changed once a survey has been activated.", 'unescaped'); ?>
        </p>
    </div>
</div>

<?php if ($oSurvey->getIsDateExpired()): ?>
    <div class="row">
        <div class='col-md-12'>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT('Note: This survey has a past expiration date configured and is currently not available to participants. Please remember to update/remove the expiration date in the survey settings after activation.'),
                'type' => 'info',
                'htmlOptions' => ['class' => 'controls']
            ]);
            ?>
        </div>
    </div>
<?php endif; ?>

<?php echo CHtml::form(
    ["surveyAdministration/activate/"],
    'post',
    array('class' => 'form-horizontal')
); ?>

<div class="row">
    <!-- Anonymized responses -->
    <div class='col-md-6'>
        <div class="ex-form-group mb-3">
            <label class=" form-label" for='anonymized'><?php eT("Anonymized responses"); ?></label>
            <i class="ri-information-line"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, responses will be anonymized - there will be no way to connect responses and participants."); ?>"
            ></i>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'anonymized',
                    'id' => 'activate_anonymized',
                    'checkedOption' => $aSurveysettings['anonymized'],
                    'selectOptions' => $optionsOnOff
                ]);
                ?>
            </div>
        </div>
    </div>

    <!-- Date Stamp -->
    <div class='col-md-6'>
        <div class="ex-form-group mb-3">
            <label class=" form-label" for='datestamp'><?php eT("Date stamp"); ?></label>
            <i class="ri-information-line"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the submission time of a response will be recorded."); ?>"
            ></i>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'datestamp',
                    'checkedOption' => $aSurveysettings['datestamp'],
                    'selectOptions' => $optionsOnOff
                ]);
                ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class='col-md-6'>
        <div class="ex-form-group mb-3">
            <label class=" form-label" for='ipaddr'><?php eT("Save IP address"); ?></label>
            <i class="ri-information-line"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the IP address of the survey respondent will be stored together with the response."); ?>"
            ></i>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'ipaddr',
                    'checkedOption' => $aSurveysettings['ipaddr'],
                    'selectOptions' => $optionsOnOff
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class="ex-form-group mb-3">
            <label class="form-label" for='ipanonymize'><?php eT("Anonymize IP address"); ?></label>
            <i class="ri-information-line"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the IP address of the respondent is not recorded."); ?>"
            ></i>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'ipanonymize',
                    'checkedOption' => $aSurveysettings['ipanonymize'],
                    'selectOptions' => $optionsOnOff
                ]);
                ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class='col-md-6'>
        <div class="ex-form-group mb-3">
            <label class="form-label" for='savetimings'><?php eT("Save timings"); ?></label>
            <i class="ri-information-line"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the time spent on each page of the survey by each survey participant is recorded."); ?>"
            ></i>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'savetimings',
                    'checkedOption' => $aSurveysettings['savetimings'],
                    'selectOptions' => $optionsOnOff
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class="ex-form-group mb-3">
            <label class="form-label" for='refurl'><?php eT("Save referrer URL"); ?></label>
            <i class="ri-information-line"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the referrer URL will be stored together with the response."); ?>"
            ></i>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'refurl',
                    'checkedOption' => $aSurveysettings['refurl'],
                    'selectOptions' => $optionsOnOff
                ]);
                ?>
            </div>
        </div>
    </div>
</div>

    <div class="row sub_footer">
        <div class="col-12 mt-5 mb-3">
            <div class="sub_footer_border"></div>
        </div>
        <h3><?php eT("Do you want your survey to be public for everyone (open-access mode) or invite only (closed-access mode)?"); ?></h3>
        <div class='col-md-10'>
            <div class="mb-5">
                <?php

                //only allow here to switch to close-access-mode (and to open-access-mode)
                //close-access-mode means that 'N' should be selected
                $optionsOnOff = ['Y' => gT('Open-access mode'), 'N' => gT('Closed-access mode')];
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'openAccessMode',
                    'checkedOption' => $closeAccessMode ? 'N' : 'Y',
                    'selectOptions' => $optionsOnOff
                ]);

                ?>
            </div>
        </div>
    </div>
    <?php
?>

<input type="hidden" name="surveyId" value="<?php echo $aSurveysettings['sid']; ?>">
<input type="submit" class="d-none" id="submitActivateSurvey">

</form>
