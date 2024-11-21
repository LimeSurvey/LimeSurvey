<?php
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var CActiveDataProvider $oDataProvider Containing Quota item objects*/
/* @var array $aQuotaItems */

$tooltip = null;
$icon = null;
if ($oQuota->action == Quota::ACTION_TERMINATE){
    $tooltip = gT("Terminate survey");
    $icon = 'ri-error-warning-fill';
} elseif ($oQuota->action == Quota::ACTION_CONFIRM_TERMINATE){
    $tooltip = gT("Allow user to modify their last answers before terminating the survey.");
    $icon = 'ri-alert-fill';
}
?>

<?php /*echo "<pre>".print_r($oQuota->mainLanguagesetting->attributes,true)."</pre>";*/ ?>
<div class="card card-primary <?php echo ($oQuota->active==1 ? 'border-left-success' : 'inactive border-left-warning') ?>">
    <div class="card-header ">
        <div class="float-end small">
            <span><span class="<?= $icon ?>"></span> <?= $tooltip ?></span>
            <?php if($oQuota->autoload_url == 1):?>
                <span><span class=" ri-external-link-fill"></span> <?php echo gT('Autoload URL:').' '.htmlentities((string) $oQuota->mainLanguagesetting->quotals_url);?></span>
            <?php endif;?>
        </div>
        <?php echo CHtml::encode($oQuota->name) ;?>
    </div>
    <table class="table table-quota-items table-striped table-condensed" >
        <thead>
            <tr>
                <th><?php eT('Question');?></th>
                <th><?php eT('Answer');?></th>
                <th class="text-end">
                    <?php echo CHtml::beginForm(array("quotas/newAnswer/surveyid/{$oSurvey->getPrimaryKey()}"), 'post');?>
                    <?php echo CHtml::hiddenField('sid',$oSurvey->getPrimaryKey(), ['id'=> 'addForm_sid' ]);?>
                    <?php echo CHtml::hiddenField('action','quotas', ['id'=> 'addForm_action' ]);?>
                    <?php echo CHtml::hiddenField('quota_id',$oQuota->getPrimaryKey(), ['id'=> 'addForm_quota_id' ]);?>
                    <?php echo CHtml::hiddenField('subaction','newanswer', ['id'=> 'addForm_subaction' ]);?>
                    <?php echo CHtml::submitButton(gT("Add answer"),array(
                        'name'=>'submit',
                        'class'=>'quota_new btn btn-outline-secondary',
                    ));?>
                    <?php echo CHtml::endForm();?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($aQuotaItems) && isset($aQuotaItems[$oQuota->id]) && !empty($aQuotaItems[$oQuota->id])){ ?>
                <?php foreach($aQuotaItems[$oQuota->id] as $aQuotaItem){
                    $this->renderPartial('viewquotas_quota_members_item', array(
                        'oQuotaMember'=>$aQuotaItem['oQuotaMember'],
                        'data'=>$aQuotaItem,
                        'sBaseLang'=>$oSurvey->language)
                    );
                }?>
            <?php } else {?>
                <tr><td class="text-danger" colspan="3"><?php eT("No answers have been set for this quota.");?></td></tr>
            <?php }?>
        </tbody>
    </table>
</div>
