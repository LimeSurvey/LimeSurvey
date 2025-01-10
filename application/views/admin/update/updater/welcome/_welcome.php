<?php
/**
 * This view displays the welcome message provided by the controller.
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like all the further steps)
 *
 * @var obj $serverAnswer the object returned by the server
 */
?>

<?php $urlNew = Yii::app()->createUrl("admin/update", array("update"=>'newKey', 'destinationBuild' => $serverAnswer->destinationBuild)); ?>
<h3 class="maintitle"><?php eT('Welcome to the LimeSurvey ComfortUpdate!');?></h3>

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
<div class="row">
    <div id="welcomeMessage" class="col-12 col-xl-6">
        <?php
            echo gT('The LimeSurvey ComfortUpdate is an easy procedure to quickly update to the latest version of LimeSurvey.').'<br /><br />';
            echo '<ul><li>'.gT('The following steps will be done by this update:').'</li>';
            echo '<li>'.gT('Your GititSurvey installation is checked if the update can be run successfully.').'</li>';
            echo '<li>'.gT('New files will be downloaded and installed.').'</li>';
            echo '<li>'.gT('If necessary the database will be updated.').'</li></ul>';
        ?>
    </div>

    <!-- The key information-->
    <div class="col-xl-6 d-md-none d-lg-block">
        <div class="card card-primary">
            <div class="card-header">
                <?php eT('ComfortUpdate key information'); ?>
            </div>
            <div class="card-body">
            <?php
            if (isset($serverAnswer->html)): ?>
                <?php
                if ($serverAnswer->html == 'update_unstable' && $serverAnswer->key_infos->keyid != 'FREE'): ?>
                    <p>
                        <?php
                        eT('This is an update to an unstable version'); ?>
                        <br/>
                        <?php
                        eT('It will not affect your update key.') ?>
                    </p>
                <?php
                endif; ?>
            <?php
            endif; ?>

            <strong><?php
                eT('Your update key:'); ?><?php
                if (!(App()->getConfig('hide_update_key'))) {
                    echo $serverAnswer->key_infos->keyid;
                } else {
                    echo '<em>XXXXXXX</em>';
                } ?></strong><br/>
            <strong><?php
                eT('Valid until:'); ?><?php
                echo $sValidityDate; ?></strong><br/>
            <?php
            if ($serverAnswer->key_infos->remaining_updates != -999) { ?>
                <strong><?php
                    eT('Remaining updates:'); ?><?php
                    echo $serverAnswer->key_infos->remaining_updates; ?></strong><br/>
            <?php
            } ?>
            </div>
            <div class="card-footer">
                <?php
                if ($serverAnswer->key_infos->remaining_updates != -999): ?>
                    <a class="btn btn-outline-secondary"
                       href="https://community.limesurvey.org/comfort-update-extension/" role="button"
                       aria-disabled="false" target="_blank">
                        <?php
                        eT("Buy a new key"); ?>
                    </a>
                <?php
                endif; ?>

                <a class="btn btn-outline-secondary" href="<?php
                echo $urlNew; ?>" role="button" aria-disabled="false">
                    <?php
                    eT("Enter a new key"); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div id="btn-container" class="col-12 col-xl-6 mt-2">
        <!-- The form launching the first step : control local errors. -->
        <?php echo CHtml::beginForm(Yii::app()->getController()->createUrl('admin/update/sa/checkLocalErrors'), 'post', array('id'=>'launchCheckLocalErrorsForm')); ?>
            <?php  echo CHtml::hiddenField('destinationBuild' , $serverAnswer->destinationBuild); ?>
            <?php  echo CHtml::hiddenField('access_token' , $serverAnswer->access_token); ?>

            <a class="btn btn-cancel me-1" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button">
                <?php eT("Cancel"); ?>
            </a>

            <button type="submit" class="btn btn-primary ajax_button launch_update">
                <?php eT("Continue");?>
            </button>
        <?php echo CHtml::endForm(); ?>
    </div>

</div>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the comfortupdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#launchCheckLocalErrorsForm').comfortUpdateNextStep({'step': 0});
</script>
