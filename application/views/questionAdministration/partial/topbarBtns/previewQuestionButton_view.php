<?php if($hasSurveyContentUpdatePermission): ?>
    <?php
    $languagesDropDownItems = '';
    if (count($surveyLanguages) > 1) {
        $languagesDropDownItems = '<ul class="dropdown-menu" style="min-width : 252px;">';
        $languagesDropDownItems .= $this->renderPartial(
            '/surveyAdministration/partial/topbar/languagesDropdownItems',
            [
                'surveyLanguages' => $surveyLanguages,
                'type' => "question",
                'sid' => $surveyid,
                'gid' => $gid,
                'qid' => $qid
            ],
            true
        );
        $languagesDropDownItems .= '</ul>';
    }
    ?>
    <!-- Preview question -->
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-preview-question',
        'id' => 'ls-preview-question',
        'text' => gT('Preview question'),
        'icon' => 'ri-eye-fill',
        'isDropDown' => count($surveyLanguages) > 1,
        'dropDownContent' => $languagesDropDownItems,
        'link' => Yii::App()->createUrl("survey/index/action/previewquestion/sid/$surveyid/gid/$gid/qid/$qid"),
        'htmlOptions' => [
            'class' => 'btn btn-secondary btntooltip',
            'role' => 'button',
            'target' => '_blank',
        ],
    ]); ?>
<?php endif; ?>
