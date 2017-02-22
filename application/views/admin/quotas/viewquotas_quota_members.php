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
                &nbsp;<span class="fa fa-external-link" data-toggle="tooltip" title="url here"></span>
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
    <?php
    if (!empty($aQuotaItems) && isset($aQuotaItems[$oQuota->id]) ){

        $oDataProvider=new CArrayDataProvider($aQuotaItems[$oQuota->id]);
        $this->widget('zii.widgets.CListView', array(
            'dataProvider'=>$oDataProvider,
            'itemView'=>'/admin/quotas/viewquotas_quota_members_item',
            'viewData'=>array('oSurvey'=>$oSurvey,'oQuota'=>$oQuota),
            'sortableAttributes'=>array('question_title'),
            'itemsCssClass' =>'fake-table',
            'template' =>'{items}',
        ));

    }    ?>
    </div>
</div>

<?php endif;