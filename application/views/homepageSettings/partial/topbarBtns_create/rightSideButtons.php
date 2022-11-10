<?php

//close
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'boxes-close-button',
        'id' => 'boxes-close-button',
        'text' => gT('Close'),
        'icon' => 'fa fa-close',
        'link' => $this->createUrl('homepageSettings/index'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);

//save and close
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-and-close-form-button',
        'id' => 'save-and-close-form-button',
        'text' => gT('Save and close'),
        'icon' => 'fa fa-saved',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');",
            'data-form-id' => 'boxes-form'
        ],
    ]
);

//Save
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'save-form-button',
        'text' => gT('Save'),
        'icon' => 'fa fa-check',
        'htmlOptions' => [
            'class' => 'btn btn-success',
            'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');",
            'data-form-id' => 'boxes-form'
        ],
    ]
);
