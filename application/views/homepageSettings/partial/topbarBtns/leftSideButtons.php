<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'create-box-button',
        'id' => 'create-box-button',
        'text' => gT('Create box'),
        'icon' => 'icon-add',
        'link' => $this->createUrl('homepageSettings/createBox/'),
        'htmlOptions' => [
            'class' => 'btn btn-primary',
        ],
    ]
);
