<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'createnewmenuentry',
        'id' => 'createnewmenuentry',
        'text' => gT('Add user group'),
        'icon' => 'ri-user-add-line',
        'link' => $this->createUrl("userGroup/addGroup"),
        'htmlOptions' => [
            'class' => 'btn btn-primary',
        ],
    ]
);
