<?php

//close
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'create-import-button',
        'id' => 'create-import-button',
        'text' => gT('Close'),
        'icon' => 'fa fa-close',
        'link' => Yii::app()->request->getUrlReferrer(Yii::app()->createUrl('admin/labels/sa/view')),
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
        'icon' => 'fa fa-check',
        'link' => $this->createUrl("Save and close"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'type' => 'button',
            'data-form-id' => 'mainform',
            'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');"
        ],
    ]
);

//save
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'save-form-button',
        'text' => gT('Save'),
        'icon' => 'fa fa-check',
        'htmlOptions' => [
            'class' => 'btn btn-success',
            'role' => 'button',
            'data-form-id' => 'mainform'
        ],
    ]
);
