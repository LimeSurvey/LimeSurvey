<?php
/* @var $this AdminController */
/* @var $widget CListView */
/* @var $data array */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */

/* @var Question $oQuestion */
$oQuestion = $data['oQuestion'];
?>
<tr>
    <td data-toggle="tooltip"  data-container="body" title="<?php echo viewHelper::flatEllipsizeText($oQuestion->question,true,60,'...',0.6); ?>">
        <?php echo $oQuestion->title?>
    </td>
    <td>
        <?php echo viewHelper::flatEllipsizeText($data['answer_title'],true,80,'...',0.6); ?>
    </td>
    <td class="text-right">
        <?php $this->renderPartial('/admin/quotas/viewquotas_quota_members_actions',
            array(
                'oSurvey'=>$oSurvey,
                'oQuota'=>$oQuota,
                'oQuotaMember' =>$data['oQuotaMember'],
            ));
        ?>
    </td>
</div>
