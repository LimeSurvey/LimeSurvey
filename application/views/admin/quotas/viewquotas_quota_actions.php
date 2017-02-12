<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var string $editUrl */
/* @var string $deleteUrl */
/* @var array $aQuotaItems */

?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','update')) { ?>
    <a href="<?php echo $editUrl; ?>" class="btn btn-default" data-toggle='tooltip' title='<?php eT("Edit"); ?>'>
        <span class='glyphicon glyphicon-pencil'></span>
    </a>
<?php } ?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','delete')) { ?>
    <a data-href="<?php echo $deleteUrl; ?>" class="btn btn-default" data-toggle="modal" data-target="#confirmation-modal" data-tooltip="true" title="<?php eT("Delete");?>" >
        <span class='glyphicon glyphicon-trash text-danger'></span>
    </a>
<?php } ?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','update')) {
    echo CHtml::link(CHtml::tag('span', array(
        'class' => 'glyphicon glyphicon-ok-sign',
    ), ''
    ), array(
        'admin/validate',
        'sa'=>'quota',
        'sid'=>$oSurvey->getPrimaryKey(),
        'quota'=>$oQuota->getPrimaryKey(),
    ), array(
        'class'=>'btn btn-default',
        'title'=>sprintf(gT("Validation of quota %s"),$oQuota->name),
        'target'=>'dialog',
        'data-tooltip' => 'true'
    ));
}
// the items will be placed into the LAST column to insert an additional row for items
?>
</tr>
<tr>
    <td></td>
    <td colspan="6" style="padding:6px;">
        <div class="panel panel-<?php echo ($oQuota->active==1 ? 'primary' : 'default') ?>">
            <div class="panel-heading">
                <?php eT("Quota members");?>
                <span class="pull-right">
                <?php echo CHtml::beginForm(array("admin/quotas/sa/new_answer/surveyid/{$oSurvey->getPrimaryKey()}"), 'post');?>
                <?php echo CHtml::hiddenField('sid',$oSurvey->getPrimaryKey());?>
                <?php echo CHtml::hiddenField('action','quotas');?>
                <?php echo CHtml::hiddenField('quota_id',$oQuota->getPrimaryKey());?>
                <?php echo CHtml::hiddenField('subaction','new_answer');?>
                <?php echo CHtml::submitButton(gT("Add answer"),array(
                    'name'=>'submit',
                    'class'=>'quota_new btn btn-default btn-xs',
                ));?>
                <?php echo CHtml::endForm();?>

                </span>
            </div>
            <div class="panel-body" style="margin: 3px;padding: 3px;">
                <?php
                $this->renderPartial('/admin/quotas/viewquotas_quota_members',
                    array(
                        'oSurvey'=>$oSurvey,
                        'oQuota'=>$oQuota,
                        'aQuotaItems'=>$aQuotaItems,
                    ));
                ?>

            </div>
        </div>
    </td>

<?php // and now let gridView to finish the row <tr>