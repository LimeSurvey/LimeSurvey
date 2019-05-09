<!-- Rendering massive action widget -->
<?php

$userId = App()->user->id;
$showActions = [];
$aActions = [
    'delete' => [
        // li element
        'type' => 'action',
        'action' => 'delete',
        'url' => App()->createUrl('/admin/participants/sa/deleteParticipant/'),
        'iconClasses' => 'text-danger fa fa-trash',
        'text' => gT('Delete'),
        'grid-reload' => 'yes',
        'on-success' => "(function(result) { LS.AjaxHelper.onSuccess(result); })",

        // Modal
        'actionType' => 'modal',
        'modalType' => 'empty',
        'keepopen' => 'no',
        'sModalTitle' => gT('Delete one or more participants...'),
        'htmlModalBody' =>
            '<p>' . gT('Please choose one option.') . '</p>' .
            // The class 'post-value' will make widget post input/select to controller url
            '<select name="selectedoption" class="form-control post-value">
                    <option value="po" selected>' . gT("Delete only from the central panel") . '</option>
                    <option value="pt">' . gT("Delete from the central panel and associated surveys") . '</option>
                    <option value="ptta">' . gT("Delete from central panel, associated surveys and all associated responses") . '</option>
                </select>',
        'htmlFooterButtons' => array(
            // The class 'btn-ok' binds to URL above
            '<a class="btn btn-ok btn-danger"><span class="fa fa-trash"></span>&nbsp;' . gT('Delete') . '</a>',
            '<a class="btn btn-default" data-dismiss="modal">' . gT('Cancel') . '</a>'
        ),
        'aCustomDatas' => array(),
    ],
    'update' => [
        'type' => 'action',
        'action' => 'batchEdit',
        'url' => App()->createUrl('/admin/participants/sa/batchEdit/'),
        'iconClasses' => 'fa fa-pencil',
        'text' => gT('Batch edit'),
        'grid-reload' => 'yes',
        //modal
        'actionType' => 'modal',
        'modalType' => 'yes-no',
        'keepopen' => 'yes',
        'yes' => gT('Apply'),
        'no' => gT('Cancel'),
        'sModalTitle' => gT('Batch edit the participants'),
        'htmlModalBody' => $this->renderPartial('./participants/massive_actions/_update', [], true)
    ],
    'export' => [
        'type' => 'action',
        'action' => 'export',
        'url' => '',  // Not relevant
        'iconClasses' => 'icon-exportcsv',
        'text' => gT('Export'),
        'grid-reload' => 'no',

        'actionType' => 'custom',
        'custom-js' => '(function() { LS.CPDB.onClickExport(); })'
    ],
    'share' => [
        'type' => 'action',
        'action' => 'share',
        'url' => '',  // Not relevant
        'iconClasses' => 'fa fa-share',
        'text' => gT('Share'),
        'grid-reload' => 'no',

        'actionType' => 'custom',
        'custom-js' => '(function(itemIds) { LS.CPDB.shareMassiveAction(itemIds); })'
    ],
    'addToSurvey' => [
        'type' => 'action',
        'action' => 'add-to-survey',
        'url' => '',  // Not relevant
        'iconClasses' => 'fa fa-user-plus',
        'text' => gT('Add participants to survey'),
        'grid-reload' => 'no',

        'actionType' => 'custom',
        'custom-js' => '(function(itemIds) { LS.CPDB.addParticipantToSurvey(itemIds); })'
    ],
    'seperator' => [
        'type' => 'separator'
    ]

];

$showActions = array_merge($showActions, checkPermission('delete', $userId, $aActions, $participantOwnerUid, true));
$showActions = array_merge($showActions, checkPermission('update', $userId, $aActions, $participantOwnerUid, false));
$showActions = array_merge($showActions, checkPermission('export', $userId, $aActions, $participantOwnerUid, false));
$showActions = array_merge($showActions, checkPermission('update', $userId, $aActions, $participantOwnerUid, false, 'share'));
$showActions = array_merge($showActions, [$aActions['addToSurvey']]);

$this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
    'pk' => 'selectedParticipant',
    'gridid' => 'list_central_participants',
    'dropupId' => 'tokenListActions',
    'dropUpText' => gT('Selected participant(s)...'),

    'aActions' => $showActions
));

/**
 * @param string $permission
 * @param string $userId
 * @param array  $aActions
 * @param string $participantOwnerUid
 * @param bool   $seperator *
 * @param string $overrideDisplayedButton
 *
 * @return array
 */
function checkPermission($permission, $userId, $aActions, $participantOwnerUid, $seperator = false, $overrideDisplayedButton = '')
{
    $isSuperAdmin = Permission::model()->hasGlobalPermission('superadmin', 'read');
    $hasPermission = Permission::model()->hasGlobalPermission('participantpanel', $permission);
    $sharedParticipantExists = ParticipantShare::model()->exists('share_uid = ' . $userId);

    $displayedButton = $permission;
    if ($overrideDisplayedButton) {
        $displayedButton = $overrideDisplayedButton;
    }

    // form an Array with Buttons to be displayed
    if (!$sharedParticipantExists || $participantOwnerUid === $userId) {
        $aAction[] = $aActions[$displayedButton];
        if ($seperator) {
            array_push($aAction, $aActions['seperator']);
        }
        return $aAction;
    } elseif ($isSuperAdmin || $hasPermission) {
        $aAction[] = $aActions[$displayedButton];
        if ($seperator) {
            array_push($aAction, $aActions['seperator']);
        }
        return $aAction;
    } else {
        return [];
    }
}
