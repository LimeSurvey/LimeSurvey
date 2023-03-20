<?php

//back button
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'back-button',
        'id' => 'back-button',
        'text' => gT('Back to list'),
        'icon' => 'ri-rewind-fill',
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
        'icon' => 'ri-check-fill',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'role' => 'button',
            'data-form-id' => 'labelsetform'
        ],
    ]
);
