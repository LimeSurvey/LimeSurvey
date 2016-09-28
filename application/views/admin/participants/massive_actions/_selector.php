<!-- Rendering massive action widget -->
<?php

$this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
    'pk'          => 'selectedParticipant',
    'gridid'      => 'list_central_participants',
    'dropupId'    => 'tokenListActions',
    'dropUpText'  => gT('Selected participant(s)...'),

    'aActions'    => array(
        // Delete
        array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         =>  App()->createUrl('/admin/participants/sa/deleteParticipant/'),
            'iconClasses' => 'text-danger glyphicon glyphicon-trash',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',
            'on-success'  => "(function(result) { LS.ajaxHelperOnSuccess(result); })",

            // Modal
            'actionType'    => 'modal',
            'modalType'     => 'empty',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Delete one or more participants...'),
            'htmlModalBody' => 
                '<p>' . gT('Please choose one option.') . '</p>' .
                // The class 'post-value' will make widget post input/select to controller url
                '<select name="selectedoption" class="form-control post-value">
                        <option value="po" selected>' . gT("Delete only from the central panel") . '</option>
                        <option value="pt">' . gT("Delete from the central panel and associated surveys") . '</option>
                        <option value="ptta">' . gT("Delete from central panel, associated surveys and all associated responses") . '</option>
                </select>',
            'htmlFooterButtons'   => array(
                // The class 'btn-ok' binds to URL above
                '<a class="btn btn-ok btn-danger"><span class="fa fa-trash"></span>&nbsp;' . gT('Delete') . '</a>',
                '<a class="btn btn-default" data-dismiss="modal">' . gT('Cancel') . '</a>'
            ),
            'aCustomDatas'  => array(
            ),
        ),

        // Separator
        array('type'  => 'separator'),

        // Export
        array(
            'type' => 'action',
            'action' => 'export',
            'url' => '',  // Not relevant
            'iconClasses' => 'icon-exportcsv',
            'text' => gT('Export'),
            'grid-reload' => 'no',

            'actionType' => 'custom',
            'custom-js' => '(function() { LS.CPDB.onClickExport(); })'
        ),
        // Share
        array(
            'type' => 'action',
            'action' => 'share',
            'url' => '',  // Not relevant
            'iconClasses' => 'fa fa-share',
            'text' => gT('Share'),
            'grid-reload' => 'no',

            'actionType' => 'custom',
            'custom-js' => '(function(itemIds) { LS.CPDB.shareMassiveAction(itemIds); })'
        ),
        // Add to survey
        array(
            'type' => 'action',
            'action' => 'add-to-survey',
            'url' => '',  // Not relevant
            'iconClasses' => 'fa fa-user-plus',
            'text' => gT('Add participants to survey'),
            'grid-reload' => 'no',

            'actionType' => 'custom',
            'custom-js' => '(function(itemIds) { LS.CPDB.addParticipantToSurvey(itemIds); })'
        )
    )
));

?>
