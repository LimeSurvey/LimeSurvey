<?php
/**
 * Floating Actions Widget – Actions for User Management
 *
 * Provides action definitions for the floating action bar shown above
 * the user management gridview pagination area.
 *
 * @var UserManagementController $this
 */

$aActions = [
    // ResendLoginData (main button)
    [
        'type'          => 'action',
        'action'        => 'resendlogindata',
        'url'           => App()->createUrl('userManagement/batchSendAndResetLoginData'),
        'iconClasses'   => 'ri-refresh-line',
        'text'          => gT('Resend login data'),
        'grid-reload'   => 'yes',
        'actionType'    => 'modal',
        'modalType'     => 'cancel-resend',
        'keepopen'      => 'yes',
        'showSelected'  => 'yes',
        'selectedUrl'   => App()->createUrl('userManagement/renderSelectedItems/'),
        'sModalTitle'   => gT('Resend login data'),
        'htmlModalBody' => gT('Are you sure you want to reset and resend selected users login data?'),
    ],
    // Edit Permissions (main button)
    [
        'type'              => 'action',
        'action'            => 'batchPermissions',
        'url'               => App()->createUrl('userManagement/batchPermissions'),
        'iconClasses'       => 'ri-lock-2-line',
        'text'              => gT('Edit permissions'),
        'grid-reload'       => 'yes',
        'actionType'        => 'modal',
        'modalType'         => 'cancel-apply',
        'largeModalView'    => true,
        'keepopen'          => 'yes',
        'showSelected'      => 'yes',
        'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
        'sModalTitle'       => gT('Edit permissions'),
        'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updatepermissions', [], true)
    ],
];

// Add to user group (main button, for authorized users)
if (Permission::model()->hasGlobalPermission('users', 'update')) {
    $aActions[] = [
        'type'              => 'action',
        'action'            => 'batchaddtogroup',
        'url'               => App()->createUrl('userManagement/batchAddGroup'),
        'iconClasses'       => 'ri-user-add-line',
        'text'              => gT('Add to user group'),
        'grid-reload'       => 'yes',
        'actionType'        => 'modal',
        'modalType'         => 'cancel-add',
        'keepopen'          => 'yes',
        'showSelected'      => 'yes',
        'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
        'sModalTitle'       => gT('Add to user group'),
        'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_addtousergroup', [], true)
    ];
}

// More actions dropdown
$aMoreActionsItems = [];

// Edit Status (in dropdown)
$aMoreActionsItems[] = [
    'type'              => 'action',
    'id'                => 'edit-status',
    'action'            => 'batchStatus',
    'url'               => App()->createUrl('userManagement/batchStatus'),
    'text'              => gT('Edit status'),
    'grid-reload'       => 'yes',
    'actionType'        => 'modal',
    'modalType'         => 'cancel-apply',
    'largeModalView'    => true,
    'keepopen'          => 'yes',
    'showSelected'      => 'yes',
    'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
    'sModalTitle'       => gT('Edit status'),
    'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updatestatus', [], true)
];

// Set Expiration Date (in dropdown)
$aMoreActionsItems[] = [
    'type'              => 'action',
    'id'                => 'edit-expires',
    'action'            => 'batchExpires',
    'url'               => App()->createUrl('userManagement/batchExpires'),
    'text'              => gT('Set expiration date'),
    'grid-reload'       => 'yes',
    'actionType'        => 'modal',
    'modalType'         => 'cancel-apply',
    'largeModalView'    => true,
    'keepopen'          => 'yes',
    'showSelected'      => 'yes',
    'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
    'sModalTitle'       => gT('Set expiration date'),
    'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updateexpires', [], true)
];

// Add role (in dropdown, for superadmins only)
if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
    $aMoreActionsItems[] = [
        'type'              => 'action',
        'action'            => 'batchaddrole',
        'url'               => App()->createUrl('userManagement/batchApplyRoles'),
        'text'              => gT('Add role'),
        'grid-reload'       => 'yes',
        'actionType'        => 'modal',
        'modalType'         => 'cancel-add',
        'keepopen'          => 'yes',
        'showSelected'      => 'yes',
        'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
        'sModalTitle'       => gT('Add role'),
        'htmlFooterButtons' => [],
        'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updaterole', [], true)
    ];
}

// Add "More actions" dropdown if there are items
if (!empty($aMoreActionsItems)) {
    $aActions[] = [
        'type'  => 'dropdown',
        'text'  => gT('More actions'),
        'items' => $aMoreActionsItems
    ];
}

// Delete (main button, last)
$aActions[] = [
    'type'          => 'action',
    'action'        => 'delete',
    'url'           => App()->createUrl('userManagement/deleteMultiple'),
    'iconClasses'   => 'ri-delete-bin-fill text-danger',
    'text'          => gT('Delete'),
    'grid-reload'   => 'yes',
    'actionType'    => 'modal',
    'modalType'     => 'cancel-delete',
    'keepopen'      => 'yes',
    'showSelected'  => 'yes',
    'selectedUrl'   => App()->createUrl('userManagement/renderSelectedItems/'),
    'sModalTitle'   => gT('Delete user'),
    'htmlModalBody' => gT('Are you sure you want to delete the selected user?'),
];

return $aActions;







