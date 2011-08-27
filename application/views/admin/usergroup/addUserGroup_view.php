<div class='header ui-widget-header'><?php echo $clang->gT("Add User Group"); ?></div>
    <form action='<?php echo site_url("admin/usergroups/add"); ?>' id='usergroupform' class='form30' method='post'>
    <ul>
        <li><label for='group_name'><?php echo $clang->gT("Name:"); ?></label>
        <input type='text' size='50' id='group_name' name='group_name' />
        <font color='red' face='verdana' size='1'> <?php echo $clang->gT("Required"); ?></font></li>
        <li><label for='group_description'><?php echo $clang->gT("Description:"); ?></label>
        <textarea cols='50' rows='4' id='group_description' name='group_description'></textarea></li>
        </ul><p><input type='submit' value='<?php echo $clang->gT("Add Group"); ?>' />
        <input type='hidden' name='action' value='usergroupindb' />
    </form>