<?php
/**
 * @var $hasResponsesCreatePermission bool
 * @var $hasResponsesExportPermission bool
 * @var $hasStatisticsReadPermission bool
 * @var $hasResponsesDeletePermission bool
 * @var $oSurvey Survey
 */

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

if ($hasResponsesExportPermission) {
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
 }

if ($hasResponsesCreatePermission) {
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
 }

if ($hasStatisticsReadPermission) {
    if ($oSurvey->getIsSaveTimings()) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name'        => 'response-timingStatistics',
                'id'          => 'response-timingStatistics',
                'text'        => gT('Timing statistics'),
                'icon'        => 'ri-time-line',
                'link'        => App()->createUrl("responses/time/", ['surveyId' => $oSurvey->sid]),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role'  => 'button'
                ],
            ]
        );
    } else {
        ?>
        <span class="btntooltip d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("Timing statistics are disabled for this survey."); ?>"
              data-bs-toggle="tooltip" data-bs-placement="bottom">
        <?php
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name'        => 'response-timingStatistics',
                'id'          => 'response-timingStatistics',
                'text'        => gT('Timing statistics'),
                'icon'        => 'ri-time-line',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role'  => 'button',
                    'disabled' => 'disabled'
                ],
            ]
        );
        ?>
        </span>
        <?php
    }
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
    } else {
        // Show a disabled button if the survey is anonymized or token persistence is disabled
        ?>
        <span class="btntooltip d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("This survey is anonymized and/or token persistence is disabled."); ?>" data-bs-toggle="tooltip" data-bs-placement="bottom">
        <?php
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'response-saved',
                'id' => 'response-saved',
                'text' => gT('Iterate survey'),
                'icon' => 'ri-repeat-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary disabled btntooltip',
                    'role' => 'button',
                    'disabled' => 'disabled'                ],
            ]
        );
        ?>
        </span>
        <?php
    }
}
