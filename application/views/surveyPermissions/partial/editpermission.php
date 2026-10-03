<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT("Edit permissions")]
);
?>

<?php  echo CHtml::form(array("surveyPermissions/savePermissions/surveyid/{$surveyid}")); ?>
    <div class="modal-body selector--edit-permissions-container">
        <table id='UserManagement--userpermissions-table' class='activecell table table-striped'>
            <thead>
                <tr>
                    <th></th>
                    <th><?php eT("Permission"); ?></th>
                    <th>
                        <input type="checkbox" class="selector--select-all-permissions" id="UserManagement--userpermissions-select-all" aria-label="<?php eT('Select all permissions'); ?>" />
                        <?php eT("General"); ?>
                    </th>
                    <th><?php eT("Create"); ?></th>
                    <th><?php eT("View/read"); ?></th>
                    <th><?php eT("Update"); ?></th>
                    <th><?php eT("Delete"); ?></th>
                    <th><?php eT("Import"); ?></th>
                    <th><?php eT("Export"); ?></th>
                </tr>
            </thead>
        <!-- Permissions -->
        <tbody>
            <?php foreach ($aPermissions as $sPermission => $aCurrentPermissions) : ?>
                <tr>
                    <td>
                        <i
                            class="<?php echo $aCurrentPermissions['img'] ?> text-success"
                            tabindex="0"
                            role="img"
                            data-bs-toggle="tooltip"
                            data-bs-placement="right"
                            title="<?= CHtml::encode($aCurrentPermissions['description']) ?>"
                            aria-label="<?= CHtml::encode($aCurrentPermissions['description']) ?>"
                        ></i>
                    </td>
                    <td
                        tabindex="0"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        title="<?= CHtml::encode($aCurrentPermissions['description']) ?>"
                    ><?= $aCurrentPermissions['title'] ?></td>
                    <!-- checkbox  -->
                    <td>
                        <input type="checkbox" class="general-row-selector" id='all_<?php echo $sPermission; ?>' name='PermissionAll[<?php echo $sPermission; ?>]' />
                    </td>

                    <?php foreach ($aCurrentPermissions['current'] as $sKey => $aValues) : ?>
                        <td class='specific-settings-block'>
                            <?php if ($aCurrentPermissions[$sKey] && !$aValues['forced']) {
                                echo CHtml::checkBox(
                                    "set[{$aCurrentPermissions['entity']}][{$sPermission}][$sKey]",
                                    $aValues['checked'],
                                    [
                                        'class'              => 'specific-permission-selector',
                                        'value'              => 1,
                                        'data-indeterminate' => $aValues['indeterminate'],
                                        'id'                 => CHtml::getIdByName("set[{$aCurrentPermissions['entity']}][{$sPermission}][$sKey]"),
                                        'uncheckValue'       => 0,
                                        /* See issue #14551 : https://bugs.limesurvey.org/view.php?id=14551 */
                                        'disabled'           => $aValues['disabled'],
                                    ]
                                );
                            } ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <p class="text-muted small mb-0">
            <i class="ri-information-line" aria-hidden="true"></i>
            <?php eT("Hover over a permission title to see a detailed explanation."); ?>
        </p>
    </div>

    <div class="modal-footer">
        <button class="btn btn-cancel selector--exitForm" id="permission-modal-exitForm" data-bs-dismiss="modal">
            <?= gT('Cancel') ?>
        </button>
        <button class="btn btn-primary selector--submitForm" id="permission-modal-submitForm">
            <?= gT('Save') ?>
        </button>
    </div>

    <input class='btn btn-outline-secondary d-none'  type='submit' value='<?=gT("Save Now") ?>' />
            <?php
            if ($isUserGroup) { ?>
                    <input type='hidden' name='ugid' value="<?= $id?>" />
                    <input type='hidden' name='action' value='usergroup' />
                <?php
            } else {?>
                    <input type='hidden' name='uid' value="<?= $id?>" />
                    <input type='hidden' name='action' value='user' />
            <?php }
            ?>

</form>