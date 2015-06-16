
<div class='header ui-widget-header'><?php printf(gT("Edit user permissions for user %s"),"<span style='font-style:italic'>".$oUser->users_name."</span>"); ?></div>
<br />
<?php echo CHtml::form(array("admin/user/sa/savepermissions"), 'post');?>
<table style='margin:0 auto;' class='userpermissions activecell'><thead>

        <tr><th></th><th><?php eT("Permission");?></th>
            <th><input type='button' id='btnToggleAdvanced' value='<<' /></th>
            <th class='extended'><?php eT("Create");?></th>
            <th class='extended'><?php eT("View/read");?></th>
            <th class='extended'><?php eT("Update");?></th>
            <th class='extended'><?php eT("Delete");?></th>
            <th class='extended'><?php eT("Import");?></th>
            <th class='extended'><?php eT("Export");?></th>
        </tr></thead>

    <?php
        foreach($aBasePermissions as $sPermissionKey=>$aCRUDPermissions)
        {
            if ($sPermissionKey == 'auth_ldap' && !App()->getPluginManager()->isPluginActive('AuthLDAP')
                || $sPermissionKey == 'auth_webserver' && !App()->getPluginManager()->isPluginActive('Authwebserver'))
            {
                continue;
            }
        ?>
        <tr><td><img src='<?php echo $sImageURL.$aCRUDPermissions['img'];?>_30.png' alt='<?php echo $aCRUDPermissions['description'];?>'/></td>
            <td><?php if ($sPermissionKey=='superadmin') {?> <span class='warning'> <?php }; echo $aCRUDPermissions['title']; if ($sPermissionKey=='superadmin') {?> </span> <?php };?></td>
            <td><input type="checkbox" class="markrow" id='all_<?php echo $sPermissionKey;?>' name='all_<?php echo $sPermissionKey;?>' /></td>
            <?php
                foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue)
                {
                    if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue;
                ?>
                <td class='extended'>

                    <?php 
                        if ($CRUDValue)
                        {
                            if (!($sPermissionKey=='survey' && $sCRUDKey=='read'))
                            { ?>
                            <input type="checkbox"  class="checkboxbtn" name='perm_<?php echo $sPermissionKey.'_'.$sCRUDKey;?>' id='perm_<?php echo $sPermissionKey.'_'.$sCRUDKey;?>' <?php 
                                if(Permission::model()->hasGlobalPermission( $sPermissionKey, $sCRUDKey, $oUser->uid)) {?>
                                checked="checked"
                                <?php } ?>
                                 <?php
                                if(substr($sPermissionKey,0,5) === 'auth_' && $sCRUDKey === 'read') {?>
                                style="visibility:hidden"
                                <?php } ?>/>
                            <?php
                            }
                        }
                    ?>
                </td>
                <?php
            } ?>
        </tr>
        <?php
    } ?>

    </table>
    <p><input type='submit' value='<?php eT("Save");?>' />
    <input type='hidden' name='action' value='surveyrights' />
    <input type='hidden' name='uid' value='<?php echo $oUser->uid;?>' />
</form>


