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
/** @var String $type */
/** @var boolean $isFilled */

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
    //change closing 'x' when type has a dark background color
    $addColorWhite = '';
    if (in_array($type, [ 'info', 'dark']) && $isFilled) {
        //closing x must be white
        $addColorWhite = 'btn-close-white';
    }
    echo CHtml::htmlButton(
        '',
        [
            'type' => 'button',
            'class' => 'btn-close ' . $addColorWhite,
            'data-bs-dismiss' => 'alert',
            'aria-label' => gT("Close")
        ]
    );
}
echo CHtml::closeTag($tag);
