<?php
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var QuotaMember $oQuotaMember */
?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','update')) { ?>
    <?php echo CHtml::beginForm(array("quotas/deleteAnswer/surveyid/{$oSurvey->getPrimaryKey()}"), 'post',array('style'=>'display:inline-block')); ?>
    <input name="submit" type="submit" class="btn btn-outline-secondary" value="<?php eT("Remove");?>" />
    <input type="hidden" name="sid" value="<?php echo $oSurvey->getPrimaryKey();?>" />
    <input type="hidden" name="action" value="quotas" />
    <input type="hidden" name="quota_member_id" value="<?php echo $oQuotaMember->getPrimaryKey();?>" />
    <input type="hidden" name="quota_qid" value="<?php echo $oQuotaMember->qid;?>" />
    <input type="hidden" name="quota_anscode" value="<?php echo $oQuotaMember->code;?>" />
    <input type="hidden" name="subaction" value="quota_delans" />
<?php } ?>
</form>
