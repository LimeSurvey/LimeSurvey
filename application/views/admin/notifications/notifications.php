<?php

$alertTypes = ['success', 'primary', 'secondary', 'danger', 'error', 'warning', 'info', 'light', 'dark'];
?>

<div class="row">
    <div id="notif-container" class="col-12 content-right">
        <?php
        foreach ($aMessage as $message) : ?>
            <?php
            if (isset($message['type']) && in_array($message['type'], $alertTypes)) {
                $alertType = $message['type'];
                if ($alertType == 'error') {
                    $alertType = 'danger';
                }
            } else {
                $alertType = 'success';
            }
            $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => $message['message'],
                    'type' => $alertType,
                    'isFilled' => false,
                    'showCloseButton' => true,
                    'htmlOptions' => [
                        'class' => 'non-ajax-alert',
                    ],
                ]);
            ?>
            <?php
        endforeach; ?>
    </div>
</div>
