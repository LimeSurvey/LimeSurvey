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
            'action'      => 'export',
            'url'         => App()->createUrl('/admin/quotas/massiveAction/action/activate'),
            'iconClasses' => 'icon-active',
            'text'        =>  gT("Activate"),

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
            'action'      => 'export',
            'url'         => App()->createUrl('/admin/quotas/massiveAction/action/deactivate'),
            'iconClasses' => 'icon-inactive',
            'text'        =>  gT("Deactivate"),

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'yes-no',
            'keepopen'    => 'no',
            'sModalTitle'   => gT('Dectivate quotas'),
            'htmlModalBody' => gT('Deactivate?').' '.gT('Continue?'),
        ),

    ),

));
?>

