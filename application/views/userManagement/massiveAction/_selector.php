<!-- Rendering massive action widget -->
<?php

$aActionsArray = array(
    'pk'          => 'selectedUser',
    'gridid'      => 'usermanagement--identity-gridPanel',
    'dropupId'    => 'usermanagement--actions',
    'dropUpText'  => gT('Selected users(s)...'),

    'aActions'    => array(
        
        // Delete
        array(
            'type'          => 'action',
            'action'        => 'delete',
            'url'           =>  App()->createUrl('userManagement/deleteMultiple'),
            'iconClasses'   => 'text-danger fa fa-trash',
            'text'          =>  gT('Delete'),
            'grid-reload'   => 'yes',
            'actionType'    => 'modal',
            'modalType'     => 'cancel-delete',
            'keepopen'      => 'yes',
            'showSelected'  => 'yes',
            'selectedUrl'   => App()->createUrl('userManagement/renderSelectedItems/'),
            'sModalTitle'   => gT('Delete user'),
            'htmlModalBody' => gT('Are you sure you want to delete the selected user?'),
        ),
        // ResendLoginData
        array(
            'type'          => 'action',
            'action'        => 'resendlogindata',
            'url'           =>  App()->createUrl('userManagement/batchSendAndResetLoginData'),
            'iconClasses'   => 'text-success fa fa-refresh',
            'text'          =>  gT('Resend login data'),
            'grid-reload'   => 'yes',
            'actionType'    => 'modal',
            'modalType'     => 'cancel-apply',
            'keepopen'      => 'yes',
            'showSelected'  => 'yes',
            'selectedUrl'   => App()->createUrl('userManagement/renderSelectedItems/'),
            'sModalTitle'   => gT('Resend login data user'),
            'htmlModalBody' => gT('Are you sure you want to reset and resend selected users login data?'),
        ),
        // Mass Edit
        array(
            'type'              => 'action',
            'action'            => 'batchPermissions',
            'url'               => App()->createUrl('userManagement/batchPermissions'),
            'iconClasses'       => 'fa fa-unlock',
            'text'              => gT('Edit permissions'),
            'grid-reload'       => 'yes',
            //modal
            'actionType'        => 'modal',
            'modalType'         => 'cancel-apply',
            'largeModalView'    => true,
            'keepopen'          => 'yes',
            'showSelected'      => 'yes',
            'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
            'sModalTitle'       => gT('Edit permissions'),
            //'htmlFooterButtons' => [],
            'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updatepermissions', [], true)
        ),

        // Template Permission
        /* Decision: take this out and reimplement the action button (this massive action was never working/implemented correctly)
        array(
            'type' => 'action',
            'action' => 'templatePermission',
            'url' => Yii::app()->getController()->createUrl('userManagement/userTemplatePermissions', ['userid' => $userid]),
            'iconClasses' => 'fa fa-paint-brush',
            'text' => gT("Template permissions"),
            'grid-reload' => 'yes',
            'actionType' => 'modal',
            'modalType' => 'cancel-apply',
            'largeModalView' => true,
            'keepopen' => 'yes',
            'showSelected' => 'yes',
            'selectedUrl' => '',
            'sModalTitle' => gT("Template permissions"),
             'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updatepermissions', [], true),
        ),*/
        
    ),
);

if(Permission::model()->hasGlobalPermission('users', 'update')) {
    // Mass Edit -> roles only for superadmins
    $aActionsArray['aActions'][] = array(
        'type'          => 'action',
        'action'        => 'batchaddtogroup',
        'url'           => App()->createUrl('userManagement/batchAddGroup'),
        'iconClasses'   => 'fa fa-users',
        'text'          => gT('Add to usergroup'),
        'grid-reload'   => 'yes',
        //modal
        'actionType'    => 'modal',
        'modalType'     => 'cancel-apply',
        'keepopen'      => 'yes',
        'showSelected'  => 'yes',
        'selectedUrl'   => App()->createUrl('userManagement/renderSelectedItems/'),
        'sModalTitle'   => gT('Add to usergroup'),
        'htmlModalBody' => App()->getController()->renderPartial('/userManagement/massiveAction/_addtousergroup', [], true)
    );
}

if(Permission::model()->hasGlobalPermission('superadmin','read')) {
    // Mass Edit -> roles only for superadmins
    $aActionsArray['aActions'][] = array(
        'type'              => 'action',
        'action'            => 'batchaddrole',
        'url'               => App()->createUrl('userManagement/batchApplyRoles'),
        'iconClasses'       => 'fa fa-address-card-o',
        'text'              => gT('Add role'),
        'grid-reload'       => 'yes',
        //modal
        'actionType'        => 'modal',
        'modalType'         => 'cancel-apply',
        'largeModalView'    => true,
        'keepopen'          => 'yes',
        'showSelected'      => 'yes',
        'selectedUrl'       => App()->createUrl('userManagement/renderSelectedItems/'),
        'sModalTitle'       => gT('Add role'),
        'htmlFooterButtons' => [],
        'htmlModalBody'     => App()->getController()->renderPartial('/userManagement/massiveAction/_updaterole', [], true)
    );
}


$this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', $aActionsArray);
