<div class='header ui-widget-header'><?php echo $clang->gT("Group members"); ?></div>
<?php
if(isset($headercfg))
{
	if ($headercfg["type"] == "success")
	{ ?>
		<div class="successheader"><?php echo $headercfg["message"];?></div>
		<?php
	}
	if ($headercfg["type"] == "warning")
	{ ?>
		<div class="warningheader"><?php echo $headercfg["message"];?></div>
		<?php
	}
}
?>


<?php
if(isset($groupfound))
{ ?>
<table width='100%' border='0'>
	<tr><td align='justify' colspan='2' height='4'>
 	<font size='2' ><strong><?php echo $clang->gT("Description: ");?></strong>
    <?php echo $usergroupdescription;?></font></td></tr>
</table>
<?php
}
?>

<table class='users'>
	<thead><tr>
    	<th><?php echo $clang->gT("Action");?></th>
        <th><?php echo $clang->gT("Username");?></th>
        <th><?php echo $clang->gT("Email");?></th>
        </tr></thead>
	<tbody>
	<?php
	foreach ($userloop as $currentuser)
    {
		?>
		<tr class='<?php echo $currentuser["rowclass"];?>'>
			<td align='center'>
			<?php
			if($currentuser["displayactions"])
            { ?>
				<form method='post' action='scriptname?action=deleteuserfromgroup&amp;ugid=$ugid'>
				<input type='image' src='<?php echo $this->yii->getConfig('imageurl')?>/token_delete.png' alt='"<?php echo $clang->gT("Delete this user from group");?>' onclick='return confirm("<?php echo $clang->gT("Are you sure you want to delete this entry?","js");?>")' />
				<input type='hidden' name='user' value='<?php echo $currentuser["username"];?>' />"
				<input name='uid' type='hidden' value='<?php echo $currentuser["userid"];?>' />"
				<input name='ugid' type='hidden' value='<?php echo $usergroupid;?>' />";
				</form>
                <?php
			}
			else
			{
				?>
				&nbsp;
			<?php
			}
			?>
            </td>
            <td align='center'><?php echo $currentuser["username"];?></td>
            <td align='center'><?php echo $currentuser["email"];?></td>
		</tr>
        <?php
	}
	?>
	</tbody>
</table>
<?php
if	($useradddialog)
{
	?>
	<form action='<?php echo $useraddurl?>' method='post'>
		<table class='users'><tbody><tr><td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align='center'>
		<select name='uid'>
			<?php echo $useraddusers;?>
		</select>
		<input type='submit' value='<?php echo $clang->gT("Add User");?>' />
		<input type='hidden' name='action' value='addusertogroup' /></td>
		</tr></tbody></table>
	</form>
	<?php
}
?>
