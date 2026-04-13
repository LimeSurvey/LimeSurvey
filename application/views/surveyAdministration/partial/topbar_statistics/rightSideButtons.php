<?php
/** @var bool $experstats */

/** @var int $surveyid */

if (isset($expertstats) && $expertstats === true) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'expert-mode',
            'id' => 'expert-mode',
            'text' => gT("Expert mode"),
            'icon' => 'ri-bar-chart-fill',
            'link' => Yii::app()->createUrl('/admin/statistics/sa/index/surveyid/' . $surveyid),
            'htmlOptions' => [
                'class' => 'btn btn-info',
            ],
        ]
    );
} else {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'simple-mode',
            'id' => 'simple-mode',
            'text' => gT("Simple mode"),
            'icon' => 'ri-bar-chart-fill',
            'link' => Yii::app()->createUrl('/admin/statistics/sa/simpleStatistics/surveyid/' . $surveyid),
            'htmlOptions' => [
                'class' => 'btn btn-info',
            ],
        ]
    );

    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'view-button',
            'id' => 'view-button',
            'text' => gT("View statistics"),
            'htmlOptions' => [
                'class' => 'btn btn-primary',
                'data-submit-form' => 1,
            ],
        ]
    );

    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'clear-button',
            'id' => 'clear-button',
            'text' => gT("Clear"),
            'icon' => 'ri-refresh-line',
            'link' => Yii::app()->createUrl("admin/statistics/sa/index/", array('surveyid' => $surveyid)),
            'htmlOptions' => [
                'class' => 'btn btn-warning',
            ],
        ]
    );
}

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'close-button',
        'id' => 'close-button',
        'text' => gT("Close"),
        'icon' => 'ri-close-fill',
        'link' => Yii::app()->createUrl("surveyAdministration/view", ["surveyid" => $surveyid]),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);
