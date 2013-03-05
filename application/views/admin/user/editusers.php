<div class='header ui-widget-header'><?php $clang->eT("User control");?></div><br />
<table id='users' class='users'>
    <thead>
        <tr>
            <th><?php $clang->eT("Action");?></th>

            <th style='width:20%'><?php $clang->eT("Username");?></th>
            <th style='width:20%'><?php $clang->eT("Email");?></th>
            <th style='width:20%'><?php $clang->eT("Full name");?></th>
            <?php if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) { ?>
                <th style='width:5%'><?php $clang->eT("No of surveys");?></th>
                <?php } ?>
            <th style='width:15%'><?php $clang->eT("Created by");?></th>
        </tr></thead><tbody>
        <tr >
            <td style='padding:3px;'>
                <?php echo CHtml::form(array('admin/user/sa/modifyuser'), 'post');?>            
                    <input type='image' src='<?php echo $imageurl;?>edit_16.png' alt='<?php $clang->eT("Edit user");?>' />
                    <input type='hidden' name='action' value='modifyuser' />
                    <input type='hidden' name='uid' value='<?php echo htmlspecialchars($usrhimself['uid']);?>' />
                </form>

                <?php if ($usrhimself['parent_id'] != 0 && Yii::app()->session['USER_RIGHT_DELETE_USER'] == 1 ) { ?>
                <?php echo CHtml::form(array('admin/user/sa/deluser'), 'post', array('onsubmit'=>'return confirm("'.$clang->gT("Are you sure you want to delete this entry?","js").'")') );?>            
                        <input type='submit' value='<?php $clang->eT("Delete");?>' />
                        <input type='hidden' name='action' value='deluser' />
                        <input type='hidden' name='user' value='<?php echo htmlspecialchars($usrhimself['user']);?>' />
                        <input type='hidden' name='uid' value='<?php echo $usrhimself['uid'];?>' />
                    </form>
                    <?php } ?>

            </td>

            <td><strong><?php echo htmlspecialchars($usrhimself['user']);?></strong></td>
            <td><strong><?php echo htmlspecialchars($usrhimself['email']);?></strong></td>
            <td><strong><?php echo htmlspecialchars($usrhimself['full_name']);?></strong></td>

            <?php if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) { ?>
                <td><strong><?php echo $noofsurveys;?></strong></td>
                <?php } ?>

            <?php if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) { ?>
                <td><strong><?php echo $row;?></strong></td>
                <?php } else { ?>
                <td><strong>---</strong></td>
                <?php } ?>
        </tr>

        <?php for($i=1; $i<=count($usr_arr); $i++) {
                $usr = $usr_arr[$i];
            ?>
            <tr>

                <td style='padding:3px;'>
                    <?php if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || $usr['uid'] == Yii::app()->session['loginID'] || (Yii::app()->session['USER_RIGHT_CREATE_USER'] == 1 && $usr['parent_id'] == Yii::app()->session['loginID'])) { ?>
                        <?php echo CHtml::form(array('admin/user/sa/modifyuser'), 'post');?>            
                            <input type='image' src='<?php echo $imageurl;?>edit_16.png' alt='<?php $clang->eT("Edit this user");?>' />
                            <input type='hidden' name='action' value='modifyuser' />
                            <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                        </form>
                        <?php } ?>

                    <?php if ( ((Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 &&
                        $usr['uid'] != Yii::app()->session['loginID'] ) ||
                        (Yii::app()->session['USER_RIGHT_CREATE_USER'] == 1 &&
                        $usr['parent_id'] == Yii::app()->session['loginID'])) && $usr['uid']!=1) { ?>
                        <?php echo CHtml::form(array('admin/user/sa/setUserRights'), 'post');?>            
                            <input type='image' src='<?php echo $imageurl;?>security_16.png' alt='<?php $clang->eT("Set global permissions for this user");?>' />
                            <input type='hidden' name='action' value='setUserRights' />
                            <input type='hidden' name='user' value='<?php echo htmlspecialchars($usr['user']);?>' />
                            <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                        </form>
                        <?php }
                        if (Yii::app()->session['loginID'] == "1" && $usr['parent_id'] !=1 ) { ?>

                        <?php echo CHtml::form(array('admin/user/sa/setasadminchild'), 'post');?>            
                            <input type='image' src='<?php echo $imageurl;?>takeownership.png' alt='<?php $clang->eT("Take ownership");?>' />
                            <input type='hidden' name='action' value='setasadminchild' />
                            <input type='hidden' name='user' value='<?php echo htmlspecialchars($usr['user']);?>' />
                            <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                        </form>
                        <?php }
                        if ((Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] == 1)  && $usr['uid']!=1) { ?>
                        <?php echo CHtml::form(array('admin/user/sa/setusertemplates'), 'post');?>            
                            <input type='image' src='<?php echo $imageurl;?>templatepermissions_small.png' alt='<?php $clang->eT("Set template permissions for this user");?>' />
                            <input type='hidden' name='action' value='setusertemplates' />
                            <input type='hidden' name='user' value='<?php echo htmlspecialchars($usr['user']);?>' />
                            <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                        </form>
                        <?php }
                        if ((Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || (Yii::app()->session['USER_RIGHT_DELETE_USER'] == 1  && $usr['parent_id'] == Yii::app()->session['loginID']))&& $usr['uid']!=1) { ?>
                        <?php echo CHtml::form(array('admin/user/sa/deluser'), 'post');?>            
                            <input type='image' src='<?php echo $imageurl;?>token_delete.png' alt='<?php $clang->eT("Delete this user");?>' onclick='return confirm("<?php $clang->eT("Are you sure you want to delete this entry?","js");?>")' />
                            <input type='hidden' name='action' value='deluser' />
                            <input type='hidden' name='user' value='<?php echo htmlspecialchars($usr['user']);?>' />
                            <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
                        </form>
                        <?php } ?>

                </td>
                <td><?php echo htmlspecialchars($usr['user']);?></td>
                <td><a href='mailto:<?php echo htmlspecialchars($usr['email']);?>'><?php echo htmlspecialchars($usr['email']);?></a></td>
                <td><?php echo htmlspecialchars($usr['full_name']);?></td>

                <?php if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1) { ?>
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

            </tr>
            <?php $row++;
        } ?>
    </tbody></table><br />
<?php if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_CREATE_USER']) { ?>
    <?php echo CHtml::form(array('admin/user/sa/adduser'), 'post');?>            
        <table class='users'><tr class='oddrow'>
                <th><?php $clang->eT("Add user:");?></th>
                <td style='width:20%'><input type='text' name='new_user' /></td>
                <td style='width:20%'><input type='text' name='new_email' /></td>
                <td style='width:20%'><input type='text' name='new_full_name' /></td><td style='width:8%'>&nbsp;</td>
                <td style='width:15%'><input type='submit' value='<?php $clang->eT("Add user");?>' />
                    <input type='hidden' name='action' value='adduser' /></td>
            </tr></table></form><br />
    <?php } ?>
