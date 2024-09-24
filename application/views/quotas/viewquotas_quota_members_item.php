<?php
/* @var $widget CListView */
/* @var $data array */
/* @var QuotaMember $oQuotaMember */
if($oQuotaMember->question):
?>
<tr class="<?php echo !$data['valid'] ? "bg-warning text-danger":""; ?>"><!-- because lime-admin-colors.css force tr:nth-child(2n+1) td color, must add text-danger -->
    <td data-bs-toggle="tooltip"  data-container="body"
        title="<?php echo CHtml::encode(viewHelper::flatEllipsizeText(
            $oQuotaMember->question->questionl10ns[$sBaseLang]->question,
            true,
            60,
            '...',
            0.6)); ?>">
        <?php echo $oQuotaMember->question->title?>
    </td>
    <td>
        <?php echo viewHelper::flatEllipsizeText($data['answer_title'],true,80,'...',0.6); ?>
    </td>
    <td class="text-end">
        <?php $this->renderPartial(
            'viewquotas_quota_members_actions',
            array(
                'oSurvey'=>$oQuotaMember->survey,
                'oQuota'=>$oQuotaMember->quota,
                'oQuotaMember' =>$data['oQuotaMember'],
            )
        );
        ?>
    </td>
</tr>
<?php endif;?>
