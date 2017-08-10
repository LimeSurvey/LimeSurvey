<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota The last Quota as base for Massive edits */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings The last Quota LanguageSettings */
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
            'url'         => App()->createUrl('/admin/quotas/sa/massiveAction/action/activate'),
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
            'url'         => App()->createUrl('/admin/quotas/sa/massiveAction/action/deactivate'),
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
            'action'      => 'changeLanguageSettings',
            'url'         => App()->createUrl('/admin/quotas/sa/massiveAction/action/changeLanguageSettings'),
            'iconClasses' => 'fa fa-external-link text-success',
            'text'        =>  gT("Change texts"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'yes-no',
            'keepopen'    => 'yes',
            'sModalTitle'   => gT('Change settings'),
            'htmlModalBody' => $this->renderPartial('/admin/quotas/viewquotas_massive_langsettings_form',
                array(
                    'oQuota'=>$oQuota,
                    'aQuotaLanguageSettings'=>$aQuotaLanguageSettings,
                    ),true),
        ),

        // Separator
        array(

            // li element
            'type'  => 'separator',
        ),
        array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         => App()->createUrl('/admin/quotas/sa/massiveAction/action/delete'),
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

