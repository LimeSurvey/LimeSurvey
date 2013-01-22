<div class='header ui-widget-header'><?php $clang->eT("Editing user");?></div><br />
<?php echo CHtml::form(array("admin/user/sa/moduser"), 'post', array('name'=>'moduserform', 'id'=>'moduserform')); ?>

<table class='edituser'><thead><tr>
<th><?php $clang->eT("Username");?></th>
<th><?php $clang->eT("Email");?></th>
<th><?php $clang->eT("Full name");?></th>
<th><?php $clang->eT("Password");?></th>
</tr></thead>
<tbody><tr>
<?php
function rsdsl($mur) {
	foreach ($mur as $mds) {
		if(is_array($mds)) {
			return TRUE;
		}else{
			return FALSE;
		}
	}
}
if(rsdsl($mur)) {
foreach ($mur as $mrw) { ?>
    <td align='center'><strong><?php echo $mrw['users_name'];?></strong></td>
    <td align='center'> <input type='email' size='30' name='email' value="<?php echo $mrw['email'];?>" /></td>
    <td align='center'> <input type='text' size='30' name='full_name' value="<?php echo $mrw['full_name'];?>" />
    <input type='hidden' name='user' value="<?php echo $mrw['users_name'];?>" />
    <input type='hidden' name='uid' value="<?php echo $mrw['uid'];?>" /></td>
    <td align='center'> <input type='password' name='pass' value="%%unchanged%%" /></td>
<?php }}else{
	$mur = array_map('htmlspecialchars', $mur); ?>
	<td align='center'><strong><?php echo $mur['users_name'];?></strong></td>
    <td align='center'> <input type='email' size='30' name='email' value="<?php echo $mur['email'];?>" /></td>
    <td align='center'> <input type='text' size='30' name='full_name' value="<?php echo $mur['full_name'];?>" />
    <input type='hidden' name='user' value="<?php echo $mur['users_name'];?>" />
    <input type='hidden' name='uid' value="<?php echo $mur['uid'];?>" /></td>
    <td align='center'> <input type='password' name='pass' value="%%unchanged%%" /></td>
<?php } ?>
</tr>
</tbody>
</table>
<p>
<input type='submit' value='<?php $clang->eT("Save");?>' />
<input type='hidden' name='action' value='moduser' />
</p>
</form>