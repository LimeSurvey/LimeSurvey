<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'createnewmenuentry',
        'id' => 'createnewmenuentry',
        'text' => gT('Add user group'),
        'icon' => 'icon-add text-success',
        'link' => $this->createUrl("userGroup/addGroup"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);
