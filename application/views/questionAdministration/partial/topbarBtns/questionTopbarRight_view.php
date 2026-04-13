<?php
/**
 * @var Survey $oSurvey
 * @var string $qid
 */

?>

<?php
// new question editor btn
$this->renderPartial('/surveyAdministration/partial/topbar/_newQuestionEditorBtn', [
    'editorUrl' => $editorUrl,
    'enableEditorButton' => $enableEditorButton,
    'editorEnabled' => $editorEnabled
]);
?>

<!-- Edit button -->
<?php
if ($hasSurveyContentUpdatePermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'id' => 'questionEditorButton',
            'name' => 'questionEditorButton',
            'text' => gT('Edit'),
            'icon' => 'ri-pencil-fill',
            'link' => '#',
            'htmlOptions' => [
                'class' => 'btn btn-primary pjax',
                'onclick' => "LS.questionEditor.showEditor(); return false;",
                'role' => 'button',
            ],
        ]
    );
}
