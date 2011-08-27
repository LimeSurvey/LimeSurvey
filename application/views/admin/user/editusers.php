<div class='header ui-widget-header'><?php echo $clang->gT("User control");?></div><br />
<table id='users' class='users' width='100%' border='0'>
<thead>
<tr>
<th><?php echo $clang->gT("Action");?></th>
	    
<th width='20%'><?php echo $clang->gT("Username");?></th>
<th width='20%'><?php echo $clang->gT("Email");?></th>
<th width='20%'><?php echo $clang->gT("Full name");?></th>
<?php if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1) { ?>
    <th width='5%'><?php echo $clang->gT("No of surveys");?></th>
<?php } ?>
<th width='15%'><?php echo $clang->gT("Created by");?></th>
</tr></thead><tbody>
<tr >
<td align='center' style='padding:3px;'>
<form method='post' action='<?php echo site_url("admin/user/modifyuser");?>'>
<input type='image' src='<?php echo $imageurl;?>/token_edit.png' value='<?php echo $clang->gT("Edit user");?>' />
<input type='hidden' name='action' value='modifyuser' />
<input type='hidden' name='uid' value='<?php echo $usrhimself['uid'];?>' />
</form>

<?php if ($usrhimself['parent_id'] != 0 && $this->session->userdata('USER_RIGHT_DELETE_USER') == 1 ) { ?>
    <form method='post' action='$scriptname?action=deluser'>
    <input type='submit' value='<?php echo $clang->gT("Delete");?>' onclick='return confirm("<?php echo $clang->gT("Are you sure you want to delete this entry?","js");?>")' />
    <input type='hidden' name='action' value='deluser' />
    <input type='hidden' name='user' value='<?php echo $usrhimself['user'];?>' />
    <input type='hidden' name='uid' value='<?php echo $usrhimself['uid'];?>' />
    </form>
<?php } ?>

</td>

<td align='center'><strong><?php echo $usrhimself['user'];?></strong></td>
<td align='center'><strong><?php echo $usrhimself['email'];?></strong></td>
<td align='center'><strong><?php echo $usrhimself['full_name'];?></strong></td>

<?php if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1) { ?>
    <td align='center'><strong><?php echo $noofsurveys;?></strong></td>
<?php } ?>

<?php if(isset($usrhimself['parent_id']) && $usrhimself['parent_id']!=0) { ?>
    <td align='center'><strong><?php echo $srow['users_name'];?></strong></td>
<?php } else { ?>
    <td align='center'><strong>---</strong></td>
<?php } ?>
</tr>

<?php for($i=1; $i<=count($usr_arr); $i++) {
	$usr = $usr_arr[$i]; ?>
    <tr>

    <td align='center' style='padding:3px;'>
    <?php if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $usr['uid'] == $_SESSION['loginID'] || ($this->session->userdata('USER_RIGHT_CREATE_USER') == 1 && $usr['parent_id'] == $this->session->userdata('loginID'))) { ?>
        <form method='post' action='<?php echo site_url("admin/user/modifyuser");?>'>
        <input type='image' src='<?php echo $imageurl;?>/token_edit.png' alt='<?php echo $clang->gT("Edit this user");?>' />
        <input type='hidden' name='action' value='modifyuser' />
        <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
        </form>
    <?php } ?>

    <?php if ( (($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 &&
    $usr['uid'] != $this->session->userdata('loginID') ) ||
    ($this->session->userdata('USER_RIGHT_CREATE_USER') == 1 &&
    $usr['parent_id'] == $this->session->userdata('loginID'))) && $usr['uid']!=1) { ?>
        <form method='post' action='<?php echo site_url("admin/user/setuserrights/");?>'>
        <input type='image' src='<?php echo $imageurl;?>/security_16.png' alt='<?php echo $clang->gT("Set global permissions for this user");?>' />
        <input type='hidden' name='action' value='setuserrights' />
        <input type='hidden' name='user' value='<?php echo $usr['user'];?>' />
        <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
        </form>
    <?php }
    if ($this->session->userdata('loginID') == "1" && $usr['parent_id'] !=1 ) { ?>
        <form method='post' action='<?php echo $scriptname;?>'>
        <input type='submit' value='<?php echo $clang->gT("Take Ownership");?>' />
        <input type='hidden' name='action' value='setasadminchild' />
        <input type='hidden' name='user' value='<?php echo $usr['user'];?>' />
        <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
        </form>
    <?php }
    if (($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') == 1)  && $usr['uid']!=1) { ?>
        <form method='post' action='<?php echo site_url("admin/user/setusertemplates/");?>'>
        <input type='image' src='<?php echo $imageurl;?>/templatepermissions_small.png' alt='<?php echo $clang->gT("Set template permissions for this user");?>' />
        <input type='hidden' name='action' value='setusertemplates' />
        <input type='hidden' name='user' value='<?php echo $usr['user'];?>' />
        <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
        </form>
    <?php }
    if (($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || ($this->session->userdata('USER_RIGHT_DELETE_USER') == 1  && $usr['parent_id'] == $this->session->userdata('loginID')))&& $usr['uid']!=1) { ?>
        <form method='post' action='<?php echo site_url("admin/user/deluser");?>'>
        <input type='image' src='<?php echo $imageurl;?>/token_delete.png' alt='<?php echo $clang->gT("Delete this user");?>' onclick='return confirm("<?php echo $clang->gT("Are you sure you want to delete this entry?","js");?>")' />
        <input type='hidden' name='action' value='deluser' />
        <input type='hidden' name='user' value='<?php echo $usr['user'];?>' />
        <input type='hidden' name='uid' value='<?php echo $usr['uid'];?>' />
        </form>
    <?php } ?>

	</td>
    <td align='center'><?php echo $usr['user'];?></td>
    <td align='center'><a href='mailto:<?php echo $usr['email'];?>'><?php echo $usr['email'];?></a></td>
    <td align='center'><?php echo $usr['full_name'];?></td>

    <td align='center'><?php echo $noofsurveyslist[$i];?></td>

    <?php $uquery = "SELECT users_name FROM ".$this->db->dbprefix('users')." WHERE uid=".$usr['parent_id'];
    $uresult = db_execute_assoc($uquery); //Checked
    $userlist = array();
    $srow = $uresult->row_array();
    
    $usr['parent'] = $srow['users_name']; ?>
    
    <?php if (isset($usr['parent_id'])) { ?>
        <td align='center'><?php echo $usr['parent'];?></td>
    <?php } else { ?>
        <td align='center'>-----</td>
    <?php } ?>

    </tr>
    <?php $row++; 
} ?>
</tbody></table><br />

<?php if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_CREATE_USER')) { ?>
    <form action='<?php echo site_url("admin/user/adduser");?>' method='post'>
    <table class='users'><tr class='oddrow'>
    <th><?php echo $clang->gT("Add user:");?></th>
    <td align='center' width='20%'><input type='text' name='new_user' /></td>
    <td align='center' width='20%'><input type='text' name='new_email' /></td>
    <td align='center' width='20%' ><input type='text' name='new_full_name' /></td><td width='8%'>&nbsp;</td>
    <td align='center' width='15%'><input type='submit' value='<?php echo $clang->gT("Add User");?>' />
    <input type='hidden' name='action' value='adduser' /></td>
    </tr></table></form><br />
<?php } ?>