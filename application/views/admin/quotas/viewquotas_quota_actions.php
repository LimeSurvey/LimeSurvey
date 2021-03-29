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
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','update')) { ?>
    <a 
      href='#' 
      data-remote-link="<?=App()->createUrl('admin/validate/', ["sa" => 'quota', 'sid'=>$oSurvey->getPrimaryKey(), 'quota'=>$oQuota->getPrimaryKey()])?>"
      class="btn btn-default selector__quota_open_validation"
      data-tooltip="true"
      title="<?=sprintf(gT("Validation of quota %s"),htmlentities($oQuota->name))?>"
      data-toggel="modal"
      data-target="quotaValidation"
    >
        <i class='fa fa-tasks'></i>
    </a>
<?php } ?>

<?php 
    Yii::app()->getClientScript()->registerScript('quotas_load_validationmodal', "
    $('.selector__quota_open_validation').remoteModal({
        saveButton: false,
    }, {
        closeIcon : '<button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span aria-hidden=\"true\">&times;</span></button>',
        closeButton : '<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">".gT("Close")."</button>',
        saveButton : '<button type=\"button\" class=\"btn btn-primary\">".gT("Close")."</button>'
    })
", LSYii_ClientScript::POS_POSTSCRIPT);

?>