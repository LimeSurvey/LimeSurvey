<?php
/**
 * This view render the Update Notfication displayed on top of the LimeSurvey admin interface.
 * It is called from Survey_Common action which launch AdminController::_getUpdateNotification();
 * 
 * @var $security_update_available
 */

?>
<?php $urlUpdate = Yii::app()->createUrl("admin/globalsettings", array("update"=>'updatebuttons')); ?>

<?php if($security_update_available):?>
<div class="alert alert-warning alert-dismissible" role="alert" style="background-color: #fff; border: 1px solid #800051; color: #800051; margin-top:  1em;">
        <strong>Security Update !</strong> a security update is available. <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate.');?></a>
</div>
<?php else:?>
<div class="alert alert-info alert-dismissible" role="alert"  style="background-color: #fff; border: 1px solid #84BD00; color: #84BD00; margin-top:  1em;">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: #84BD00;"><span aria-hidden="true">&times;</span></button>
        <strong><?php eT('New update available:');?></strong> <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate.');?></a>
</div>
<?php endif;?>
