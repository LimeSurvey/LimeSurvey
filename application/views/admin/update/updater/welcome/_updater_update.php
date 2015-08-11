<?php
/**
 * This file display the Updater Update Message
 */
?>
<h2 class="maintitle"><?php eT("ComfortUpdate needs to be updated");?></h2>

<?php 
    if( isset($serverAnswer->html) )
        echo $serverAnswer->html;
?>

<div class="updater-background">
    <?php eT("It seems you didn't update your LimeSurvey installation regularly.");?>
    <br/>
    <?php eT("Before you proceed to the LimeSurvey update, we must first update ComfortUpdate itself.");?>
    <br/>
    <?php eT("At the end of the process we'll proceed to the LimeSurvey update.");?>
</div>



<!-- The form launching the update of the updater. -->
<?php $url = Yii::app()->createUrl("admin/update/sa/updateUpdater"); ?>
<?php echo CHtml::beginForm($url, 'post', array('id'=>'launchUpdateUpdaterForm')); ?>
    <?php  echo CHtml::hiddenField('destinationBuild' , $serverAnswer->destinationBuild); ?>
    <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
        <span class="ui-button-text"><?php eT("Cancel"); ?></span>
    </a>
    <?php echo CHtml::submitButton(gT("Continue"), array('class'=>"ajax_button ui-button ui-widget ui-state-default ui-corner-all",)); ?>
<?php echo CHtml::endForm(); ?>

<!-- this javascript code manage the step changing. It will catch the form submission, then load ComfortUpdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#launchUpdateUpdaterForm').comfortUpdateNextStep({'step': 0});   
</script>