<p><input name="submit" type="submit" value="<?php $clang->eT("Save quota");?>" />
<input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
<input type="hidden" name="action" value="quotas" />
<input type="hidden" name="subaction" value="modifyquota" />
<input type="hidden" name="quota_id" value="<?php echo $quotainfo['id'];?>" />
<button type="button" onclick="window.open('<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/$surveyid");?>', '_top')"><?php $clang->eT("Cancel");?></button>
</div></form>