<!-- Rendering massive action widget for CPDB share panel -->
<?php

$this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
    'pk'          => 'selectedParticipantShare',
    'gridid'      => 'share_central_participants',
    'dropupId'    => 'tokenListActions',
    'dropUpText'  => gT('Selected participant share(s)...'),

    'aActions'    => array(
        // Delete
        array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         =>  App()->createUrl('/admin/participants/sa/deleteMultipleParticipantShare/'),
            'iconClasses' => 'text-danger fa fa-trash',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',
            'on-success'  => "(function(result) { LS.AjaxHelper.onSuccess(result); })",

            // Modal
            'actionType'    => 'modal',
            'modalType'     => 'yes-no',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Confirm'),
            'htmlModalBody' => gT('Are you sure?'),
            'aCustomDatas'  => array()
        ),
    )
));

?>
