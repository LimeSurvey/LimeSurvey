<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'ls-question-tools-button',
        'id' => 'ls-question-tools-button',
        'text' => gT('Back'),
        'icon' => 'fa fa-backward"',
        'link' => $this->createUrl('admin/index'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'reset-button',
        'id' => 'reset-button',
        'text' => gT('Reset'),
        'icon' => 'fa fa-refresh',
        'link' => $this->createUrl('homepageSettings/resetAllBoxes/'),
        'htmlOptions' => [
            'class' => 'btn btn-warning',
            'data-confirm' => gT('This will delete all current boxes to restore the default ones. Are you sure you want to continue?')
        ],
    ]
);

//this save button should only be visible when tab #boxsettings is active
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save_boxes_setting',
        'id' => 'save_boxes_setting',
        'text' => gT('Save'),
        'icon' => 'fa fa-refresh',
        'htmlOptions' => [
            'class' => 'btn btn-success',
            'data-url' => $this->createUrl('homepageSettings/updateBoxesSettings')
        ],
    ]
);

