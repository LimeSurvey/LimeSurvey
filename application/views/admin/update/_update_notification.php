<?php
/**
 * This view render the Update Notfication displayed on top of the LimeSurvey admin interface.
 * It is called from SurveyCommonAction which launch AdminController::_getUpdateNotification();
 *
 * @var $security_update_available
 */

?>
<?php
    $urlUpdate = Yii::app()->createUrl("admin/update");
    $urlUpdateNotificationState = Yii::app()->createUrl("admin/update/sa/notificationstate");
?>

<?php if(Yii::app()->session['notificationstate']==1):?>
    <div class="col-12" id="update-container">
        <?php if($security_update_available):?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => '<strong>' . gT("Security update!") . '</strong> ' . gT("A security update is available.") . ' <a href="' . $urlUpdate . '"> ' . gT('Click here to use ComfortUpdate.') . '</a>',
                'type' => 'warning',
                'showCloseButton' => true,
                'htmlOptions' => ['id' => 'update-alert', 'data-url-notification-state' => $urlUpdateNotificationState]
            ]);
            ?>
        <?php elseif(Yii::app()->session['unstable_update']):?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => '<strong>' . gT("New UNSTABLE update available:") . '</strong> <a href="' . $urlUpdate . '"> ' . gT('Click here to use ComfortUpdate or to download it.') . ' <strong>' . gT("You don't need an update key.") . '</strong></a>',
                'type' => 'info',
                'showCloseButton' => true,
                'htmlOptions' => ['id' => 'update-alert', 'data-url-notification-state' => $urlUpdateNotificationState]
            ]);
            ?>
        <?php else:?>
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => '<strong>' . gT("New update available:") . '</strong> <a href="' . $urlUpdate . '"> ' . gT('Click here to use ComfortUpdate or to download it.') . '</a>',
                'type' => 'info',
                'showCloseButton' => true,
                'htmlOptions' => ['id' => 'update-alert', 'data-url-notification-state' => $urlUpdateNotificationState]
            ]);
            ?>
        <?php endif;?>
    </div>
<?php endif;?>
