<?php

/** @var bool $hasPermissionCreate */

if ($hasPermissionCreate) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'create-import-button',
            'id' => 'create-import-button',
            'text' => gT('Create or import new label set(s)'),
            'icon' => 'icon-add',
            'link' => $this->createUrl("admin/labels/sa/newlabelset"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}
