<?php
/**
 * This view displays the welcome message provided by the controller.
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like all the further steps)
 *
 * @var obj $serverAnswer the object returned by the server
 */
?>

<?php $urlNew = Yii::app()->createUrl("admin/globalsettings", array("update"=>'newKey', 'destinationBuild' => $serverAnswer->destinationBuild)); ?>

<h2 class="maintitle"><?php eT('Welcome to the LimeSurvey ComfortUpdate!');?></h2>

<?php
    if( isset($serverAnswer->html) )
    {
        if ( $serverAnswer->html != 'update_unstable')
        {
            echo $serverAnswer->html;
        }

    }
?>

<!-- Welcome Message -->
<div id="welcomeMessageContainer">
    <p><?php
        echo gT('The LimeSurvey ComfortUpdate is an easy procedure to quickly update to the latest version of LimeSurvey.').'</p><p>';
        eT('The following steps will be done by this update:').'</p>';
        echo '<ul><li>'.gT('Your LimeSurvey installation is checked if the update can be run successfully.').'</li>';
        echo '<li>'.gT('A backup of your old files will be created.').'</li>';
        echo '<li>'.gT('New files will be downloaded and installed.').'</li>';
        echo '<li>'.gT('The database will be updated (if necessary).').'</li></ul>';
    ?>

    <div id="welcomeMessageContainerButtons">
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
<div id="keyInfos">
    <p></p>
    <div>
        <h4><?php eT('ComfortUpdate key information'); ?></h4>
        <?php if( isset($serverAnswer->html) ): ?>
            <?php if ( $serverAnswer->html == 'update_unstable' && $serverAnswer->key_infos->keyid != 'FREE'):?>
                <p>
                    <?php eT('This is an update to an unstable version'); ?>
                    <br/>
                    <?php eT('It will not affect your update key.')?>
                </p>
            <?php endif;?>
        <?php endif;?>

        <strong><?php eT('Your update key:');?> <?php echo $serverAnswer->key_infos->keyid; ?></strong><br/>
        <strong><?php eT('Valid until:');?> <?php echo $serverAnswer->key_infos->validuntil; ?></strong><br/>
        <?php  if ($serverAnswer->key_infos->remaining_updates!=-999) { ?>
            <strong><?php eT('Remaining updates:');?> <?php echo $serverAnswer->key_infos->remaining_updates;?></strong><br/>
        <?php } ?>
    </div>
    <div id="keyInfosbuttons">
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
