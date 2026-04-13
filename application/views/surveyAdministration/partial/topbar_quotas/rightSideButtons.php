<?php

$linkQuickCsv = Yii::App()->createUrl("quotas/quickCSVReport/surveyid/$surveyid");
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'quota-quick-csv',
        'id' => 'quota-quick-csv',
        'text' => gT('Quick CSV report'),
        'icon' => '',
        'link' => '',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'onClick' => "window.open('$linkQuickCsv', '_top')"
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'quota-add',
        'id' => 'quota-add',
        'text' => gT('Add new quota'),
        'icon' => '',
        'link' => Yii::App()->createUrl("quotas/AddNewQuota/surveyid/$surveyid"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);
