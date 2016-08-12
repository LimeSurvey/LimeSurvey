<?php
/**
 * Set user permissions
 */
?>

<!-- set user permissions -->
<h3 class="pagetitle"><?php printf(gT("Edit user permissions for user %s"),"<span style='font-style:italic'>".$oUser->users_name."</span>"); ?></h3>

<div class="row" style="margin-bottom: 100px">
    <div class="col-lg-10 col-lg-offset-1">

        <!-- Form -->
        <?php echo CHtml::form(array("admin/user/sa/savepermissions"), 'post', array('id'=>'savepermissions'));?>
            <table class='userpermissions activecell table table-striped'>
                <thead>
                    <tr>
                        <th></th>
                        <th><?php eT("Permission");?></th>
                        <th><input type='button' class="btn btn-default btn-sm" id='btnToggleAdvanced' value='<<' /></th>
                        <th class='extended'><?php eT("Create");?></th>
                        <th class='extended'><?php eT("View/read");?></th>
                        <th class='extended'><?php eT("Update");?></th>
                        <th class='extended'><?php eT("Delete");?></th>
                        <th class='extended'><?php eT("Import");?></th>
                        <th class='extended'><?php eT("Export");?></th>
                    </tr>
               </thead>

                <!-- Permissions -->
                <?php foreach($aBasePermissions as $sPermissionKey=>$aCRUDPermissions): ?>
                    <?php
                        if ($sPermissionKey == 'auth_ldap' && !App()->getPluginManager()->isPluginActive('AuthLDAP')
                            || $sPermissionKey == 'auth_webserver' && !App()->getPluginManager()->isPluginActive('Authwebserver'))
                        {
                            continue;
                        }
                    ?>

                    <tr>
                        <!-- Icon -->
                        <td>
                            <span class="icon-<?php echo $aCRUDPermissions['img']; ?> text-success"></span>
                            <?php echo $aCRUDPermissions['description'];?>
                        </td>

                        <!-- Warning super admin -->
                        <td>
                            <?php if ($sPermissionKey=='superadmin') {?> <span class='warning'> <?php }; echo $aCRUDPermissions['title']; if ($sPermissionKey=='superadmin') {?> </span> <?php };?>
                        </td>

                        <!-- checkbox  -->
                        <td>
                            <input type="checkbox" class="markrow" id='all_<?php echo $sPermissionKey;?>' name='all_<?php echo $sPermissionKey;?>' />
                        </td>

                        <!-- CRUD -->
                        <?php foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue): ?>
                            <?php if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue; ?>

                            <!-- Extended container -->
                            <td class='extended'>
                                <?php if ($CRUDValue): ?>
                                    <?php if (!($sPermissionKey=='survey' && $sCRUDKey=='read')): ?>

                                        <!-- checkbox -->
                                        <input type="checkbox"  class="checkboxbtn" name='perm_<?php echo $sPermissionKey.'_'.$sCRUDKey;?>' id='perm_<?php echo $sPermissionKey.'_'.$sCRUDKey;?>'
                                            <?php if(Permission::model()->hasGlobalPermission( $sPermissionKey, $sCRUDKey, $oUser->uid)):?>
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

                <!-- submit button -->
                <p>
                    <input type='submit' class="hidden"  value='<?php eT("Save");?>' />
                    <input type='hidden' name='action' value='surveyrights' />
                    <input type='hidden' name='uid' value='<?php echo $oUser->uid;?>' />
                </p>
            </form>
    </div>
</div>
