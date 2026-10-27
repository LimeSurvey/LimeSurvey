<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT("Edit user permissions")]
);
?>

<?= TbHtml::formTb(null, App()->createUrl('userManagement/saveUserPermissions'), 'post', ["id" => "UserManagement--modalform"]) ?>
<div class="modal-body overflow-scroll selector--edit-permissions-container">
    <div class="row ls-space margin top-5 bottom-5 d-none" id="UserManagement--errors" role="alert" aria-live="assertive" aria-atomic="true" tabindex="-1">
    </div>
    <?php if (!empty($aRoleNames)) : ?>
        <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => sprintf(
                    gT("Warning: This user has the following roles: %s. Setting individual user permissions will remove all roles from this user!"),
                    CHtml::encode(implode(', ', $aRoleNames))
                ),
                'type' => 'warning',
            ]);
        ?>
    <?php endif; ?>
    <input type='hidden' name='userid' value='<?php echo (isset($oUser) ? $oUser->uid : ''); ?>' />
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
        <?php $hasIndeterminate = false; ?>
        <?php foreach ($aBasePermissions as $sPermissionKey => $aCRUDPermissions) : ?>
            <tr>
                <!-- Icon -->
                <td>
                    <div>
                        <i
                            class="<?php echo $aCRUDPermissions['img']; ?> text-success"
                            tabindex="0"
                            role="img"
                            data-bs-toggle="tooltip"
                            data-bs-placement="right"
                            title="<?= $aCRUDPermissions['description'] ?>"
                            aria-label="<?= $aCRUDPermissions['description'] ?>"
                        ></i>
                    </div>
                </td>

                <!-- Warning super admin -->
                <td>
                    <span
                        tabindex="0"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        title="<?= $aCRUDPermissions['description'] ?>"
                    >
                        <?php if ($sPermissionKey == 'superadmin') {
                            ?> <span class='warning'> <?php
                        };
                                                                                        echo $aCRUDPermissions['title'];
                        if ($sPermissionKey == 'superadmin') {
                            ?> </span> <?php
                        }; ?>
                    </span>
                    <?php if (!empty($aCRUDPermissions['warning'])) : ?>
                        <i
                            class="ri-error-warning-fill text-danger"
                            tabindex="0"
                            role="img"
                            data-bs-toggle="tooltip"
                            data-bs-placement="right"
                            title="<?= $aCRUDPermissions['warning'] ?>"
                            aria-label="<?= $aCRUDPermissions['warning'] ?>"
                        ></i>
                    <?php endif; ?>
                </td>

                <!-- checkbox  -->
                <td>
                    <input type="checkbox" class="general-row-selector" id='all_<?php echo $sPermissionKey; ?>' name='PermissionAll[<?php echo $sPermissionKey; ?>]' />
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
                                <?php
                                $isHidden = substr((string) $sPermissionKey, 0, 5) === 'auth_' && $sCRUDKey === 'read';
                                $isChecked = !empty($aUserPermissions[$sPermissionKey]) && $aUserPermissions[$sPermissionKey]->getAttribute($sCRUDKey . '_p');
                                /* The user didn't have the permission set, but have it by other way (role, superadmin …) */
                                $isIndeterminate = !$isHidden && !$isChecked && Permission::model()->hasGlobalPermission($sPermissionKey, $sCRUDKey, $oUser->uid);
                                $hasIndeterminate = $hasIndeterminate || $isIndeterminate;
                                ?>
                                <!-- checkbox -->
                                <input
                                    type="checkbox"
                                    class="specific-permission-selector"
                                    name='Permission[<?php echo $sPermissionKey . '][' . $sCRUDKey; ?>]'
                                    id='perm_<?php echo $sPermissionKey . '_' . $sCRUDKey; ?>'
                                    <?= $isChecked ? 'checked="checked"' : '' ?>
                                    <?= $isIndeterminate ? 'data-indeterminate="1" aria-describedby="permission-indeterminate-help"' : '' ?>
                                    <?= $isHidden ? 'style="visibility:hidden"' : '' ?>
                                />
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>

    </table>
    <?php if ($hasIndeterminate) : ?>
        <p id="permission-indeterminate-help" class="form-text">
            <?= gT("A checkbox with a dash means that the user already has this permission in another way, for example through a role.") ?>
        </p>
    <?php endif; ?>
    <p class="text-muted small mb-0">
        <i class="ri-information-line" aria-hidden="true"></i>
        <?php eT("Hover over a permission title to see a detailed explanation."); ?>
    </p>
</div>

<div class="modal-footer">
    <button class="btn btn-cancel selector--exitForm" id="permission-modal-exitForm" data-bs-dismiss="modal">
        <?= gT('Cancel') ?>
    </button>
    <button
        class="btn btn-primary selector--submitForm"
        id="permission-modal-submitForm"
        <?php if (!empty($aRoleNames)) : ?>
            data-confirm-title="<?= CHtml::encode(gT("Remove roles?")) ?>"
            data-confirm-message="<?= CHtml::encode(sprintf(gT("Saving will remove the following roles from this user: %s. The user will then only have the permissions selected here."), implode(', ', $aRoleNames))) ?>"
            data-confirm-btntext="<?= CHtml::encode(gT("Remove roles and save")) ?>"
        <?php endif; ?>
    >
        <?= gT('Save') ?>
    </button>
</div>
</form>
