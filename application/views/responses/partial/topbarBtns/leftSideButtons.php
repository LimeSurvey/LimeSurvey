<?php


if ($hasResponsesReadPermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'response-summary',
            'id' => 'response-summary',
            'text' => gT('Summary'),
            'icon' => 'ri-list-unordered',
            'link' => Yii::App()->createUrl("responses/index/", ['surveyId' => $oSurvey->sid]),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}
if ($hasResponsesReadPermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'response-display',
            'id' => 'response-display',
            'text' => gT('Display responses'),
            'icon' => 'ri-list-check',
            'link' => Yii::App()->createUrl("responses/browse/", ['surveyId' => $oSurvey->sid]),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}

if ($hasResponsesCreatePermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'response-dataentry',
            'id' => 'response-dataentry',
            'text' => gT('Data entry'),
            'icon' => 'ri-keyboard-box-line',
            'link' => Yii::App()->createUrl("admin/dataentry/sa/view/surveyid/$oSurvey->sid"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}

if ($hasStatisticsReadPermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'response-statistics',
            'id' => 'response-statistics',
            'text' => gT('Statistics'),
            'icon' => 'ri-bar-chart-fill',
            'link' => Yii::App()->createUrl("admin/statistics/sa/index/surveyid/$oSurvey->sid"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );

    if ($isTimingEnabled == "Y") {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'response-statistics-time',
                'id' => 'response-statistics-time',
                'text' => gT('Timing statistics'),
                'icon' => 'ri-time-line',
                'link' => Yii::App()->createUrl("responses/time/", ['surveyId' => $oSurvey->sid]),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role' => 'button'
                ],
            ]
        );
    }
}

if ($hasResponsesExportPermission) { ?>
    <?php
    $exportDropdownItems = $this->renderPartial(
        '/responses/partial/topbarBtns/responsesExportDropdownItems',
        get_defined_vars(),
        true
    );
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-tools-button',
        'id' => 'ls-tools-button',
        'text' => gT('Export'),
        'icon' => 'ri-upload-2-fill',
        'isDropDown' => true,
        'dropDownContent' => $exportDropdownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]);
    ?>
<?php }

if ($hasResponsesCreatePermission) { ?>
        <?php
        $importDropdownItems = $this->renderPartial(
            '/responses/partial/topbarBtns/responsesImportDropdownItems',
            get_defined_vars(),
            true
        );
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-tools-button',
            'id' => 'ls-tools-button',
            'text' => gT('Import'),
            'icon' => 'ri-download-2-fill',
            'isDropDown' => true,
            'dropDownContent' => $importDropdownItems,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]);
        ?>
<?php }


if ($hasResponsesReadPermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'response-saved',
            'id' => 'response-saved',
            'text' => gT('View Saved but not submitted Responses'),
            'icon' => 'ri-save-line',
            'link' => Yii::App()->createUrl("admin/saved/sa/view/surveyid/$oSurvey->sid"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}


if ($hasResponsesDeletePermission) {
    if (!$oSurvey->isAnonymized && $oSurvey->isTokenAnswersPersistence) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'response-saved',
                'id' => 'response-saved',
                'text' => gT('Iterate survey'),
                'icon' => 'ri-repeat-fill',
                'link' => Yii::App()->createUrl("admin/dataentry/sa/iteratesurvey/surveyid/$oSurvey->sid"),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role' => 'button'
                ],
            ]
        );
    }
}

if ($hasResponsesDeletePermission) {

    $dataText = gT('Enter a list of response IDs that are to be deleted, separated by comma.');
    $dataText .= '<br/>';
    $dataText .= gT('Please note that if you delete an incomplete response during a running survey,
    the participant will not be able to complete it.');
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'response-batch-deletion',
            'id' => 'response-batch-deletion',
            'text' => gT('Batch deletion'),
            'icon' => 'ri-delete-bin-fill text-danger',
            'link' => Yii::App()->createUrl("responses/delete/", ["surveyId" => $oSurvey->sid]),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary selector--ConfirmModal',
                'role' => 'button',
                'data-post' => "{}",
                'data-show-text-area' => 'true',
                'data-use-ajax' => 'true',
                'data-grid-id' => 'responses-grid',
                'data-grid-reload' => 'true',
                'data-button-no' => gT('Cancel'),
                'data-button-yes' => gT('Delete'),
                'data-button-type' => 'btn-danger',
                'data-close-button-type' => 'btn-cancel',
                'data-text' => $dataText,
                'title' => gt('Batch deletion'),
            ],
        ]
    );
}

