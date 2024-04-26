<?php
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->widget('ext.admin.survey.PageTitle.PageTitle', array(
        'title' => sprintf(gT("Edit quota “%s”"), '<em>' . CHtml::encode($oQuota->name) . '</em>'),
        'model' => $oSurvey,
    )); ?>
    <div class="row">
        <div class="col-12 content-right">
            <?php $this->renderPartial('/quotas/_form',
                array(
                    'oQuota'=>$oQuota,
                    'aQuotaLanguageSettings'=>$aQuotaLanguageSettings,
                ))?>
        </div>
    </div>
</div>
