<?php

//close button
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'back-button',
        'id' => 'back-button',
        'text' => gT('Close'),
        'icon' => 'fa fa-close',
        'link' => $this->createUrl("admin/labels/sa/view"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'role' => 'button'
        ],
    ]
);

//export
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'save-form-button',
        'text' => gT('Export'),
        'icon' => 'fa fa-check',
        'htmlOptions' => [
            'class' => 'btn btn-success',
            'role' => 'button',
            'data-form-id' => 'exportlabelset'
        ],
    ]
);


