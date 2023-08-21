<?php

/** @var bool $addGroupSave */

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
