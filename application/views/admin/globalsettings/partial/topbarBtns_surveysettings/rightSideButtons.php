<?php


//close
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'boxes-close-button',
        'id' => 'boxes-close-button',
        'text' => gT('Close'),
        'icon' => 'ri-close-fill',
        'link' => Yii::app()->createUrl('dashboard/view'),
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
        'icon' => 'ri-checkbox-circle-fill',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');",
            'data-form-id' => 'survey-settings-form'
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
        'icon' => 'ri-check-fill',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');",
            'data-form-id' => 'survey-settings-form'
        ],
    ]
);
