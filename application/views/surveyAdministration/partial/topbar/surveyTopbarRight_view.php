<?php
/**
 * @var string $closeUrl
 * @var string $returnUrl
 * @var string $editorUrl
 * @var bool   $enableEditorButton
 * @var bool   $editorEnabled
 */

?>

<?php
// new question editor btn
if (isset($editorUrl)) {
 $this->renderPartial('/surveyAdministration/partial/topbar/_newQuestionEditorBtn', [
     'editorUrl' => $editorUrl,
     'enableEditorButton' => $enableEditorButton,
     'editorEnabled' => $editorEnabled
 ]);
}
?>

<!-- White Close button -->
<?php
if (!empty($showWhiteCloseButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'close_button',
            'text' => gT('Close'),
            'icon' => 'ri-close-fill',
            'link' => $closeUrl,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}
?>

<!-- Save and Close -->
<?php
if (!empty($showSaveAndCloseButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'save-and-close-button',
            'id' => 'save-and-close-button',
            'text' => gT('Save and close'),
            'icon' => 'ri-checkbox-circle-fill',
            'link' => $closeUrl,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button',
                'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');",
            ],
        ]
    );
}
?>

<!-- Green Save and Close -->
<?php
if (!empty($showGreenSaveAndCloseButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'save-and-close-button',
            'id' => 'save-and-close-button',
            'text' => gT('Save and close'),
            'icon' => 'ri-checkbox-circle-fill',
            'link' => $closeUrl,
            'htmlOptions' => [
                'class' => 'btn btn-primary',
                'role' => 'button',
                'onclick' => "$(this).addClass('disabled').attr('onclick', 'return false;');",
            ],
        ]
    );
}
?>

<!-- Save -->
<?php
if (!empty($showSaveButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'save-button',
            'id' => 'save-button',
            'text' => gT('Save'),
            'icon' => 'ri-check-fill',
            'htmlOptions' => [
                'class' => 'btn btn-primary float-end',
                'role' => 'button'
            ],
        ]
    );
}
?>

<!-- Close -->
<?php
if (!empty($showCloseButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => '',
            'text' => gT('Close'),
            'icon' => 'ri-close-fill',
            'link' => $closeUrl,
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button',
            ],
        ]
    );
}
?>

<!-- Export -->
<?php
if (!empty($showExportButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'export-button',
            'id' => 'export-button',
            'text' => gT('Export'),
            'icon' => 'ri-upload-2-fill',
            'htmlOptions' => [
                'class' => 'btn btn-primary',
                'role' => 'button',
                'data-submit-form' => 1,
            ],
        ]
    );
}
?>

<!-- Import -->
<?php
if (!empty($showImportButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'import-button',
            'id' => 'import-button',
            'text' => gT('Import'),
            'icon' => 'ri-download-2-fill',
            'htmlOptions' => [
                'class' => 'btn btn-primary',
                'role' => 'button',
                'data-submit-form' => 1,
            ],
        ]
    );
}
?>
