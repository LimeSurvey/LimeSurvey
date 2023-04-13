<?php

/** @var $oSurvey Survey */
/** @var $hasSurveyContentCreatePermission */

if ($oSurvey->isActive) : ?>
    <span class="btntooltip d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom"
          title="<?php eT("This survey is currently active."); ?>">
        <?php
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'add-new-group-button',
                'text' => gT('Add new group'),
                'icon' => 'ri-add-circle-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary btntooltip',
                    'disabled' => 'disabled',
                ],
            ]
        );
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'import-group-button',
                'text' => gT('Import group'),
                'icon' => 'ri-download-2-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary btntooltip',
                    'disabled' => 'disabled',
                ],
            ]
        );
        ?>
    </span>
<?php elseif ($hasSurveyContentCreatePermission) : ?>
    <!-- Add group -->
    <?php
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'add-new-group-button',
            'text' => gT('Add new group'),
            'icon' => 'ri-add-circle-fill',
            'link' => Yii::App()->createUrl("questionGroupsAdministration/add/surveyid/" . $oSurvey->sid),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
    ?>
    <!-- Import -->
    <?php
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'import-group-button',
            'text' => gT('Import group'),
            'icon' => 'ri-download-2-fill',
            'link' => Yii::App()->createUrl("questionGroupsAdministration/importview/surveyid/" . $oSurvey->sid),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
    ?>
<?php endif; ?>
