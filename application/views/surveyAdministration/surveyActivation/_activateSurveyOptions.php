<?php
/** @var $aSurveysettings array */
/** @var  $oSurvey Survey */
/** @var bool $closeAccessMode */

$optionsOnOff = ['Y' => gT('On'), 'N' => gT('Off')];
?>
<div class="row">
    <div class='col-md-12'>
        <?php // Plain text with the look of a heading: it does not introduce a section of the dialog ?>
        <p class="h2"><?php eT("Please keep in mind:"); ?></p>
        <p class="h2"><?php eT("Once a survey has been activated you can no longer add or delete questions, question groups or subquestions.") ?> </p>
        <p>
            <?php eT("Editing questions, question groups or subquestions is still possible. The following settings cannot be changed once a survey has been activated.", 'unescaped'); ?>
        </p>
    </div>
</div>

<?php if ($oSurvey->getIsDateExpired()) : ?>
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
            <?php // Label of the radio button group below (there is no single field it could be a <label> for) ?>
            <span class="form-label" id="activate-anonymized-label"><?php eT("Anonymized responses"); ?></span>
            <?php // A button, so the tooltip is also shown with the keyboard (on focus); its text is the name of the button ?>
            <button type="button" class="btn btn-sm btn-link p-0 align-baseline text-body"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, responses will be anonymized - there will be no way to connect responses and participants."); ?>"
               aria-label="<?= gT("If enabled, responses will be anonymized - there will be no way to connect responses and participants."); ?>"
            ><i class="ri-information-line" aria-hidden="true"></i></button>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'anonymized',
                    'ariaLabelledBy' => 'activate-anonymized-label',
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
            <?php // Label of the radio button group below (there is no single field it could be a <label> for) ?>
            <span class="form-label" id="activate-datestamp-label"><?php eT("Date stamp"); ?></span>
            <?php // A button, so the tooltip is also shown with the keyboard (on focus); its text is the name of the button ?>
            <button type="button" class="btn btn-sm btn-link p-0 align-baseline text-body"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the submission time of a response will be recorded."); ?>"
               aria-label="<?= gT("If enabled, the submission time of a response will be recorded."); ?>"
            ><i class="ri-information-line" aria-hidden="true"></i></button>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'datestamp',
                    'ariaLabelledBy' => 'activate-datestamp-label',
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
            <?php // Label of the radio button group below (there is no single field it could be a <label> for) ?>
            <span class="form-label" id="activate-ipaddr-label"><?php eT("Save IP address"); ?></span>
            <?php // A button, so the tooltip is also shown with the keyboard (on focus); its text is the name of the button ?>
            <button type="button" class="btn btn-sm btn-link p-0 align-baseline text-body"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the IP address of the survey respondent will be stored together with the response."); ?>"
               aria-label="<?= gT("If enabled, the IP address of the survey respondent will be stored together with the response."); ?>"
            ><i class="ri-information-line" aria-hidden="true"></i></button>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'ipaddr',
                    'ariaLabelledBy' => 'activate-ipaddr-label',
                    'checkedOption' => $aSurveysettings['ipaddr'],
                    'selectOptions' => $optionsOnOff
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class="ex-form-group mb-3">
            <?php // Label of the radio button group below (there is no single field it could be a <label> for) ?>
            <span class="form-label" id="activate-ipanonymize-label"><?php eT("Anonymize IP address"); ?></span>
            <?php // A button, so the tooltip is also shown with the keyboard (on focus); its text is the name of the button ?>
            <button type="button" class="btn btn-sm btn-link p-0 align-baseline text-body"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the IP address of the respondent is not recorded."); ?>"
               aria-label="<?= gT("If enabled, the IP address of the respondent is not recorded."); ?>"
            ><i class="ri-information-line" aria-hidden="true"></i></button>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'ipanonymize',
                    'ariaLabelledBy' => 'activate-ipanonymize-label',
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
            <?php // Label of the radio button group below (there is no single field it could be a <label> for) ?>
            <span class="form-label" id="activate-savetimings-label"><?php eT("Save timings"); ?></span>
            <?php // A button, so the tooltip is also shown with the keyboard (on focus); its text is the name of the button ?>
            <button type="button" class="btn btn-sm btn-link p-0 align-baseline text-body"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the time spent on each page of the survey by each survey participant is recorded."); ?>"
               aria-label="<?= gT("If enabled, the time spent on each page of the survey by each survey participant is recorded."); ?>"
            ><i class="ri-information-line" aria-hidden="true"></i></button>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'savetimings',
                    'ariaLabelledBy' => 'activate-savetimings-label',
                    'checkedOption' => $aSurveysettings['savetimings'],
                    'selectOptions' => $optionsOnOff
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class="ex-form-group mb-3">
            <?php // Label of the radio button group below (there is no single field it could be a <label> for) ?>
            <span class="form-label" id="activate-refurl-label"><?php eT("Save referrer URL"); ?></span>
            <?php // A button, so the tooltip is also shown with the keyboard (on focus); its text is the name of the button ?>
            <button type="button" class="btn btn-sm btn-link p-0 align-baseline text-body"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the referrer URL will be stored together with the response."); ?>"
               aria-label="<?= gT("If enabled, the referrer URL will be stored together with the response."); ?>"
            ><i class="ri-information-line" aria-hidden="true"></i></button>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'refurl',
                    'ariaLabelledBy' => 'activate-refurl-label',
                    'checkedOption' => $aSurveysettings['refurl'],
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
            <?php // Label of the radio button group below (there is no single field it could be a <label> for) ?>
            <span class="form-label" id="activate-savequotaexit-label"><?php eT("Save quota exit"); ?></span>
            <?php // A button, so the tooltip is also shown with the keyboard (on focus); its text is the name of the button ?>
            <button type="button" class="btn btn-sm btn-link p-0 align-baseline text-body"
               data-bs-toggle="tooltip"
               title="<?= gT("If enabled, the ID of the matched quota will be stored together with the response."); ?>"
               aria-label="<?= gT("If enabled, the ID of the matched quota will be stored together with the response."); ?>"
            ><i class="ri-information-line" aria-hidden="true"></i></button>
            <div class="">
                <?php
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'savequotaexit',
                    'ariaLabelledBy' => 'activate-savequotaexit-label',
                    'checkedOption' => $aSurveysettings['savequotaexit'],
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
        <p class="h3" id="activate-openAccessMode-label"><?php eT("Do you want your survey to be public for everyone (open-access mode) or invite only (closed-access mode)?"); ?></p>
        <div class='col-md-10' style="width: 100%;">
            <div class="mb-5" style="width: 100%;">
                <?php

                //only allow here to switch to close-access-mode (and to open-access-mode)
                //close-access-mode means that 'N' should be selected
                $optionsOnOff = ['Y' => gT('Open-access mode'), 'N' => gT('Closed-access mode')];
                $icons = ['Y' => 'ri-global-line', 'N' => 'ri-lock-2-line'];
                $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'name' => 'openAccessMode',
                    'ariaLabelledBy' => 'activate-openAccessMode-label',
                    'checkedOption' => $closeAccessMode ? 'N' : 'Y',
                    'selectOptions' => $optionsOnOff,
                    'htmlOptions' => ['icon' => $icons,
                    'style' => 'width: 100%']
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
