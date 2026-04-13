<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'back-button',
        'id' => 'back-button',
        'text' => gT('Manage your key'),
        'icon' => 'ri-key-2-fill',
        'link' => $this->createUrl('admin/update/sa/managekey/'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);
