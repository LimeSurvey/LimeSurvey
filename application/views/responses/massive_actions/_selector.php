<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>


<!-- Rendering massive action widget -->
<?php
    $buttons = [];
    $surveyId = intval(App()->getRequest()->getQuery('surveyId'));
if (Permission::model()->hasSurveyPermission($surveyId, 'responses', 'delete')) {
    // Delete
    $buttons[] = [
        // li element
        'type'        => 'action',
        'action'      => 'delete',
        'url'         =>  App()->createUrl("responses/delete/", ['surveyId' => $surveyId]),
        'iconClasses' => 'ri-delete-bin-fill text-danger',
        'text'        =>  gT('Delete'),
        'grid-reload' => 'yes',

        // modal
        'actionType'    => 'modal',
        'modalType'     => 'cancel-delete',
        'keepopen'      => 'no',
        'sModalTitle'   => gT('Delete responses'),
        'htmlModalBody' => gT('Are you sure you want to delete the selected responses?')
            . '<br/>'
            . gT('Please note that if you delete an incomplete response during a running survey, the participant will not be able to complete it.'),
        'aCustomDatas'  => [
            ['name' =>'sid', 'value' => $surveyId],
        ],
    ];

    $buttons[] = [
        'type'        => 'action',
        'action'      => 'deleteAttachments',
        //'url'         =>  App()->createUrl("admin/responses", array("sa"=>"actionDeleteAttachments")),
        'url'         =>  App()->createUrl("responses/deleteAttachments/", ["surveyId" => $surveyId]),
        'iconClasses' => 'text-danger ri-attachment-2',
        'text'        =>  gT('Delete attachments'),
        'grid-reload' => 'yes',

        // modal
        'actionType'    => 'modal',
        'modalType'     => 'cancel-delete',
        'keepopen'      => 'no',
        'sModalTitle'   => gT('Delete attachments'),
        'htmlModalBody' => gT('Are you sure you want to delete all uploaded files from the selected responses?'),
        'aCustomDatas'  => [
            ['name' =>'sid', 'value' => $surveyId],
        ],
    ];
}

if (Permission::model()->hasSurveyPermission($surveyId, 'responses', 'read')) {
    // Download ZIP archive of file upload question types
    $buttons[] = [
        'type' => 'action',
        'action' => 'downloadZip',
        'url' => App()->createUrl('responses/downloadfiles/', ['surveyId' => $surveyId, 'responseIds' => '']),
        'iconClasses' => 'ri-download-fill test',
        'text' => gT('Download files'),
        'grid-reload' => 'no',

        'actionType' => 'window-location-href'
    ];


    // Export responses
    $buttons[] = [
        // li element
        'type'            => 'action',
        'action'          => 'export',
        'url'             =>  App()->createUrl('admin/export/sa/exportresults/', ['surveyId' => $surveyId]),
        'iconClasses'     => 'ri-upload-fill',
        'text'            =>  gT('Export'),
        'aLinkSpecificDatas'  => [
            'input-name' => 'responseIds',
            'input-separator' => ',',
            'target' => '_self',
        ],
        'actionType'    => 'redirect',
    ];
}

$this->widget(
    'ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget',
    [
        'pk'         => 'id',
        'gridid'     => 'responses-grid',
        'dropupId'   => 'responsesListActions',
        'dropUpText' => gT('Selected response(s)...'),
        'aActions'   => $buttons
    ]
);
?>
