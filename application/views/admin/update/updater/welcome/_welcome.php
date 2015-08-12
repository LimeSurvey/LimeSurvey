<?php 
/**
 * This view displays the welcome message provided by the controller. 
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like all the further steps)
 * 
 * @var obj $serverAnswer the object returned by the server 
 */
?>

<?php $urlNew = Yii::app()->createUrl("admin/globalsettings", array("update"=>'newKey', 'destinationBuild' => $serverAnswer->destinationBuild)); ?>

<h2 class="maintitle"><?php eT($serverAnswer->title);?></h2>

<?php 
    if( isset($serverAnswer->html) )
        echo $serverAnswer->html;
?>

<!-- Welcome Message -->
<div style="width: 450px; float: left; border-right:1px solid #EEE">
    <?php
    
        echo gT('The LimeSurvey ComfortUpdate is an easy procedure to quickly update to the latest version of LimeSurvey.').'<br /><br />';
        echo '<ul><li>'.gT('The following steps will be done by this update:').'</li>';
        echo '<li>'.gT('Your LimeSurvey installation is checked if the update can be run successfully.').'</li>';
        echo '<li>'.gT('New files will be downloaded and installed.').'</li>';
        echo '<li>'.gT('If necessary the database will be updated.').'</li></ul>';
    ?>
    
    <div style="float: left;">
        <!-- The form launching the first step : control local errors. -->
        <?php $url = Yii::app()->createUrl("admin/update/sa/checkLocalErrors"); ?>
        <?php echo CHtml::beginForm($url, 'post', array('id'=>'launchCheckLocalErrorsForm')); ?>
            <?php  echo CHtml::hiddenField('destinationBuild' , $serverAnswer->destinationBuild); ?>
            <?php  echo CHtml::hiddenField('access_token' , $serverAnswer->access_token); ?>  
            <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
                <span class="ui-button-text"><?php eT("Cancel"); ?></span>
            </a>
            <?php echo CHtml::submitButton(gT("Continue"), array('class'=>"ajax_button ui-button ui-widget ui-state-default ui-corner-all",)); ?>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<!-- The key informations-->
<div style="width: 420px; float: left; padding-left: 20px; background-color: #fff;">
    <p></p>
    <div style="">
        <h4><?php eT('ComfortUpdate key information'); ?></h4>
        <strong><?php printf(gT('Your update key is %s.'),$serverAnswer->key_infos->keyid); ?></strong><br/>
        <strong><?php printf(gT('Your key is valid until %s.'),$serverAnswer->key_infos->validuntil); ?></strong><br/>
        <?php  if ($serverAnswer->key_infos->remaining_updates!=-999) { ?>
            <strong><?php printf(ngT('It still has %s remaining updates.',$serverAnswer->key_infos->remaining_updates),$serverAnswer->key_infos->remaining_updates); ?> </strong><br/>
        <?php } ?>
    </div>
    <div style ="float: right; clear: both; padding-top: 100px;" >
        <?php  if ($serverAnswer->key_infos->remaining_updates!=-999) { ?>
        <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="https://www.limesurvey.org/en/" role="button" aria-disabled="false" target="_blank">
            <span class="ui-button-text"><?php eT("Renew this key"); ?></span>
        </a>
        <?php } ?>
    
        <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo $urlNew;?>" role="button" aria-disabled="false">
            <span class="ui-button-text"><?php eT("Enter a new key"); ?></span>
        </a>
    </div>
</div>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the ComfortUpdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#launchCheckLocalErrorsForm').comfortUpdateNextStep({'step': 0});    
</script>