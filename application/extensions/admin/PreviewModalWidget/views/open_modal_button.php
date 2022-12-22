<!-- The button -->


<?php

$this->widget('ext.InputWidget.InputWidget', [
    'name' => 'question[title]',
    'id' => 'selector__' . $this->widgetsJsName . '--buttonText',
    'value' => $this->currentSelected .  gT($this->debugKeyCheck) . $this->value,
    'isAttached' => true,
    'attachContent' => '<button type="button" data-bs-toggle="modal"
    aria-haspopup="true"
    aria-expanded="false"' .
        'data-bs-target="#selector__' . $this->widgetsJsName . '-modal"' .
        'class="btn position-absolute" style="position: absolute; top:3px; right:5px; background:#7C8191; color:#FFFFFF">
    Select  <span class="ri-arrow-drop-down-line"/> </button>',
    'wrapperHtmlOptions' => [
        'id' => 'trigger_' . $this->widgetsJsName . '_button',
        'class' => 'w-100'
    ],
]);
?>