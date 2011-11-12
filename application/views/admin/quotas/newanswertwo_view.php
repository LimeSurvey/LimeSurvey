<?php if (count($question_answers) == $x) { ?>
<div class="header"><?php echo $clang->gT("Add Answer");?>: <?php echo $clang->gT("Question Selection");?></div><br />
	<div class="messagebox">
		<?php echo $clang->gT("All answers are already selected in this quota.");?>
		<br/><br/><input type="submit" onclick="window.open('<?php echo site_url("admin/quotas/$surveyid");?>', '_top')" value="<?php echo $clang->gT("Continue");?>"/>
	</div>
<?php } else { ?>
<div class="header ui-widget-header"><?php echo $clang->gT("Survey Quota");?>: <?php echo $clang->gT("Add Answer");?></div><br />
	<div class="messagebox ui-corner-all" style="width: 600px">
		<form action="<?php echo site_url("admin/quotas/$surveyid");?>#quota_<?php echo $_POST['quota_id'];?>" method="post">
			<table class="addquotaanswer" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F8FF">
				<tbody>
					<thead>
					<tr>
					  <th class="header ui-widget-header" colspan="2"><?php echo sprintf($clang->gt("New Answer for Quota '%s'"), $quota_name);?></th>
					</tr>
					</thead>
					<tr class="evenrow">
						<td align="center">&nbsp;</td>
						<td align="center">&nbsp;</td>
					</tr>
					<tr class="evenrow">
						<td width="35%" align="center" valign="top"><strong><?php echo $clang->gT("Select Answer");?>:</strong></td>
						<td align="left">
							<select name="quota_anscode" size="15">
<?php
    while (list($key,$value) = each($question_answers))
    {
        if (!isset($value['rowexists'])) echo '<option value="'.$key.'">'.strip_tags(substr($value['Display'],0,40)).'</option>';
    }
?>
							</select>
						</td>
					</tr>
					<tr align="left" class="evenrow">
						<td width="35%" align="center" valign="top"><strong><?php echo $clang->gT("Save this, then create another:");?></strong></td>
						<td><input type="checkbox" name="createanother"></td>
					</tr>
					<tr align="left" class="evenrow">
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr align="left" class="evenrow">
						<td>&nbsp;</td>
						<td>
							<input name="submit" type="submit" class="submit" value="<?php echo $clang->gT("Next");?>" />
							<input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
							<input type="hidden" name="action" value="quotas" />
							<input type="hidden" name="subaction" value="insertquotaanswer" />
							<input type="hidden" name="quota_qid" value="<?php echo $_POST['quota_qid'];?>" />
							<input type="hidden" name="quota_id" value="<?php echo $_POST['quota_id'];?>" />
						</td>
					</tr>
				</tbody>
			</table><br />
		</form>
	</div>
<?php } ?>