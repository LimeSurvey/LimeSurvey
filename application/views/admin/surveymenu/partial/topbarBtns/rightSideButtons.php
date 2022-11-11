<?php

/** @var bool $resetPermission */

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'boxes-close-button',
        'id' => 'boxes-close-button',
        'text' => gT('Back'),
        'icon' => 'fa fa-backward',
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
            'icon' => 'fa fa-refresh',
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
        'icon' => 'fa fa-refresh',
        'htmlOptions' => [
            'class' => 'btn btn-warning',
        ],
    ]
);
