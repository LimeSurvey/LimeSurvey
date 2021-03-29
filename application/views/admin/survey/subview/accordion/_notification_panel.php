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
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
", LSYii_ClientScript::POS_BEGIN);

$googleAnalyticsOptions = array(
    "N"=>gT("None",'unescaped'),
    "Y"=>gT("Use settings below",'unescaped'),
    "G"=>gT("Use global settings",'unescaped')
);
$googleAnalyticsStyleOptions = array(
    "0"=>gT("Off",'unescaped'),
    "1"=>gT("Default",'unescaped'),
    "2"=>gT("Survey-SID/Group",'unescaped')
);
?>
<!-- Notification panel -->
<div id='notification-panel'  class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <!-- Date Stamp -->
            <div class="form-group">
                <label class=" control-label" for='datestamp'><?php  eT("Date stamp:"); ?></label>
                <div class="">
                    <?php if ($oSurvey->isActive) { ?>
                        <?php if ($oSurvey->datestamp != "Y") {
                                eT("Responses will not be date stamped.");
                            } else {
                                eT("Responses will be date stamped.");
                        } ?>
                        <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                        <?php echo CHtml::hiddenField('datestamp',$oSurvey->datestamp); // Maybe use a readonly dropdown? ?>
                        <?php }
                        else {
                            $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'datestamp',
                            'value'=> $oSurvey->datestamp,
                            'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->datestamp . ']')): $optionsOnOff,
                            /*'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                                if ($('#anonymized').is(':checked') == true) {
                                $('#datestampModal_1').modal();
                                }
                            }")*/
                            ));
                            /*$this->widget('bootstrap.widgets.TbModal', array(
                                'id' => 'datestampModal_1',
                                'htmlOptions' => ['class' => 'selector_dateStampModal_notification'],
                                'header' => gt('Warning','unescaped'),
                                'content' => '<p>'.gT("If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants.").'</p>',
                                'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
                            ));*/
                            }
                        ?>
                </div>
            </div>
            <!-- Save IP Address -->
            <div class="form-group">
                <label class=" control-label" for='ipaddr'><?php  eT("Save IP address:"); ?></label>
                <div class="">
                    <?php if ($oSurvey->isActive) {
                        if ($oSurvey->ipaddr!= "Y") {
                            eT("Responses will not have the IP address logged.");
                        } else {
                            eT("Responses will have the IP address logged.");
                        } ?>
                        <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                        <?php echo CHtml::hiddenField('ipaddr',$oSurvey->ipaddr);
                    } else {
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'ipaddr',
                            'value'=> $oSurvey->ipaddr,
                            'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->ipaddr . ']')): $optionsOnOff,
                            /*'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                                if ($('#anonymized').is(':checked') == true) {
                                $('#datestampModal_2').modal();
                                }
                            }")*/
                        ));

                        /*$this->widget('bootstrap.widgets.TbModal', array(
                            'id' => 'datestampModal_2',
                            'header' => gt('Warning','unescaped'),
                            'content' => '<p>'.gT("If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants. If you are running a closed survey you will NOT be able to link responses to participants if the survey is set to be anonymous.").'</p>',
                            'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
                        ));*/
                        }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <label class=" control-label" for='ipanonymize'><?php  eT("Anonymize IP address:"); ?></label>
                <div class="">
                    <?php
                    if ($oSurvey->isActive) {
                        if ($oSurvey->ipanonymize!= "Y") {
                            eT("Responses will not have the IP address anonymized.");
                        } else {
                            eT("Responses will have the IP address anonymized.");
                        } ?>
                        <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                        <?php echo CHtml::hiddenField('ipanonymize',$oSurvey->ipanonymize);
                    }else {
                        // <!-- only visible if ipaddr is set to yes in db or switch to yes -->
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'ipanonymize',
                            'value' => $oSurvey->ipanonymize,
                            'selectOptions' => ($bShowInherited) ? array_merge($optionsOnOff, array(
                                'I' => gT('Inherit', 'unescaped') . ' [' . $oSurveyOptions->ipanonymize . ']'
                            )) : $optionsOnOff,
                        ));
                    }
                    ?>
                </div>
            </div>

            <!-- Save referrer URL -->
            <div class="form-group">
                <label class=" control-label" for='refurl'><?php  eT("Save referrer URL:"); ?></label>
                <div class="">
                    <?php if ($oSurvey->isActive) { ?>
                        <?php  if ($oSurvey->refurl != "Y") {
                                eT("Responses will not have their referring URL logged.");
                            } else {
                                eT("Responses will have their referring URL logged.");
                        } ?>
                        <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                        <?php echo CHtml::hiddenField('refurl',$oSurvey->refurl);?>
                        <?php } else {
                            $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'refurl',
                            'value'=> $oSurvey->refurl,
                            'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->refurl . ']')): $optionsOnOff,
                            ));
                    } ?>
                </div>
            </div>

            <!-- Save timings -->
            <div class="form-group">
                <label class=" control-label" for='savetimings'><?php  eT("Save timings:"); ?></label>
                <div class="">
                    <?php if ($oSurvey->isActive): ?>
                        <?php if ($oSurvey->savetimings != "Y"): ?>
                            <?php  eT("Timings will not be saved."); ?>
                        <?php else: ?>
                            <?php  eT("Timings will be saved."); ?>
                            <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                            <?php echo CHtml::hiddenField('savetimings',$oSurvey->savetimings);  // Maybe use a readonly dropdown? ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php
                            $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                                'name' => 'savetimings',
                                'value'=> $oSurvey->savetimings,
                                'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->savetimings . ']')): $optionsOnOff,
                            ));
                        ?>
                    <?php endif;?>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-6">

            <!-- Enable assessment mode -->
            <div class="form-group">
                <label class=" control-label" for='assessments'><?php  eT("Enable assessment mode:"); ?></label>
                <div class=""><?php
                    $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'assessments',
                        'value'=> $oSurvey->assessments,
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->assessments . ']')): $optionsOnOff,
                    ));
                ?></div>
            </div>

            <!-- Participant may save and resume  -->
            <div class="form-group">
                <label class=" control-label" for='allowsave'><?php  eT("Participant may save and resume later:"); ?></label>
                <div class="">
                <?php
                    $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'allowsave',
                        'value'=> $oSurvey->allowsave,
                        'selectOptions'=>($bShowInherited)?array_merge($optionsOnOff, array('I' => gT('Inherit','unescaped').' ['. $oSurveyOptions->allowsave . ']')): $optionsOnOff,
                    ));
                ?>
                </div>
            </div>
            <!-- email basic to -->
            <div class="form-group">
            <?php $emailnotificationto = $oSurvey->emailnotificationto; ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 content-right">
                        <label class=" control-label"  for='emailnotificationto'><?php  eT("Send basic admin notification email to:"); ?></label>
                            <input class="form-control inherit-edit <?php echo ($bShowInherited && $emailnotificationto === 'inherit' ? 'hide' : 'show'); ?>" type='<?php echo ($bShowInherited)?'text':'email'; ?>' size='50' id='emailnotificationto' name='emailnotificationto' value="<?php echo htmlspecialchars($emailnotificationto); ?>" data-inherit-value="inherit" data-saved-value="<?php echo $emailnotificationto; ?>"/>
                            <input class="form-control inherit-readonly <?php echo ($bShowInherited && $emailnotificationto === 'inherit' ? 'show' : 'hide'); ?>" type='text' size='50' value="<?php echo htmlspecialchars($oSurveyOptions->emailnotificationto); ?>" readonly />
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 content-right <?php echo ($bShowInherited ? 'show' : 'hide'); ?>">
                        <label class=" control-label content-center col-sm-12"  for='emailnotificationto'><?php  eT("Inherit:"); ?></label>
                        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'emailnotificationtobutton',
                            'value'=> ($bShowInherited && $emailnotificationto === 'inherit' ? 'Y' : 'N'),
                            'selectOptions'=>$optionsOnOff,
                            'htmlOptions' => array(
                                'class' => 'text-option-inherit'
                                )
                            ));
                            ?>
                    </div>
                </div>
            </div>

            <!-- email detail to  -->
            <div class="form-group">
            <?php $emailresponseto = $oSurvey->emailresponseto; ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 content-right">
                        <label class=" control-label"  for='emailresponseto'><?php  eT("Send detailed admin notification email to:"); ?></label>
                            <input class="form-control inherit-edit <?php echo ($bShowInherited && $emailresponseto === 'inherit' ? 'hide' : 'show'); ?>" type='<?php echo ($bShowInherited)?'text':'email'; ?>' size='50' id='emailresponseto' name='emailresponseto' value="<?php echo htmlspecialchars($emailresponseto); ?>" data-inherit-value="inherit" data-saved-value="<?php echo $emailresponseto; ?>"/>
                            <input class="form-control inherit-readonly <?php echo ($bShowInherited && $emailresponseto === 'inherit' ? 'show' : 'hide'); ?>" type='text' size='50' value="<?php echo htmlspecialchars($oSurveyOptions->emailresponseto); ?>" readonly />
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 content-right <?php echo ($bShowInherited ? 'show' : 'hide'); ?>">
                        <label class=" control-label content-center col-sm-12"  for='emailresponseto'><?php  eT("Inherit:"); ?></label>
                        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'emailresponsetobutton',
                            'value'=> ($bShowInherited && $emailresponseto === 'inherit' ? 'Y' : 'N'),
                            'selectOptions'=>$optionsOnOff,
                            'htmlOptions' => array(
                                'class' => 'text-option-inherit'
                                )
                            ));
                            ?>
                    </div>
                </div>
            </div>
        
            <?php if ($bShowAllOptions === true){ ?>
            <!-- GoogleAnalytics settings to be used -->
            <div class="form-group">
                <label class=" control-label" for="googleanalyticsapikeysetting">
                    <?php echo gT('Google Analytics settings:');?>
                </label>
                <div class="">
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'googleanalyticsapikeysetting',
                        'value'=>  $oSurvey->googleanalyticsapikeysetting,
                        'selectOptions'=>$googleAnalyticsOptions,
                    ));?>
                </div>
            </div>
            <!-- Google Analytics -->
            <div class="form-group">
                <label class=" control-label" for='googleanalyticsapikey'><?php  eT("Google Analytics Tracking ID:"); ?></label>
                <div class="">
                    <?php echo CHtml::textField(
                        'googleanalyticsapikey',
                        $oSurvey->googleanalyticsapikey,
                        array('size'=>20, 'class' => 'form-control')
                    ); ?>
                </div>
            </div>
            <!-- Google Analytics style -->
            <div class="form-group">
                <label class=" control-label" for='googleanalyticsstyle'><?php  eT("Google Analytics style:"); ?></label>
                <div class="">
                <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'googleanalyticsstyle',
                        'value'=> $oSurvey->googleanalyticsstyle ,
                        'selectOptions'=>$googleAnalyticsStyleOptions,
                        ));?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'survey_edit_notificationpanel.js', LSYii_ClientScript::POS_BEGIN); ?>
