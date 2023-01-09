<?php
$script = "
        var alertContainer = $('.non-ajax-alert');
        LS.autoCloseAlert(alertContainer, 1000000);
        ";
Yii::app()->clientScript->registerScript('notif-autoclose', $script, CClientScript::POS_END);

$alertTypesAndIcons = [
    'success' => 'ri-checkbox-circle-fill',
    'primary' => 'ri-notification-2-line',
    'secondary' => 'ri-notification-2-line',
    'danger' => 'ri-error-warning-fill',
    'error' => 'ri-error-warning-fill',
    'warning' => 'ri-alert-fill',
    'info' => 'ri-notification-2-line',
    'light' => 'ri-notification-2-line',
    'dark' => 'ri-notification-2-line',
];
?>

<div class="container-fluid">
    <div class="row">
        <div id="notif-container" class="col-12 content-right">
            <?php foreach ($aMessage as $message): ?>
                <?php
                if (isset($message['type']) && array_key_exists($message['type'], $alertTypesAndIcons)) {
                    $sType = $message['type'];
                    if ($sType == 'error') {
                        $sType = 'danger';
                    }
                    $icon = $alertTypesAndIcons[$message['type']];
                } else {
                    $sType = 'success';
                    $icon = 'ri-notification-2-line';
                }

                $iconElement = '<span class="' . $icon . ' me-2"></span>';
                echo CHtml::openTag("div", array('class' => "alert alert-{$sType} alert-dismissible non-ajax-alert", 'role' => 'alert'));
                echo CHtml::openTag("span", array('class' => $icon . ' me-2'));
                echo CHtml::closeTag("span");
                echo $message['message'];
                echo CHtml::htmlButton(false, array('type' => 'button', 'class' => 'btn-close', 'data-bs-dismiss' => 'alert', 'aria-label' => gT("Close")));
                echo CHtml::closeTag("div");
                ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
