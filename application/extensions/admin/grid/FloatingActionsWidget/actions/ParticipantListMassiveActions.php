<?php

namespace actions;

/**
 * Action definitions for the CPDB floating action bar.
 */
class ParticipantListMassiveActions
{
    /**
     * @param array $permissions Participant panel permissions array.
     * @return array
     */
    public static function getActions(array $permissions): array
    {
        $actions = [
            [
                'type' => 'action',
                'action' => 'batchEdit',
                'url' => \App()->createUrl('/admin/participants/sa/batchEdit/'),
                'iconClasses' => 'ri-pencil-line',
                'text' => gT('Batch edit'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-save',
                'keepopen' => 'yes',
                'sModalTitle' => gT('Batch edit the participants'),
                'htmlModalBody' => \Yii::app()->getController()->renderPartial(
                    '/admin/participants/massive_actions/_update',
                    [],
                    true
                ),
            ],
            [
                'type' => 'action',
                'action' => 'export',
                'url' => '#',
                'iconClasses' => 'ri-download-line',
                'text' => gT('Export'),
                'grid-reload' => 'no',
                'actionType' => 'custom',
                'aLinkSpecificDatas' => [
                    'custom-js' => 'LS.CPDB.onClickExport',
                ],
            ],
            [
                'type' => 'action',
                'action' => 'share',
                'url' => '#',
                'iconClasses' => 'ri-share-forward-line',
                'text' => gT('Share'),
                'grid-reload' => 'no',
                'actionType' => 'custom',
                'aLinkSpecificDatas' => [
                    'custom-js' => 'LS.CPDB.shareMassiveAction',
                ],
            ],
            [
                'type' => 'action',
                'action' => 'add-to-survey',
                'url' => '#',
                'iconClasses' => 'ri-user-add-line',
                'text' => gT('Add participants to survey'),
                'grid-reload' => 'no',
                'actionType' => 'custom',
                'aLinkSpecificDatas' => [
                    'custom-js' => 'LS.CPDB.addParticipantToSurvey',
                ],
            ],
            [
                'type' => 'action',
                'action' => 'delete',
                'url' => \App()->createUrl('/admin/participants/sa/deleteParticipant/'),
                'iconClasses' => 'ri-delete-bin-fill',
                'btnClass' => 'floating-delete-button',
                'text' => gT('Delete'),
                'grid-reload' => 'yes',
                'actionType' => 'modal',
                'modalType' => 'cancel-delete',
                'keepopen' => 'no',
                'aLinkSpecificDatas' => [
                    'on-success' => 'LS.AjaxHelper.onSuccess',
                ],
                'sModalTitle' => gT('Delete one or more participants...'),
                'htmlModalBody' =>
                    '<p>' . gT('Please choose one option.') . '</p>' .
                    '<select name="selectedoption" class="form-select post-value">' .
                        '<option value="po" selected>' . gT('Delete only from the central panel') . '</option>' .
                        '<option value="ptt">' . gT('Delete from the central panel and associated surveys') . '</option>' .
                        '<option value="ptta">' . gT('Delete from central panel, associated surveys and all associated responses') . '</option>' .
                    '</select>',
                'htmlFooterButtons' => [
                    '<a class="btn btn-ok btn-danger"><span class="ri-delete-bin-fill"></span>&nbsp;' . gT('Delete') . '</a>',
                    '<a class="btn btn-cancel" data-bs-dismiss="modal">' . gT('Cancel') . '</a>',
                ],
            ],
        ];

        return \Participant::model()->permissionCheckedActionsArray($actions, $permissions);
    }
}

