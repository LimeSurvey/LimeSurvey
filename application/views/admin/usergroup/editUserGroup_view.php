<div class='header ui-widget-header'><?php echo sprintf($clang->gT("Editing user group (Owner: %s)"),$this->session->userdata('user')); ?></div>
    <form action='<?php echo site_url("admin/usergroups/edit/".$ugid); ?>' id='usergroupform' class='form30' name='usergroupform' method='post'>
        <ul>
        <li><label for='name'><?php echo $clang->gT("Name:"); ?></label>
        <input type='text' size='50' maxlength='20' id='name' name='name' value="<?php echo $esrow['name']; ?>" /></li>
        <li><label for='description'><?php echo $clang->gT("Description:"); ?></label>
        <textarea cols='50' rows='4' id='description' name='description'><?php echo $esrow['description']; ?></textarea></li>
        <ul><p><input type='submit' value='<?php echo $clang->gT("Update User Group"); ?>' />
        <input type='hidden' name='action' value='editusergroupindb' />
        <input type='hidden' name='owner_id' value='<?php echo $this->session->userdata('loginID'); ?>' />
        <input type='hidden' name='ugid' value='<?php echo $ugid; ?>' />
    </form>