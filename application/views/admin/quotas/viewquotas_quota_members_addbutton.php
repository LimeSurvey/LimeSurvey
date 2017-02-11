<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var QuotaMember $oQuotaMember */
?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','update')):?>
    <?php echo CHtml::form(array("admin/quotas/sa/new_answer/surveyid/{$oSurvey->getPrimaryKey()}"), 'post'); ?>
    <input name="submit" type="submit" class="quota_new btn btn-default"  value="<?php eT("Add answer");?>" />
    <input type="hidden" name="sid" value="<?php echo $oSurvey->getPrimaryKey();?>" />
    <input type="hidden" name="action" value="quotas" />
    <input type="hidden" name="quota_id" value="<?php echo $oQuota->getPrimaryKey();?>" />
    <input type="hidden" name="subaction" value="new_answer" />
    </form>
<?php endif; ?>
