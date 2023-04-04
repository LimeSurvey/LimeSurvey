<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-button',
        'id' => 'save-button',
        'text' => gT("Import"),
        'icon' => 'ri-download-fill',
        'link' => '',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'type' => 'button'
        ],
    ]
);
