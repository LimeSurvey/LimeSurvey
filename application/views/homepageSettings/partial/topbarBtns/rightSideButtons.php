<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'reset-button',
        'id' => 'reset-button',
        'text' => gT('Reset'),
        'icon' => 'ri-refresh-line',
        'htmlOptions' => [
            'class' => 'btn btn-warning',
            'data-bs-toggle' => "modal",
            'data-bs-target' => '#confirmation-modal',
            'data-btnclass' => 'btn-primary',
            'data-btntext' => gT('OK'),
            'data-post-url' => $this->createUrl('homepageSettings/resetAllBoxes/'),
            'data-title' => gT("Please confirm"),
            'data-message' => gT('This will delete all current boxes to restore the default ones. Are you sure you want to continue?'),
        ],
    ]
);


