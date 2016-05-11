<h3 class="pagetitle"><?php eT("Editing user");?></h3>
        
<div class="row" style="margin-bottom: 100px">
    <div class="col-lg-12 content-right">
<?php echo CHtml::form(array("admin/user/sa/moduser"), 'post', array('name'=>'moduserform', 'id'=>'moduserform')); ?>

<table class='edituser table'><thead><tr>
<th><?php eT("Username");?></th>
<th><?php eT("Email");?></th>
<th><?php eT("Full name");?></th>
<th><?php eT("Password");?></th>
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
    <td ><strong><?php echo $mrw['users_name'];?></strong></td>
    <td > <input type='email' size='30' name='email' value="<?php echo $mrw['email'];?>" /></td>
    <td > <input type='text' size='30' name='full_name' value="<?php echo $mrw['full_name'];?>" />
    <input type='hidden' name='user' value="<?php echo $mrw['users_name'];?>" />
    <input type='hidden' name='uid' value="<?php echo $mrw['uid'];?>" /></td>
    <td > <input type='password' name='pass' value="%%unchanged%%" /></td>
<?php }}else{
    $mur = array_map('htmlspecialchars', $mur); ?>
    <td ><strong><?php echo $mur['users_name'];?></strong></td>
    <td > <input type='email' size='30' name='email' value="<?php echo $mur['email'];?>" /></td>
    <td > <input type='text' size='30' name='full_name' value="<?php echo $mur['full_name'];?>" />
    <input type='hidden' name='user' value="<?php echo $mur['users_name'];?>" />
    <input type='hidden' name='uid' value="<?php echo $mur['uid'];?>" /></td>
    <td > <input type='password' name='pass' value="%%unchanged%%" /></td>
<?php } ?>
</tr>
</tbody>
</table>
<p>
<input type='submit' class="hidden" value='<?php eT("Save");?>' />
<input type='hidden' name='action' value='moduser' />
</p>
</form>

    </div>
</div>
