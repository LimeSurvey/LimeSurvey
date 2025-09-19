<?php
/**
 * Publication Panel
 * @var AdminController $this
 * @var Survey $oSurvey
 * @var array $dateformatdetails
 */
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPublicationOptions');

 App()->getClientScript()->registerScript("publication-panel-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
    var expirationLowerThanStartError = '" . gT("Expiration date can't be lower than the start date") . "';
", LSYii_ClientScript::POS_BEGIN);
?>
<!-- Publication panel -->
<div id='publication-panel'>
    <?php if ($bShowAllOptions === true){ ?>
    <div class="row">
            <h1><?php eT("Publication date"); ?></h1>
            <!-- Start date/time -->
            <div class="col-lg-3 mb-3">
                <label class=" form-label" for='startdate'><?php  eT("Start date/time:"); ?></label>
                <div class=" has-feedback">
                    <?php Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                            'name' => "startdate",
                            'id' => 'startdate',
                            'value' => ($oSurvey->startdate ? date($dateformatdetails['phpdate']." H:i",strtotime((string) $oSurvey->startdate)) : ''),
                            'pluginOptions' => array(
                                'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                'allowInputToggle' =>true,
                                'showClear' => true,
                                'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                            )
                        ));
                    ?>
                </div>
            </div>

            <!-- Expiry date/time -->
            <div class="col-lg-3 mb-3">
                <label class=" form-label" for='expires'><?php  eT("Expiry date/time:"); ?></label>
                <div class=" has-feedback">
                    <?php Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                            'name' => "expires",
                            'id' => 'expires',
                            'value' => ($oSurvey->expires ? date($dateformatdetails['phpdate']." H:i",strtotime((string) $oSurvey->expires)) : ''),
                            'pluginOptions' => array(
                                'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                'allowInputToggle' =>true,
                                'showClear' => true,
                                'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                            )
                        ));
                    ?>
                </div>
            </div>
    </div>
    <?php } ?>
    <div>
            <!-- List survey publicly -->
            <div class="mb-3 mt-4">
                <h1 role="heading" aria-level="2"><?php eT("Access control"); ?></h1>
                <label class=" form-label" for='listpublic'><?php printf(gT("Link survey on %spublic index page%s:"), "<a href='" . Yii::app()->getConfig("publicurl") . "' target='_blank' >", "</a>");?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'listpublic',
                        'checkedOption' => $oSurvey->listpublic,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->listpublic . " ᴵ" ])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>
            <!-- Set cookie to prevent repeated participation -->
            <div class="mb-3">
                <label class=" form-label" for='usecookie'><?php eT("Set cookie to prevent repeated participation:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'usecookie',
                        'checkedOption' => $oSurvey->usecookie,
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->usecookie . " ᴵ" ])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>

            <!-- Use CAPTCHA for survey access -->
            <?php $usecap = $oSurvey->usecaptcha; // Just a short-hand 
            $aCaptchaSurveyAccessYes        = array('A', 'B', 'C', 'X', 'F', 'H', 'K', 'O', 'T');
            $aCaptchaSurveyAccessInherit    = array('E', 'G', 'I', 'J', 'L', 'M', '1', '2', '4');
            $aCaptchaRegistrationYes        = array('A', 'B', 'D', 'R', 'F', 'G', 'I', 'M', 'U');
            $aCaptchaRegistrationInherit    = array('E', 'H', 'J', 'K', 'O', 'P', '1', '3', '6');
            $aCaptchaLoadSaveYes            = array('A', 'C', 'D', 'S', 'G', 'H', 'J', 'L', 'P');
            $aCaptchaLoadSaveInherit        = array('E', 'F', 'I', 'K', 'T', 'U', '2', '3', '5');
            
            ?>
            <div class="mb-3 mt-4">
                <h1><?php eT("CAPTCHA"); ?></h1>
                <label class=" form-label" for='usecaptcha_surveyaccess'><?php  eT("Use CAPTCHA for survey access:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'usecaptcha_surveyaccess',
                        'checkedOption'         => (in_array($usecap, $aCaptchaSurveyAccessYes))
                            ? 'Y'
                            : ((in_array($usecap, $aCaptchaSurveyAccessInherit)) ? ('I') : ('N')),
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->useCaptchaSurveyAccess . " ᴵ" ])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>

            <!-- Use CAPTCHA for registration -->
            <div class="mb-3">
                <label class=" form-label" for='usecaptcha_registration'><?php  eT("Use CAPTCHA for registration:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'usecaptcha_registration',
                        'checkedOption' => (in_array($usecap, $aCaptchaRegistrationYes))
                            ? 'Y'
                            : ((in_array($usecap, $aCaptchaRegistrationInherit))
                                ? ('I')
                                : ('N')),
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->useCaptchaRegistration . " ᴵ"])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>

            <!-- Use CAPTCHA for save and load -->
            <div class="mb-3">
                <label class=" form-label" for='usecaptcha_saveandload'><?php  eT("Use CAPTCHA for save and load:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'usecaptcha_saveandload',
                        'checkedOption' => (in_array($usecap, $aCaptchaLoadSaveYes))
                            ? 'Y'
                            : ((in_array($usecap, $aCaptchaLoadSaveInherit)) ? ('I') : ('N')),
                        'selectOptions' => ($bShowInherited)
                            ? array_merge($optionsOnOff, ['I' => $oSurveyOptions->useCaptchaSaveAndLoad . " ᴵ" ])
                            : $optionsOnOff,
                    ]); ?>
                </div>
            </div>
            <?php if (!extension_loaded('gd')) { ?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => '<strong>' . gT('Warning!') . '</strong> ' . gT("The CAPTCHA settings won't have any effect because you don't have the required GD library activated in your PHP configuration."),
                    'type' => 'warning',
                ]);
                ?>
            <?php }?>
    </div>
</div>
<?php $this->renderPartial('/surveyAdministration/_inherit_sub_footer'); ?>


