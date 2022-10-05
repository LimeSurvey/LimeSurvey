<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var string $editUrl */
/* @var string $deleteUrl */
/* @var array $aQuotaItems */


?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','update')) { ?>
    <a href="<?php echo $editUrl; ?>" class="btn btn-outline-secondary" data-bs-toggle='tooltip' title='<?php eT("Edit"); ?>'>
        <span class='fa fa-pencil'></span>
    </a>
<?php } ?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','delete')) { ?>
    <span data-bs-toggle="tooltip" title="<?php eT("Delete");?>">
        <a
                data-post-url="<?php echo $deleteUrl; ?>"
                class="btn btn-outline-secondary"
                data-bs-toggle="modal"
                data-bs-target="#confirmation-modal"
                data-btnclass="btn-danger"
                data-btntext="<?= gt('Delete')?>"
                data-message="<?php eT("Are you sure you want to delete the selected quotas?","js"); ?>"
        >
            <span class='fa fa-trash text-danger'></span>
        </a>
    </span>
<?php } ?>
<?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','update')) { ?>
    <span data-bs-toggle="tooltip" title="<?=sprintf(gT("Validation of quota %s"),htmlentities($oQuota->name))?>">
        <a
          href='#'
          data-remote-link="<?=App()->createUrl('admin/validate/', ["sa" => 'quota', 'sid'=>$oSurvey->getPrimaryKey(), 'quota'=>$oQuota->getPrimaryKey()])?>"
          class="btn btn-outline-secondary selector__quota_open_validation"
          data-bs-toggle="modal"
          data-bs-target="quotaValidation"
        >
            <i class='fa fa-tasks'></i>
        </a>
    </span>
<?php } ?>

<?php 
    Yii::app()->getClientScript()->registerScript('quotas_load_validationmodal', "
    $('.selector__quota_open_validation').remoteModal({
        saveButton: false,
    }, {
        closeIcon : '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\"></button>',
        closeButton : '<button type=\"button\" class=\"btn btn-cancel\" data-bs-dismiss=\"modal\">".gT("Close")."</button>',
        saveButton : '<button type=\"button\" class=\"btn btn-primary\">".gT("Close")."</button>'
    })
", LSYii_ClientScript::POS_POSTSCRIPT);

?>
