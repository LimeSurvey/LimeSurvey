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
", LSYii_ClientScript::POS_BEGIN);
?>
<!-- Publication panel -->
<div id='publication-panel' class="container-fluid">
    <div class="row">
    <?php if ($bShowAllOptions === true){ ?>
        <div class="col-sm-12 col-md-6">

            <!-- Start date/time -->
            <div class="form-group">
                <label class=" control-label" for='startdate'><?php  eT("Start date/time:"); ?></label>
                <div class=" has-feedback">
                    <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                            'name' => "startdate",
                            'id' => 'startdate',
                            'value' => ($oSurvey->startdate ? date($dateformatdetails['phpdate']." H:i",strtotime($oSurvey->startdate)) : ''),
                            'pluginOptions' => array(
                                'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                'allowInputToggle' =>true,
                                'showClear' => true,
                                'tooltips' => array(
                                    'clear'=> gT('Clear selection'),
                                    'prevMonth'=> gT('Previous month'),
                                    'nextMonth'=> gT('Next month'),
                                    'selectYear'=> gT('Select year'),
                                    'prevYear'=> gT('Previous year'),
                                    'nextYear'=> gT('Next year'),
                                    'selectDecade'=> gT('Select decade'),
                                    'prevDecade'=> gT('Previous decade'),
                                    'nextDecade'=> gT('Next decade'),
                                    'prevCentury'=> gT('Previous century'),
                                    'nextCentury'=> gT('Next century'),
                                    'selectTime'=> gT('Select time')
                                ),
                                'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                            )
                        ));
                    ?>
                </div>
            </div>

            <!-- Expiry date/time -->
            <div class="form-group">
                <label class=" control-label" for='expires'><?php  eT("Expiry date/time:"); ?></label>
                <div class=" has-feedback">
                    <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                            'name' => "expires",
                            'id' => 'expires',
                            'value' => ($oSurvey->expires ? date($dateformatdetails['phpdate']." H:i",strtotime($oSurvey->expires)) : ''),
                            'pluginOptions' => array(
                                'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                'allowInputToggle' =>true,
                                'showClear' => true,
                                'tooltips' => array(
                                    'clear'=> gT('Clear selection'),
                                    'prevMonth'=> gT('Previous month'),
                                    'nextMonth'=> gT('Next month'),
                                    'selectYear'=> gT('Select year'),
                                    'prevYear'=> gT('Previous year'),
                                    'nextYear'=> gT('Next year'),
                                    'selectDecade'=> gT('Select decade'),
                                    'prevDecade'=> gT('Previous decade'),
                                    'nextDecade'=> gT('Next decade'),
                                    'prevCentury'=> gT('Previous century'),
                                    'nextCentury'=> gT('Next century'),
                                        'selectTime'=> gT('Select time')
                                ),
                                'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                            )
                        ));
                    ?>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="col-sm-12 col-md-6">
            <!-- List survey publicly -->
            <div class="form-group">
                <label class=" control-label" for='listpublic'><?php  eT("List survey publicly:");?></label>
                <div class="">
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'listpublic',
                        'value'=> $oSurvey->listpublic,
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->listpublic . ']')): $optionsOnOff,
                        ));
                    ?>
                </div>
            </div>
            <!-- Set cookie to prevent repeated participation -->
            <div class="form-group">
                <label class=" control-label" for='usecookie'><?php  eT("Set cookie to prevent repeated participation:"); ?></label>
                <div class="">
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'usecookie',
                        'value'=> $oSurvey->usecookie,
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->usecookie . ']')): $optionsOnOff,
                        ));
                    ?>
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
            <div class="form-group">
                <label class=" control-label" for='usecaptcha_surveyaccess'><?php  eT("Use CAPTCHA for survey access:"); ?></label>
                <div class="">
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'usecaptcha_surveyaccess',
                        'value'=> (in_array($usecap, $aCaptchaSurveyAccessYes))?'Y':((in_array($usecap, $aCaptchaSurveyAccessInherit))?('I'):('N')),
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->useCaptchaSurveyAccess . ']')): $optionsOnOff,
                    ));
                    ?>
                </div>
            </div>

            <!-- Use CAPTCHA for registration -->
            <div class="form-group">
                <label class=" control-label" for='usecaptcha_registration'><?php  eT("Use CAPTCHA for registration:"); ?></label>
                <div class="">
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'usecaptcha_registration',
                        'value'=> (in_array($usecap, $aCaptchaRegistrationYes))?'Y':((in_array($usecap, $aCaptchaRegistrationInherit))?('I'):('N')),
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->useCaptchaRegistration . ']')): $optionsOnOff,
                    ));
                    ?>
                </div>
            </div>

            <!-- Use CAPTCHA for save and load -->
            <div class="form-group">
                <label class=" control-label" for='usecaptcha_saveandload'><?php  eT("Use CAPTCHA for save and load:"); ?></label>
                <div class="">
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'usecaptcha_saveandload',
                        'value'=> (in_array($usecap, $aCaptchaLoadSaveYes))?'Y':((in_array($usecap, $aCaptchaLoadSaveInherit))?('I'):('N')),
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->useCaptchaSaveAndLoad . ']')): $optionsOnOff,
                    ));
                    ?>
                </div>
            </div>
            <?php if(!extension_loaded('gd')) { ?>
                <div class="alert alert-warning " role="alert">
                    <p><strong><?php eT('Warning!'); ?></strong> <?php eT("The CAPTCHA settings won't have any effect because you don't have the required GD library activated in your PHP configuration."); ?></p>
                </div>
            <?php }?>
        </div>
    </div>
</div>
