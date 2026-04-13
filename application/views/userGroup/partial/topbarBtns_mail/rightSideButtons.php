<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'reset-form-button',
        'id' => 'reset-form-button',
        'text' => gT('Reset'),
        'icon' => 'ri-refresh-line',
        'htmlOptions' => [
            'class' => 'btn btn-warning',
            'role' => 'button',
            'type' => 'reset',
            'form' => 'mailusergroup'
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'reset-form-button',
        'id' => 'save-form-button',
        'text' => gT('Send'),
        'icon' => 'ri-mail-fill',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'type' => 'submit',
            'data-form-id' => 'mailusergroup'
        ],
    ]
);
