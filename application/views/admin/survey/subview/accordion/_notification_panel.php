<?php
/**
 * Notificatin panel
 */
?>
<!-- Notification panel -->
<div id='notification'  class="tab-pane fade in">

    <!-- email basic to -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='emailnotificationto'><?php  eT("Send basic admin notification email to:"); ?></label>
        <div class="col-sm-7">
            <?php echo CHtml::textField('emailnotificationto',$esrow['emailnotificationto'],array('size'=>70, 'class'=>"form-control")); ?>
        </div>
    </div>

    <!-- email detail to  -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='emailresponseto'><?php  eT("Send detailed admin notification email to:"); ?></label>
        <div class="col-sm-7">
            <?php echo CHtml::textField('emailresponseto',$esrow['emailresponseto'],array('size'=>70, 'class'=>"form-control")) ?>
        </div>
    </div>

    <!-- Date Stamp -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='datestamp'><?php  eT("Date stamp:"); ?></label>
        <div class="col-sm-7">
            <?php if ($esrow['active'] == "Y") { ?>
                <?php if ($esrow['datestamp'] != "Y") {
                         eT("Responses will not be date stamped.");
                    } else {
                         eT("Responses will be date stamped.");
                } ?>
                <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('datestamp',$esrow['datestamp']); // Maybe use a readonly dropdown? ?>
                <?php }
                else {
                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'datestamp',
                    'value'=> $esrow['datestamp'] == "Y",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off'),
                    'events'=>array('switchChange.bootstrapSwitch'=>"function(event,state){
                        if ($('#anonymized').is(':checked') == true) {
                          $('#datestampModal').modal();
                        }
                    }")
                    ));
                    $this->widget('bootstrap.widgets.TbModal', array(
                        'id' => 'datestampModal',
                        'header' => gt('Warning','unescaped'),
                        'content' => '<p>'.gT("If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants.").'</p>',
                        'footer' => TbHtml::button('Close', array('data-dismiss' => 'modal'))
                    ));
                    }
                ?>
        </div>
    </div>

    <!-- Save IP Address -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='ipaddr'><?php  eT("Save IP address:"); ?></label>
        <div class="col-sm-7">
            <?php if ($esrow['active'] == "Y") {
                if ($esrow['ipaddr'] != "Y") {
                    eT("Responses will not have the IP address logged.");
                } else {
                    eT("Responses will have the IP address logged");
                } ?>
                <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('ipaddr',$esrow['ipaddr']);
            } else {
                $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'ipaddr',
                    'value'=> $esrow['ipaddr'] == "Y",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                ));
            } ?>
        </div>
    </div>

    <!-- Save referrer URL -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='refurl'><?php  eT("Save referrer URL:"); ?></label>
        <div class="col-sm-7">
            <?php if ($esrow['active'] == "Y") { ?>
                <?php  if ($esrow['refurl'] != "Y") {
                         eT("Responses will not have their referring URL logged.");
                    } else {
                         eT("Responses will have their referring URL logged.");
                } ?>
                <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('refurl',$esrow['refurl']);?>
                <?php } else {
                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'refurl',
                    'value'=> $esrow['refurl'] == "Y",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
             } ?>
        </div>
    </div>

    <!-- Save timings -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='savetimings'><?php  eT("Save timings:"); ?></label>
        <div class="col-sm-7">
            <?php if ($esrow['active']=="Y"): ?>
                <?php if ($esrow['savetimings'] != "Y"): ?>
                    <?php  eT("Timings will not be saved."); ?>
                <?php else: ?>
                    <?php  eT("Timings will be saved."); ?>
                    <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                    <?php echo CHtml::hiddenField('savetimings',$esrow['savetimings']);  // Maybe use a readonly dropdown? ?>
                <?php endif; ?>
            <?php else: ?>
                <?php
                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'savetimings',
                        'value'=> $esrow['savetimings'] == "Y",
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                    ));
                ?>
            <?php endif;?>            
        </div>
    </div>

    <!-- Enable assessment mode -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='assessments'><?php  eT("Enable assessment mode:"); ?></label>
        <div class="col-sm-7"><?php
            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                'name' => 'assessments',
                'value'=> $esrow['assessments'] == "Y",
                'onLabel'=>gT('On'),
                'offLabel'=>gT('Off')
            ));
        ?></div>
    </div>

    <!-- Participant may save and resume  -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='allowsave'><?php  eT("Participant may save and resume later:"); ?></label>
        <div class="col-sm-7">
        <?php
            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                'name' => 'allowsave',
                'value'=> $esrow['allowsave'] == "Y",
                'onLabel'=>gT('On'),
                'offLabel'=>gT('Off')
            ));
        ?>
        </div>
    </div>

    <!-- GoogleAnalytics settings to be used -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for="googleanalyticsapikeysetting">
            <?php echo gT('Google Analytics settings:');?>
        </label>
        <div class="col-sm-7">
            <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'googleanalyticsapikeysetting',
                'value'=>  $esrow['googleanalyticsapikeysetting'],
                'selectOptions'=>array(
                    "N"=>gT("None",'unescaped'),
                    "Y"=>gT("Use settings below",'unescaped'),
                    "G"=>gT("Use global settings",'unescaped')
                )
            ));?>
        </div>
    </div>
    <!-- Google Analytics -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='googleanalyticsapikey'><?php  eT("Google Analytics Tracking ID:"); ?></label>
        <div class="col-sm-7">
            <?php echo CHtml::textField('googleanalyticsapikey',$esrow['googleanalyticsapikey'],array('size'=>20), array('class'=>"form-control")); ?>
        </div>
    </div>
    <!-- Google Analytics style -->
    <div class="form-group">
        <label class="col-sm-5 control-label" for='googleanalyticsstyle'><?php  eT("Google Analytics style:"); ?></label>
        <div class="col-sm-7">
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'googleanalyticsstyle',
                'value'=> $esrow['googleanalyticsstyle'] ,
                'selectOptions'=>array(
                "0"=>gT("Off",'unescaped'),
                "1"=>gT("Default",'unescaped'),
                "2"=>gT("Survey-SID/Group",'unescaped'))
                ));?>
        </div>
    </div>
</div>
<?php
$oAdminTheme = AdminTheme::getInstance();
$oAdminTheme->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'survey_edit_notificationpanel.js');
?>
