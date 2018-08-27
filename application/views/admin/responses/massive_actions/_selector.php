<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>


<!-- Rendering massive action widget -->
<?php
    $buttons = array();
    if (Permission::model()->hasSurveyPermission($_GET['surveyid'], 'responses','delete')) {
        // Delete
        $buttons[] = array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         =>  App()->createUrl('/admin/responses/sa/actionDelete/surveyid/'.$_GET['surveyid']),
            'iconClasses' => 'text-danger fa fa-trash',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',

            // modal
            'actionType'    => 'modal',
            'modalType'     => 'yes-no',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Delete responses'),
            'htmlModalBody' => gT('Are you sure you want to delete the selected responses?'),
            'aCustomDatas'  => array(
                array( 'name'=>'sid',  'value'=> $_GET['surveyid']),
            ),
        );

        $buttons[] = array(
            'type'        => 'action',
            'action'      => 'deleteAttachments',
            //'url'         =>  App()->createUrl("admin/responses", array("sa"=>"actionDeleteAttachments")),
            'url'         =>  App()->createUrl("/admin/responses/sa/actionDeleteAttachments/", array("surveyid" => $_GET['surveyid'] )),
            'iconClasses' => 'text-danger fa fa-paperclip',
            'text'        =>  gT('Delete attachments'),
            'grid-reload' => 'yes',

            // modal
            'actionType'    => 'modal',
            'modalType'     => 'yes-no',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Delete attachments'),
            'htmlModalBody' => gT('Are you sure you want to delete all uploaded files from the selected responses?'),
            'aCustomDatas'  => array(
                array( 'name'=>'sid',  'value'=> $_GET['surveyid']),
            ),
        );
    }

    if (Permission::model()->hasSurveyPermission($_GET['surveyid'], 'responses', 'read')) {
        // Download ZIP archive of file upload question types
        $buttons[] = array(
            'type' => 'action',
            'action' => 'downloadZip',
            'url' => App()->createUrl('/admin/responses/sa/actionDownloadfiles/iSurveyId/' . $_GET['surveyid']) . '/sResponseId/',
            'iconClasses' => 'fa fa-download',
            'text' => gT('Download files'),
            'grid-reload' => 'no',

            'actionType' => 'window-location-href'
        );


        // Export responses
        $buttons[] = array(
            // li element
            'type'            => 'action',
            'action'          => 'export',
            'url'             =>  App()->createUrl('admin/export/sa/exportresults/surveyid/'.$_GET['surveyid']),
            'iconClasses'     => 'fa fa-upload',
            'text'            =>  gT('Export'),

            'aLinkSpecificDatas'  => array(
                'input-name'     => 'tokenids',
            ),

            // modal
            'actionType'    => 'fill-session-and-redirect',
        );

    }

    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'id',
            'gridid'      => 'responses-grid',
            'dropupId'    => 'responsesListActions',
            'dropUpText'  => gT('Selected response(s)...'),
            'aActions'    => $buttons
    ));
?>
