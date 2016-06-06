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
    <div class="col-sm-12" id="update-container">
        <?php if($security_update_available):?>
            <div class="alert alert-warning alert-dismissible alert-security-update" role="alert" id="update-alert" data-url-notification-state="<?php echo $urlUpdateNotificationState; ?>">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                <strong><?php eT('Security update!');?></strong> <?php eT('A security update is available.');?> <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate.');?></a>
            </div>
        <?php elseif(Yii::app()->session['unstable_update']):?>
        <div id="update-alert" data-url-notification-state="<?php echo $urlUpdateNotificationState; ?>" class="alert alert-info alert-dismissible unstable-update" role="alert" >
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" ><span>&times;</span></button>
                <strong><?php eT('New UNSTABLE update available:');?></strong> <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate or to download it.');?><strong><?php eT("You don't need an update key.");?></strong></a>
        </div>
        <?php else:?>
            <div class="alert alert-info alert-dismissible" role="alert" id="update-alert" data-url-notification-state="<?php echo $urlUpdateNotificationState; ?>">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                <strong><?php eT("New update available:");?> </strong> <a href="<?php echo $urlUpdate; ?>"><?php eT('Click here to use ComfortUpdate or to download it.');?></a>
            </div>
        <?php endif;?>
    </div>
<?php endif;?>
