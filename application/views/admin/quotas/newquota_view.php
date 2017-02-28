<?php
/* @var $this AdminController */
/* @var string $lang */
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class='col-lg-8'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oQuota->survey, 'active'=> gT("New quota"))); ?>
            <h3>
                <?php eT("New quota");?>
            </h3>
            <?php $this->renderPartial('/admin/quotas/_form',
                array(
                    'oQuota'=>$oQuota,
                    'aQuotaLanguageSettings'=>$aQuotaLanguageSettings,
                ))?>
        </div>
    </div>
</div>
