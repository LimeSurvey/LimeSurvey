<?php

/** @var string $backUrl */

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'boxes-close-button',
        'id' => 'boxes-close-button',
        'text' => gT('Return to plugin list'),
        'icon' => 'ri-rewind-fill',
        'link' => $backUrl ?? Yii::app()->createUrl('admin/index'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);

//difference between tabs overview and settings for a single plugin
//close save$close and save button --- but ONLY if tab==settings is active --> use js to show/hide buttons
/*
echo Yii::app()->getController()->renderPartial(
    '/layouts/partial_topbar/right_close_saveclose_save',
    [
        'isCloseBtn' => true,
        'backUrl' => Yii::app()->createUrl('themeOptions'),
        'isSaveBtn' => true,
        'isSaveAndCloseBtn' => false,
        'formIdSave' => 'template-options-form'
    ],
    true
); */
