<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var CActiveDataProvider $oDataProvider Containing Quota item objects*/
/* @var array $aQuotaItems */

$tooltip = null;
$icon = null;
if ($oQuota->action == Quota::ACTION_TERMINATE){
    $tooltip = gT("Terminate survey");
    $icon = 'fa-exclamation-circle';
} elseif ($oQuota->action == Quota::ACTION_CONFIRM_TERMINATE){
    $tooltip = gT("Allow user to modify his last answers before terminate survey.");
    $icon = 'fa-exclamation-triangle';
}
?>

<?php /*echo "<pre>".print_r($oQuota->mainLanguagesetting->attributes,true)."</pre>";*/ ?>
<div class="panel panel-<?php echo ($oQuota->active==1 ? 'primary' : 'default') ?>">
    <div class="panel-heading">
        <div class="pull-right small">
            <span><span class="fa <?php echo $icon?>"></span> <?php echo $tooltip;?></span>
            <?php if($oQuota->autoload_url == 1):?>
                <span><span class="fa fa-external-link"></span> <?php echo gT('Autoload URL:').' '.htmlentities($oQuota->mainLanguagesetting->quotals_url);?></span>
            <?php endif;?>
        </div>
        <?php echo htmlentities(viewHelper::flatEllipsizeText($oQuota->name)) ;?>
    </div>
    <table class="table table-quota-items table-striped table-condensed" >
        <thead>
            <tr>
                <th><?php eT('Question');?></th>
                <th><?php eT('Answer');?></th>
                <th class="text-right">
                    <?php echo CHtml::beginForm(array("admin/quotas/sa/new_answer/surveyid/{$oSurvey->getPrimaryKey()}"), 'post');?>
                    <?php echo CHtml::hiddenField('sid',$oSurvey->getPrimaryKey(), ['id'=> 'addForm_sid' ]);?>
                    <?php echo CHtml::hiddenField('action','quotas', ['id'=> 'addForm_action' ]);?>
                    <?php echo CHtml::hiddenField('quota_id',$oQuota->getPrimaryKey(), ['id'=> 'addForm_quota_id' ]);?>
                    <?php echo CHtml::hiddenField('subaction','new_answer', ['id'=> 'addForm_subaction' ]);?>
                    <?php echo CHtml::submitButton(gT("Add answer"),array(
                        'name'=>'submit',
                        'class'=>'quota_new btn btn-default',
                    ));?>
                    <?php echo CHtml::endForm();?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($aQuotaItems) && isset($aQuotaItems[$oQuota->id]) && !empty($aQuotaItems[$oQuota->id])){ ?>
                <?php foreach($aQuotaItems[$oQuota->id] as $aQuotaItem){
                    $this->renderPartial('/admin/quotas/viewquotas_quota_members_item',array(
                        'oQuotaMember'=>$aQuotaItem['oQuotaMember'],
                        'data'=>$aQuotaItem));
                }?>
            <?php } else {?>
                <tr><td class="text-danger" colspan="3"><?php eT("No answers have been set for this quota.");?></td></tr>
            <?php }?>
        </tbody>
    </table>
</div>
