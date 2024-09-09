<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT("Edit permissions")]
);
?>

<?= TbHtml::formTb(null, App()->createUrl('userManagement/saveUserPermissions'), 'post', ["id" => "UserManagement--modalform"]) ?>
<div class="modal-dialog-scrollable">
    <div class="modal-body selector--edit-permissions-container">
        <div class="row ls-space margin top-5 bottom-5 hidden" id="UserManagement--errors">
        </div>
        <input type='hidden' name='userid' value='<?php echo (isset($oUser) ? $oUser->uid : ''); ?>' />
        <table id='UserManagement--userpermissions-table' class='activecell table '>
            <thead>
            <tr>
                <th><?php eT("Name"); ?></th>
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
                    <!-- Warning super admin -->
                    <td
                        data-bs-toggle="tooltip"
                        data-bs-placement="bottom"
                        title="<?php echo $aCRUDPermissions['description']; ?>"
                        data-bs-original-title="<?php echo $aCRUDPermissions['description']; ?>"
                    >
                        <i class="<?php echo $aCRUDPermissions['img']; ?> text-success"></i>

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
</div>

<div class="modal-footer">
    <button class="btn btn-primary selector--submitForm" id="permission-modal-submitForm">
        <?= gT('Save changes') ?>
    </button>
    <button class="btn btn-light selector--exitForm" id="permission-modal-exitForm" data-bs-dismiss="modal">
        <?= gT('Discard') ?>
    </button>
</div>
</form>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
