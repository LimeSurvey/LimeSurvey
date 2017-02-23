<?php
/* @var $this AdminController */
/* @var $widget CListView */
/* @var $data array */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */

/* @var Question $oQuestion */
$oQuestion = $data['oQuestion'];
?>
<div style="display: table-row;">
    <div data-container="body" data-toggle="tooltip" title="<?php echo $oQuestion->question;?>" style="display: table-cell">
        <?php echo $oQuestion->title?>
    </div>
    <div style="display: table-cell">
        <?php echo $data['answer_title']?>
    </div>
    <div style="display: table-cell" align="right">
        <?php $this->renderPartial('/admin/quotas/viewquotas_quota_members_actions',
            array(
                'oSurvey'=>$oSurvey,
                'oQuota'=>$oQuota,
                'oQuotaMember' =>$data['oQuotaMember'],
            ));
        ?>
    </div>
</div>
