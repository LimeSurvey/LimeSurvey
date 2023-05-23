<?php



$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'save-form-button',
        'text' => gT('Save'),
        'icon' => 'ri-check-fill',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'role' => 'button',
            'data-form-id' => 'labelsetform'
        ],
    ]
);
