<?php

//white close button
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'close-button',
        'id' => 'close-button',
        'text' => gT('Close'),
        'icon' => 'fa fa-close',
        'link' => $this->createUrl('admin/index'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);

//save button
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'save-form-button', //this one is important to trigger the click for submit button
        'text' => gT('Save'),
        'icon' => 'fa fa-check',
        'htmlOptions' => [
            'class' => 'btn btn-success',
            'data-form-id' => 'addnewsurvey',
            'role' => 'button',
            'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');"
        ],
    ]
);

