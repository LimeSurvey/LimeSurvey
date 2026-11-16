<?php

namespace actions;

use LimeSurvey\Models\Services\Quotas;

/**
 * Action definitions for the survey quotas floating action bar.
 */
class QuotaListMassiveActions
{
    /**
     * Return the action definitions for the quota-grid floating bar.
     *
     * @param int                   $surveyId
     * @param \Quota                $oQuota                 Last quota – used as template for the "Change texts" form.
     * @param \QuotaLanguageSetting[] $aQuotaLanguageSettings Language settings of $oQuota.
     * @return array
     */
    public static function getActions(int $surveyId, \Quota $oQuota, array $aQuotaLanguageSettings): array
    {
        $buttons = [];

        // ------------------------------------------------------------------ activate / deactivate / change texts
        if (\Permission::model()->hasSurveyPermission($surveyId, 'quotas', 'update')) {

            // Activate
            $buttons[] = [
                'type'          => 'action',
                'action'        => Quotas::MASSIVE_ACTION_ACTIVATE,
                'url'           => \App()->createUrl('quotas/massiveAction', ['action' => Quotas::MASSIVE_ACTION_ACTIVATE, 'surveyid' => $surveyId]),
                'iconClasses'   => 'ri-play-fill',
                'text'          => \gT('Activate'),
                'grid-reload'   => 'yes',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-apply',
                'keepopen'      => 'no',
                'sModalTitle'   => \gT('Activate quotas'),
                'htmlModalBody' => \gT('Are you sure you want to activate all selected quotas?'),
            ];

            // Deactivate
            $buttons[] = [
                'type'          => 'action',
                'action'        => Quotas::MASSIVE_ACTION_DEACTIVATE,
                'url'           => \App()->createUrl('quotas/massiveAction', ['action' => Quotas::MASSIVE_ACTION_DEACTIVATE, 'surveyid' => $surveyId]),
                'iconClasses'   => 'ri-stop-fill',
                'text'          => \gT('Deactivate'),
                'grid-reload'   => 'yes',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-apply',
                'keepopen'      => 'no',
                'sModalTitle'   => \gT('Deactivate quotas'),
                'htmlModalBody' => \gT('Are you sure you want to deactivate all selected quotas?'),
            ];

            // Change texts
            $buttons[] = [
                'type'           => 'action',
                'action'         => Quotas::MASSIVE_ACTION_CHANGE_LANGUAGE_SETTINGS,
                'url'            => \App()->createUrl('quotas/massiveAction', ['action' => Quotas::MASSIVE_ACTION_CHANGE_LANGUAGE_SETTINGS, 'surveyid' => $surveyId]),
                'iconClasses'    => 'ri-refresh-line',
                'text'           => \gT('Change texts'),
                'grid-reload'    => 'yes',
                'actionType'     => 'modal',
                'modalType'      => 'cancel-save',
                'largeModalView' => true,
                'keepopen'       => 'yes',
                'sModalTitle'    => \gT('Change settings'),
                'htmlModalBody'  => \Yii::app()->getController()->renderPartial(
                    '/quotas/viewquotas_massive_langsettings_form',
                    [
                        'oQuota'                 => $oQuota,
                        'aQuotaLanguageSettings' => $aQuotaLanguageSettings,
                    ],
                    true
                ),
            ];
        }

        // ------------------------------------------------------------------ delete
        if (\Permission::model()->hasSurveyPermission($surveyId, 'quotas', 'delete')) {
            $buttons[] = [
                'type'          => 'action',
                'action'        => Quotas::MASSIVE_ACTION_DELETE,
                'url'           => \App()->createUrl('quotas/massiveAction', ['action' => Quotas::MASSIVE_ACTION_DELETE, 'surveyid' => $surveyId]),
                'iconClasses'   => 'ri-delete-bin-fill',
                'btnClass'      => 'floating-delete-button',
                'text'          => \gT('Delete'),
                'grid-reload'   => 'yes',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-delete',
                'keepopen'      => 'no',
                'sModalTitle'   => \gT('Delete quotas'),
                'htmlModalBody' => \gT('Are you sure you want to delete all selected quotas?'),
            ];
        }

        return $buttons;
    }
}

