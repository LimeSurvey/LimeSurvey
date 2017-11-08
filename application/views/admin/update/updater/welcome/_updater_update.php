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
    <?php eT("Before you proceed to the LimeSurvey update, we must first update ComfortUpdate itself.");?>
    <br/>
    <?php eT("At the end of the process we'll proceed to the LimeSurvey update.");?>
</div>



<!-- The form launching the update of the updater. -->
<?php echo CHtml::beginForm(Yii::app()->getController()->createUrl('admin/update/sa/updateUpdater'), 'post', array('id'=>'launchUpdateUpdaterForm')); ?>
    <?php  echo CHtml::hiddenField('destinationBuild' , $serverAnswer->destinationBuild); ?>
    <a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
        <?php eT("Cancel"); ?>
    </a>
    <?php echo CHtml::submitButton(gT("Continue",'unescaped'), array('class'=>"ajax_button btn btn-default",)); ?>
<?php echo CHtml::endForm(); ?>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the comfortupdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#launchUpdateUpdaterForm').comfortUpdateNextStep({'step': 0});
</script>
