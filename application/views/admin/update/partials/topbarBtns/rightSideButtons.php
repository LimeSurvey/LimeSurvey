<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'back-button',
        'id' => 'back-button',
        'text' => gT('Manage your key'),
        'icon' => 'fa fa-key',
        'link' => $this->createUrl('admin/update/sa/managekey/'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);
