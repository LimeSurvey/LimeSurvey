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

<div class="selector--edit-permissions-container" data-states-url="<?= App()->createUrl('userManagement/batchPermissionStates') ?>">
    <div class="alert alert-danger d-none selector--permission-states-error" role="alert">
        <?= gT("The permissions of the selected users could not be loaded.") ?>
    </div>
    <table id='UserManagement--userpermissions-table' class='activecell table table-striped'>
        <caption class="visually-hidden"><?php eT('Edit permissions'); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th><?php eT("Permission"); ?></th>
                <th id="batch-permission-col-general"><?php eT("General"); ?></th>
                <th id="batch-permission-col-create"><?php eT("Create"); ?></th>
                <th id="batch-permission-col-read"><?php eT("View/read"); ?></th>
                <th id="batch-permission-col-update"><?php eT("Update"); ?></th>
                <th id="batch-permission-col-delete"><?php eT("Delete"); ?></th>
                <th id="batch-permission-col-import"><?php eT("Import"); ?></th>
                <th id="batch-permission-col-export"><?php eT("Export"); ?></th>
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
                <td id="batch-permission-row-<?= $sPermissionKey ?>">
                    <?php if ($sPermissionKey == 'superadmin') {
                        ?> <span class='warning'> <?php
                    };
                                                                                    echo $aCRUDPermissions['title'];
                    if ($sPermissionKey == 'superadmin') {
                        ?> </span> <?php
                    }; ?>
                </td>

                <!-- checkbox  -->
                <td>
                    <input type="checkbox" class="general-row-selector" id='all_<?php echo $sPermissionKey; ?>' name='PermissionAll[<?php echo $sPermissionKey; ?>]' aria-labelledby="batch-permission-row-<?= $sPermissionKey ?> batch-permission-col-general" />
                </td>

                <!-- CRUD -->
                <?php foreach ($aCRUDPermissions as $sCRUDKey => $CRUDValue) : ?>
                    <?php if (!in_array($sCRUDKey, array('create', 'read', 'update', 'delete', 'import', 'export'))) {
                        continue;
                    } ?>

                    <!-- Extended container -->
                    <td class='specific-settings-block'>
                        <?php if ($CRUDValue) : ?>
                            <?php if (!($sPermissionKey == 'survey' && $sCRUDKey == 'read')) : ?>
                                <!-- checkbox: Only the changed ones are posted, see LS.UserManagement.wireMassPermissions -->
                                <input
                                    type="checkbox"
                                    class="specific-permission-selector"
                                    name='Permission[<?php echo $sPermissionKey . '][' . $sCRUDKey; ?>]'
                                    id='perm_<?php echo $sPermissionKey . '_' . $sCRUDKey; ?>'
                                    data-permission="<?= $sPermissionKey ?>"
                                    data-crud="<?= $sCRUDKey ?>"
                                    aria-labelledby="batch-permission-row-<?= $sPermissionKey ?> batch-permission-col-<?= $sCRUDKey ?>"
                                    <?= (substr((string) $sPermissionKey, 0, 5) === 'auth_' && $sCRUDKey === 'read') ? 'style="visibility:hidden"' : '' ?>
                                />
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    <p id="batch-permission-mixed-help" class="form-text d-none">
        <?= gT("A checkbox with a dash means that only some of the selected users have this permission. As long as it shows the dash, this permission is not changed for any of them.") ?>
    </p>
    <div class="selector--changed-permissions"></div>
</div>