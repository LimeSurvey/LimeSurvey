

<?php if($hasSurveyContentUpdatePermission): ?>
    <?php
    $languagesDropDownItems = '';
    if (count($surveyLanguages) > 1) {
        $languagesDropDownItems = '<ul class="dropdown-menu" style="min-width : 252px;">';
        $languagesDropDownItems .= $this->render(
            'includes/languagesDropdownItems',
            [
                'surveyLanguages' => $surveyLanguages,
                'type' => 'questionGroup',
                'sid' => $surveyid,
                'gid' => $gid,
            ],
            true
        );
        $languagesDropDownItems .= '</ul>';
    }
    ?>
    <!-- Preview group -->
    <div class="d-inline-flex">
    <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-group-preview-button',
            'id' => 'ls-group-preview-button',
            'text' => gT('Preview question group'),
            'icon' => 'fa fa-eye',
            'isDropDown' => count($surveyLanguages) > 1,
            'dropDownContent' => $languagesDropDownItems,
            'link' => Yii::App()->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"),
            'htmlOptions' => [
                'class' => 'btn btn-secondary btntooltip',
                'role' => 'button',
                'target' => '_blank',
            ],
        ]); ?>
    </div>
<?php endif; ?>

