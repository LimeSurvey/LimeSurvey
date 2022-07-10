<?php
/**
 * @var int $surveyId
 */
$buttons = [];
if (Permission::model()->hasSurveyPermission($surveyId, 'responses', 'update')) {
    // Delete
    $buttons[] = [
        // li element
        'type'          => 'action',
        'action'        => 'delete',
        'url'           => App()->createUrl("responses/delete/", ['surveyId' => $surveyId]),
        'iconClasses'   => 'fa fa-trash text-danger',
        'text'          => gT('Delete'),
        'grid-reload'   => 'yes',

        // modal
        'actionType'    => 'modal',
        'modalType'     => 'cancel-delete',
        'keepopen'      => 'no',
        'sModalTitle'   => gT('Delete failed e-mail notifications'),
        'htmlModalBody' => gT('Are you sure you want to delete the selected notifications?'),
    ];
    $buttons[] = [
        'type' => 'action',
        'action' => 'resend',
        'url' => App()->createUrl('failedemail/resend/', ['surveyid' => $surveyId]),
        'iconClasses' => 'fa fa-envelope',
        'text' => gT('Resend e-mails'),
        'grid-reload' => 'yes',
        //modal
        'actionType' => 'modal',
        'modalType' => 'cancel-resend',
        'keepopen' => 'yes',

        'sModalTitle' => gT('Resend selected e-mails'),
        'htmlModalBody' => $this->renderPartial('./partials/resend/modal_body', [], true)
    ];
}

$this->widget(
    'ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget',
    [
        'pk'         => 'id',
        'gridid'     => 'failedEmail-grid',
        'dropupId'   => 'failedEmailActions',
        'dropUpText' => gT('Selected e-mail(s)...'),
        'aActions'   => $buttons
    ]
);
