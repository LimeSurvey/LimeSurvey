<?php
$script = "
        var alertContainer = $('.non-ajax-alert');
        LS.autoCloseAlert(alertContainer);
        ";
Yii::app()->clientScript->registerScript('notif-autoclose', $script, CClientScript::POS_END);
?>

<div class="container-fluid">
    <div class="row">
        <div id="notif-container" class="col-12 content-right">
            <?php foreach ($aMessage as $message): ?>
                <?php
                if (isset($message['type']) && in_array($message['type'], array('error', 'success', 'danger', 'warning', 'info'))) {
                    $sType = $message['type'];
                    if ($sType == 'error') {
                        $sType = 'danger';
                    }
                } else {
                    $sType = 'success';
                }
                echo CHtml::openTag("div", array('class' => "alert alert-{$sType} alert-dismissible non-ajax-alert", 'role' => 'alert'));
                echo CHtml::htmlButton("<span></span>", array('type' => 'button', 'class' => 'btn-close', 'data-bs-dismiss' => 'alert', 'aria-label' => gT("Close")));
                echo $message['message'];
                echo CHtml::closeTag("div");
                ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
