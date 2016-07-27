<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>


<!-- Rendering massive action widget -->
<?php
    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'id',
            'gridid'      => 'responses-grid',
            'dropupId'    => 'responsesListActions',
            'dropUpText'  => gT('Selected response(s)...'),

            'aActions'    => array(

                // Delete
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'delete',
                    'url'         =>  App()->createUrl('/admin/responses/sa/actionDelete/surveyid/'.$_GET['surveyid']),
                    'iconClasses' => 'text-danger glyphicon glyphicon-trash',
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
                ),
            ),

    ));
?>
