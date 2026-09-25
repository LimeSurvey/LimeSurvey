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
                            title="<?= CHtml::encode($aCRUDPermissions['description']) ?>"
                            aria-label="<?= CHtml::encode($aCRUDPermissions['description']) ?>"
                        ></i>
                    </div>
                </td>

                <!-- Warning super admin -->
                <td>
                    <span
                        tabindex="0"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        title="<?= CHtml::encode($aCRUDPermissions['description']) ?>"
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
                            title="<?= CHtml::encode($aCRUDPermissions['warning']) ?>"
                            aria-label="<?= CHtml::encode($aCRUDPermissions['warning']) ?>"
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
                                <!-- checkbox -->
                                <input type="checkbox" class="specific-permission-selector" name='Permission[<?php echo $sPermissionKey . '][' . $sCRUDKey; ?>]' id='perm_<?php echo $sPermissionKey . '_' . $sCRUDKey; ?>' <?php if (Permission::model()->hasGlobalPermission($sPermissionKey, $sCRUDKey, $oUser->uid)) :
                                    ?> checked="checked" <?php
                                                                                                             endif; ?> <?php if (substr((string) $sPermissionKey, 0, 5) === 'auth_' && $sCRUDKey === 'read') :
    ?> style="visibility:hidden" <?php
                                                                                                             endif; ?> />
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>

    </table>
    <p class="text-muted small mb-0">
        <i class="ri-information-line" aria-hidden="true"></i>
        <?php eT("Hover over a permission title to see a detailed explanation."); ?>
    </p>
    <div class="row ls-space margin top-25">
       <?php if (safecount(Permission::model()->getUserRole($oUser->uid)) > 0) : ?>
            <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("Warning: The user has at least one role assigned. Setting individual user permissions will remove all roles from this user!"),
                    'type' => 'warning',
                ]);
            ?>
       <?php endif; ?>
    </div>
</div>

<div class="modal-footer">
    <button class="btn btn-cancel selector--exitForm" id="permission-modal-exitForm" data-bs-dismiss="modal">
        <?= gT('Cancel') ?>
    </button>
    <button class="btn btn-primary selector--submitForm" id="permission-modal-submitForm">
        <?= gT('Save') ?>
    </button>
</div>
</form>
