<?php if ($oQuestion->qid !== 0) : ?>
    <?php if ($showToolsMenu) : ?>
        <?php $toolsDropDownItems = $this->renderPartial(
            '/questionAdministration/partial/topbarBtns/questionToolsDropdownMenu',
            get_defined_vars(),
            true
        ); ?>
        <!-- Tools  -->
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-question-editor-tools-button',
            'id' => 'ls-question-editor-tools-button',
            'text' => gT('Tools'),
            'isDropDown' => true,
            'dropDownContent' => $toolsDropDownItems,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]); ?>
    <?php endif; ?>

    <?php
    $this->renderPartial(
        '/surveyAdministration/partial/topbar/previewOrRunButton_view',
        [
            'survey' => $oSurvey,
            'surveyLanguages' => $surveyLanguages,
        ]
    );
    ?>

    <?php if ($hasSurveyContentUpdatePermission) : ?>
        <?php
        $this->renderPartial('/questionGroupsAdministration/partial/topbarBtns/previewGroupButton_view', get_defined_vars());
        $this->renderPartial('partial/topbarBtns/previewQuestionButton_view', get_defined_vars());
        ?>
    <?php endif; ?>
<?php else : ?>
    <!-- Import -->
    <?php if ($hasSurveyContentCreatePermission) :?>
        <?php if ($oSurvey->active != 'Y') : ?>
            <?php
            $this->widget(
                'ext.ButtonWidget.ButtonWidget',
                [
                    'id' => 'import-button',
                    'name' => 'import-button',
                    'text' => gT('Import question'),
                    'icon' => 'ri-download-2-fill icon',
                    'link' => Yii::App()->createUrl("questionAdministration/importView", ["surveyid" => $surveyid, "groupid" => $gid]),
                    'htmlOptions' => [
                        'class' => 'btn btn-outline-secondary',
                        'role' => 'button',
                    ],
                ]
            );
            ?>
        <?php else : ?>
        <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("You can not import questions because the survey is currently active."); ?>">
            <?php
            $this->widget(
                'ext.ButtonWidget.ButtonWidget',
                [
                    'name' => 'import-button',
                    'text' => gT('Import question'),
                    'icon' => 'ri-download-2-fill icon',
                    'htmlOptions' => [
                        'class' => 'btn btn-outline-secondary btntooltip',
                        'role' => 'button',
                        'disabled' => 'disabled',
                    ],
                ]
            );
            ?>
        </span>
        <?php endif; ?>
    <?php endif;?>
<?php endif; ?>

