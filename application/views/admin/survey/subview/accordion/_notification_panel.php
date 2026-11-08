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
    "2" => gT("Survey ID/group ID", 'unescaped')
);
?>
<!-- Notification panel -->
<div id='notification-panel'>
    <?php if ($oSurvey->isActive) : ?>
        <div class="row ls-panelboxes mb-5">
            <div class="col-12 col-lg-6 ls-flex-column">
                <div class="card card-primary ">
                    <div class="card-body d-flex">
                        <?php eT("Please note that you need to deactivate this survey if you want to change the disabled settings below."); ?>
                    </div>
                    <div class="card-footer d-flex">
                        <a class="btn btn-danger" href="<?php echo $this->createUrl("surveyAdministration/deactivate/surveyid/$oSurvey->sid"); ?>" role="button">
                            <?php eT("Stop survey"); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <div class="row">
        <div class="col-12 col-lg-6 position-relative">
            <!-- Date Stamp -->
            <div class="ex-form-group mb-3">
                <label id="datestamp-label" class="form-label"><?php eT("Date stamp:"); ?></label>
                <div class="">
                    <?php
                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'datestamp',
                        'ariaLabelledBy' => 'datestamp-label',
                        'checkedOption'         => $oSurvey->datestamp,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->datestamp . " ᴵ"])
                            : $optionsOnOff
                    ]);
                    ?>
                </div>
            </div>

            <!-- Save IP Address -->
            <div class="ex-form-group mb-3">
                <label id="ipaddr-label" class="form-label"><?php eT("Save IP address:"); ?></label>
                <div class="">
                    <?php
                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'ipaddr',
                        'ariaLabelledBy' => 'ipaddr-label',
                        'checkedOption' => $oSurvey->ipaddr,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->ipaddr . " ᴵ"])
                            : $optionsOnOff,
                    ]);
                    ?>
                </div>
            </div>

            <!-- Save Anonymize IP Address -->
            <div class="ex-form-group mb-3">
                <label id="ipanonymize-label" class="form-label"><?php eT("Anonymize IP address:"); ?></label>
                <div>
                    <?php
                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'ipanonymize',
                        'ariaLabelledBy' => 'ipanonymize-label',
                        'checkedOption' => $oSurvey->ipanonymize,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->ipanonymize . " ᴵ"])
                            : $optionsOnOff,
                    ]);
                    ?>
                </div>
            </div>

            <!-- Save referrer URL -->
            <div class="ex-form-group mb-3">
                <label id="refurl-label" class="form-label"><?php eT("Save referrer URL:"); ?></label>
                <div>
                    <?php
                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'refurl',
                        'ariaLabelledBy' => 'refurl-label',
                        'checkedOption' => $oSurvey->refurl,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->refurl . " ᴵ"])
                            : $optionsOnOff,
                    ]);
                    ?>
                </div>
            </div>

            <!-- Save timings -->
            <div class="ex-form-group mb-3">
                <label id="savetimings-label" class="form-label"><?php eT("Save timings:"); ?></label>
                <div class="">
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'savetimings',
                        'ariaLabelledBy' => 'savetimings-label',
                        'checkedOption' => $oSurvey->savetimings,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->savetimings . " ᴵ"])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>

            <!-- Save quota exit -->
            <div class="ex-form-group mb-3">
                <label id="savequotaexit-label" class="form-label"><?php eT("Save quota exit:"); ?></label>
                <div>
                    <?php
                    $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'savequotaexit',
                        'ariaLabelledBy' => 'savequotaexit-label',
                        'checkedOption' => $oSurvey->savequotaexit,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->savequotaexit . " ᴵ"])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>
            <?php if ($oSurvey->isActive) : ?>
                <div class="ls-option-disabled"></div>
            <?php endif; ?>
        </div>

        <div class="col-12 col-lg-6">

            <!-- Encryption method -->
            <?php
            $optionsEncryptionMethod = array(
                'B' => gT('Basic', 'unescaped'),
                'H' => gT('Hardened', 'unescaped'),
            );
            if ($bShowInherited) {
                $optionsEncryptionMethod['I'] = $oSurveyOptions->encryption_method . " ᴵ";
            }
            /* Hardened can not be used if a quota uses an encrypted question */
            $hardenedDisabledByQuota = $oSurvey instanceof Survey
                && $oSurvey->encryption_method !== 'H'
                && $oSurvey->hasQuotaOnEncryptedQuestion();
            if ($hardenedDisabledByQuota) {
                unset($optionsEncryptionMethod['H']);
                /* Inherit is hardened too : not available (except if current value) */
                if ($oSurvey->encryption_method !== 'I' && $oSurvey->getResolvedEncryptionMethod('I') === 'H') {
                    unset($optionsEncryptionMethod['I']);
                }
            }
            ?>
            <div class="ex-form-group mb-3">
                <label class="form-label" id='encryption_method-label' for='encryption_method'><?php eT("Encryption method:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'encryption_method',
                        'htmlOptions'   => [
                            'aria-labelledby' => 'encryption_method-label',
                            'aria-describedby' => 'encryption_method-help',
                            /* Responses are encrypted with this method : can not be changed when survey is active */
                            'disabled' => $oSurvey instanceof Survey && $oSurvey->isActive,
                        ],
                        'checkedOption' => $oSurvey->encryption_method,
                        'selectOptions' => $optionsEncryptionMethod,
                    ]); ?>
                </div>
                <div class="form-text"  id='encryption_method-help'>
                    <?php eT("Basic encryption allows filtering and searching in browse responses, as well as statistics generation. Hardened encryption uses stronger cryptographic methods but disables all statistics and filtering features."); ?>
                    <?php eT("With hardened encryption, encrypted questions cannot be used in quotas."); ?>
                    <?php if ($oSurvey instanceof Survey && $oSurvey->isActive) {
                        eT("The encryption method cannot be changed while the survey is active.");
                    } ?>
                </div>
                <?php if ($hardenedDisabledByQuota) {
                    $hardenedAlertText = gT("Hardened encryption is not available: at least one quota uses an encrypted question.");
                    if ($oSurvey->encryption_method === 'B' && $oSurvey->getResolvedEncryptionMethod('I') === 'H') {
                        /* Forced to Basic instead of inherited hardened */
                        $hardenedAlertText .= " " . gT("Basic encryption is used instead of the inherited hardened encryption.");
                    }
                    App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                        'text' => $hardenedAlertText,
                        'type' => 'warning',
                        'htmlOptions' => ['class' => 'mt-2'],
                    ]);
                } ?>
                <?php
                /* Existing participants are not re-encrypted : survey not active with a participant list, or group / global settings (inherited by surveys) */
                if (!($oSurvey instanceof Survey) || (!$oSurvey->isActive && $oSurvey->hasTokensTable)) {
                    App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                        'text' => $oSurvey instanceof Survey
                            ? gT("Changing the encryption method does not re-encrypt the existing survey participants. After a change, export and re-import the survey participant list, otherwise filters and duplicate checks can miss participants encrypted with the previous method.")
                            : gT("Changing the encryption method does not re-encrypt the existing survey participants of the surveys inheriting it. After a change, export and re-import the survey participant list of these surveys, otherwise filters and duplicate checks can miss participants encrypted with the previous method."),
                        'type' => 'warning',
                        'htmlOptions' => ['class' => 'mt-2'],
                    ]);
                }
                ?>
            </div>

            <!-- Enable assessment mode -->
            <div class="ex-form-group mb-3">
                <label id="assessments-label" class="form-label"><?php eT("Enable assessment mode:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'assessments',
                        'ariaLabelledBy' => 'assessments-label',
                        'checkedOption' => $oSurvey->assessments,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->assessments . " ᴵ"])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>

            <!-- Participant may save and resume  -->
            <div class="ex-form-group mb-3">
                <label id="allowsave-label" class="form-label"><?php eT("Participant may save and resume later:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'allowsave',
                        'ariaLabelledBy' => 'allowsave-label',
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
                        <label id="emailnotificationto-label" class=" form-label" for='emailnotificationto'><?php eT("Send basic admin notification email to:"); ?></label>
                        <input class="form-control inherit-edit 
                            <?php echo ($bShowInherited && $emailnotificationto === 'inherit' ? 'd-none' : 'd-block'); ?>" type='
                            <?php echo ($bShowInherited) ? 'text' : 'email'; ?>' size='50' id='emailnotificationto' name='emailnotificationto'
                            value="<?php echo htmlspecialchars((string) $emailnotificationto); ?>" data-inherit-value="inherit"
                            data-saved-value="<?php echo htmlspecialchars((string) $emailnotificationto); ?>" />
                        <input class="form-control inherit-readonly
                         <?php echo ($bShowInherited && $emailnotificationto === 'inherit' ? 'd-block' : 'd-none'); ?>" type='text' size='50' value="
                         <?= htmlspecialchars((string) $oSurveyOptions->emailnotificationto ?? '') ?>" readonly />
                    </div>
                    <div class="col-12 col-lg-4 content-right <?php echo ($bShowInherited ? 'd-block' : 'd-none'); ?>">
                        <label id="emailnotificationtobutton-label" class="form-label content-center col-12"><?php eT("Inherit:"); ?></label>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'emailnotificationtobutton',
                            'ariaLabelledBy' => 'emailnotificationto-label emailnotificationtobutton-label',
                            'ariaLabel' => gT('Inherit:'),
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
                        <label id="emailresponseto-label" class=" form-label" for='emailresponseto'><?php eT("Send detailed admin notification email to:"); ?></label>
                        <input class="form-control inherit-edit
                            <?php echo ($bShowInherited && $emailresponseto === 'inherit' ? 'd-none' : 'd-block'); ?>" type='
                            <?php echo ($bShowInherited) ? 'text' : 'email'; ?>' size='50' id='emailresponseto' name='emailresponseto'
                            value="<?php echo htmlspecialchars((string) $emailresponseto); ?>" data-inherit-value="inherit"
                            data-saved-value="<?php echo htmlspecialchars((string) $emailresponseto); ?>" />
                        <input class="form-control inherit-readonly 
                            <?php echo ($bShowInherited && $emailresponseto === 'inherit' ? 'd-block' : 'd-none'); ?>" type='text' size='50' value="
                            <?= htmlspecialchars((string) $oSurveyOptions->emailresponseto ?? '') ?>" readonly />
                    </div>
                    <div class="col-12 col-lg-4 content-right <?php echo ($bShowInherited ? 'd-block' : 'd-none'); ?>">
                        <label id="emailresponsetobutton-label" class="form-label content-center col-12"><?php eT("Inherit:"); ?></label>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'emailresponsetobutton',
                            'ariaLabelledBy' => 'emailresponseto-label emailresponsetobutton-label',
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
                    <label id="googleanalyticsapikeysetting-label" class="form-label">
                        <?php echo gT('Google Analytics settings:'); ?>
                    </label>
                    <div>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'googleanalyticsapikeysetting',
                            'ariaLabelledBy' => 'googleanalyticsapikeysetting-label',
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
                    <label id="googleanalyticsstyle-label" class="form-label"><?php eT("Google Analytics style:"); ?></label>
                    <div>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'googleanalyticsstyle',
                            'ariaLabelledBy' => 'googleanalyticsstyle-label',
                            'checkedOption' => $oSurvey->googleanalyticsstyle,
                            'selectOptions' => $googleAnalyticsStyleOptions,
                        ]); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php $this->renderPartial('/surveyAdministration/_inherit_sub_footer'); ?>
<?php App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'survey_edit_notificationpanel.js', LSYii_ClientScript::POS_BEGIN); ?>
