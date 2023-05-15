<?php
/** @var bool $ownsAddAttributeButton */

if (isset($ownsAddAttributeButton) && ($ownsAddAttributeButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'addParticipantAttributeName',
            'id' => 'addParticipantAttributeName',
            'text' => gT('Add new attribute'),
            'icon' => 'ri-add-circle-fill',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => '',
        'text' => gT('Back'),
        'icon' => 'ri-rewind-fill',
        'link' => Yii::app()->createUrl('admin/index'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);