<?php

/** @var bool $hasPermissionExport */

if ($hasPermissionExport) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'exportButton',
            'id' => 'exportButton',
            'text' => gT('Export multiple label sets'),
            'icon' => 'ri-upload-2-fill',
            'link' => $this->createUrl("admin/labels/sa/exportmulti"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}
