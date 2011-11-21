<p><input name="submit" type="submit" value="<?php echo $clang->gT("Save quota");?>" />
<input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
<input type="hidden" name="action" value="quotas" />
<input type="hidden" name="subaction" value="modifyquota" />
<input type="hidden" name="quota_id" value="<?php echo $quotainfo['id'];?>" />
<button type="button" onclick="window.open('<?php echo $this->createUrl("admin/quotas/surveyid/$surveyid");?>', '_top')"><?php echo $clang->gT("Cancel");?></button>
</div></form>