<div class='header ui-widget-header'><?php echo $clang->gT("Editing user");?></div><br />
<form action='<?php echo site_url("admin/user/moduser");?>' method='post'>
<table class='edituser'><thead><tr>
<th><?php echo $clang->gT("Username");?></th>
<th><?php echo $clang->gT("Email");?></th>
<th><?php echo $clang->gT("Full name");?></th>
<th><?php echo $clang->gT("Password");?></th>
</tr></thead>
<tbody><tr>
<?php foreach ($mur->result_array() as $mrw) {
    $mrw = array_map('htmlspecialchars', $mrw); ?>
    <td align='center'><strong><?php echo $mrw['users_name'];?></strong></td>
    <td align='center'> <input type='text' size='30' name='email' value="<?php echo $mrw['email'];?>" /></td>
    <td align='center'> <input type='text' size='30' name='full_name' value="<?php echo $mrw['full_name'];?>" />
    <input type='hidden' name='user' value="<?php echo $mrw['users_name'];?>" />
    <input type='hidden' name='uid' value="<?php echo $mrw['uid'];?>" /></td>
    <td align='center'> <input type='password' name='pass' value="%%unchanged%%" /></td>
<?php } ?>
</tr>
</tbody>
</table>
<p>
<input type='submit' value='<?php echo $clang->gT("Save");?>' />
<input type='hidden' name='action' value='moduser' />
</p>
</form>