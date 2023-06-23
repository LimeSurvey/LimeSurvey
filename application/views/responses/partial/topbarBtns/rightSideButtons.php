<?php if ($hasResponsesDeletePermission) {

  $dataText = gT('Enter a list of response IDs that are to be deleted, separated by comma.');
  $dataText .= '<br/>';
  $dataText .= gT('Please note that if you delete an incomplete response during a running survey,
the participant will not be able to complete it.');
  $this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
      'name' => 'response-batch-deletion',
      'id' => 'response-batch-deletion',
      'text' => gT('Batch deletion'),
      'icon' => 'ri-delete-bin-fill text-danger',
      'link' => Yii::App()->createUrl("responses/delete/", ["surveyId" => $oSurvey->sid]),
      'htmlOptions' => [
        'class' => 'btn btn-outline-secondary selector--ConfirmModal',
        'role' => 'button',
        'data-post' => "{}",
        'data-show-text-area' => 'true',
        'data-use-ajax' => 'true',
        'data-grid-id' => 'responses-grid',
        'data-grid-reload' => 'true',
        'data-button-no' => gT('Cancel'),
        'data-button-yes' => gT('Delete'),
        'data-button-type' => 'btn-danger',
        'data-close-button-type' => 'btn-cancel',
        'data-text' => $dataText,
        'title' => gt('Batch deletion'),
      ],
    ]
  );
}
