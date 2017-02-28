<?php
/* @var $this AdminController */
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <h3>
                <?php eT("Edit quota");?>
            </h3>
            <?php $this->renderPartial('/admin/quotas/_form',
                array(
                    'oQuota'=>$oQuota,
                    'aQuotaLanguageSettings'=>$aQuotaLanguageSettings,
                ))?>
        </div>
    </div>
</div>
