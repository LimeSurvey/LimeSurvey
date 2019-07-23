<div class="modal-header">
    <h3>
        <?php eT("Edit permissions");?>
    </h3>
</div>
<div class="modal-body selector--edit-permissions-container">
    <div class="container-center">        
        <?=TbHtml::formTb(
            null, 
            App()->createUrl('admin/roles/sa/savepermissions', ['ptid' => $oModel->ptid]), 
            'post', 
            ["id"=>"RoleControl--modalform"]
        )?>
            <input type='hidden' name='ptid' value='<?php echo (isset($oModel) ? $oModel->ptid : '');?>' />
            <table id='RoleControl--permissions-table' class='activecell table table-striped'>
                <thead>
                    <tr>
                        <th></th>
                        <th><?php eT("Permission");?></th>
                        <th><?php eT("General");?></th>
                        <th><?php eT("Create");?></th>
                        <th><?php eT("View/read");?></th>
                        <th><?php eT("Update");?></th>
                        <th><?php eT("Delete");?></th>
                        <th><?php eT("Import");?></th>
                        <th><?php eT("Export");?></th>
                    </tr>
               </thead>

                <!-- Permissions -->
                <?php foreach($aBasePermissions as $sPermissionKey=>$aCRUDPermissions): ?>
                    <tr>
                        <!-- Icon -->
                        <td>
                            <i class="<?php echo $aCRUDPermissions['img']; ?> text-success"></i>
                            <?php echo $aCRUDPermissions['description'];?>
                        </td>

                        <!-- Warning super admin -->
                        <td>
                            <?php if ($sPermissionKey=='superadmin') {?> <span class='warning'> <?php }; echo $aCRUDPermissions['title']; if ($sPermissionKey=='superadmin') {?> </span> <?php };?>
                        </td>

                        <!-- checkbox  -->
                        <td>
                            <input type="checkbox" class="general-row-selector" id='all_<?php echo $sPermissionKey;?>' name='PermissionAll[<?php echo $sPermissionKey;?>]' />
                        </td>

                        <!-- CRUD -->
                        <?php foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue): ?>
                            <?php if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue; ?>

                            <!-- Extended container -->
                            <td class='specific-settings-block'>
                                <?php if ($CRUDValue): ?>
                                    <?php if (!($sPermissionKey=='survey' && $sCRUDKey=='read')): ?>

                                        <!-- checkbox -->
                                        <input type="checkbox"  class="specific-permission-selector" name='Permission[<?php echo $sPermissionKey.']['.$sCRUDKey;?>]' id='perm_<?php echo $sPermissionKey.'_'.$sCRUDKey;?>'
                                            <?php if(Permission::model()->hasRolePermission( $oModel->ptid, $sPermissionKey, $sCRUDKey)):?>
                                                checked="checked"
                                            <?php endif; ?>
                                            <?php if(substr($sPermissionKey,0,5) === 'auth_' && $sCRUDKey === 'read'): ?>
                                                style="visibility:hidden"
                                            <?php endif; ?>/>
                                        <?php endif; ?>
                                    <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>

                </table>
            <div class="row ls-space margin top-25">
                <button class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1 selector--submitForm" id="submitForm"><?=gT('Save')?></button>
                <button class="btn btn-error col-sm-3 col-xs-5 col-xs-offset-1 selector--exitForm" id="exitForm"><?=gT('Cancel')?></button>
            </div>
        </form>
    </div>
</div>
