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
        <span class='fa fa-pencil'></span>
    </a>
<?php } ?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','delete')) { ?>
    <a data-href="<?php echo $deleteUrl; ?>" class="btn btn-default" data-toggle="modal" data-target="#confirmation-modal" data-tooltip="true" title="<?php eT("Delete");?>" >
        <span class='fa fa-trash text-danger'></span>
    </a>
<?php } ?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','update')) {
    echo CHtml::link(CHtml::tag('span', array(
        'class' => 'fa fa-tasks',
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
