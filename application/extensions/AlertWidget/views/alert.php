<?php

/** @var String $tag */

/** @var String $text */
/** @var String $type */
/** @var boolean $isFilled */
/** @var boolean $showIcon */
/** @var boolean $showCloseButton */
/** @var array $errors */
/** @var mixed $errorSummaryModel */
/** @var array $htmlOptions */
$inErrorMode = $errorSummaryModel !== null && !empty($errors);
$notInErrorMode = $errorSummaryModel === null;

$alertClass = ' d-flex whitespace-pre-wrap alert alert-';
$alertClass .= $isFilled ? 'filled-' . $type : $type;
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
if ($showCloseButton) {
    $htmlOptions['class'] .= ' alert-dismissible';
}
if ($notInErrorMode || $inErrorMode) {
    echo CHtml::openTag($tag, $htmlOptions);
    if ($showIcon) {
        echo CHtml::openTag("span", ['class' => $icon . ' me-2']);
        echo CHtml::closeTag("span");
    }

    if ($inErrorMode) {
        echo CHtml::openTag('div', ['class' => 'd-flex flex-column']);
        echo $text;
        echo $this->render('error-summary', ['errors' => $errors]);
        echo CHtml::closeTag('div');
    } else {
        echo $text;
    }
    if ($showCloseButton) {
        echo CHtml::htmlButton(
            '',
            [
                'type' => 'button',
                'class' => 'btn-close',
                'data-bs-dismiss' => 'alert',
                'aria-label' => gT("Close")
            ]
        );
    }
    echo CHtml::closeTag($tag);
}
