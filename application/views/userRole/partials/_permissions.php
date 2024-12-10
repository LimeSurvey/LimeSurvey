<?php


/** @var Permissiontemplates $oModel */

Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Edit permissions')]
);
?>

<?= TbHtml::formTb(
    null,
    App()->createUrl('userRole/savePermissions'),
    'post',
    ["id" => "RoleControl--modalform"]
) ?>

<div class="modal-body selector--edit-permissions-container">
    <input type='hidden' name='ptid' value='<?php echo (isset($oModel) ? $oModel->ptid : ''); ?>' />
    <table id='RoleControl--permissions-table' class='activecell table table-striped'>
        <thead>
            <tr>
                <th></th>
                <th><?php
                    eT("Permission"); ?></th>
                <th><?php
                    eT("General"); ?></th>
                <th><?php
                    eT("Create"); ?></th>
                <th><?php
                    eT("View/read"); ?></th>
                <th><?php
                    eT("Update"); ?></th>
                <th><?php
                    eT("Delete"); ?></th>
                <th><?php
                    eT("Import"); ?></th>
                <th><?php
                    eT("Export"); ?></th>
            </tr>
        </thead>

        <!-- Permissions -->
        <?php
        foreach ($aBasePermissions as $sPermissionKey => $aCRUDPermissions) : ?>
            <tr>
                <!-- Icon -->
                <td>
                    <div><i class="<?php
                                echo $aCRUDPermissions['img']; ?> text-success"></i>
                    <?php
                    echo $aCRUDPermissions['description']; ?></div>
                    <?php if (!empty($aCRUDPermissions['warning'])) : ?>
                        <div class="text-danger"><i class="ri-error-warning-fill" aria-hidden="true"></i>
                        <?php echo $aCRUDPermissions['warning']; ?></div>
                    <?php endif; ?>
                </td>

                <!-- Warning super admin -->
                <td>
                    <?php
                    if ($sPermissionKey == 'superadmin') { ?> <span class='warning'> <?php
                                                                                    };
                                                                                    echo $aCRUDPermissions['title'];
                                                                                    if ($sPermissionKey == 'superadmin') { ?> </span> <?php
                                                                                                                                    }; ?>
                </td>

                <!-- checkbox  -->
                <td>
                    <input type="checkbox" class="general-row-selector" id='all_<?php
                                                                                echo $sPermissionKey; ?>' name='PermissionAll[<?php
                                                                                                                                echo $sPermissionKey; ?>]' />
                </td>

                <!-- CRUD -->
                <?php
                foreach ($aCRUDPermissions as $sCRUDKey => $CRUDValue) : ?>
                    <?php
                    if (!in_array($sCRUDKey, array('create', 'read', 'update', 'delete', 'import', 'export'))) {
                        continue;
                    } ?>

                    <!-- Extended container -->
                    <td class='specific-settings-block'>
                        <?php
                        if ($CRUDValue) : ?>
                            <?php
                            if (!($sPermissionKey == 'survey' && $sCRUDKey == 'read')) : ?>

                                <!-- checkbox -->
                                <input type="checkbox" class="specific-permission-selector" name='Permission[<?php
                                                                                                                echo $sPermissionKey . '][' . $sCRUDKey; ?>]' id='perm_<?php
                                                                                                                                                                        echo $sPermissionKey . '_' . $sCRUDKey; ?>' <?php
                                                                                                                                                                                                                    if (Permission::model()->roleHasPermission(
                                                                                                                                                                                                                        $oModel->ptid,
                                                                                                                                                                                                                        $sPermissionKey,
                                                                                                                                                                                                                        $sCRUDKey
                                                                                                                                                                                                                    )) : ?> checked="checked" <?php
                                                                                                                                                                                                                    endif; ?> <?php
                                                                                                                                                    if (substr((string) $sPermissionKey, 0, 5) === 'auth_' && $sCRUDKey === 'read') : ?> style="visibility:hidden" <?php
                                                                                                                                                                                                endif; ?> />
                            <?php
                            endif; ?>
                        <?php
                        endif; ?>
                    </td>
                <?php
                endforeach; ?>
            </tr>
        <?php
        endforeach; ?>

    </table>
</div>
<div class="modal-footer modal-footer-buttons" style="margin-top: 15px;">
    <button class="btn btn-cancel selector--exitForm" id="exitForm" data-bs-dismiss="modal">
        <?= gT('Cancel') ?>
    </button>
    <button class="btn btn-primary selector--submitForm" id="submitForm">
        <?= gT('Save') ?>
    </button>
</div>
</form>
