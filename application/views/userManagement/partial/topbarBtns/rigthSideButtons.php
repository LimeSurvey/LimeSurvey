<?php
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'ls-question-tools-button',
        'id' => 'ls-question-tools-button',
        'text' => gT('Back'),
        'icon' => 'fa fa-backward"',
        'link' => $this->createUrl('admin/index'),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);
