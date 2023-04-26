<?php
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
            'url'         => App()->createUrl('/quotas/massiveAction/action/activate', ['surveyid' => $oSurvey->sid]),
            'iconClasses' => 'ri-play-fill text-success',
            'text'        =>  gT("Activate"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'cancel-apply',
            'keepopen'    => 'yes',
            'sModalTitle'   => gT('Activate quotas'),
            'htmlModalBody' => gT('Are you sure you want to activate all selected quotas?'),
        ),
        array(
            // li element
            'type'        => 'action',
            'action'      => 'deactivate',
            'url'         => App()->createUrl('/quotas/massiveAction/action/deactivate', ['surveyid' => $oSurvey->sid]),
            'iconClasses' => 'ri-pause-fill',
            'text'        =>  gT("Deactivate"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'cancel-apply',
            'keepopen'    => 'yes',
            'sModalTitle'   => gT('Deactivate quotas'),
            'htmlModalBody' => gT('Are you sure you want to deactivate all selected quotas?'),
        ),
        array(
            // li element
            'type'        => 'action',
            'action'      => 'changeLanguageSettings',
            'url'         => App()->createUrl(
                '/quotas/massiveAction/action/changeLanguageSettings',
                ['surveyid' => $oSurvey->sid]
            ),
            'iconClasses' => 'ri-external-link-fill text-success',
            'text'        =>  gT("Change texts"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'cancel-apply',
            'keepopen'    => 'yes',
            'sModalTitle'   => gT('Change settings'),
            'htmlModalBody' => $this->renderPartial(
                'viewquotas_massive_langsettings_form',
                array(
                    'oQuota'=>$oQuota,
                    'aQuotaLanguageSettings'=>$aQuotaLanguageSettings,
                    ),
                true
            ),
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
            'url'         => App()->createUrl('/quotas/massiveAction/action/delete', ['surveyid' => $oSurvey->sid]),
            'iconClasses' => 'ri-delete-bin-fill text-danger',
            'text'        =>  gT("Delete"),
            'grid-reload' => 'yes',

            // modal
            'actionType'  => 'modal',
            'modalType'   => 'cancel-delete',
            'keepopen'    => 'yes',
            'sModalTitle'   => gT('Delete quotas'),
            'htmlModalBody' => gT('Are you sure you want to delete all selected quotas?'),
        ),

    ),

));
?>

