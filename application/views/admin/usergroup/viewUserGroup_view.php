<div class='header ui-widget-header'><?php $clang->eT("Group members"); ?></div>
<?php
if(isset($headercfg))
{
	if ($headercfg["type"] == "success")
	{ ?>
		<div class="header successheader"><?php echo $headercfg["message"];?></div>
		<?php
	}
	if ($headercfg["type"] == "warning")
	{ ?>
		<div class="header warningheader"><?php echo $headercfg["message"];?></div>
		<?php
	}
}
?>

<?php
if(isset($groupfound))
{ ?>
<table width='100%' border='0'>
	<tr><td align='justify' colspan='2' height='4'>
 	<font size='2' ><strong><?php $clang->eT("Description: ");?></strong>
    <?php echo htmlspecialchars($usergroupdescription);?></font></td></tr>
</table>
<?php
}
?>

<?php if (!empty($userloop)) { ?>
    <table class='users'>
        <thead><tr>
            <th><?php $clang->eT("Action");?></th>
            <th><?php $clang->eT("Username");?></th>
            <th><?php $clang->eT("Email");?></th>
            </tr></thead>
        <tbody>
        <?php
        foreach ($userloop as $currentuser)
        {
            ?>
            <tr class='<?php echo $currentuser["rowclass"];?>'>
                <td align='center'>
                <?php
                if(isset($currentuser["displayactions"]) && $currentuser["displayactions"] == true)
                { ?>
                    <?php echo CHtml::form(array("admin/usergroups/sa/user/ugid/{$ugid}/action/remove"), 'post'); ?>
                    <input type='image' src='<?php echo Yii::app()->getConfig('adminimageurl')?>/token_delete.png' alt='<?php $clang->eT("Delete this user from group");?>' onclick='return confirm("<?php $clang->eT("Are you sure you want to delete this entry?","js");?>")' />
                    <input name='uid' type='hidden' value='<?php echo $currentuser["userid"]; ?>' />
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
<?php } ?>

<?php
if (!empty($useradddialog))
{
	?>
        <?php echo CHtml::form(array("admin/usergroups/sa/user/ugid/{$ugid}/action/add"), 'post'); ?>
            <table class='users'>
                <tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align='center'>
                            <select name='uid'>
                                <?php echo $useraddusers; ?>
                            </select>
                            <input type='submit' value='<?php $clang->eT("Add user"); ?>' />
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
	<?php
}
?>
