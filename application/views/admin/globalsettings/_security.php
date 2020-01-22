<?php
/**
* This view generate the 'security' tab inside global settings.
*
*/
?>
<div class="form-group">

    <label class=" control-label"  for='surveyPreview_require_Auth'><?php eT("Survey preview only for administration users:"); ?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
            'name' => 'surveyPreview_require_Auth',
            'id'=>'surveyPreview_require_Auth',
            'value' => getGlobalSetting('surveyPreview_require_Auth'),
            'onLabel'=>gT('On'),
            'offLabel' => gT('Off')));
        ?>
    </div>
</div>

<div class="form-group">
    <label class=" control-label"  for='filterxsshtml'><?php eT("Filter HTML for XSS:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
            'name' => 'filterxsshtml',
            'id'=>'filterxsshtml',
            'value' => getGlobalSetting('filterxsshtml'),
            'onLabel'=>gT('On'),
            'offLabel' => gT('Off')
            ));
        ?>
    </div>
    <div class="">
        <span class='hint'><?php eT("Note: XSS filtering is always disabled for the superadministrator."); ?></span>
    </div>
</div>

<div class="form-group">
    <label class=" control-label"  for='usercontrolSameGroupPolicy'><?php eT("Group member can only see own group:"); ?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
            'name' => 'usercontrolSameGroupPolicy',
            'id'=>'usercontrolSameGroupPolicy',
            'value' => getGlobalSetting('usercontrolSameGroupPolicy'),
            'onLabel'=>gT('On'),
            'offLabel' => gT('Off')));
        ?>
    </div>
</div>

<div class="form-group">
    <label class=" control-label"  for="x_frame_options">
    <?php if (Yii::app()->getConfig("demoMode")==true){ ?>
    <span class="text-danger asterisk"></span>
    <?php }; ?>
     <?php eT('IFrame embedding allowed:'); echo ((Yii::app()->getConfig("demoMode")==true)?'*':'');?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
            'name' => 'x_frame_options',
            'value'=> getGlobalSetting('x_frame_options'),
            'selectOptions'=>array(
                "allow"=>gT("Allow",'unescaped'),
                "sameorigin"=>gT("Same origin",'unescaped')
            )
        ));?>
    </div>
</div>

<div class="form-group">
    <label class=" control-label"  for="force_ssl">
    <?php if (Yii::app()->getConfig("demoMode")==true){ ?>
    <span class="text-danger asterisk"></span>
    <?php }; ?>    
    <?php eT('Force HTTPS:'); echo ((Yii::app()->getConfig("demoMode")==true)?'*':'');?></label>
    <div class="">
        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
            'name' => 'force_ssl',
            'value'=> getGlobalSetting('force_ssl'),
            'selectOptions'=>array(
                "on"=>gT("On",'unescaped'),
                "off"=>gT("Off",'unescaped')
            )
        ));?>        
    </div>
</div>
<?php
$warning_force_ssl = sprintf(gT('Warning: Before turning on HTTPS,%s check if this link works.%s'),'<a href="https://'.$_SERVER['HTTP_HOST'].$this->createUrl("admin/globalsettings/sa").'" title="'. gT('Test if your server has SSL enabled by clicking on this link.').'">','</a>')
.'<br/> '
. gT("If the link does not work and you turn on HTTPS, LimeSurvey will break and you won't be able to access it.");
?>
<div class="form-group">
    <span style='font-size:0.7em;'><?php echo $warning_force_ssl; ?></span>
</div>




<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
    <?php endif; ?>
