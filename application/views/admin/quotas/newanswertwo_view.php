<?php if (count($question_answers) == $x) { ?>
<div class="header"><?php $clang->eT("Add answer");?>: <?php $clang->eT("Question Selection");?></div><br />
	<div class="messagebox">
		<?php $clang->eT("All answers are already selected in this quota.");?>
		<br/><br/><input type="submit" onclick="window.open('<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/$iSurveyId");?>', '_top')" value="<?php $clang->eT("Continue");?>"/>
	</div>
<?php } else { ?>
<div class="header ui-widget-header"><?php $clang->eT("Survey quota");?>: <?php $clang->eT("Add answer");?></div><br />
	<div class="messagebox ui-corner-all" style="width: 600px">
        <?php echo CHtml::form(array("admin/quotas/sa/insertquotaanswer/surveyid/{$iSurveyId}"), 'post', array('#'=>'quota_'.sanitize_int($_POST['quota_id']))); ?>
			<table class="addquotaanswer">
				<tbody>
					<thead>
					<tr>
					  <th class="header ui-widget-header" colspan="2"><?php echo sprintf($clang->gT("New answer for quota '%s'"), $quota_name);?></th>
					</tr>
					</thead>
					<tr class="evenrow">
						<td align="center">&nbsp;</td>
						<td align="center">&nbsp;</td>
					</tr>
					<tr class="evenrow">
						<td width="35%" align="center" valign="top"><strong><?php $clang->eT("Select Answer");?>:</strong></td>
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
						<td width="35%" align="center" valign="top"><strong><?php $clang->eT("Save this, then create another:");?></strong></td>
						<td><input type="checkbox" name="createanother"></td>
					</tr>
					<tr align="left" class="evenrow">
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr align="left" class="evenrow">
						<td>&nbsp;</td>
						<td>
							<input name="submit" type="submit" class="submit" value="<?php $clang->eT("Next");?>" />
							<input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
							<input type="hidden" name="action" value="quotas" />
							<input type="hidden" name="subaction" value="insertquotaanswer" />
							<input type="hidden" name="quota_qid" value="<?php echo sanitize_int($_POST['quota_qid']);?>" />
							<input type="hidden" name="quota_id" value="<?php echo sanitize_int($_POST['quota_id']);?>" />
						</td>
					</tr>
				</tbody>
			</table><br />
		</form>
	</div>
<?php } ?>
