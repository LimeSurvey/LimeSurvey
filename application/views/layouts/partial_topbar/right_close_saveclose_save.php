<?php

/** @var bool $isCloseBtn */
/** @var bool $isSaveAndCloseBtn */
/** @var bool $isSaveBtn */

if ($isCloseBtn) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'close-button',
            'id' => 'close-button',
            'text' => gT('Close'),
            'icon' => 'fa fa-close',
            'link' => $backUrl ?? Yii::app()->createUrl('admin/index'),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

if ($isSaveAndCloseBtn) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'save-and-close-form-button',
            'id' => 'save-and-close-form-button',
            'text' => gT('Save and close'),
            'icon' => '',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'data-form-id' => $formIdSaveClose ?? '',
                'type' => 'submit',
                'role' => 'button'
            ],
        ]
    );
}

if ($isSaveBtn) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'save-form-button',
            'id' => 'save-form-button',
            'text' => gT('Save'),
            'icon' => 'fa fa-check',
            'htmlOptions' => [
                'class' => 'btn btn-primary',
                'data-form-id' => $formIdSave ?? '',
                'type' => 'submit',
                'role' => 'button'
            ],
        ]
    );
}
