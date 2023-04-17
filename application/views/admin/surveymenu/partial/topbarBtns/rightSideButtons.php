<?php

/** @var bool $resetPermission */

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
