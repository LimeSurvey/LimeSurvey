
<?php /* Ported from previous versions: Pending to adapt to screen own JS for saving (and validations) 
<!-- Save and new group -->
<?php if(!empty($showSaveAndNewGroupButton)): ?>
    <a class="btn btn-outline-secondary" id='save-and-new-button' role="button">
        <span class="ri-add-box-fill"></span>
        <?php eT("Save and new group"); ?>
    </a>
<?php endif; ?>

<!-- Save and add question -->
<?php if(!empty($showSaveAndNewQuestionButton)): ?>
    <a class="btn btn-outline-secondary" id='save-and-new-question-button' role="button">
        <span class="ri-add-line"></span>
        <?php eT("Save and add question"); ?>
    </a>
<?php endif; ?>
*/ ?>

<?php
// new question editor btn
$this->renderPartial('/surveyAdministration/partial/topbar/_newQuestionEditorBtn', [
    'editorUrl' => $editorUrl,
    'enableEditorButton' => $enableEditorButton,
    'editorEnabled' => $editorEnabled
]);
?>

<!-- Close -->
<?php if(!empty($showCloseButton)): ?>
    <?php if (!empty($oQuestion->qid)): ?>
        <?php
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'close-button',
                'text' => gT("Close"),
                'icon' => 'ri-close-fill',
                'link' => '#',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role' => 'button',
                    'onclick' => "LS.questionEditor.showOverview(); return false;",
                ],
            ]
        );
        ?>
    <?php else: ?>
        <?php
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'close-button',
                'text' => gT("Close"),
                'icon' => 'ri-close-fill',
                'link' => $closeUrl,
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'role' => 'button',
                ],
            ]
        );
        ?>
    <?php endif; ?>
<?php endif;?>

<!-- Save and close -->
<?php if(!empty($showSaveAndCloseButton)): ?>
    <?php
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'id' => 'save-and-close-button-create-question',
            'name' => 'save-and-close-button-create-question',
            'text' => gT('Save and close'),
            'icon' => 'ri-checkbox-fill',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'onclick' => "return LS.questionEditor.checkIfSaveIsValid(event, 'overview');"
            ],
        ]
    );
    ?>
<?php endif; ?>

<!-- Save -->
<?php if(!empty($showSaveButton)): ?>
    <?php
    $htmlOptions = [
        'class' => 'btn btn-primary',
        'onclick' => "return LS.questionEditor.checkIfSaveIsValid(event, 'editor');"
    ];
    if ($oQuestion->qid !== 0) {
        $htmlOptions['data-save-with-ajax'] = "true";
    }
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'id' => 'save-button-create-question',
            'name' => 'save-button-create-question',
            'text' => gT('Save'),
            'icon' => 'ri-check-fill',
            'htmlOptions' => $htmlOptions,
        ]
    );
    ?>
<?php endif; ?>
