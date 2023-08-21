<?php
/**
 * @var int $surveyId
 * @var array $permissions
 */
$buttons = [];
$buttons[] = [
    // dropdown button
    'type'          => 'action',
    'action'        => 'delete',
    'url'           => App()->createUrl("failedEmail/delete/", ['surveyId' => $surveyId]),
    'iconClasses'   => 'ri-delete-bin-fill text-danger',
    'text'          => gT('Delete'),
    'grid-reload'   => 'yes',
    'disabled'      => !$permissions['delete'],
    // modal
    'actionType'    => 'modal',
    'modalType'     => 'cancel-delete',
    'keepopen'      => 'yes',
    'sModalTitle'   => gT('Delete failed email notifications'),
    'htmlModalBody' => gT('Are you sure you want to delete the selected notifications?'),
    'aCustomDatas'  => [
        ['name' => 'surveyid', 'value' => $surveyId],
    ]
];
$buttons[] = [
    // dropdown button
    'type'        => 'action',
    'action'      => 'resend',
    'url'         => App()->createUrl('failedEmail/resend/', ['surveyid' => $surveyId]),
    'iconClasses' => 'ri-mail-fill',
    'text'        => gT('Resend emails'),
    'grid-reload' => 'yes',
    'disabled'    => !$permissions['update'],
    //modal
    'actionType'  => 'modal',
    'modalType'   => 'cancel-resend',
    'keepopen'    => 'yes',
    'sModalTitle'   => gT('Resend selected emails'),
    'htmlModalBody' => App()->getController()->renderPartial('/failedEmail/partials/modal/resend_body', [], true),
    'aCustomDatas'  => [
        ['name' => 'surveyid', 'value' => $surveyId],
    ]
];

$this->widget(
    'ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget',
    [
        'pk'         => 'id',
        'gridid'     => 'failedemail-grid',
        'dropupId'   => 'failedEmailActions',
        'dropUpText' => gT('Selected email(s)...'),
        'aActions'   => $buttons
    ]
);
