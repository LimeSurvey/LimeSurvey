<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var CActiveDataProvider $oDataProvider Containing Quota item objects*/
/* @var array $aQuotaItems */

$tooltip = null;
$icon = null;
if ($oQuota->action == 1){
    $tooltip = gT("Terminate survey");
    $icon = 'fa-exclamation-circle';
} elseif ($oQuota->action == 2){
    $tooltip = gT("Terminate survey with warning");
    $icon = 'fa-exclamation-triangle';
}


?>
<?php if (!empty($aQuotaItems) ):?>


<div class="panel panel-<?php echo ($oQuota->active==1 ? 'primary' : 'default') ?>">
    <div class="panel-heading">
        <span class="h4" >
            <span class="fa <?php echo $icon?>" data-toggle="tooltip" title="<?php echo $tooltip;?>"></span>
            <?php if($oQuota->autoload_url == 1):?>
                &nbsp;<span class="fa fa-external-link" data-toggle="tooltip" data-html="true" title="<?php echo eT('Autoload URL:').' '.$oQuota->mainLanguagesetting->quotals_url;?>"></span>
            <?php endif;?>
            &nbsp;<?php echo $oQuota->name;?>
        </span>
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
        <div style="display: table; width: 100%;" >
            <div style="display: table-row;"  >
                <div style="display: table-cell" class="h5"><?php eT('Question');?></div>
                <div style="display: table-cell" class="h5"><?php eT('Answer');?></div>
                <div style="display: table-cell"></div>
            </div>
        <?php if (!empty($aQuotaItems) && isset($aQuotaItems[$oQuota->id]) && !empty($aQuotaItems[$oQuota->id])){
            foreach($aQuotaItems[$oQuota->id] as $aQuotaItem){
                $this->renderPartial('/admin/quotas/viewquotas_quota_members_item',array('oSurvey'=>$oSurvey,'oQuota'=>$oQuota,'data'=>$aQuotaItem));
            }
        }?>
        </div>
    </div>
</div>

<?php endif;