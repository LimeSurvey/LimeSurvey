<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>


<!-- Rendering massive action widget -->
<?php
    $buttons = [];
if (Permission::model()->hasSurveyPermission($_GET['surveyId'], 'responses', 'delete')) {
    // Delete
    $buttons[] = [
        // li element
        'type'        => 'action',
        'action'      => 'delete',
        'url'         =>  App()->createUrl("responses/delete/", ['surveyId' => $_GET['surveyId']]),
        'iconClasses' => 'fa fa-trash text-danger',
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
            ['name' =>'sid', 'value' => $_GET['surveyId']],
        ],
    ];

    $buttons[] = [
        'type'        => 'action',
        'action'      => 'deleteAttachments',
        //'url'         =>  App()->createUrl("admin/responses", array("sa"=>"actionDeleteAttachments")),
        'url'         =>  App()->createUrl("responses/deleteAttachments/", ["surveyId" => $_GET['surveyId']]),
        'iconClasses' => 'text-danger fa fa-paperclip',
        'text'        =>  gT('Delete attachments'),
        'grid-reload' => 'yes',

        // modal
        'actionType'    => 'modal',
        'modalType'     => 'cancel-delete',
        'keepopen'      => 'no',
        'sModalTitle'   => gT('Delete attachments'),
        'htmlModalBody' => gT('Are you sure you want to delete all uploaded files from the selected responses?'),
        'aCustomDatas'  => [
            ['name' =>'sid', 'value' => $_GET['surveyId']],
        ],
    ];
}

if (Permission::model()->hasSurveyPermission($_GET['surveyId'], 'responses', 'read')) {
    // Download ZIP archive of file upload question types
    $buttons[] = [
        'type' => 'action',
        'action' => 'downloadZip',
        'url' => App()->createUrl('responses/downloadfiles/', ['surveyId' => $_GET['surveyId'], 'responseIds' => '']),
        'iconClasses' => 'fa fa-download test',
        'text' => gT('Download files'),
        'grid-reload' => 'no',

        'actionType' => 'window-location-href'
    ];


    // Export responses
    $buttons[] = [
        // li element
        'type'            => 'action',
        'action'          => 'export',
        'url'             =>  App()->createUrl('admin/export/sa/exportresults/surveyId/'.$_GET['surveyId']),
        'iconClasses'     => 'fa fa-upload',
        'text'            =>  gT('Export'),

        'aLinkSpecificDatas'  => [
            'input-name'     => 'tokenids',
        ],

        // modal
        'actionType'    => 'fill-session-and-redirect',
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
