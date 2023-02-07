<?php

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
