<?php

/** @var bool $addGroupSave */

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'boxes-close-button',
        'id' => 'boxes-close-button',
        'text' => gT('Back'),
        'icon' => 'ri-rewind-fill',
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
            'icon' => 'ri-check-fill',
            'htmlOptions' => [
                'class' => 'btn btn-primary',
                'data-form-id' => 'usergroupform',
                'type' => 'submit',
                'role' => 'button'
            ],
        ]
    );
}
