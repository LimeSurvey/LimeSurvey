<?php
/**
 * This view render the Update Notfication displayed on top of the LimeSurvey admin interface.
 * It is called from Survey_Common action which launch AdminController::_getUpdateNotification();
 *
 * @var $security_update_available
 */

?>
<?php
    $urlUpdate = Yii::app()->createUrl("admin/globalsettings", array("update"=>'updatebuttons'));
    $urlUpdateNotificationState = Yii::app()->createUrl("admin/update/sa/notificationstate");
?>

<?php if(Yii::app()->session['notificationstate']==1):?>
    <?php if($security_update_available):?>
        <div id="update-alert" data-url-notification-state="<?php echo $urlUpdateNotificationState; ?>" class="alert alert-warning alert-dismissible" role="alert" style="background-color: #fff; border: 1px solid #800051; color: #800051; margin-top:  1em;">
                <strong>Security Update !</strong> a security update is available. <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate.');?></a>
        </div>
    <?php elseif(Yii::app()->session['unstable_update']):?>
        <div id="update-alert" data-url-notification-state="<?php echo $urlUpdateNotificationState; ?>" class="alert alert-info alert-dismissible unstable-update" role="alert"  style="background-color: #fff; border: 1px solid #A0352F; color: #A0352F; margin-top:  1em;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" ><span aria-hidden="true">&times;</span></button>
                <strong><?php eT('New UNSTABLE update available:');?></strong> <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate or to download it.');?><strong><?php eT("You don't need an update key.");?></strong></a>
        </div>
    <?php else:?>
        <div id="update-alert" data-url-notification-state="<?php echo $urlUpdateNotificationState; ?>" class="alert alert-info alert-dismissible" role="alert"  style="background-color: #fff; border: 1px solid #84BD00; color: #84BD00; margin-top:  1em;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" ><span aria-hidden="true">&times;</span></button>
                <strong><?php eT('New update available:');?></strong> <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate or to download it.');?></a>
        </div>
    <?php endif;?>
<?php endif;?>
