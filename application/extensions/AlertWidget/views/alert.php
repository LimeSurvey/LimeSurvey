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

// Only add flexbox classes when using default div tag and icon is shown
$useFlexbox = ($tag === 'div' && $showIcon);

if (!isset($htmlOptions['class'])) {
    $htmlOptions['class'] = '';
}
if ($useFlexbox) {
    $htmlOptions['class'] .= ' d-flex align-items-start';
}

echo CHtml::openTag($tag, $htmlOptions);

if ($showIcon) {
    echo CHtml::openTag("span", ['class' => $icon . ' me-2 flex-shrink-0 align-self-center']);
    echo CHtml::closeTag("span");
}

// Only wrap in flex-grow div when using flexbox layout
if ($useFlexbox) {
    echo CHtml::openTag("div", ['class' => 'flex-grow-1']);
}

if ($header != '') {
    echo CHtml::openTag("span", ['class' => 'alert-header']);
    echo $header;
    echo CHtml::closeTag("span");
    echo CHtml::openTag('br');
}
echo $text;
if ($inErrorMode) {
    echo $this->render('error-summary', ['errors' => $errors]);
}

if ($useFlexbox) {
    echo CHtml::closeTag("div");
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