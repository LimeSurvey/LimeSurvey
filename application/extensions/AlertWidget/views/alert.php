<?php
/** @var String $tag */

/** @var String $text */
/** @var String $type */
/** @var boolean $isFilled */
/** @var boolean $showIcon */
/** @var boolean $showCloseButton */
/** @var array $htmlOptions */

$alertClass = ' alert alert-';
$alertClass .= $isFilled ? 'filled-' . $type : $type . ' alert-dismissible';
if (!array_key_exists('class', $htmlOptions)) {
    $htmlOptions['class'] = $alertClass;
} else {
    $htmlOptions['class'] .= $alertClass;
}
$htmlOptions['role'] = 'alert';
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

if (isset($type) && array_key_exists($type, $alertTypesAndIcons)) {
    $messageType = $type;
    if ($messageType == 'error') {
        $messageType = 'danger';
    }
    $icon = $alertTypesAndIcons[$type];
} else {
    $messageType = 'success';
    $icon = 'ri-notification-2-line';
}

if($showIcon) {
    $content = CHtml::openTag("span", array('class' => $icon . ' me-2'));
    $content .= CHtml::closeTag("span");
}
$content .= $text;
if($showCloseButton) {
    $content .= CHtml::htmlButton(
        false,
        array('type' => 'button', 'class' => 'btn-close', 'data-bs-dismiss' => 'alert', 'aria-label' => gT("Close"))
    );
}
echo CHtml::tag($tag, $htmlOptions, $content, true);
