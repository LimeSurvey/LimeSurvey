<?php
// Check permissions
$aBasePermissions = Permission::model()->getGlobalBasePermissions();
if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
    // if not superadmin filter the available permissions as no admin may give more permissions than he owns
    Yii::app()->session['flashmessage'] = gT("Note: You can only give limited permissions to other users because your own permissions are limited, too.");
    $aFilteredPermissions = array();
    foreach ($aBasePermissions as $PermissionName => $aPermission) {
        foreach ($aPermission as $sPermissionKey => &$sPermissionValue) {
            if ($sPermissionKey != 'title' && $sPermissionKey != 'img' && !Permission::model()->hasGlobalPermission($PermissionName, $sPermissionKey)) {
                $sPermissionValue = false;
            }
        }
        // Only show a row for that permission if there is at least one permission he may give to other users
        if ($aPermission['create'] || $aPermission['read'] || $aPermission['update'] || $aPermission['delete'] || $aPermission['import'] || $aPermission['export']) {
            $aFilteredPermissions[$PermissionName] = $aPermission;
        }
    }
    $aBasePermissions = $aFilteredPermissions;
}
?>

<div class="selector--edit-permissions-container">
    <table id='UserManagement--userpermissions-table' class='activecell table table-striped'>
        <thead>
            <tr>
                <th></th>
                <th><?php eT("Permission"); ?></th>
                <th><?php eT("General"); ?></th>
                <th><?php eT("Create"); ?></th>
                <th><?php eT("View/read"); ?></th>
                <th><?php eT("Update"); ?></th>
                <th><?php eT("Delete"); ?></th>
                <th><?php eT("Import"); ?></th>
                <th><?php eT("Export"); ?></th>
            </tr>
        </thead>

        <!-- Permissions -->
        <?php foreach ($aBasePermissions as $sPermissionKey => $aCRUDPermissions) : ?>
            <tr>
                <!-- Icon -->
                <td>
                    <i class="<?php echo $aCRUDPermissions['img']; ?> text-success"></i>
                    <?php echo $aCRUDPermissions['description']; ?>
                </td>

                <!-- Warning super admin -->
                <td>
                    <?php if ($sPermissionKey == 'superadmin') { ?> <span class='warning'> <?php };
                                                                                    echo $aCRUDPermissions['title'];
                                                                                    if ($sPermissionKey == 'superadmin') { ?> </span> <?php }; ?>
                </td>

                <!-- checkbox  -->
                <td>
                    <input type="checkbox" class="general-row-selector custom-data" id='all_<?php echo $sPermissionKey; ?>' name='PermissionAll[<?php echo $sPermissionKey; ?>]' />
                </td>

                <!-- CRUD -->
                <?php foreach ($aCRUDPermissions as $sCRUDKey => $CRUDValue) : ?>
                    <?php if (!in_array($sCRUDKey, array('create', 'read', 'update', 'delete', 'import', 'export'))) continue; ?>

                    <!-- Extended container -->
                    <td class='specific-settings-block'>
                        <?php if ($CRUDValue) : ?>
                            <?php if (!($sPermissionKey == 'survey' && $sCRUDKey == 'read')) : ?>

                                <!-- checkbox -->
                                <input type="checkbox" class="specific-permission-selector custom-data" name='Permission[<?php echo $sPermissionKey . '][' . $sCRUDKey; ?>]' id='perm_<?php echo $sPermissionKey . '_' . $sCRUDKey; ?>' <?php if (substr((string) $sPermissionKey, 0, 5) === 'auth_' && $sCRUDKey === 'read') : ?> style="visibility:hidden" <?php endif; ?> />
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    <div id="hereBeUserIds">
    </div>
</div>