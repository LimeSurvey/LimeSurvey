<?php

//back button
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'back-button',
        'id' => 'back-button',
        'text' => gT('Back to list'),
        'icon' => 'fa fa-backward',
        'link' => $this->createUrl("admin/labels/sa/view"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'role' => 'button'
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'save-form-button',
        'text' => gT('Save'),
        'icon' => 'fa fa-check',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'role' => 'button',
            'data-form-id' => 'labelsetform'
        ],
    ]
);
