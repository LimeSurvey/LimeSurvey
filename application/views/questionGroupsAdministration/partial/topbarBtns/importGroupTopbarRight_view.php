<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-button',
        'id' => 'save-button',
        'text' => gT("Import"),
        'icon' => 'ri-save-3-fill',
        'link' => '',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'type' => 'button'
        ],
    ]
);
