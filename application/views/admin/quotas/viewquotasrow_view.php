<tr>
	<td colspan="6" style="background-color: #567081; height: 2px">
	</td>
</tr>
<tr>
	<td align="center"><?php echo $quotalisting['name'];?></td>
	<td align="center">
		<?php if ($quotalisting['active'] == 1)
		{
		    echo '<font color="#48B150">'.$clang->gT("Active").'</font>';
		} else {
		    echo '<font color="#B73838">'.$clang->gT("Not Active").'</font>';
		}
		?>
	</td>
	<td align="center">
<?php if ($quotalisting['action'] == 1) {
    echo $clang->gT("Terminate survey");
} elseif ($quotalisting['action'] == 2) {
    echo $clang->gT("Terminate survey with warning");
} ?>
	</td>
	<td align="center"><?php echo $quotalisting['qlimit'];?></td>
	<td align="center" <?php echo $highlight;?>><?php echo $completed;?></td>
	<td align="center" style="padding: 3px;">
<?php if (bHasSurveyPermission($surveyid, 'quotas','update')) { ?>
    <form action="<?php echo $this->createUrl("admin/quotas/surveyid/$surveyid/subaction/quota_editquota");?>" method="post">
                        <input name="submit" type="submit" class="submit" value="<?php echo $clang->gT("Edit");?>" />
                        <input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
                        <input type="hidden" name="action" value="quotas" />
                        <input type="hidden" name="quota_id" value="<?php echo $quotalisting['id'];?>" />
                        <input type="hidden" name="subaction" value="quota_editquota" />
                    </form>
<?php } if (bHasSurveyPermission($surveyid, 'quotas','delete')) { ?>
    <form action="<?php echo $this->createUrl("admin/quotas/surveyid/$surveyid/subaction/quota_delquota");?>" method="post">
			            <input name="submit" type="submit" class="submit" value="<?php echo $clang->gT("Remove");?>" />
			            <input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
			            <input type="hidden" name="action" value="quotas" />
			            <input type="hidden" name="quota_id" value="<?php echo $quotalisting['id'];?>" />
			            <input type="hidden" name="subaction" value="quota_delquota" />
		            </form>
<?php } ?>
</td></tr>

<tr class="evenrow">
	<td align="center">&nbsp;</td>
	<td align="center"><strong><?php echo $clang->gT("Questions");?></strong></td>
	<td align="center"><strong><?php echo $clang->gT("Answers");?></strong></td>
	<td align="center">&nbsp;</td>
	<td align="center">&nbsp;</td>
	<td style="padding: 3px;" align="center">
<?php if (bHasSurveyPermission($surveyid, 'quotas','update')) { ?>
    <form action="<?php echo $this->createUrl("admin/quotas/surveyid/$surveyid/subaction/new_answer");?>" method="post">
                        <input name="submit" type="submit" class="quota_new" value="<?php echo $clang->gT("Add Answer");?>" />
                        <input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
                        <input type="hidden" name="action" value="quotas" />
                        <input type="hidden" name="quota_id" value="<?php echo $quotalisting['id'];?>" />
                        <input type="hidden" name="subaction" value="new_answer" />
                    </form>
<?php } ?>
</td>
</tr>