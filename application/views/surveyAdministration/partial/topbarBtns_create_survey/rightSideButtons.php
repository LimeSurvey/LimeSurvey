<?php

//white close button
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'close-button',
        'id' => 'close-button',
        'text' => gT('Close'),
        'icon' => 'ri-close-fill',
        'link' => $this->createUrl('dashboard/view'),
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
        'icon' => 'ri-check-fill',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'data-form-id' => 'addnewsurvey',
            'role' => 'button',
            'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');"
        ],
    ]
);

