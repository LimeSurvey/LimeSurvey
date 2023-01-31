<?php

/** @var bool $resetPermission */

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
if ($resetPermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'restoreBtn',
            'id' => 'restoreBtn',
            'text' => gT('Reset'),
            'icon' => 'ri-refresh-line',
            'htmlOptions' => [
                'class' => 'btn btn-warning',
            ],
        ]
    );
}

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'reorderentries',
        'id' => 'reorderentries',
        'text' => gT('Reorder'),
        'icon' => 'ri-refresh-line',
        'htmlOptions' => [
            'class' => 'btn btn-warning',
        ],
    ]
);
