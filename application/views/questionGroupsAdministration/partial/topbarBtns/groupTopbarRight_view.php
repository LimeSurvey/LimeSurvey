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

<!-- Edit button -->
<?php
if($hasSurveyContentUpdatePermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'edit-button',
            'id' => 'edit-button',
            'text' => gT('Edit'),
            'icon' => 'ri-pencil-fill',
            'link' => Yii::App()->createUrl("questionGroupsAdministration/edit/surveyid/{$surveyid}/gid/{$gid}/"),
            'htmlOptions' => [
                'class' => 'btn btn-primary pjax',
            ],
        ]
    );
}
?>
