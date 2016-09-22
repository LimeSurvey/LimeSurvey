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
            'url'         =>  App()->createUrl('/admin/participants/sa/delParticipant/'),
            'iconClasses' => 'text-danger glyphicon glyphicon-trash',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',
            'on-success'  => "(function(result) { var result = JSON.parse(result); notifyFader(result.successMessage, 'well-lg bg-primary text-center'); })",

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

        // Export
        array(
            'type' => 'action',
            'action' => 'export',
            'url' => App()->createUrl('/admin/participants/sa/export'),
            'iconClasses' => 'icon-exportcsv text-success',
            'text' => gT('Export'),
            'grid-reload' => 'no',

            'actionType' => 'custom',
            'custom-js' => '(function() { LS.CPDB.onClickExport(); })'
        ),
        /*
        // Share
        array(
        ),
        // Add to survey
        array(
        )
         */
    )
));

?>
