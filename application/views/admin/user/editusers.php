<h3 class="pagetitle"><?php eT("User control");?></h3>

<div class="row" style="margin-bottom: 100px">
    <div class="col-lg-12 content-right">

<table id='users' class='users table table-striped'>
    <thead>
        <tr>
            <th class="col-md-1" ><?php eT("User ID");?></th>
            <th class="col-md-2" ><?php eT("Username");?></th>
            <th class="col-md-3"><?php eT("Email");?></th>
            <th class="col-md-2"><?php eT("Full name");?></th>
            <?php if(Permission::model()->hasGlobalPermission('superadmin','read')) { ?>
                <th ><?php eT("No of surveys");?></th>
                <?php } ?>
            <th><?php eT("Created by");?></th>
            <th class="col-md-2"><?php eT("Action");?></th>
        </tr>
    </thead>
    <tbody>
        <tr >
            <td><strong><?php echo $usrhimself['uid'];?></strong></td>
            <td><strong><?php echo htmlspecialchars($usrhimself['user']);?></strong></td>
            <td><strong><?php echo htmlspecialchars($usrhimself['email']);?></strong></td>
            <td><strong><?php echo htmlspecialchars($usrhimself['full_name']);?></strong></td>

            <?php if(Permission::model()->hasGlobalPermission('superadmin','read')) { ?>
                <td><strong><?php echo $noofsurveys;?></strong></td>
                <?php } ?>

            <?php if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) { ?>
                <td><strong><?php echo $row;?></strong></td>
                <?php } else { ?>
                <td><strong>---</strong></td>
                <?php } ?>

                <td style='padding:3px;'>
                    <?php echo CHtml::form(array('admin/user/sa/modifyuser'), 'post',array('class'=>'pull-left'));?>
                        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo eT("Edit this user"); ?>" >
                            <button type='submit' class="btn btn-default btn-xs"><span class="glyphicon glyphicon-pencil text-success"></span></button> <?php // eT("Edit this user");?>
                            <input type='hidden' name='action' value='modifyuser' />
                            <input type='hidden' name='uid' value='<?php echo htmlspecialchars($usrhimself['uid']);?>' />
                        </span>
                    </form>

                    <?php if ($usrhimself['parent_id'] != 0 && Permission::model()->hasGlobalPermission('users','delete') ) { ?>
                    <?php echo CHtml::form(array('admin/user/sa/deluser'), 'post', array('class'=>'pull-left','onsubmit'=>'return confirm("'.gT("Are you sure you want to delete this entry?","js").'")') );?>
                        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo eT("Delete this user"); ?>" >
                            <button type='submit' class="btn btn-default btn-xs"><span class="glyphicon glyphicon-trash  text-danger"></span></button> <?php //<?php eT("Delete this user");?>
                            <input type='hidden' name='action' value='deluser' />
                            <input type='hidden' name='user' value='<?php echo htmlspecialchars($usrhimself['user']);?>' />
                            <input type='hidden' name='uid' value='<?php echo $usrhimself['uid'];?>' />
                        </span>
                    </form>
                    <?php } ?>
                </td>


        </tr>

        <?php for($i=1; $i<=count($usr_arr); $i++) {
                $usr = $usr_arr[$i];
            ?>
            <tr>
                <td><?php echo $usr['uid'];?></td>
                <td><?php echo htmlspecialchars($usr['user']);?></td>
                <td><a href='mailto:<?php echo htmlspecialchars($usr['email']);?>'><?php echo htmlspecialchars($usr['email']);?></a></td>
                <td><?php echo htmlspecialchars($usr['full_name']);?></td>

                <?php if(Permission::model()->hasGlobalPermission('superadmin','read')) { ?>
                    <td><?php echo $noofsurveyslist[$i];?></td>
                <?php } ?>

                <?php $uquery = "SELECT users_name FROM {{users}} WHERE uid=".$usr['parent_id'];
                    $uresult = dbExecuteAssoc($uquery); //Checked
                    $userlist = array();
                    $srow = $uresult->read();

                    $usr['parent'] = $srow['users_name']; ?>

                <?php if (isset($usr['parent_id'])) { ?>
                    <td><?php echo htmlspecialchars($usr['parent']);?></td>
                    <?php } else { ?>
                    <td>-----</td>
                    <?php } ?>
                    <td style='padding:3px;'>
                        <?php if (Permission::model()->hasGlobalPermission('superadmin','read') || $usr['uid'] == Yii::app()->session['loginID'] || (Permission::model()->hasGlobalPermission('users','update') && $usr['parent_id'] == Yii::app()->session['loginID'])) { ?>
                            <?php echo CHtml::form(array('admin/user/sa/modifyuser'), 'post', array( 'class'=>'pull-left'));?>
                                <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo eT("Edit this user"); ?>" >
                                    <button type='submit' class="btn btn-default btn-xs"><span class="glyphicon glyphicon-pencil  text-success"></span></button> <?php // eT("Edit this user");?>
                                    <input type='hidden' name='action' value='modifyuser' />
                                    <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                                </span>
                            </form>
                            <?php } ?>

                        <?php if ( ((Permission::model()->hasGlobalPermission('superadmin','read') &&
                            $usr['uid'] != Yii::app()->session['loginID'] ) ||
                            (Permission::model()->hasGlobalPermission('users','update') &&
                            $usr['parent_id'] == Yii::app()->session['loginID'])) && $usr['uid']!=1) { ?>
                            <?php echo CHtml::form(array('admin/user/sa/setuserpermissions'), 'post', array( 'class'=>'pull-left'));?>
                                <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo eT("Set global permissions for this user"); ?>" >
                                    <button type='submit' class="btn btn-default btn-xs"><span class="icon-security text-success"></span></button> <?php // eT("Set global permissions for this user");?>
                                    <input type='hidden' name='action' value='setuserpermissions' />
                                    <input type='hidden' name='user' value='<?php echo htmlspecialchars($usr['user']);?>' />
                                    <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                                </span>
                            </form>
                            <?php }
                            if ((Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('templates','read'))  && $usr['uid']!=1) { ?>
                            <?php echo CHtml::form(array('admin/user/sa/setusertemplates'), 'post', array( 'class'=>'pull-left'));?>
                                <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo eT("Set template permissions for this user"); ?>" >
                                    <button type='submit' class="btn btn-default btn-xs"><span class="icon-templatepermissions text-success"></span></button> <?php // eT("Set template permissions for this user");?>
                                    <input type='hidden' name='action' value='setusertemplates' />
                                    <input type='hidden' name='user' value='<?php echo htmlspecialchars($usr['user']);?>' />
                                    <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                                </span>
                            </form>
                            <?php }
                            if ((Permission::model()->hasGlobalPermission('superadmin','read') || (Permission::model()->hasGlobalPermission('users','delete')  && $usr['parent_id'] == Yii::app()->session['loginID']))&& $usr['uid']!=1) { ?>
                            <?php echo CHtml::form(array('admin/user/sa/deluser'), 'post', array( 'class'=>'pull-left'));?>

                                <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo eT("Delete this user"); ?>" >
                                    <button type='submit' class="btn btn-default btn-xs" onclick='return confirm("<?php eT("Are you sure you want to delete this user?","js");?>")' ><span class="glyphicon glyphicon-trash text-danger"></span></button> <?php // eT("Delete this user");?>
                                    <input type='hidden' name='action' value='deluser' />
                                    <input type='hidden' name='user' value='<?php echo htmlspecialchars($usr['user']);?>' />
                                    <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                                </span>
                            </form>
                            <?php }
                            if (Yii::app()->session['loginID'] == "1" && $usr['parent_id'] !=1 ) { ?>

                            <?php echo CHtml::form(array('admin/user/sa/setasadminchild'), 'post');?>
                                <button class="btn btn-default btn-sm" type="submit">
                                    <span class="icon-takeownership text-success"></span>
                                    <?php eT("Take ownership");?>
                                </button>

                                <input type='hidden' name='action' value='setasadminchild' />
                                <input type='hidden' name='user' value='<?php echo htmlspecialchars($usr['user']);?>' />
                                <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                            </form>
                            <?php } ?>
                    </td>
            </tr>
            <?php $row++;
        } ?>

    </tbody></table><br />
