<?php

/** @var String $tag */
/** @var String $text */
/** @var String $header */
/** @var boolean $showIcon */
/** @var boolean $showCloseButton */
/** @var array $errors */
/** @var boolean $inErrorMode */
/** @var array $htmlOptions */
/** @var String $icon */

echo CHtml::openTag($tag, $htmlOptions);
if ($showIcon && $header == '') {
    echo CHtml::openTag("span", ['class' => $icon . ' me-2']);
    echo CHtml::closeTag("span");
}

if ($header != '') {
    echo CHtml::openTag("span", ['class' => 'alert-header']);
    if ($showIcon) {
        echo CHtml::openTag("span", ['class' => $icon . ' me-2']);
        echo CHtml::closeTag("span");
    }
    echo $header;
    echo CHtml::closeTag("span");
    echo CHtml::openTag('br');
}
echo $text;
if ($inErrorMode) {
    echo $this->render('error-summary', ['errors' => $errors]);
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
