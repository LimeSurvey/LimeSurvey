<?php
/**
 * This view render the Update Notfication displayed on top of the LimeSurvey admin interface.
 * It is called from Survey_Common action which launch AdminController::_getUpdateNotification();
 *
 * @var $security_update_available
 */

?>
<?php
    $urlUpdate = Yii::app()->createUrl("admin/update");
    $urlUpdateNotificationState = Yii::app()->createUrl("admin/update/sa/notificationstate");
?>

<?php if(Yii::app()->session['notificationstate']==1):?>
    <div class="col-lg-12" id="update-container">
        <?php if($security_update_available):?>
            <div class="alert alert-warning alert-dismissible alert-security-update" role="alert" id="update-alert" data-url-notification-state="<?php echo $urlUpdateNotificationState; ?>">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Security Update !</strong> a security update is available. <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate.');?></a>, <?php eT('or update manually'); ?>
            </div>
        <?php else:?>
            <div class="alert alert-info alert-dismissible" role="alert" id="update-alert" data-url-notification-state="<?php echo $urlUpdateNotificationState; ?>">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong><?php eT("a new update is available");?> </strong> <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate.');?></a>, <?php eT('or update manually'); ?>
            </div>
        <?php endif;?>
    </div>
<?php endif;?>