<?php if(Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('users','create')) { ?>
    <?php echo CHtml::form(array('admin/user/sa/adduser'), 'post', array('class'=>'form-inline'));?>
        <table class='users table table-responsive' id="user-control-table"><tr class='oddrow'>
                <?php if (App()->getPluginManager()->isPluginActive('AuthLDAP')) {
                          echo "<td  class='col-md-1'>";
                          echo CHtml::dropDownList('user_type', 'DB', array('DB' => gT("Internal database authentication",'unescaped'), 'LDAP' => gT("LDAP authentication",'unescaped')));
                          echo "</td>";
                      }
                      else
                      {
                          echo "<td class='col-md-1'><input type='hidden' id='user_type' name='user_type' value='DB'/></td>";
                      }
                ?>

                <td class="col-lg-2">
                    <div class="form-group">
                        <label for="new_user"><?php eT("Username:");?></label> <input type='text' id='new_user' name='new_user' required />
                    </div>
                </td>
                <td class="col-md-2">
                    <div class="form-group">
                        <label for="new_email" ><?php eT("Email:");?></label> <input type='email' id='new_email' name='new_email' required />
                    </div>
                </td>
                <td class="col-md-2">
                    <div class="form-group">
                        <label for="new_full_name"><?php eT("Full name:");?></label> <input type='text' id='new_full_name' name='new_full_name' required />
                    </div>
                </td>
                <td class="col-md-2">
                    <input type='submit' id='add_user_btn' class="btn btn-default" value='<?php eT("Add user");?>' />
                    <input type='hidden' name='action' value='adduser' /></td>
                <td style='width:5%'>&nbsp;</td>
             </tr>
             </table></form><br />
<?php } ?>



    </div>
</div>
