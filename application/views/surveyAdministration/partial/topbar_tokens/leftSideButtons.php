<?php


if ($hasTokensCreatePermission || $hasTokensImportPermission) {
    $createDropdownItems = $this->renderPartial(
        '/surveyAdministration/partial/topbar_tokens/tokensCreateDropdownItems',
        get_defined_vars(),
        true
    );
    ?>
    <?php $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-create-token-button',
        'id' => 'ls-create-token-button',
        'text' => gT('Add...'),
        'icon' => 'ri-add-circle-fill',
        'isDropDown' => true,
        'dropDownContent' => $createDropdownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]);
    ?>
    <?php
}

if ($tokenexists) {
    if ($hasTokensUpdatePermission || $hasSurveySettingsUpdatePermission) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'tokens-manage-attributes',
                'id' => 'tokens-manage-attributes',
                'text' => gT('Manage attributes'),
                'icon' => 'ri-server-fill',
                'link' => Yii::App()->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$oSurvey->sid"),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role' => 'button'
                ],
            ]
        );
    }
    
    if ($hasTokensExportPermission) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'tokens-export-attributes',
                'id' => 'tokens-export-attributes',
                'text' => gT('Export'),
                'icon' => 'ri-upload-2-fill',
                'link' => Yii::App()->createUrl("admin/tokens/sa/exportdialog/surveyid/$oSurvey->sid"),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role' => 'button'
                ],
            ]
        );
    }
    
    if ($hasTokensUpdatePermission) {
        $invRemDropDownItems = $this->renderPartial(
            '/surveyAdministration/partial/topbar_tokens/tokensInvRemDropdownItems',
            get_defined_vars(),
            true
        );
    
    
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-inv-rem-button',
            'id' => 'ls-inv-rem-button',
            'text' => gT('Invite & remind'),
            'icon' => 'ri-mail-settings-line',
            'isDropDown' => true,
            'dropDownContent' => $invRemDropDownItems,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]);
    
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'tokens-access-codes',
                'id' => 'tokens-access-codes',
                'text' => gT('Generate access codes'),
                'icon' => 'ri-settings-5-fill',
                'link' => Yii::App()->createUrl("admin/tokens/sa/tokenify/surveyid/$oSurvey->sid"),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role' => 'button'
                ],
            ]
        );
    
        $url = Yii::App()->createUrl("/admin/participants/sa/displayParticipants");
    
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'tokens-cpdb',
                'id' => 'tokens-cpdb',
                'text' => gT('View in CPDB'),
                'icon' => 'ri-group-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role' => 'button',
                    'onclick' => "window.LS.sendPost('$url',false,{'searchcondition': 'surveyid||equal|| $oSurvey->sid '});"
                ],
            ]
        );
    }
    
}
/* --> btn not necessary because it is already a side menu link (entry)
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'tokens-access-codes',
        'id' => 'tokens-access-codes',
        'text' => gT('Survey quotas'),
        'icon' => 'ri-bar-chart-horizontal-fill',
        'link' => Yii::App()->createUrl("admin/quotas/sa/index/surveyid/$oSurvey->sid"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'role' => 'button'
        ],
    ]
);
 */
