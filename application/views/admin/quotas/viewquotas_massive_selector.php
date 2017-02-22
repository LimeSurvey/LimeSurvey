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
            'iconClasses' => 'icon-active',
            'text'        =>  gT("Activate"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'yes-no',
            'keepopen'    => 'yes',
            'sModalTitle'   => gT('Activate quotas'),
            'htmlModalBody' => gT('Go?').' '.gT('Continue?'),
        ),
        array(
            // li element
            'type'        => 'action',
            'action'      => 'deactivate',
            'url'         => App()->createUrl('/admin/quotas/massiveAction/action/deactivate'),
            'iconClasses' => 'icon-inactive',
            'text'        =>  gT("Deactivate"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'yes-no',
            'keepopen'    => 'yes',
            'sModalTitle'   => gT('Dectivate quotas'),
            'htmlModalBody' => gT('Deactivate?').' '.gT('Continue?'),
        ),

    ),

));
?>

