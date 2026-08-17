<?php
namespace actions;
/**
 * Action definitions for the survey participants (tokens) floating action bar.
 */
class TokenListMassiveActions
{
    /**
     * Return the action definitions for the token-grid floating bar.
     *
     * @param int $surveyId
     * @return array
     */
    public static function getActions(int $surveyId): array
    {
        $buttons = [];
        // batch edit
        if (\Permission::model()->hasSurveyPermission($surveyId, 'tokens', 'update')) {
            $aLanguageCodes = \Survey::model()->findByPk($surveyId)->getAllLanguages();
            $aLanguages = [];
            foreach ($aLanguageCodes as $code) {
                $aLanguages[$code] = getLanguageNameFromCode($code, false);
            }

            $buttons[] = [
                'type'           => 'action',
                'action'         => 'edit',
                'url'            => \App()->createUrl('/admin/tokens/sa/editMultiple/'),
                'iconClasses'    => 'ri-pencil-fill',
                'text'           => \gT('Batch-edit'),
                'grid-reload'    => 'yes',
                'actionType'     => 'modal',
                'largeModalView' => true,
                'modalType'      => 'cancel-save',
                'keepopen'       => 'yes',
                'sModalTitle'    => \gT('Batch-edit participants'),
                'htmlModalBody'  => \Yii::app()->getController()->renderFile(
                    \Yii::app()->getBasePath() . '/views/admin/token/massive_actions/_update.php',
                    [
                        'dateformatdetails' => getDateFormatData(\Yii::app()->session['dateformat']),
                        'aLanguages'        => $aLanguages,
                    ],
                    true
                ),
            ];
        }

        // ------------------------------------------------------------------ send email (dropdown)
        if (\Permission::model()->hasSurveyPermission($surveyId, 'tokens', 'update')) {
            $emailItems = [
                [
                    'type' => 'dropdown-header',
                    'text' => \gT('Email'),
                ],
                [
                    'action'             => 'invite',
                    'url'                => \App()->createUrl('/admin/tokens/sa/email/surveyid/' . $surveyId),
                    'iconClasses'        => '',
                    'text'               => \gT('Send email invitation'),
                    'grid-reload'        => 'no',
                    'actionType'         => 'redirect',
                    'aLinkSpecificDatas' => [
                        'input-name'      => 'tokenids',
                        'input-separator' => ',',
                        'target'          => '_top',
                    ],
                ],
                [
                    'action'             => 'remind',
                    'url'                => \App()->createUrl('/admin/tokens/sa/email/action/remind/surveyid/' . $surveyId),
                    'iconClasses'        => '',
                    'text'               => \gT('Send email reminder'),
                    'grid-reload'        => 'no',
                    'actionType'         => 'redirect',
                    'aLinkSpecificDatas' => [
                        'input-name'      => 'tokenids',
                        'input-separator' => ',',
                        'target'          => '_top',
                    ],
                ],
            ];

            $buttons[] = [
                'type'  => 'dropdown',
                'icon'  => 'ri-mail-fill',
                'text'  => \gT('Send email'),
                'items' => $emailItems,
            ];
        }

        // ------------------------------------------------------------------ CPDB
        if (\Permission::model()->hasGlobalPermission('participantpanel', 'create')) {
            $buttons[] = [
                'type'               => 'action',
                'action'             => 'addCPDB',
                'url'                => \App()->createUrl('admin/participants/sa/attributeMapToken/sid/' . $surveyId),
                'iconClasses'        => 'ri-user-add-fill',
                'text'               => \gT('Add to central participants database'),
                'grid-reload'        => 'no',
                'actionType'         => 'fill-session-and-redirect',
                'aLinkSpecificDatas' => [
                    'input-name'      => 'tokenids',
                    'input-separator' => ',',
                ],
            ];
        }

        // ------------------------------------------------------------------ delete
        if (\Permission::model()->hasSurveyPermission($surveyId, 'tokens', 'delete')) {
            $buttons[] = [
                'type'          => 'action',
                'action'        => 'delete',
                'url'           => \App()->createUrl('/admin/tokens/sa/deleteMultiple/'),
                'iconClasses'   => 'ri-delete-bin-fill',
                'btnClass'      => 'text-danger',
                'text'          => \gT('Delete'),
                'grid-reload'   => 'yes',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-delete',
                'keepopen'      => 'no',
                'sModalTitle'   => \gT('Delete survey participants'),
                'htmlModalBody' => \gT('Are you sure you want to delete the selected participants?'),
                'aCustomDatas'  => [
                    ['name' => 'sid', 'value' => $surveyId],
                ],
            ];
        }

        return $buttons;
    }
}
