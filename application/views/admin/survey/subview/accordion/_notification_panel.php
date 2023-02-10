<?php

/**
 * Notificatin panel
 * @var AdminController $this
 * @var Survey $oSurvey
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyNotificationOptions');

App()->getClientScript()->registerScript("notification-panel-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '" . gT("If you are using token functions or notifications emails you need to set an administrator email address.", 'js') . "'
    var sURLParameters = '';
    var sAddParam = '';
", LSYii_ClientScript::POS_BEGIN);

$googleAnalyticsOptions = array(
    "N" => gT("None", 'unescaped'),
    "Y" => gT("Use settings below", 'unescaped'),
    "G" => gT("Use global settings", 'unescaped')
);
$googleAnalyticsStyleOptions = array(
    "0" => gT("Off", 'unescaped'),
    "1" => gT("Default", 'unescaped'),
    "2" => gT("Survey-SID/Group", 'unescaped')
);
?>
<!-- Notification panel -->
<div id='notification-panel'>
    <div class="row">
        <div class="col-12 col-lg-6">
            <!-- Date Stamp -->
            <div class="ex-form-group mb-3">
                <label class=" form-label" for='datestamp'><?php eT("Date stamp:"); ?></label>
                <div class="">
                    <?php if ($oSurvey->isActive) { ?>
                        <?php if ($oSurvey->datestamp != "Y") {
                            eT("Responses will not be date stamped.");
                        } else {
                            eT("Responses will be date stamped.");
                        } ?>
                        <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                        <?php echo CHtml::hiddenField('datestamp', $oSurvey->datestamp); // Maybe use a readonly dropdown? 
                        ?>
                    <?php } else {
                        $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'datestamp',
                            'checkedOption'         => $oSurvey->datestamp,
                            'selectOptions' => ($bShowInherited)
                                ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->datestamp . " ᴵ"])
                                : $optionsOnOff
                        ]);
                    } ?>
                </div>
            </div>
            <!-- Save IP Address -->
            <div class="ex-form-group mb-3">
                <label class=" form-label" for='ipaddr'><?php eT("Save IP address:"); ?></label>
                <div class="">
                    <?php if ($oSurvey->isActive) {
                        if ($oSurvey->ipaddr != "Y") {
                            eT("Responses will not have the IP address logged.");
                        } else {
                            eT("Responses will have the IP address logged.");
                        } ?>
                        <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                    <?php echo CHtml::hiddenField('ipaddr', $oSurvey->ipaddr);
                    } else {
                        $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'ipaddr',
                            'checkedOption' => $oSurvey->ipaddr,
                            'selectOptions' => ($bShowInherited)
                                ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->ipaddr . " ᴵ"])
                                : $optionsOnOff,
                        ]);
                    } ?>
                </div>
            </div>
            <div class="ex-form-group mb-3">
                <label class=" form-label" for='ipanonymize'><?php eT("Anonymize IP address:"); ?></label>
                <div>
                    <?php if ($oSurvey->isActive) {
                        if ($oSurvey->ipanonymize != "Y") {
                            eT("Responses will not have the IP address anonymized.");
                        } else {
                            eT("Responses will have the IP address anonymized.");
                        } ?>
                        <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                    <?php echo CHtml::hiddenField('ipanonymize', $oSurvey->ipanonymize);
                    } else {
                        // <!-- only visible if ipaddr is set to yes in db or switch to yes -->
                        $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'ipanonymize',
                            'checkedOption' => $oSurvey->ipanonymize,
                            'selectOptions' => ($bShowInherited)
                                ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->ipanonymize . " ᴵ"])
                                : $optionsOnOff,
                        ]);
                    } ?>
                </div>
            </div>

            <!-- Save referrer URL -->
            <div class="ex-form-group mb-3">
                <label class=" form-label" for='refurl'><?php eT("Save referrer URL:"); ?></label>
                <div>
                    <?php if ($oSurvey->isActive) { ?>
                        <?php if ($oSurvey->refurl != "Y") {
                            eT("Responses will not have their referring URL logged.");
                        } else {
                            eT("Responses will have their referring URL logged.");
                        } ?>
                        <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                        <?php echo CHtml::hiddenField('refurl', $oSurvey->refurl); ?>
                    <?php } else {
                        $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'refurl',
                            'checkedOption' => $oSurvey->refurl,
                            'selectOptions' => ($bShowInherited)
                                ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->refurl . " ᴵ"])
                                : $optionsOnOff,
                        ]);
                    } ?>
                </div>
            </div>

            <!-- Save timings -->
            <div class="ex-form-group mb-3">
                <label class=" form-label" for='savetimings'><?php eT("Save timings:"); ?></label>
                <div class="">
                    <?php if ($oSurvey->isActive) : ?>
                        <?php if ($oSurvey->savetimings != "Y") : ?>
                            <?php eT("Timings will not be saved."); ?>
                        <?php else : ?>
                            <?php eT("Timings will be saved."); ?>
                            <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                            <?php echo CHtml::hiddenField('savetimings', $oSurvey->savetimings);  // Maybe use a readonly dropdown? 
                            ?>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'savetimings',
                            'checkedOption' => $oSurvey->savetimings,
                            'selectOptions' => ($bShowInherited)
                                ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->savetimings . " ᴵ"])
                                : $optionsOnOff,
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">

            <!-- Enable assessment mode -->
            <div class="ex-form-group mb-3">
                <label class=" form-label" for='assessments'><?php eT("Enable assessment mode:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'assessments',
                        'checkedOption' => $oSurvey->assessments,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->assessments . " ᴵ"])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>

            <!-- Participant may save and resume  -->
            <div class="ex-form-group mb-3">
                <label class=" form-label" for='allowsave'><?php eT("Participant may save and resume later:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'allowsave',
                        'checkedOption' => $oSurvey->allowsave,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->allowsave . " ᴵ"])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>
            <!-- email basic to -->
            <div class="ex-form-group mb-3">
                <?php $emailnotificationto = $oSurvey->emailnotificationto; ?>
                <div class="row">
                    <div class="col-12 col-lg-8 content-right">
                        <label class=" form-label" for='emailnotificationto'><?php eT("Send basic admin notification email to:"); ?></label>
                        <input class="form-control inherit-edit <?php echo ($bShowInherited && $emailnotificationto === 'inherit' ? 'd-none' : 'd-block'); ?>" type='<?php echo ($bShowInherited) ? 'text' : 'email'; ?>' size='50' id='emailnotificationto' name='emailnotificationto' value="<?php echo htmlspecialchars($emailnotificationto); ?>" data-inherit-value="inherit" data-saved-value="<?php echo $emailnotificationto; ?>" />
                        <input class="form-control inherit-readonly <?php echo ($bShowInherited && $emailnotificationto === 'inherit' ? 'd-block' : 'd-none'); ?>" type='text' size='50' value="<?= htmlspecialchars($oSurveyOptions->emailnotificationto ?? '') ?>" readonly />
                    </div>
                    <div class="col-12 col-lg-4 content-right <?php echo ($bShowInherited ? 'd-block' : 'd-none'); ?>">
                        <label class=" form-label content-center col-12" for='emailnotificationto'><?php eT("Inherit:"); ?></label>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'emailnotificationtobutton',
                            'checkedOption' => ($bShowInherited && $emailnotificationto === 'inherit' ? 'Y' : 'N'),
                            'selectOptions' => $optionsOnOff,
                            'htmlOptions'   => [
                                'class' => 'text-option-inherit'
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>

            <!-- email detail to  -->
            <div class="ex-form-group mb-3">
                <?php $emailresponseto = $oSurvey->emailresponseto; ?>
                <div class="row">
                    <div class="col-12 col-lg-8 content-right">
                        <label class=" form-label" for='emailresponseto'><?php eT("Send detailed admin notification email to:"); ?></label>
                        <input class="form-control inherit-edit <?php echo ($bShowInherited && $emailresponseto === 'inherit' ? 'd-none' : 'd-block'); ?>" type='<?php echo ($bShowInherited) ? 'text' : 'email'; ?>' size='50' id='emailresponseto' name='emailresponseto' value="<?php echo htmlspecialchars($emailresponseto); ?>" data-inherit-value="inherit" data-saved-value="<?php echo $emailresponseto; ?>" />
                        <input class="form-control inherit-readonly <?php echo ($bShowInherited && $emailresponseto === 'inherit' ? 'd-block' : 'd-none'); ?>" type='text' size='50' value="<?= htmlspecialchars($oSurveyOptions->emailresponseto ?? '') ?>" readonly />
                    </div>
                    <div class="col-12 col-lg-4 content-right <?php echo ($bShowInherited ? 'd-block' : 'd-none'); ?>">
                        <label class=" form-label content-center col-12" for='emailresponseto'><?php eT("Inherit:"); ?></label>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'emailresponsetobutton',
                            'checkedOption' => ($bShowInherited && $emailresponseto === 'inherit' ? 'Y' : 'N'),
                            'selectOptions' => $optionsOnOff,
                            'htmlOptions'   => [
                                'class' => 'text-option-inherit'
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>

            <?php if ($bShowAllOptions === true) { ?>
                <!-- GoogleAnalytics settings to be used -->
                <div class="ex-form-group mb-3">
                    <label class=" form-label" for="googleanalyticsapikeysetting">
                        <?php echo gT('Google Analytics settings:'); ?>
                    </label>
                    <div>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'googleanalyticsapikeysetting',
                            'checkedOption' => $oSurvey->googleanalyticsapikeysetting,
                            'selectOptions' => $googleAnalyticsOptions,
                        ]); ?>
                    </div>
                </div>
                <!-- Google Analytics -->
                <div class="ex-form-group mb-3">
                    <label class=" form-label" for='googleanalyticsapikey'><?php eT("Google Analytics Tracking ID:"); ?></label>
                    <div class="">
                        <?php echo CHtml::textField(
                            'googleanalyticsapikey',
                            $oSurvey->googleanalyticsapikey,
                            array('size' => 20, 'class' => 'form-control')
                        ); ?>
                    </div>
                </div>
                <!-- Google Analytics style -->
                <div class="ex-form-group mb-3">
                    <label class=" form-label" for='googleanalyticsstyle'><?php eT("Google Analytics style:"); ?></label>
                    <div>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'googleanalyticsstyle',
                            'checkedOption' => $oSurvey->googleanalyticsstyle,
                            'selectOptions' => $googleAnalyticsStyleOptions,
                        ]); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php $this->renderPartial('_sub_footer'); ?>
<?php App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'survey_edit_notificationpanel.js', LSYii_ClientScript::POS_BEGIN); ?>