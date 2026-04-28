<?php
/**
 * @var string $backUrl
 */

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-button',
        'id' => 'save-button',
        'text' => gT("Save"),
        'icon' => 'ri-check-fill',
        'link' => '',
        'htmlOptions' => [
            'class' => 'btn btn-primary float-end',
            'type' => 'button'
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'back-button',
        'id' => 'back-button',
        'text' => gT("Group list"),
        'icon' => 'ri-arrow-left-s-line',
        'link' => $backUrl,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-and-new-question-button',
        'id' => 'save-and-new-question-button',
        'text' => gT("Save & add question"),
        'icon' => 'ri-add-line',
        'link' => '',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'type' => 'button'
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-and-new-button',
        'id' => 'save-and-new-button',
        'text' => gT("Save & add group"),
        'icon' => 'ri-add-line',
        'link' => '',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'type' => 'button'
        ],
    ]
);
