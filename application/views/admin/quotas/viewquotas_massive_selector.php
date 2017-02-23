<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
?>

<?php $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
    'pk'          => 'id',
    'gridid'      => 'quota-grid',
    'dropUpText'  => gT('Selected quota(s)...'),

    'aActions'    => array(
        // Export multiple survey archive
        array(
            // li element
            'type'        => 'action',
            'action'      => 'activate',
            'url'         => App()->createUrl('/admin/quotas/massiveAction/action/activate'),
            'iconClasses' => 'fa fa-play text-success',
            'text'        =>  gT("Activate"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'yes-no',
            'keepopen'    => 'no',
            'sModalTitle'   => gT('Activate quotas'),
            'htmlModalBody' => gT('Are you sure you want to activate all selected quotas?'),
        ),
        array(
            // li element
            'type'        => 'action',
            'action'      => 'deactivate',
            'url'         => App()->createUrl('/admin/quotas/massiveAction/action/deactivate'),
            'iconClasses' => 'fa fa-pause text-warning',
            'text'        =>  gT("Deactivate"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'yes-no',
            'keepopen'    => 'no',
            'sModalTitle'   => gT('Deactivate quotas'),
            'htmlModalBody' => gT('Are you sure you want to deactivate all selected quotas?'),
        ),
        array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         => App()->createUrl('/admin/quotas/massiveAction/action/delete'),
            'iconClasses' => 'fa fa-trash text-danger',
            'text'        =>  gT("Delete"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'yes-no',
            'keepopen'    => 'no',
            'sModalTitle'   => gT('Delete quotas'),
            'htmlModalBody' => gT('Are you sure you want to delete all selected quotas?'),
        ),

    ),

));
?>

