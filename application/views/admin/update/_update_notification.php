<?php
/**
 * This view renders the update notification displayed on top of the LimeSurvey admin interface.
 * It is called from LayoutHelper::updatenotification() and SurveyCommonAction::updatenotification().
 *
 * @var bool $security_update_available Whether a security update is available
 * @var string[] $stability_labels Array of non-stable stability levels present in filtered updates (e.g. ['alpha', 'beta'])
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
            $labelMap = ['alpha' => gT('Alpha'), 'beta' => gT('Beta'), 'rc' => gT('Release Candidate')];
            $labelNames = [];
            foreach ($stability_labels as $label) {
                if (isset($labelMap[$label])) {
                    $labelNames[] = $labelMap[$label];
                }
            }
            $stabilityText = !empty($labelNames) ? implode(', ', $labelNames) : gT('unstable');
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => '<strong>' . sprintf(gT('New %s update available:'), $stabilityText) . '</strong> <a href="' . $urlUpdate . '"> ' . gT('Click here to use ComfortUpdate or to download it.') . ' <strong>' . gT("You don't need an update key.") . '</strong></a>',
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
