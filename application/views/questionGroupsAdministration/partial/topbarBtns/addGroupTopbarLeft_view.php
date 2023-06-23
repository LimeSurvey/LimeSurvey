<?php

/** @var int $surveyid */

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'import-group',
        'id' => 'import-group',
        'text' => gT("Import group"),
        'icon' => 'ri-download-fill',
        'link' => Yii::App()->createUrl("questionGroupsAdministration/importview/surveyid/" . $surveyid),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'role' => 'button'
        ],
    ]
);
