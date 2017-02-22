<?php
/* @var $this AdminController */
/* @var $widget CListView */
/* @var $data array */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */

/* @var Question $oQuestion */
$oQuestion = $data['oQuestion'];
?>
<div class="row" style="display: table-row;">
    <span data-container="body" data-toggle="tooltip" title="<?php echo $oQuestion->question;?>" style="display: table-cell">
        <?php echo $oQuestion->title?>
    </span>
        <span style="display: table-cell">
        <?php echo $data['answer_title']?>
    </span>
    <span style="display: table-cell;text-align: right;">
        <?php $this->renderPartial('/admin/quotas/viewquotas_quota_members_actions',
            array(
                'oSurvey'=>$oSurvey,
                'oQuota'=>$oQuota,
                'oQuotaMember' =>$data['oQuotaMember'],
            ));
        ?>
    </span>


</div>


