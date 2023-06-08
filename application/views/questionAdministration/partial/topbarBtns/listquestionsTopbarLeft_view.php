<?php if ($oSurvey->isActive): ?>
    <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block">
        <?php
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'add-question-button',
                'text' => gT('Add new question'),
                'icon' => 'ri-add-circle-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary btntooltip',
                    'disabled' => 'disabled'
                ],
            ]
        );
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'import-question-button',
                'text' => gT('Import a question'),
                'icon' => 'ri-download-2-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary btntooltip',
                    'disabled' => 'disabled'
                ],
            ]
        );
        ?>
    </span>
<?php elseif ($hasSurveyContentCreatePermission): ?>
    <?php if (!$oSurvey->groups): ?>
        <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block">
            <?php
            $this->widget(
                'ext.ButtonWidget.ButtonWidget',
                [
                    'name' => 'add-question-button',
                    'text' => gT('Add new question'),
                    'icon' => 'ri-add-circle-fill',
                    'htmlOptions' => [
                        'class' => 'btn btn-outline-secondary btntooltip',
                        'disabled' => 'disabled'
                    ],
                ]
            );
            $this->widget(
                'ext.ButtonWidget.ButtonWidget',
                [
                    'name' => 'import-question-button',
                    'text' => gT('Import a question'),
                    'icon' => 'ri-download-2-fill',
                    'htmlOptions' => [
                        'class' => 'btn btn-outline-secondary btntooltip',
                        'disabled' => 'disabled'
                    ],
                ]
            );
            ?>
        </span>
    <?php else : ?>
        <?php
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'add-question-button',
                'text' => gT('Add new question'),
                'icon' => 'ri-add-circle-fill',
                'link' => Yii::App()->createUrl("questionAdministration/create/surveyid/" . $oSurvey->sid),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary btntooltip',
                ],
            ]
        );
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'import-question-button',
                'text' => gT('Import a question'),
                'icon' => 'ri-download-2-fill',
                'link' => Yii::App()->createUrl("questionAdministration/importView/surveyid/" . $oSurvey->sid),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary btntooltip',
                ],
            ]
        );
        ?>
    <?php endif; ?>
<?php endif; ?>
