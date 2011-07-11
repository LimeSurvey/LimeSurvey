<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
    <strong><?php echo $clang->gT("User Group"); ?></strong>
    <?php if($ugid && $grpresultcount > 0)
    {
        echo "{$grow['name']}";
    } ?>


    </div>
    <div class='menubar-main'>
    <div class='menubar-left'>
    <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='55' height='20' />
    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />

    <?php if($ugid && $grpresultcount > 0)
    { ?>
        <a href="#" onclick="window.location='<?php echo site_url("admin/usergroups/mail/".$ugid); ?>'"
         title='<?php echo $clang->gTview("Mail to all Members"); ?>'> 
        <img src='<?php echo $this->config->item('imageurl'); ?>/invite.png' alt='<?php echo $clang->gT("Mail to all Members"); ?>' name='MailUserGroup' /></a>
    <?php }
    else
    { ?>
        <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' height='20' />
    <?php } ?>
    <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='78' height='20' />
    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />

    <?php if($ugid && $grpresultcount > 0 &&
    $this->session->userdata('loginID') == $grow['owner_id'])
    { ?>
        <a href="#" onclick="window.location='<?php echo site_url("admin/usergroups/edit/".$ugid); ?>'"
         title='<?php echo $clang->gTview("Edit Current User Group"); ?>'>
        <img src='<?php echo $this->config->item('imageurl'); ?>/edit.png' alt='<?php echo $clang->gT("Edit Current User Group"); ?>' name='EditUserGroup' /></a>
    <?php }
    else
    { ?>
        <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' height='20' />
    <?php } 

    if($ugid && $grpresultcount > 0 &&
    $this->session->userdata('loginID') == $grow['owner_id'])
    { ?>
        
        <a href='#' onclick="if (confirm('<?php echo $clang->gT("Are you sure you want to delete this entry?","js"); ?>')) {<?php echo get2post(site_url('admin/usergroups/delete')."?action=delusergroup&amp;ugid=$ugid"); ?>}"
         title='<?php echo $clang->gTview("Delete Current User Group"); ?>'>
        <img src='<?php echo $this->config->item('imageurl'); ?>/delete.png' alt='<?php echo $clang->gT("Delete Current User Group"); ?>' name='DeleteUserGroup'  /></a>
    <?php }
    else
    { ?>
        <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' height='20' />
    <?php } ?>
    <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='92' height='20' />
    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
    </div>
    <div class='menubar-right'>
    <font class="boxcaption"><?php echo $clang->gT("User Groups"); ?>:</font>&nbsp;<select name='ugid' 
     onchange="window.location=this.options[this.selectedIndex].value">
    <?php echo getusergrouplist($ugid,'optionlist'); ?>
    </select>
    <?php if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
    { ?>
        <a href='<?php echo site_url("admin/usergroups/add"); ?>'
         title='<?php echo $clang->gTview("Add New User Group"); ?>'>
        <img src='<?php echo $this->config->item('imageurl'); ?>/add.png' alt='<?php echo $clang->gT("Add New User Group"); ?>' 
        name='AddNewUserGroup' onclick="window.location=''" /></a>
    <?php } ?>
    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
    <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='82' height='20' />
    </div></div>
    </div>
    <p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>