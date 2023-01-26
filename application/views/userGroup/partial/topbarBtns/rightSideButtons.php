<?php

/** @var bool $addGroupSave */

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'boxes-close-button',
        'id' => 'boxes-close-button',
        'text' => gT('Back'),
        'icon' => 'fa fa-backward',
        'link' => $backUrl ?? Yii::app()->createUrl('admin/index'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);

if ($addGroupSave) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'save-form-button',
            'id' => 'save-form-button',
            'text' => gT('Save'),
            'icon' => 'fa fa-check',
            'htmlOptions' => [
                'class' => 'btn btn-primary',
                'data-form-id' => 'usergroupform',
                'type' => 'submit',
                'role' => 'button'
            ],
        ]
    );
}
