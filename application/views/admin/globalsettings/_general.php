<?php
/**
* This view generate the 'general' tab inside global settings.
*
*/
?>
<?php
$thisdefaulttheme=getGlobalSetting('defaulttheme');
$templatenames=array_keys(Template::getTemplateList());
$thisadmintheme=getGlobalSetting('admintheme');
$thisdefaulthtmleditormode=getGlobalSetting('defaulthtmleditormode');
$thismaintenancemode=!empty(getGlobalSetting('maintenancemode')) ? getGlobalSetting('maintenancemode') : 'off';
$thisdefaultquestionselectormode=getGlobalSetting('defaultquestionselectormode');
$thisdefaultthemeteeditormode=getGlobalSetting('defaultthemeteeditormode');
$dateformatdata=getDateFormatData(Yii::app()->session['dateformat']);
?>

<div class="container-fluid">
    <div class="ls-flex-column ls-space padding left-5 right-5 col-md-7">
        <!-- Global sitename -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='sitename'>
                    <?php eT("Site name:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?>
                </label>
                <div class="col-sm-12 text-left">
                    <input class="form-control" type='text' size='50' id='sitename' name='sitename' value="<?php echo htmlspecialchars(getGlobalSetting('sitename')); ?>" />
                </div>
            </div>
        </div>
        <!-- Default Template -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for="defaulttheme">
                <?php eT("Default theme:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':'');?>
                </label>
                <div class="col-sm-12">
                    <select class="form-control" name="defaulttheme" id="defaulttheme">
                        <?php foreach ($templatenames as $templatename) : ?>
                        <option value='<?php echo $templatename; ?>' <?php echo ($thisdefaulttheme==$templatename) ? "selected='selected'" : ""?> >
                            <?php echo $templatename; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <!-- Administrative Template -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for="admintheme">
                <?php eT("Administration theme:"); ?>
                </label>
                <div class="col-sm-12">
                    <select class="form-control" name="admintheme" id="admintheme">
                        <?php  foreach($aListOfThemeObjects as $templatename => $templateconfig): ?>
                        <option value='<?php echo $templatename; ?>' <?php echo ($thisadmintheme==$templatename)? "selected='selected'" : "" ?> >
                            <?php echo $templateconfig->metadata->name; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if(Permission::model()->hasGlobalPermission('superadmin','read')):?>
                <div class="col-sm-12 control-label ">
                    <span class="hint">
                    <?php eT("You can add your custom themes in upload/admintheme");?>
                    </span>
                </div>
                <?php endif;?>
            </div>
      </div>
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='timeadjust'>
                    <?php eT("Time difference (in hours):"); ?>
                </label>
                <div class="col-sm-4">
                    <span>
                        <input class="form-control"  type='text' id='timeadjust' name='timeadjust' value="<?php echo htmlspecialchars(str_replace(array('+',' hours',' minutes'),array('','',''),getGlobalSetting('timeadjust'))/60); ?>" />
                    </span>
                </div>
                <div class="col-sm-8">
                    <?php echo gT("Server time:").' '.convertDateTimeFormat(date('Y-m-d H:i:s'),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i')
                        ."<br>"
                        . gT("Corrected time:").' '
                        .convertDateTimeFormat(dateShift(date("Y-m-d H:i:s"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')),'Y-m-d H:i:s',$dateformatdata['phpdate'].' H:i'); ?>
                </div>
            </div>
        </div>
        <?php if( isset(Yii::app()->session->connectionID) ): ?>
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12" >
                <label class="col-sm-12 text-left control-label" for='iSessionExpirationTime'>
                    <?php eT("Session lifetime for surveys (seconds):"); ?>
                </label>
                <div class="col-sm-12">
                    <input class="form-control" type='text' size='10' id='iSessionExpirationTime' name='iSessionExpirationTime' value="<?php echo htmlspecialchars(getGlobalSetting('iSessionExpirationTime')); ?>" />
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='ipInfoDbAPIKey'>
                <?php eT("IP Info DB API Key:"); ?>
                </label>
                <div class="col-sm-12">
                <input class="form-control" type='text' size='35' id='ipInfoDbAPIKey' name='ipInfoDbAPIKey' value="<?php echo htmlspecialchars(getGlobalSetting('ipInfoDbAPIKey')); ?>" />
                </div>
            </div>
        </div>
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='googleMapsAPIKey'>
                <?php eT("Google Maps API key:"); ?>
                </label>
                <div class="col-sm-12">
                    <input class="form-control" type='text' size='35' id='googleMapsAPIKey' name='googleMapsAPIKey' value="<?php echo htmlspecialchars(getGlobalSetting('googleMapsAPIKey')); ?>" />
                </div>
            </div>
        </div>

        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='googleanalyticsapikey'>
                <?php eT("Google Analytics Tracking ID:"); ?>
                </label>
                <div class="col-sm-12">
                    <input class="form-control" type='text' size='35' id='googleanalyticsapikey' name='googleanalyticsapikey' value="<?php echo htmlspecialchars(getGlobalSetting('googleanalyticsapikey')); ?>" />
                </div>
            </div>
        </div>
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='googletranslateapikey'>
                <?php eT("Google Translate API key:"); ?>
                </label>
                <div class="col-sm-12">
                    <input class="form-control" type='text' size='35' id='googletranslateapikey' name='googletranslateapikey' value="<?php echo htmlspecialchars(getGlobalSetting('googletranslateapikey')); ?>" />
                </div>
            </div>
        </div>
        <div class="row ls-space margin top-10">
            <div class='form-group col-xs-12'>
                <label class='col-sm-12 text-left control-label' for='characterset'>
                    <?php eT("Character set for file import/export:") ?>
                </label>
                <div class='col-sm-12'>
                    <select class='form-control' name='characterset' id='characterset'>
                    <?php foreach ($aEncodings as $code => $charset): ?>
                        <option value='<?php echo $code; ?>'
                        <?php if (array_key_exists($thischaracterset, $aEncodings) && $code==$thischaracterset): ?>
                            selected='selected'
                        <?php elseif (!array_key_exists($thischaracterset, $aEncodings) && $code == "auto"): ?>
                            selected='selected'
                        <?php endif; ?>
                        >
                            <?php echo $charset; ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="ls-flex-column ls-space padding left-5 right-5 col-md-5">

       <!-- Maintenance mode -->
       <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for="maintenancemode" title="<?php echo gT('Maintenance modes: 
Off
Soft lock - participants are able to finish started surveys, no new participants are allowed
Full lock - none of participants are allowed to take survey, even if they already started to take it'); ?> ">
                <?php eT("Maintenance mode:"); ?>
                </label>
                <div class="col-sm-12">
                    <?php
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'maintenancemode',
                            'value'=> $thismaintenancemode,
                            'selectOptions'=>array(
                                "off"=>gT("Off",'unescaped'),
                                "soft"=>gT("Soft lock",'unescaped'),
                                "hard"=>gT("Full lock",'unescaped')
                                )
                            ));
                    ?>
                </div>
            </div>
        </div>
         <!-- Refresh assets -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='refreshassets'>
                <?php eT("Clear assets cache:"); ?> <small>(<?php echo  getGlobalSetting('customassetversionnumber');?>)</small>
                </label>
                <div class="col-sm-12">
                    <a href="<?php echo App()->createUrl('admin/globalsettings', array("sa"=>"refreshAssets")); ?>" class="btn btn-success btn-large"><?php eT("Clear now");?></a>
                </div>
            </div>
        </div>

        <!-- Default Editor mode -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='defaulthtmleditormode'>
                <?php eT("Default HTML editor mode:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?>
                </label>
                <div class="col-sm-12">
                    <?php
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'defaulthtmleditormode',
                            'value'=> $thisdefaulthtmleditormode ,
                            'selectOptions'=>array(
                                "wysiwyg"=>gT("WYSIWYG editor",'unescaped'),
                                "source"=>gT("Sourcecode",'unescaped'),
                                )
                            ));
                    ?>
                </div>
            </div>
        </div>
        <!-- Side menu behaviour -->
        <?php /* This setting is just remaining here for campatibility reasons. It is not yet implemented into the new admmin panel */ ?>
        <div class="row" style="display:none">
            <div class='form-group'>
                <label class='col-sm-12 text-left control-label' for='sideMenuBehaviour'>
                    <?php eT("Side-menu behaviour:"); ?>
                </label>
                <div class='col-sm-4'>
                    <?php
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'sideMenuBehaviour',
                        'value'=> $sideMenuBehaviour ,
                        'selectOptions'=>array(
                            "adaptive"=>gT("Adaptive",'unescaped'),
                            "alwaysOpen"=>gT("Always open",'unescaped'),
                            "alwaysClosed"=>gT("Always closed",'unescaped')
                            )
                        ));
                    ?>
                </div>
            </div>
        </div>
        <!-- Default question type selector mode -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='defaultquestionselectormode'>
                <?php eT("Question type selector:"); echo((Yii::app()->getConfig("demoMode")==true)?'*':''); ?>
                </label>
                <div class="col-sm-12">
                    <?php
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'defaultquestionselectormode',
                            'value'=> $thisdefaultquestionselectormode ,
                            'selectOptions'=>array(
                                "default"=>gT("Full",'unescaped'),
                                "none"=>gT("Simple",'unescaped')
                                )
                            ));
                    ?>
                </div>
            </div>
        </div>
        <!-- Default theme editor mode -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='defaultthemeteeditormode'>
                    <?php eT("Template editor:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?>
                </label>
                <div class="col-sm-12">
                    <?php
                        $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'defaultthemeteeditormode',
                            'value'=> $thisdefaultthemeteeditormode ,
                            'selectOptions'=>array(
                                "default"=>gT("Full",'unescaped'),
                                "none"=>gT("Simple",'unescaped')
                                )
                            ));
                    ?>
                </div>
            </div>
        </div>
        <!-- Default theme editor mode -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='javascriptdebugbcknd'>
                    <?php eT("JS-Debug mode [Backend]:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?>
                </label>
                <div class="col-sm-12">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'javascriptdebugbcknd',
                    'id'=>'javascriptdebugbcknd',
                    'value' => getGlobalSetting('javascriptdebugbcknd'),
                    'onLabel'=>gT('On'),
                    'offLabel' => gT('Off')
                    ));
                ?>
                </div>
            </div>
        </div>
        <!-- Default theme editor mode -->
        <div class="row ls-space margin top-10">
            <div class="form-group col-xs-12">
                <label class="col-sm-12 text-left control-label" for='javascriptdebugfrntnd'>
                    <?php eT("JS-Debug mode [Frontend]:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?>
                </label>
                <div class="col-sm-12">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'javascriptdebugfrntnd',
                    'id'=>'javascriptdebugfrntnd',
                    'value' => getGlobalSetting('javascriptdebugfrntnd'),
                    'onLabel'=>gT('On'),
                    'offLabel' => gT('Off')
                    ));
                ?>
                </div>
            </div>
        </div>

        <!-- Allow unstable extension updates (only visible for super admin)-->
        <?php if(Permission::model()->hasGlobalPermission('superadmin','read')):?>
            <div class="row ls-space margin top-10">
                <div class="form-group col-xs-12">
                    <label class="col-sm-12 text-left control-label" for='allow_unstable_extension_update'>
                        <?php eT('Allow unstable extension updates:'); ?>
                    </label>
                    <div class="col-sm-12">
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'allow_unstable_extension_update',
                            'id'=>'allow_unstable_extension_update',
                            'value' => getGlobalSetting('allow_unstable_extension_update'),
                            'onLabel'=>gT('On'),
                            'offLabel' => gT('Off')
                            ));
                        ?>
                    </div>
                    <div class="col-sm-12 control-label ">
                        <span class="hint">
                            <?php eT("Enabling unstable updates will allow you to try alpha and beta versions of extensions. Talk to the extension author for more information.");?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (Yii::app()->getConfig("demoMode")==true):?>
<p>
    <?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?>
</p>
<?php endif; ?>
